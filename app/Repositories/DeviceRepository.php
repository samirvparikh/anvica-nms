<?php

namespace App\Repositories;

use App\Models\Device;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class DeviceRepository
{
    public function allForUser(User $user): Collection
    {
        return $user->isAdmin()
            ? Device::with(['service', 'vendor', 'user'])->orderBy('name')->get()
            : $user->devices()->with(['service', 'vendor'])->orderBy('name')->get();
    }

    public function find(int $id): ?Device
    {
        return Device::with(['service', 'vendor', 'metrics', 'interfaces', 'alerts'])->find($id);
    }

    public function create(array $data): Device
    {
        return Device::create($data);
    }

    public function update(Device $device, array $data): Device
    {
        $device->update($data);

        return $device->fresh();
    }

    public function delete(Device $device): void
    {
        $device->delete();
    }

    public function pollable(): Collection
    {
        return Device::with(['service', 'vendor'])
            ->whereNotNull('service_id')
            ->get();
    }
}
