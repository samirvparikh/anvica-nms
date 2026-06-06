<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDeviceRequest;
use App\Http\Requests\UpdateDeviceRequest;
use App\Models\Device;
use App\Models\DeviceVendor;
use App\Models\Service;
use App\Repositories\DeviceRepository;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    public function __construct(
        protected DeviceRepository $deviceRepository,
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();

        return view('devices.index', [
            'devices' => $this->deviceRepository->allForUser($user),
            'services' => Service::where('status', Service::STATUS_ACTIVE)->orderBy('name')->get(),
            'vendors' => DeviceVendor::with('service')->where('status', DeviceVendor::STATUS_ACTIVE)->orderBy('name')->get(),
            'customers' => $user->isAdmin()
                ? \App\Models\User::where('is_admin', false)->where('role', \App\Models\User::ROLE_USER)->orderBy('name')->get()
                : collect(),
            'canAddDevice' => $user->canAddDevice(),
            'deviceLimit' => $user->isAdmin() ? null : $user->device_limit,
            'deviceCount' => $user->isAdmin() ? null : $user->deviceCount(),
            'isAdmin' => $user->isAdmin(),
        ]);
    }

    public function store(StoreDeviceRequest $request)
    {
        $user = $request->user();

        if (! $user->canAddDevice()) {
            return back()->withErrors(['device_limit' => 'Device limit reached. You cannot add more devices.']);
        }

        if (! $user->isActive()) {
            return back()->withErrors(['account' => 'Your account has expired. Please contact administrator.']);
        }

        $data = $this->prepareDeviceData($request->validated());
        $data['user_id'] = $user->isAdmin()
            ? ($request->input('user_id') ?: null)
            : $user->id;

        $this->deviceRepository->create($data);

        return redirect()->route('devices.index')->with('success', 'Device added successfully.');
    }

    public function update(UpdateDeviceRequest $request, Device $device)
    {
        $user = $request->user();
        $this->authorizeDevice($request, $device);

        $data = $this->prepareDeviceData($request->validated(), $device);
        if ($user->isAdmin()) {
            $data['user_id'] = $request->input('user_id') ?: null;
        }
        $this->deviceRepository->update($device, $data);

        return redirect()->route('devices.index')->with('success', 'Device updated successfully.');
    }

    public function destroy(Request $request, Device $device)
    {
        $this->authorizeDevice($request, $device);
        $this->deviceRepository->delete($device);

        return redirect()->route('devices.index')->with('success', 'Device deleted successfully.');
    }

    private function prepareDeviceData(array $data, ?Device $device = null): array
    {
        $service = Service::find($data['service_id']);
        $data['type'] = $service?->name ?? ($data['type'] ?? 'Other');
        $data['device_type'] = $data['type'];

        if (empty($data['api_password'])) {
            unset($data['api_password']);
        }

        if (empty($data['hostname'])) {
            $data['hostname'] = $data['name'];
        }

        return $data;
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
