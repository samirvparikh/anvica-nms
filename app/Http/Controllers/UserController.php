<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with(['services', 'creator', 'assignedRole'])
            ->orderByDesc('id')
            ->get();

        $services = Service::where('status', Service::STATUS_ACTIVE)->orderBy('name')->get();
        $assignableRoles = User::assignableRolesForCreator(auth()->user());
        $staffRoleIds = Role::query()->where('is_staff', true)->pluck('id');

        return view('users.index', compact('users', 'services', 'assignableRoles', 'staffRoleIds'));
    }

    public function create()
    {
        $slaPolicies = \App\Models\SlaPolicy::orderBy('name')->get();
        $assignableRoles = User::assignableRolesForCreator(auth()->user());
        $staffRoleIds = Role::query()->where('is_staff', true)->pluck('id');

        return view('users.create', compact('slaPolicies', 'assignableRoles', 'staffRoleIds'));
    }

    public function edit(User $user)
    {
        $slaPolicies = \App\Models\SlaPolicy::orderBy('name')->get();
        $assignableRoles = User::assignableRolesForEditor(auth()->user(), $user);
        $staffRoleIds = Role::query()->where('is_staff', true)->pluck('id');

        return view('users.edit', compact('user', 'slaPolicies', 'assignableRoles', 'staffRoleIds'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateUser($request);
        $role = Role::findOrFail($validated['role_id']);
        $isAdminRole = $role->grantsAdminAccess();
        $isStaffRole = $role->is_staff;

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'mobile' => $validated['mobile'] ?? null,
            'password' => Hash::make($validated['password']),
            'role_id' => $role->id,
            'is_admin' => $isAdminRole,
            'status' => $validated['status'],
            'device_limit' => $isAdminRole ? null : ($validated['device_limit'] ?? 10),
            'start_date' => $isAdminRole ? null : ($validated['start_date'] ?? null),
            'expire_date' => $isAdminRole ? null : ($validated['expire_date'] ?? null),
            'created_by' => $request->user()->id,
        ];

        if ($isStaffRole && ($request->isMethod('post') || $request->has('username'))) {
            $data = array_merge($data, $this->staffProfileData($validated, $request, $role));
        }

        $user = User::create($data);

        if ($isStaffRole) {
            $this->handleStaffUploads($request, $user);
            $user->save();
        }

        if ($isStaffRole) {
            $user->services()->sync($this->allActiveServiceIds());
        }

        $user->load('assignedRole');

        return redirect()->route('users.index')->with('success', $user->roleLabel() . ' created successfully.');
    }

    public function update(Request $request, User $user)
    {
        $selectedRole = Role::find($request->input('role_id'));
        $isStaffRole = (bool) $selectedRole?->is_staff;

        if ($user->id === $request->user()->id && $selectedRole && ! $selectedRole->grantsAdminAccess() && $request->user()->isAdmin()) {
            $redirect = $isStaffRole ? redirect()->route('users.edit', $user) : $this->redirectToUsersIndexWithEdit($user);

            return $redirect->withErrors(['role_id' => 'You cannot change your own account to a staff role.']);
        }

        if ($user->id === $request->user()->id && $request->input('status') === User::STATUS_INACTIVE) {
            $redirect = $isStaffRole ? redirect()->route('users.edit', $user) : $this->redirectToUsersIndexWithEdit($user);

            return $redirect->withErrors(['status' => 'You cannot deactivate your own account.']);
        }

        $validator = Validator::make($request->all(), $this->userRules($request, $user));

        if ($validator->fails()) {
            if ($isStaffRole) {
                return redirect()->route('users.edit', $user)
                    ->withErrors($validator)
                    ->withInput();
            }

            return $this->redirectToUsersIndexWithEdit($user)
                ->withErrors($validator)
                ->withInput();
        }

        $validated = $validator->validated();
        $role = Role::findOrFail($validated['role_id']);
        $isAdminRole = $role->grantsAdminAccess();
        $isStaffRole = $role->is_staff;

        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'mobile' => $validated['mobile'] ?? null,
            'role_id' => $role->id,
            'is_admin' => $isAdminRole,
            'status' => $validated['status'],
            'device_limit' => $isAdminRole ? null : ($validated['device_limit'] ?? 10),
            'start_date' => $isAdminRole ? null : ($validated['start_date'] ?? null),
            'expire_date' => $isAdminRole ? null : ($validated['expire_date'] ?? null),
        ]);

        if ($isStaffRole && ($request->isMethod('put') || $request->isMethod('patch') || $request->has('username'))) {
            $user->fill($this->staffProfileData($validated, $request, $role));
            $this->handleStaffUploads($request, $user);
        }

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        if ($isAdminRole) {
            $user->services()->sync([]);
        }

        $user->load('assignedRole');

        return redirect()->route('users.index')->with('success', $user->roleLabel() . ' updated successfully.');
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->id === $request->user()->id) {
            abort(403, 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'Account deleted successfully.');
    }

    private function validateUser(Request $request, ?User $user = null): array
    {
        return $request->validate($this->userRules($request, $user));
    }

    private function userRules(Request $request, ?User $user = null): array
    {
        $allowedRoleIds = Role::allowedIdsForCreator($request->user(), $user);
        $selectedRole = Role::find($request->input('role_id'));
        $isStaffRole = (bool) $selectedRole?->is_staff;
        $isStore = $request->isMethod('post');

        $rules = [
            'name' => 'required|string|max:191',
            'email' => [
                'required',
                'email',
                'max:191',
                Rule::unique('users', 'email')->ignore($user?->id),
            ],
            'mobile' => 'nullable|string|max:20',
            'role_id' => ['required', Rule::in($allowedRoleIds)],
            'status' => 'required|in:Active,Inactive',
            'password' => $user
                ? 'nullable|string|min:6|confirmed'
                : 'required|string|min:6|confirmed',
        ];

        if ($isStaffRole) {
            $minDeviceLimit = $user ? max(1, $user->deviceCount()) : 1;
            $rules['device_limit'] = 'required|integer|min:' . $minDeviceLimit;
            $rules['start_date'] = 'required|date';
            $rules['expire_date'] = 'required|date|after_or_equal:start_date';

            if ($isStore || $request->has('username')) {
                $rules['username'] = [
                    'required',
                    'string',
                    'max:191',
                    Rule::unique('users', 'username')->ignore($user?->id),
                ];
                $rules['department'] = 'required|string|max:191';
                $rules['designation'] = 'required|string|max:191';
                $rules['reporting_manager'] = 'required|string|max:191';
                $rules['office_location'] = 'required|string|max:191';
                $rules['timezone'] = 'required|string|max:191';
                $rules['sla_policy_id'] = 'required|exists:sla_policies,id';
                $rules['employee_id'] = 'nullable|string|max:191';
                $rules['alternate_number'] = 'nullable|string|max:20';
                $rules['dob'] = 'nullable|date';
                $rules['gender'] = 'nullable|string|max:50';
                $rules['language'] = 'nullable|string|max:50';
                $rules['profile_photo'] = 'nullable|image|max:2048';
                $rules['signature'] = 'nullable|image|max:2048';
                $rules['work_location'] = 'nullable|string|max:191';
                $rules['address'] = 'nullable|string';
                $rules['landline'] = 'nullable|string|max:20';
                $rules['extension'] = 'nullable|string|max:20';
                $rules['password_expiry_days'] = 'nullable|integer|min:0';
                $rules['failed_login_attempts'] = 'nullable|integer|min:0';
                $rules['lockout_minutes'] = 'nullable|integer|min:0';
                $rules['two_factor'] = 'nullable|boolean';
                $rules['force_password_change'] = 'nullable|boolean';
                $rules['business_unit'] = 'nullable|string|max:191';
                $rules['max_tickets_per_day'] = 'nullable|integer|min:0';
                $rules['max_changes_per_week'] = 'nullable|integer|min:0';
                $rules['notification_methods'] = 'nullable|array';
                $rules['working_hours'] = 'nullable|string|max:191';
                $rules['escalation_group'] = 'nullable|string|max:191';
                $rules['preferred_dashboard'] = 'nullable|string|max:191';
                $rules['skills'] = 'nullable|array';
                $rules['certifications'] = 'nullable|string';
                $rules['notes'] = 'nullable|string';
                $rules['id_proof'] = 'nullable|file|max:5120';
                $rules['offer_letter'] = 'nullable|file|max:5120';
                $rules['other_document'] = 'nullable|file|max:5120';
            }
        }

        return $rules;
    }

    /** @param  array<string, mixed>  $validated */
    private function staffProfileData(array $validated, Request $request, ?Role $role = null): array
    {
        return [
            'username' => $validated['username'] ?? null,
            'access_level' => $role?->name,
            'employee_id' => $validated['employee_id'] ?? null,
            'alternate_number' => $validated['alternate_number'] ?? null,
            'dob' => $validated['dob'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'language' => $validated['language'] ?? null,
            'department' => $validated['department'] ?? null,
            'designation' => $validated['designation'] ?? null,
            'reporting_manager' => $validated['reporting_manager'] ?? null,
            'office_location' => $validated['office_location'] ?? null,
            'work_location' => $validated['work_location'] ?? null,
            'timezone' => $validated['timezone'] ?? null,
            'address' => $validated['address'] ?? null,
            'landline' => $validated['landline'] ?? null,
            'extension' => $validated['extension'] ?? null,
            'auth_type' => 'Local Authentication',
            'password_expiry_days' => $validated['password_expiry_days'] ?? null,
            'failed_login_attempts' => $validated['failed_login_attempts'] ?? 0,
            'lockout_minutes' => $validated['lockout_minutes'] ?? 30,
            'two_factor' => $request->boolean('two_factor'),
            'force_password_change' => $request->boolean('force_password_change'),
            'sla_policy_id' => $validated['sla_policy_id'] ?? null,
            'business_unit' => $validated['business_unit'] ?? null,
            'max_tickets_per_day' => $validated['max_tickets_per_day'] ?? null,
            'max_changes_per_week' => $validated['max_changes_per_week'] ?? null,
            'notification_methods' => $validated['notification_methods'] ?? [],
            'alert_emails' => ! empty($validated['email']) ? [$validated['email']] : [],
            'working_hours' => $validated['working_hours'] ?? null,
            'escalation_group' => $validated['escalation_group'] ?? null,
            'preferred_dashboard' => $validated['preferred_dashboard'] ?? null,
            'skills' => $validated['skills'] ?? [],
            'certifications' => $validated['certifications'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ];
    }

    /** @return list<int> */
    private function allActiveServiceIds(): array
    {
        return Service::where('status', Service::STATUS_ACTIVE)
            ->orderBy('name')
            ->pluck('id')
            ->all();
    }

    private function handleStaffUploads(Request $request, User $user): void
    {
        if ($request->hasFile('profile_photo')) {
            $file = $request->file('profile_photo');
            $filename = 'photo_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/profile_photos'), $filename);
            if ($user->profile_photo && file_exists(public_path($user->profile_photo))) {
                @unlink(public_path($user->profile_photo));
            }
            $user->profile_photo = 'uploads/profile_photos/' . $filename;
        }

        if ($request->hasFile('signature')) {
            $file = $request->file('signature');
            $filename = 'signature_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/signatures'), $filename);
            if ($user->signature && file_exists(public_path($user->signature))) {
                @unlink(public_path($user->signature));
            }
            $user->signature = 'uploads/signatures/' . $filename;
        }

        if ($request->hasFile('id_proof')) {
            $file = $request->file('id_proof');
            $filename = 'id_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/attachments'), $filename);
            if ($user->id_proof && file_exists(public_path($user->id_proof))) {
                @unlink(public_path($user->id_proof));
            }
            $user->id_proof = 'uploads/attachments/' . $filename;
        }

        if ($request->hasFile('offer_letter')) {
            $file = $request->file('offer_letter');
            $filename = 'offer_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/attachments'), $filename);
            if ($user->offer_letter && file_exists(public_path($user->offer_letter))) {
                @unlink(public_path($user->offer_letter));
            }
            $user->offer_letter = 'uploads/attachments/' . $filename;
        }

        if ($request->hasFile('other_document')) {
            $file = $request->file('other_document');
            $filename = 'doc_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/attachments'), $filename);
            if ($user->other_document && file_exists(public_path($user->other_document))) {
                @unlink(public_path($user->other_document));
            }
            $user->other_document = 'uploads/attachments/' . $filename;
        }
    }

    private function redirectToUsersIndexWithEdit(User $user): RedirectResponse
    {
        return redirect()->route('users.index')
            ->withInput()
            ->with('edit_user_id', $user->id);
    }
}
