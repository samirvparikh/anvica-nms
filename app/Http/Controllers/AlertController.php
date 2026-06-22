<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\Device;
use App\Models\ServicePoint;
use App\Repositories\AlertRepository;
use App\Services\AlertService;
use App\Services\UserScopeService;
use Illuminate\Http\Request;

class AlertController extends Controller
{
    public function __construct(
        protected AlertRepository $alertRepository,
        protected UserScopeService $userScope,
        protected AlertService $alertService,
    ) {}

    /**
     * User-facing alerts page (replaces legacy /alarms).
     */
    public function userIndex(Request $request)
    {
        $user = $request->user();

        return view('alarms.index', [
            'criticalCount' => $this->alertRepository->openCountBySeverity(Alert::SEVERITY_CRITICAL, $user),
            'warningCount' => $this->alertRepository->scopedQuery($user)
                ->where('status', Alert::STATUS_OPEN)
                ->whereIn('severity', [Alert::SEVERITY_WARNING, Alert::SEVERITY_INFO])
                ->count(),
            'ackCount' => $this->alertRepository->acknowledgedCount($user),
            'alerts' => $this->alertRepository->scopedQuery($user)
                ->with('device')
                ->orderByRaw("CASE WHEN status = 'open' THEN 0 ELSE 1 END")
                ->orderByDesc('created_at')
                ->get(),
        ]);
    }

    public function acknowledge(Request $request, Alert $alert)
    {
        $alert->load('device');
        abort_unless($this->userScope->canAccessDevice($request->user(), $alert->device), 403);

        if ($alert->status !== Alert::STATUS_OPEN) {
            return redirect()->route('alarms.index')->with('success', 'Alert is already closed.');
        }

        $alert->update([
            'acknowledged_at' => now(),
            'acknowledged_by' => $request->user()->id,
        ]);

        return redirect()->route('alarms.index')->with('success', 'Alert acknowledged successfully.');
    }

    public function index(Request $request)
    {
        $user = $request->user();

        return view('alerts.index', [
            'alerts' => $this->alertRepository->allWithRelations($user),
            'devices' => Device::orderBy('name')->get(),
            'servicePoints' => ServicePoint::with('service')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'device_id' => 'required|exists:devices,id',
            'service_point_id' => 'nullable|exists:service_points,id',
            'severity' => 'required|in:critical,warning,info',
            'message' => 'required|string|max:1000',
            'status' => 'required|in:open,closed',
        ]);

        $this->alertRepository->create($validated);

        return redirect()->route('alerts.index')->with('success', 'Alert created successfully.');
    }

    public function update(Request $request, Alert $alert)
    {
        $validated = $request->validate([
            'device_id' => 'required|exists:devices,id',
            'service_point_id' => 'nullable|exists:service_points,id',
            'severity' => 'required|in:critical,warning,info',
            'message' => 'required|string|max:1000',
            'status' => 'required|in:open,closed',
        ]);

        $this->alertRepository->update($alert, $validated);

        return redirect()->route('alerts.index')->with('success', 'Alert updated successfully.');
    }

    public function destroy(Alert $alert)
    {
        $this->alertRepository->delete($alert);

        return redirect()->route('alerts.index')->with('success', 'Alert deleted successfully.');
    }

    public function close(Alert $alert)
    {
        $this->alertService->closeAlert($alert);

        return redirect()->route('alerts.index')->with('success', 'Alert closed successfully.');
    }
}
