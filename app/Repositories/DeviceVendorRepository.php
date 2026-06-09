<?php

namespace App\Repositories;

use App\Models\DeviceVendor;
use Illuminate\Database\Eloquent\Collection;

class DeviceVendorRepository
{
    public function filtered(?int $serviceId = null, ?int $vendorId = null): Collection
    {
        if ($vendorId) {
            $vendor = DeviceVendor::find($vendorId);
            if (! $vendor || ($serviceId && $vendor->service_id !== $serviceId)) {
                $vendorId = null;
            }
        }

        return DeviceVendor::with('service')
            ->when($serviceId, fn ($query) => $query->where('service_id', $serviceId))
            ->when($vendorId, fn ($query) => $query->where('id', $vendorId))
            ->orderBy('name')
            ->get();
    }

    public function filterOptions(?int $serviceId = null): Collection
    {
        return DeviceVendor::with('service')
            ->when($serviceId, fn ($query) => $query->where('service_id', $serviceId))
            ->orderBy('name')
            ->get();
    }

    public function allWithService(): Collection
    {
        return DeviceVendor::with('service')->orderBy('name')->get();
    }

    public function forService(int $serviceId): Collection
    {
        return DeviceVendor::where('service_id', $serviceId)
            ->where('status', DeviceVendor::STATUS_ACTIVE)
            ->orderBy('name')
            ->get();
    }

    public function create(array $data): DeviceVendor
    {
        return DeviceVendor::create($data);
    }

    public function update(DeviceVendor $vendor, array $data): DeviceVendor
    {
        $vendor->update($data);

        return $vendor->fresh();
    }

    public function delete(DeviceVendor $vendor): void
    {
        $vendor->delete();
    }
}
