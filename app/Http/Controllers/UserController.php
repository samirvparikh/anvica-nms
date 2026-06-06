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
            ->where('role', User::ROLE_USER)
            ->orderByDesc('created_at')
            ->get();

        $services = Service::orderBy('name')->get();

        return view('users.index', compact('users', 'services'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:191',
            'email' => 'required|email|max:191|unique:users,email',
            'mobile' => 'nullable|string|max:20',
            'password' => 'required|string|min:6|confirmed',
            'device_limit' => 'required|integer|min:1',
            'start_date' => 'required|date',
            'expire_date' => 'required|date|after_or_equal:start_date',
            'services' => 'nullable|array',
            'services.*' => 'exists:services,id',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'mobile' => $validated['mobile'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => User::ROLE_USER,
            'device_limit' => $validated['device_limit'],
            'start_date' => $validated['start_date'],
            'expire_date' => $validated['expire_date'],
            'created_by' => $request->user()->id,
        ]);

        if (! empty($validated['services'])) {
            $user->services()->sync($validated['services']);
        }

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function update(Request $request, User $user)
    {
        if ($user->isAdmin()) {
            abort(403, 'Cannot modify admin accounts from this page.');
        }

        $minDeviceLimit = max(1, $user->deviceCount());

        $validated = $request->validate([
            'name' => 'required|string|max:191',
            'email' => ['required', 'email', 'max:191', Rule::unique('users', 'email')->ignore($user->id)],
            'mobile' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:6|confirmed',
            'device_limit' => 'required|integer|min:' . $minDeviceLimit,
            'start_date' => 'required|date',
            'expire_date' => 'required|date|after_or_equal:start_date',
            'services' => 'nullable|array',
            'services.*' => 'exists:services,id',
        ]);

        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'mobile' => $validated['mobile'] ?? null,
            'device_limit' => $validated['device_limit'],
            'start_date' => $validated['start_date'],
            'expire_date' => $validated['expire_date'],
        ]);

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();
        $user->services()->sync($validated['services'] ?? []);

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        if ($user->isAdmin()) {
            abort(403, 'Cannot delete admin accounts.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }
}
