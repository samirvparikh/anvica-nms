<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\DeviceScript;
use App\Services\MikrotikScriptService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class DeviceScriptController extends Controller
{
    public function __construct(
        protected MikrotikScriptService $scriptService,
    ) {}

    public function edit(Device $device)
    {
        $device->load([
            'script',
            'vendor.script',
            'vendor.servicePointCodes.servicePoint',
            'service.points',
        ]);

        $defaults = $this->defaultConfig($device);
        $script = $device->script;
        $relativePath = $this->scriptService->publicRelativePath($device->id);

        $config = [
            'target_ip' => old('target_ip', $script?->target_ip ?? $defaults['target_ip']),
            'snmp_community' => old('snmp_community', $script?->snmp_community ?? $defaults['snmp_community']),
            'nms_url' => old('nms_url', $script?->nms_url ?? $defaults['nms_url']),
            'interface_indexes' => old(
                'interface_indexes',
                $script
                    ? implode(', ', $script->interface_indexes)
                    : $defaults['interface_indexes']
            ),
        ];

        $servicePoints = $this->scriptService->servicePointsForDevice($device);
        $codesByPointId = $device->vendor?->servicePointCodes->keyBy('service_point_id') ?? collect();
        $resolvedTemplate = $this->scriptService->resolveVendorTemplate($device);
        $template = $resolvedTemplate['template'];
        $preview = $this->scriptService->generateForDevice(
            $device,
            $this->buildConfigFromInput($config)
        );

        return view('devices.script', [
            'device' => $device,
            'config' => $config,
            'template' => $template,
            'templateIsDefault' => $resolvedTemplate['isDefault'],
            'preview' => $preview,
            'publicUrl' => is_file(public_path($relativePath)) ? asset($relativePath) : null,
            'servicePointRows' => $this->scriptService->servicePointRows($servicePoints, $codesByPointId),
            'hasPublishedFile' => is_file(public_path($relativePath)),
        ]);
    }

    public function update(Request $request, Device $device)
    {
        $validated = $request->validate([
            'target_ip' => ['required', 'ip'],
            'snmp_community' => ['required', 'string', 'max:255'],
            'nms_url' => ['required', 'url', 'max:500'],
            'interface_indexes' => ['required', 'string', 'max:500'],
        ]);

        $indexes = $this->scriptService->parseInterfaceIndexes($validated['interface_indexes']);

        if ($indexes === []) {
            return back()
                ->withInput()
                ->withErrors(['interface_indexes' => 'Enter at least one valid interface index (e.g. 3,5,6,8).']);
        }

        $device->load(['vendor.script', 'vendor.servicePointCodes.servicePoint', 'service.points']);

        $config = [
            'target_ip' => $validated['target_ip'],
            'snmp_community' => $validated['snmp_community'],
            'nms_url' => $validated['nms_url'],
            'interface_indexes' => $indexes,
        ];

        $content = $this->scriptService->generateForDevice($device, $config);
        $relativePath = $this->scriptService->publicRelativePath($device->id);
        $absolutePath = public_path($relativePath);

        File::ensureDirectoryExists(public_path('scripts'));

        $legacyPath = public_path('scripts/device-'.$device->id.'.src');
        if (File::exists($legacyPath)) {
            File::delete($legacyPath);
        }

        File::put($absolutePath, $content);

        DeviceScript::updateOrCreate(
            ['device_id' => $device->id],
            [
                'target_ip' => $config['target_ip'],
                'snmp_community' => $config['snmp_community'],
                'nms_url' => $config['nms_url'],
                'interface_indexes' => $config['interface_indexes'],
                'public_path' => $relativePath,
            ]
        );

        return redirect()
            ->route('devices.script.edit', $device)
            ->with('success', 'Script saved and published at '.asset($relativePath));
    }

    public function preview(Request $request, Device $device)
    {
        $validated = $request->validate([
            'target_ip' => ['required', 'ip'],
            'snmp_community' => ['required', 'string', 'max:255'],
            'nms_url' => ['required', 'url', 'max:500'],
            'interface_indexes' => ['required', 'string', 'max:500'],
        ]);

        $indexes = $this->scriptService->parseInterfaceIndexes($validated['interface_indexes']);

        if ($indexes === []) {
            return response()->json(['error' => 'Invalid interface indexes'], 422);
        }

        $device->load(['vendor.script', 'vendor.servicePointCodes.servicePoint', 'service.points']);

        return response()->json([
            'content' => $this->scriptService->generateForDevice($device, [
                'target_ip' => $validated['target_ip'],
                'snmp_community' => $validated['snmp_community'],
                'nms_url' => $validated['nms_url'],
                'interface_indexes' => $indexes,
            ]),
        ]);
    }

    /**
     * @return array{target_ip: string, snmp_community: string, nms_url: string, interface_indexes: string}
     */
    private function defaultConfig(Device $device): array
    {
        return [
            'target_ip' => $device->ip_address,
            'snmp_community' => $device->snmp_community ?: 'Anvica_NMS',
            'nms_url' => rtrim(config('app.url'), '/').'/api/device/data',
            'interface_indexes' => '3,5,6,8',
        ];
    }

    /**
     * @param  array{target_ip: string, snmp_community: string, nms_url: string, interface_indexes: string}  $input
     * @return array{target_ip: string, snmp_community: string, nms_url: string, interface_indexes: array<int, string>}
     */
    private function buildConfigFromInput(array $input): array
    {
        $indexes = $this->scriptService->parseInterfaceIndexes($input['interface_indexes']);

        return [
            'target_ip' => $input['target_ip'],
            'snmp_community' => $input['snmp_community'],
            'nms_url' => $input['nms_url'],
            'interface_indexes' => $indexes !== [] ? $indexes : ['3', '5', '6', '8'],
        ];
    }
}
