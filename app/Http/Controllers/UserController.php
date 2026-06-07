<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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

    public function store(Request $request)
    {
        $validated = $this->validateUser($request);

        $isAdmin = $validated['role'] === User::ROLE_ADMIN;

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'mobile' => $validated['mobile'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'is_admin' => $isAdmin,
            'status' => $validated['status'],
            'device_limit' => $isAdmin ? null : $validated['device_limit'],
            'start_date' => $isAdmin ? null : $validated['start_date'],
            'expire_date' => $isAdmin ? null : $validated['expire_date'],
            'created_by' => $request->user()->id,
        ]);

        if (! $isAdmin && ! empty($validated['services'])) {
            $user->services()->sync($validated['services']);
        }

        $label = $isAdmin ? 'Admin' : 'User';

        return redirect()->route('users.index')->with('success', $label . ' created successfully.');
    }

    public function update(Request $request, User $user)
    {
        if ($user->id === $request->user()->id && $request->input('role') !== User::ROLE_ADMIN) {
            return back()->withErrors(['role' => 'You cannot change your own account to a regular user.']);
        }

        if ($user->id === $request->user()->id && $request->input('status') === User::STATUS_INACTIVE) {
            return back()->withErrors(['status' => 'You cannot deactivate your own account.']);
        }

        $validated = $this->validateUser($request, $user);

        $isAdmin = $validated['role'] === User::ROLE_ADMIN;

        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'mobile' => $validated['mobile'] ?? null,
            'role' => $validated['role'],
            'is_admin' => $isAdmin,
            'status' => $validated['status'],
            'device_limit' => $isAdmin ? null : $validated['device_limit'],
            'start_date' => $isAdmin ? null : $validated['start_date'],
            'expire_date' => $isAdmin ? null : $validated['expire_date'],
        ]);

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
        $isUserRole = $request->input('role', User::ROLE_USER) === User::ROLE_USER;

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
        }

        return $request->validate($rules);
    }
}
