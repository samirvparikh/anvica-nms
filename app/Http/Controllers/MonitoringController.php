<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\UserScopeService;
use Illuminate\Http\Request;

class MonitoringController extends Controller
{
    public function __construct(
        protected UserScopeService $userScope,
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $customerId = $user->isAdmin() ? $request->integer('user_id') ?: null : null;

        $devices = $this->userScope
            ->devicesQuery($user, $customerId)
            ->with(['user', 'service', 'vendor'])
            ->orderBy('name')
            ->get();

        $latestMetrics = $this->userScope->latestMetricsByDevice($user, $customerId);

        $interfaces = $this->userScope
            ->interfacesQuery($user, $customerId)
            ->with(['device.user'])
            ->orderByDesc('updated_at')
            ->get();

        $alerts = $this->userScope
            ->alertsQuery($user, $customerId)
            ->with(['device.user', 'servicePoint'])
            ->latest()
            ->get();

        $customers = $user->isAdmin()
            ? User::where('is_admin', false)->where('role', User::ROLE_USER)->orderBy('name')->get()
            : collect();

        return view('monitoring.index', compact(
            'devices',
            'latestMetrics',
            'interfaces',
            'alerts',
            'customers',
            'customerId',
        ));
    }
}
