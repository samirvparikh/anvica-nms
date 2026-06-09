<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\ServicePoint;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ServicePointController extends Controller
{
    public function index(Request $request)
    {
        $serviceId = $request->integer('service_id') ?: null;

        $servicePoints = ServicePoint::with('service')
            ->when($serviceId, fn ($query) => $query->where('service_id', $serviceId))
            ->orderBy('name')
            ->get();

        return view('service-points.index', [
            'servicePoints' => $servicePoints,
            'services' => Service::orderBy('name')->get(),
            'serviceId' => $serviceId,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'service_id' => 'required|exists:services,id',
            'name' => 'required|string|max:191',
            'method' => 'required|string|max:191',
            'unit' => 'nullable|string|max:50',
            'warning_threshold' => 'nullable|numeric',
            'critical_threshold' => 'nullable|numeric',
            'status' => 'required|in:Active,Inactive',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        ServicePoint::create($validated);

        return $this->redirectToIndex($request, 'Service point created successfully.');
    }

    public function update(Request $request, ServicePoint $servicePoint)
    {
        $validated = $request->validate([
            'service_id' => 'required|exists:services,id',
            'name' => 'required|string|max:191',
            'method' => 'required|string|max:191',
            'unit' => 'nullable|string|max:50',
            'warning_threshold' => 'nullable|numeric',
            'critical_threshold' => 'nullable|numeric',
            'status' => 'required|in:Active,Inactive',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $servicePoint->update($validated);

        return $this->redirectToIndex($request, 'Service point updated successfully.');
    }

    public function destroy(Request $request, ServicePoint $servicePoint)
    {
        $servicePoint->delete();

        return $this->redirectToIndex($request, 'Service point deleted successfully.');
    }

    protected function redirectToIndex(Request $request, string $message)
    {
        $params = $request->filled('redirect_service_id')
            ? ['service_id' => $request->integer('redirect_service_id')]
            : [];

        return redirect()
            ->route('service-points.index', $params)
            ->with('success', $message);
    }
}
