<?php

namespace App\Http\Controllers;

use App\Models\DeviceVendor;
use App\Models\ServicePoint;
use App\Models\VendorScript;
use App\Services\MikrotikScriptService;
use Illuminate\Http\Request;

class VendorScriptController extends Controller
{
    public function __construct(
        protected MikrotikScriptService $scriptService,
    ) {}

    public function edit(DeviceVendor $vendor)
    {
        $vendor->load(['service', 'servicePointCodes.servicePoint', 'script']);

        $servicePoints = ServicePoint::query()
            ->where('service_id', $vendor->service_id)
            ->where('status', ServicePoint::STATUS_ACTIVE)
            ->orderBy('name')
            ->get();

        $codesByPointId = $vendor->servicePointCodes->keyBy('service_point_id');
        $saved = $vendor->script?->template;
        $templateIsDefault = ! is_string($saved) || trim($saved) === '';
        $template = old(
            'template',
            $templateIsDefault
                ? $this->scriptService->buildDefaultTemplateFromServicePoints($servicePoints)
                : $saved
        );

        return view('vendors.script', [
            'vendor' => $vendor,
            'template' => $template,
            'templateIsDefault' => $templateIsDefault && ! old('template'),
            'servicePointRows' => $this->scriptService->servicePointRows($servicePoints, $codesByPointId),
        ]);
    }

    public function update(Request $request, DeviceVendor $vendor)
    {
        $validated = $request->validate([
            'template' => ['required', 'string', 'max:50000'],
        ]);

        VendorScript::updateOrCreate(
            ['vendor_id' => $vendor->id],
            ['template' => $validated['template']]
        );

        return redirect()
            ->route('vendors.script.edit', $vendor)
            ->with('success', 'Vendor script template saved.');
    }
}
