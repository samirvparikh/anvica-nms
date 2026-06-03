<?php

namespace App\Http\Controllers;

use App\Models\Device;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    /**
     * Display a listing of the devices.
     */
    public function index(Request $Request)
    {
        $devices = Device::all();
        return view('devices.index', compact('devices'));
    }

    /**
     * Store a newly created device in storage.
     */
    public function store(Request $Request)
    {
        $validated = $Request->validate([
            'name' => 'required|string|max:191|unique:devices,name',
            'type' => 'required|string|max:191',
            'ip_address' => 'required|ip',
            'location' => 'required|string|max:191',
            'status' => 'required|in:Up,Warning,Down',
        ]);

        Device::create($validated);

        return redirect()->route('devices.index')->with('success', 'Device added successfully.');
    }

    /**
     * Update the specified device in storage.
     */
    public function update(Request $Request, Device $device)
    {
        $validated = $Request->validate([
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
    public function destroy(Device $device)
    {
        $device->delete();
        return redirect()->route('devices.index')->with('success', 'Device deleted successfully.');
    }
}
