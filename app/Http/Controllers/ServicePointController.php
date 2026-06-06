<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\ServicePoint;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ServicePointController extends Controller
{
    public function index()
    {
        return view('service-points.index', [
            'servicePoints' => ServicePoint::with('service')->orderBy('name')->get(),
            'services' => Service::orderBy('name')->get(),
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

        return redirect()->route('service-points.index')->with('success', 'Service point created successfully.');
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

        return redirect()->route('service-points.index')->with('success', 'Service point updated successfully.');
    }

    public function destroy(ServicePoint $servicePoint)
    {
        $servicePoint->delete();

        return redirect()->route('service-points.index')->with('success', 'Service point deleted successfully.');
    }
}
