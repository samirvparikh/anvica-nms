<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\Device;
use App\Models\ServicePoint;
use App\Repositories\AlertRepository;
use Illuminate\Http\Request;

class AlertController extends Controller
{
    public function __construct(
        protected AlertRepository $alertRepository,
    ) {}

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
        $this->alertRepository->update($alert, ['status' => Alert::STATUS_CLOSED]);

        return redirect()->route('alerts.index')->with('success', 'Alert closed successfully.');
    }
}
