<?php

namespace App\Http\Controllers;

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
        $users = User::with(['services', 'creator'])
            ->orderByDesc('id')
            ->get();

        $services = Service::where('status', Service::STATUS_ACTIVE)->orderBy('name')->get();

        return view('users.index', compact('users', 'services'));
    }

    public function create()
    {
        $services = Service::where('status', Service::STATUS_ACTIVE)->orderBy('name')->get();
        $slaPolicies = \App\Models\SlaPolicy::orderBy('name')->get();

        return view('users.create', compact('services', 'slaPolicies'));
    }

    public function edit(User $user)
    {
        $services = Service::where('status', Service::STATUS_ACTIVE)->orderBy('name')->get();
        $slaPolicies = \App\Models\SlaPolicy::orderBy('name')->get();

        return view('users.edit', compact('user', 'services', 'slaPolicies'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateUser($request);

        $isAdmin = $validated['role'] === User::ROLE_ADMIN;

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'mobile' => $validated['mobile'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'is_admin' => $isAdmin,
            'status' => $validated['status'],
            'device_limit' => $isAdmin ? null : ($validated['device_limit'] ?? 10),
            'start_date' => $isAdmin ? null : ($validated['start_date'] ?? null),
            'expire_date' => $isAdmin ? null : ($validated['expire_date'] ?? null),
            'created_by' => $request->user()->id,
        ];

        if (!$isAdmin && ($request->isMethod('post') || $request->has('username'))) {
            $data['username'] = $validated['username'] ?? null;
            $data['access_level'] = $validated['access_level'] ?? null;
            $data['employee_id'] = $validated['employee_id'] ?? null;
            $data['alternate_number'] = $validated['alternate_number'] ?? null;
            $data['dob'] = $validated['dob'] ?? null;
            $data['gender'] = $validated['gender'] ?? null;
            $data['language'] = $validated['language'] ?? null;
            $data['department'] = $validated['department'] ?? null;
            $data['designation'] = $validated['designation'] ?? null;
            $data['reporting_manager'] = $validated['reporting_manager'] ?? null;
            $data['office_location'] = $validated['office_location'] ?? null;
            $data['work_location'] = $validated['work_location'] ?? null;
            $data['timezone'] = $validated['timezone'] ?? null;
            $data['address'] = $validated['address'] ?? null;
            $data['landline'] = $validated['landline'] ?? null;
            $data['extension'] = $validated['extension'] ?? null;
            $data['auth_type'] = $validated['auth_type'] ?? 'Local Authentication';
            $data['password_expiry_days'] = $validated['password_expiry_days'] ?? null;
            $data['failed_login_attempts'] = $validated['failed_login_attempts'] ?? 0;
            $data['lockout_minutes'] = $validated['lockout_minutes'] ?? 30;
            $data['two_factor'] = $request->boolean('two_factor');
            $data['force_password_change'] = $request->boolean('force_password_change');
            $data['assigned_roles'] = $validated['assigned_roles'] ?? [];
            $data['module_access'] = $validated['module_access'] ?? [];
            $data['sla_policy_id'] = $validated['sla_policy_id'] ?? null;
            $data['business_unit'] = $validated['business_unit'] ?? null;
            $data['service_categories'] = $validated['service_categories'] ?? [];
            $data['max_tickets_per_day'] = $validated['max_tickets_per_day'] ?? null;
            $data['max_changes_per_week'] = $validated['max_changes_per_week'] ?? null;
            $data['notification_methods'] = $validated['notification_methods'] ?? [];
            $data['alert_emails'] = $validated['alert_emails'] ?? [];
            $data['working_hours'] = $validated['working_hours'] ?? null;
            $data['escalation_group'] = $validated['escalation_group'] ?? null;
            $data['preferred_dashboard'] = $validated['preferred_dashboard'] ?? null;
            $data['skills'] = $validated['skills'] ?? [];
            $data['certifications'] = $validated['certifications'] ?? null;
            $data['notes'] = $validated['notes'] ?? null;

            // Handle file uploads
            if ($request->hasFile('profile_photo')) {
                $file = $request->file('profile_photo');
                $filename = 'photo_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/profile_photos'), $filename);
                $data['profile_photo'] = 'uploads/profile_photos/' . $filename;
            }

            if ($request->hasFile('signature')) {
                $file = $request->file('signature');
                $filename = 'signature_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/signatures'), $filename);
                $data['signature'] = 'uploads/signatures/' . $filename;
            }

            if ($request->hasFile('id_proof')) {
                $file = $request->file('id_proof');
                $filename = 'id_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/attachments'), $filename);
                $data['id_proof'] = 'uploads/attachments/' . $filename;
            }

            if ($request->hasFile('offer_letter')) {
                $file = $request->file('offer_letter');
                $filename = 'offer_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/attachments'), $filename);
                $data['offer_letter'] = 'uploads/attachments/' . $filename;
            }

            if ($request->hasFile('other_document')) {
                $file = $request->file('other_document');
                $filename = 'doc_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/attachments'), $filename);
                $data['other_document'] = 'uploads/attachments/' . $filename;
            }
        }

        $user = User::create($data);

        if (!$isAdmin && !empty($validated['services'])) {
            $user->services()->sync($validated['services']);
        }

        $label = $isAdmin ? 'Admin' : 'User';

        return redirect()->route('users.index')->with('success', $label . ' created successfully.');
    }

    public function update(Request $request, User $user)
    {
        $isUser = $request->input('role') === User::ROLE_USER;

        if ($user->id === $request->user()->id && $request->input('role') !== User::ROLE_ADMIN) {
            $redirect = $isUser ? redirect()->route('users.edit', $user) : $this->redirectToUsersIndexWithEdit($user);
            return $redirect->withErrors(['role' => 'You cannot change your own account to a regular user.']);
        }

        if ($user->id === $request->user()->id && $request->input('status') === User::STATUS_INACTIVE) {
            $redirect = $isUser ? redirect()->route('users.edit', $user) : $this->redirectToUsersIndexWithEdit($user);
            return $redirect->withErrors(['status' => 'You cannot deactivate your own account.']);
        }

        $validator = Validator::make($request->all(), $this->userRules($request, $user));

        if ($validator->fails()) {
            if ($isUser) {
                return redirect()->route('users.edit', $user)
                    ->withErrors($validator)
                    ->withInput();
            } else {
                return $this->redirectToUsersIndexWithEdit($user)
                    ->withErrors($validator)
                    ->withInput();
            }
        }

        $validated = $validator->validated();

        $isAdmin = $validated['role'] === User::ROLE_ADMIN;

        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'mobile' => $validated['mobile'] ?? null,
            'role' => $validated['role'],
            'is_admin' => $isAdmin,
            'status' => $validated['status'],
            'device_limit' => $isAdmin ? null : ($validated['device_limit'] ?? 10),
            'start_date' => $isAdmin ? null : ($validated['start_date'] ?? null),
            'expire_date' => $isAdmin ? null : ($validated['expire_date'] ?? null),
        ]);

        if (!$isAdmin && ($request->isMethod('put') || $request->isMethod('patch') || $request->has('username'))) {
            $user->fill([
                'username' => $validated['username'] ?? null,
                'access_level' => $validated['access_level'] ?? null,
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
                'auth_type' => $validated['auth_type'] ?? 'Local Authentication',
                'password_expiry_days' => $validated['password_expiry_days'] ?? null,
                'failed_login_attempts' => $validated['failed_login_attempts'] ?? 0,
                'lockout_minutes' => $validated['lockout_minutes'] ?? 30,
                'two_factor' => $request->boolean('two_factor'),
                'force_password_change' => $request->boolean('force_password_change'),
                'assigned_roles' => $validated['assigned_roles'] ?? [],
                'module_access' => $validated['module_access'] ?? [],
                'sla_policy_id' => $validated['sla_policy_id'] ?? null,
                'business_unit' => $validated['business_unit'] ?? null,
                'service_categories' => $validated['service_categories'] ?? [],
                'max_tickets_per_day' => $validated['max_tickets_per_day'] ?? null,
                'max_changes_per_week' => $validated['max_changes_per_week'] ?? null,
                'notification_methods' => $validated['notification_methods'] ?? [],
                'alert_emails' => $validated['alert_emails'] ?? [],
                'working_hours' => $validated['working_hours'] ?? null,
                'escalation_group' => $validated['escalation_group'] ?? null,
                'preferred_dashboard' => $validated['preferred_dashboard'] ?? null,
                'skills' => $validated['skills'] ?? [],
                'certifications' => $validated['certifications'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            // Handle file uploads
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

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        if ($isAdmin) {
            $user->services()->sync([]);
        } else {
            $user->services()->sync($validated['services'] ?? []);
        }

        $label = $isAdmin ? 'Admin' : 'User';

        return redirect()->route('users.index')->with('success', $label . ' updated successfully.');
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
        $isUserRole = $request->input('role', User::ROLE_USER) === User::ROLE_USER;
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
            'role' => 'required|in:admin,user',
            'status' => 'required|in:Active,Inactive',
            'password' => $user
                ? 'nullable|string|min:6|confirmed'
                : 'required|string|min:6|confirmed',
        ];

        if ($isUserRole) {
            $minDeviceLimit = $user ? max(1, $user->deviceCount()) : 1;
            $rules['device_limit'] = 'required|integer|min:' . $minDeviceLimit;
            $rules['start_date'] = 'required|date';
            $rules['expire_date'] = 'required|date|after_or_equal:start_date';
            $rules['services'] = 'nullable|array';
            $rules['services.*'] = 'exists:services,id';

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
                $rules['auth_type'] = 'required|string|max:191';
                $rules['access_level'] = 'required|string|max:191';
                $rules['sla_policy_id'] = 'required|exists:sla_policies,id';
                $rules['assigned_roles'] = 'required|array';

                // Nullable / Optional fields
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
                $rules['module_access'] = 'nullable|array';
                $rules['business_unit'] = 'nullable|string|max:191';
                $rules['service_categories'] = 'nullable|array';
                $rules['max_tickets_per_day'] = 'nullable|integer|min:0';
                $rules['max_changes_per_week'] = 'nullable|integer|min:0';
                $rules['notification_methods'] = 'nullable|array';
                $rules['alert_emails'] = 'nullable|array';
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

    private function redirectToUsersIndexWithEdit(User $user): RedirectResponse
    {
        return redirect()->route('users.index')
            ->withInput()
            ->with('edit_user_id', $user->id);
    }
}
