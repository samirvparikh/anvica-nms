<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::with('points')->orderBy('name')->get();

        return view('services.index', compact('services'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:191|unique:services,name',
            'status' => 'required|in:Active,Inactive',
            'points' => 'required|array|min:1',
            'points.*.point' => 'required|string|max:191',
            'points.*.method' => 'required|string|max:191',
        ]);

        $service = Service::create([
            'name' => $validated['name'],
            'status' => $validated['status'],
        ]);

        foreach ($validated['points'] as $pointData) {
            $service->points()->create($pointData);
        }

        return redirect()->route('services.index')->with('success', 'Service created successfully.');
    }

    public function update(Request $request, Service $service)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:191|unique:services,name,' . $service->id,
            'status' => 'required|in:Active,Inactive',
            'points' => 'required|array|min:1',
            'points.*.point' => 'required|string|max:191',
            'points.*.method' => 'required|string|max:191',
        ]);

        $service->update([
            'name' => $validated['name'],
            'status' => $validated['status'],
        ]);
        $service->points()->delete();

        foreach ($validated['points'] as $pointData) {
            $service->points()->create($pointData);
        }

        return redirect()->route('services.index')->with('success', 'Service updated successfully.');
    }

    public function destroy(Service $service)
    {
        $service->delete();

        return redirect()->route('services.index')->with('success', 'Service deleted successfully.');
    }
}
