<?php

namespace App\Http\Controllers;

use App\Models\Device;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    /**
     * Display a listing of the devices.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $devices = $user->isAdmin()
            ? Device::with('user')->orderBy('name')->get()
            : $user->devices()->orderBy('name')->get();

        return view('devices.index', [
            'devices' => $devices,
            'canAddDevice' => $user->canAddDevice(),
            'deviceLimit' => $user->isAdmin() ? null : $user->device_limit,
            'deviceCount' => $user->isAdmin() ? null : $user->deviceCount(),
        ]);
    }

    /**
     * Store a newly created device in storage.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        if (! $user->canAddDevice()) {
            return back()->withErrors([
                'device_limit' => 'Device limit reached. You cannot add more devices.',
            ]);
        }

        if (! $user->isActive()) {
            return back()->withErrors([
                'account' => 'Your account has expired. Please contact administrator.',
            ]);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:191|unique:devices,name',
            'type' => 'required|string|max:191',
            'ip_address' => 'required|ip',
            'location' => 'required|string|max:191',
            'status' => 'required|in:Up,Warning,Down',
        ]);

        $validated['user_id'] = $user->isAdmin() ? null : $user->id;

        Device::create($validated);

        return redirect()->route('devices.index')->with('success', 'Device added successfully.');
    }

    /**
     * Update the specified device in storage.
     */
    public function update(Request $request, Device $device)
    {
        $this->authorizeDevice($request, $device);

        $validated = $request->validate([
            'name' => 'required|string|max:191|unique:devices,name,' . $device->id,
            'type' => 'required|string|max:191',
            'ip_address' => 'required|ip',
            'location' => 'required|string|max:191',
            'status' => 'required|in:Up,Warning,Down',
        ]);

        $device->update($validated);

        return redirect()->route('devices.index')->with('success', 'Device updated successfully.');
    }

    /**
     * Remove the specified device from storage.
     */
    public function destroy(Request $request, Device $device)
    {
        $this->authorizeDevice($request, $device);

        $device->delete();

        return redirect()->route('devices.index')->with('success', 'Device deleted successfully.');
    }

    private function authorizeDevice(Request $request, Device $device): void
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            return;
        }

        if ($device->user_id !== $user->id) {
            abort(403, 'Unauthorized access to this device.');
        }
    }
}
