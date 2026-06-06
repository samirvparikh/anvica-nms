<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Repositories\ServiceRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ServiceController extends Controller
{
    public function __construct(
        protected ServiceRepository $serviceRepository,
    ) {}

    public function index()
    {
        return view('services.index', [
            'services' => $this->serviceRepository->allWithPoints(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:191|unique:services,name',
            'icon' => 'nullable|string|max:100',
            'status' => 'required|in:Active,Inactive',
            'points' => 'required|array|min:1',
            'points.*.name' => 'required|string|max:191',
            'points.*.method' => 'required|string|max:191',
            'points.*.unit' => 'nullable|string|max:50',
            'points.*.warning_threshold' => 'nullable|numeric',
            'points.*.critical_threshold' => 'nullable|numeric',
            'points.*.status' => 'nullable|in:Active,Inactive',
        ]);

        $this->serviceRepository->create(
            [
                'name' => $validated['name'],
                'slug' => Str::slug($validated['name']),
                'icon' => $validated['icon'] ?? null,
                'status' => $validated['status'],
            ],
            $this->mapPoints($validated['points'])
        );

        return redirect()->route('services.index')->with('success', 'Service created successfully.');
    }

    public function update(Request $request, Service $service)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:191|unique:services,name,' . $service->id,
            'icon' => 'nullable|string|max:100',
            'status' => 'required|in:Active,Inactive',
            'points' => 'required|array|min:1',
            'points.*.name' => 'required|string|max:191',
            'points.*.method' => 'required|string|max:191',
            'points.*.unit' => 'nullable|string|max:50',
            'points.*.warning_threshold' => 'nullable|numeric',
            'points.*.critical_threshold' => 'nullable|numeric',
            'points.*.status' => 'nullable|in:Active,Inactive',
        ]);

        $this->serviceRepository->update(
            $service,
            [
                'name' => $validated['name'],
                'slug' => Str::slug($validated['name']),
                'icon' => $validated['icon'] ?? null,
                'status' => $validated['status'],
            ],
            $this->mapPoints($validated['points'])
        );

        return redirect()->route('services.index')->with('success', 'Service updated successfully.');
    }

    public function destroy(Service $service)
    {
        $service->delete();

        return redirect()->route('services.index')->with('success', 'Service deleted successfully.');
    }

    private function mapPoints(array $points): array
    {
        return collect($points)->map(function (array $point) {
            return [
                'name' => $point['name'],
                'slug' => Str::slug($point['name']),
                'method' => $point['method'],
                'unit' => $point['unit'] ?? null,
                'warning_threshold' => $point['warning_threshold'] ?? null,
                'critical_threshold' => $point['critical_threshold'] ?? null,
                'status' => $point['status'] ?? 'Active',
            ];
        })->all();
    }
}
