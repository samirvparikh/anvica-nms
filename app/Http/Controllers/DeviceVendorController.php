<?php

namespace App\Http\Controllers;

use App\Models\DeviceVendor;
use App\Models\Service;
use App\Models\ServicePoint;
use App\Repositories\DeviceVendorRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DeviceVendorController extends Controller
{
    public function __construct(
        protected DeviceVendorRepository $vendorRepository,
    ) {}

    public function index(Request $request)
    {
        $serviceId = $request->integer('service_id') ?: null;
        $vendorId = $request->integer('vendor_id') ?: null;

        $servicePointsByService = ServicePoint::query()
            ->where('status', ServicePoint::STATUS_ACTIVE)
            ->orderBy('name')
            ->get()
            ->groupBy('service_id')
            ->map(fn ($points) => $points->map(fn ($point) => [
                'id' => $point->id,
                'name' => $point->name,
                'slug' => $point->slug,
            ])->values())
            ->all();

        return view('vendors.index', [
            'vendors' => $this->vendorRepository->filtered($serviceId, $vendorId),
            'services' => Service::orderBy('name')->get(),
            'vendorOptions' => $this->vendorRepository->filterOptions($serviceId),
            'serviceId' => $serviceId,
            'vendorId' => $vendorId,
            'servicePointsByService' => $servicePointsByService,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'name' => 'required|string|max:191',
            'logo' => 'nullable|string|max:500',
            'status' => 'required|in:Active,Inactive',
            'codes' => 'nullable|array',
            'codes.*.service_point_id' => 'nullable|integer|exists:service_points,id',
            'codes.*.name' => 'nullable|string|max:191',
            'codes.*.code' => 'nullable|string|max:191',
        ]);

        $codes = $request->input('codes', []);
        $validated = $request->only(['service_id', 'name', 'logo', 'status']);

        $validated['slug'] = Str::slug($validated['name']);
        $this->vendorRepository->create($validated, $codes);

        return $this->redirectToIndex($request, 'Vendor created successfully.');
    }

    public function update(Request $request, DeviceVendor $vendor)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'name' => 'required|string|max:191',
            'logo' => 'nullable|string|max:500',
            'status' => 'required|in:Active,Inactive',
            'codes' => 'nullable|array',
            'codes.*.service_point_id' => 'nullable|integer|exists:service_points,id',
            'codes.*.name' => 'nullable|string|max:191',
            'codes.*.code' => 'nullable|string|max:191',
        ]);

        $codes = $request->input('codes', []);
        $validated = $request->only(['service_id', 'name', 'logo', 'status']);

        $validated['slug'] = Str::slug($validated['name']);
        $this->vendorRepository->update($vendor, $validated, $codes);

        return $this->redirectToIndex($request, 'Vendor updated successfully.');
    }

    public function destroy(Request $request, DeviceVendor $vendor)
    {
        $this->vendorRepository->delete($vendor);

        return $this->redirectToIndex($request, 'Vendor deleted successfully.');
    }

    protected function redirectToIndex(Request $request, string $message)
    {
        $params = array_filter([
            'service_id' => $request->filled('redirect_service_id') ? $request->integer('redirect_service_id') : null,
            'vendor_id' => $request->filled('redirect_vendor_id') ? $request->integer('redirect_vendor_id') : null,
        ]);

        return redirect()
            ->route('vendors.index', $params)
            ->with('success', $message);
    }
}
