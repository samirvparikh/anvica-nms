<?php

namespace App\Repositories;

use App\Models\Alert;
use App\Models\User;
use App\Services\UserScopeService;
use Illuminate\Database\Eloquent\Collection;

class AlertRepository
{
    public function __construct(
        protected UserScopeService $userScope,
    ) {}

    public function allWithRelations(?User $user = null): Collection
    {
        $query = Alert::with(['device.user', 'servicePoint']);

        if ($user) {
            $query->whereIn('device_id', $this->userScope->deviceIds($user) ?: [-1]);
        }

        return $query->latest()->get();
    }

    public function openCount(?User $user = null): int
    {
        $query = Alert::where('status', Alert::STATUS_OPEN);

        if ($user) {
            $query->whereIn('device_id', $this->userScope->deviceIds($user) ?: [-1]);
        }

        return $query->count();
    }

    public function recent(int $limit = 5, ?User $user = null): Collection
    {
        $query = Alert::with('device');

        if ($user) {
            $query->whereIn('device_id', $this->userScope->deviceIds($user) ?: [-1]);
        }

        return $query->latest()->take($limit)->get();
    }

    public function create(array $data): Alert
    {
        return Alert::create($data);
    }

    public function update(Alert $alert, array $data): Alert
    {
        $alert->update($data);

        return $alert->fresh();
    }

    public function delete(Alert $alert): void
    {
        $alert->delete();
    }
}
