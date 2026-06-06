<?php

namespace App\Repositories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Collection;

class ServiceRepository
{
    public function allWithPoints(): Collection
    {
        return Service::with('points')->orderBy('name')->get();
    }

    public function create(array $data, array $points = []): Service
    {
        $service = Service::create($data);

        foreach ($points as $point) {
            $service->points()->create($point);
        }

        return $service->load('points');
    }

    public function update(Service $service, array $data, array $points = []): Service
    {
        $service->update($data);
        $service->points()->delete();

        foreach ($points as $point) {
            $service->points()->create($point);
        }

        return $service->load('points');
    }
}
