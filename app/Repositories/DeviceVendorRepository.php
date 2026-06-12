<?php

namespace App\Repositories;

use App\Models\DeviceVendor;
use App\Models\ServicePoint;
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

        return DeviceVendor::with(['service', 'servicePointCodes', 'script'])
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

    public function create(array $data, array $codes = []): DeviceVendor
    {
        $vendor = DeviceVendor::create($data);
        $this->syncPointCodes($vendor, $codes);

        return $vendor->load('servicePointCodes');
    }

    public function update(DeviceVendor $vendor, array $data, array $codes = []): DeviceVendor
    {
        $vendor->update($data);
        $this->syncPointCodes($vendor, $codes);

        return $vendor->fresh(['servicePointCodes']);
    }

    public function delete(DeviceVendor $vendor): void
    {
        $vendor->delete();
    }

    /**
     * @param  list<array{service_point_id?: int, name: string, code: string}>  $codes
     */
    public function syncPointCodes(DeviceVendor $vendor, array $codes): void
    {
        $vendor->servicePointCodes()->delete();

        foreach ($codes as $row) {
            $servicePointId = (int) ($row['service_point_id'] ?? 0);
            $code = trim($row['code'] ?? '');

            if ($servicePointId === 0 || $code === '') {
                continue;
            }

            $name = trim($row['name'] ?? '');
            if ($name === '') {
                $name = ServicePoint::query()->whereKey($servicePointId)->value('name') ?? '';
            }

            if ($name === '') {
                continue;
            }

            $vendor->servicePointCodes()->create([
                'service_point_id' => $servicePointId,
                'name' => $name,
                'code' => $code,
            ]);
        }
    }
}
