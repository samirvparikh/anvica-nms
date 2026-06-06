<?php

namespace App\Http\Controllers;

use App\Models\DeviceVendor;
use App\Models\Service;
use App\Repositories\DeviceVendorRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DeviceVendorController extends Controller
{
    public function __construct(
        protected DeviceVendorRepository $vendorRepository,
    ) {}

    public function index()
    {
        return view('vendors.index', [
            'vendors' => $this->vendorRepository->allWithService(),
            'services' => Service::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'service_id' => 'required|exists:services,id',
            'name' => 'required|string|max:191',
            'logo' => 'nullable|string|max:500',
            'status' => 'required|in:Active,Inactive',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $this->vendorRepository->create($validated);

        return redirect()->route('vendors.index')->with('success', 'Vendor created successfully.');
    }

    public function update(Request $request, DeviceVendor $vendor)
    {
        $validated = $request->validate([
            'service_id' => 'required|exists:services,id',
            'name' => 'required|string|max:191',
            'logo' => 'nullable|string|max:500',
            'status' => 'required|in:Active,Inactive',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $this->vendorRepository->update($vendor, $validated);

        return redirect()->route('vendors.index')->with('success', 'Vendor updated successfully.');
    }

    public function destroy(DeviceVendor $vendor)
    {
        $this->vendorRepository->delete($vendor);

        return redirect()->route('vendors.index')->with('success', 'Vendor deleted successfully.');
    }
}
