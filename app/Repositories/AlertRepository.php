<?php

namespace App\Repositories;

use App\Models\Alert;
use App\Models\User;
use App\Services\UserScopeService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class AlertRepository
{
    public function __construct(
        protected UserScopeService $userScope,
    ) {}

    public function scopedQuery(?User $user = null): Builder
    {
        $query = Alert::query();

        if ($user) {
            $query->whereIn('device_id', $this->userScope->deviceIds($user) ?: [-1]);
        }

        return $query;
    }

    public function allWithRelations(?User $user = null): Collection
    {
        return $this->scopedQuery($user)
            ->with(['device.user', 'servicePoint'])
            ->latest()
            ->get();
    }

    public function openCount(?User $user = null): int
    {
        return $this->scopedQuery($user)
            ->where('status', Alert::STATUS_OPEN)
            ->count();
    }

    public function openCountBySeverity(string $severity, ?User $user = null): int
    {
        return $this->scopedQuery($user)
            ->where('status', Alert::STATUS_OPEN)
            ->where('severity', $severity)
            ->count();
    }

    public function acknowledgedCount(?User $user = null): int
    {
        return $this->scopedQuery($user)
            ->whereNotNull('acknowledged_at')
            ->count();
    }

    public function recent(int $limit = 5, ?User $user = null): Collection
    {
        return $this->scopedQuery($user)
            ->with('device')
            ->latest()
            ->take($limit)
            ->get();
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
