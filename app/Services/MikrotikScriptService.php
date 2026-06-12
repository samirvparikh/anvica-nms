<?php

namespace App\Services;

use App\Models\Device;
use App\Models\ServicePoint;
use App\Models\ServicePointCode;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class MikrotikScriptService
{
    /** @var array<string, string> */
    private array $legacyVarMap = [
        'Host_Name' => 'hostData',
        'host_name' => 'hostData',
        'CPU' => 'cpu',
        'cpu' => 'cpu',
        'UP_Time' => 'uptime',
        'up_time' => 'uptime',
        'Ram_Used' => 'ram',
        'ram_used' => 'ram',
        'Total_Ram' => 'totalram',
        'total_ram' => 'totalram',
        'CPU_Temp' => 'cputemp',
        'cpu_temp' => 'cputemp',
        'MB_Temp' => 'mbtemp',
        'mb_temp' => 'mbtemp',
        'Board_Temp' => 'boardtemp',
        'board_temp' => 'boardtemp',
        'Power1_Status' => 'pw1',
        'power1_status' => 'pw1',
        'Power2_Status' => 'pw2',
        'power2_status' => 'pw2',
    ];

    /**
     * @param  array{target_ip: string, snmp_community: string, nms_url: string, interface_indexes: array<int, string>}  $config
     */
    public function generateForDevice(Device $device, array $config, ?string $vendorTemplate = null): string
    {
        $device->loadMissing([
            'vendor.script',
            'vendor.servicePointCodes.servicePoint',
            'service.points',
        ]);

        $template = $vendorTemplate ?? $this->resolveVendorTemplate($device)['template'];
        $servicePoints = $this->servicePointsForDevice($device);
        $codesByPointId = $device->vendor?->servicePointCodes->keyBy('service_point_id') ?? collect();

        $replacements = $this->buildReplacements($config, $servicePoints, $codesByPointId);
        $replacements['{INTERFACES_BLOCK}'] = $this->interfacesBlock();
        $replacements['{JSON_METRIC_LINES}'] = $this->jsonMetricLines($servicePoints, $codesByPointId);
        $replacements['{PING_BLOCK}'] = $this->pingBlock();

        $script = str_replace(array_keys($replacements), array_values($replacements), $template);

        return $this->cleanupScript($script);
    }

    /**
     * @param  array{target_ip: string, snmp_community: string, nms_url: string, interface_indexes: array<int, string>}  $config
     */
    public function generate(array $config): string
    {
        $device = new Device([
            'ip_address' => $config['target_ip'],
            'snmp_community' => $config['snmp_community'],
        ]);

        return $this->generateForDevice($device, $config, $this->defaultVendorTemplate());
    }

    public function defaultVendorTemplate(): string
    {
        return $this->wrapScriptTemplate(<<<'CHUNKS'
{Host Name}
{CPU}
{UP Time}
{Ram Used}
{Total Ram}
{CPU Temp}
{MB Temp}
{Board Temp}
{Power1 Status}
{Power2 Status}
CHUNKS);
    }

    /**
     * @return array{template: string, isDefault: bool}
     */
    public function resolveVendorTemplate(Device $device): array
    {
        $device->loadMissing(['vendor.script', 'service.points', 'vendor']);

        $saved = $device->vendor?->script?->template;
        if (is_string($saved) && trim($saved) !== '') {
            return [
                'template' => $saved,
                'isDefault' => false,
            ];
        }

        $servicePoints = $this->servicePointsForDevice($device);

        return [
            'template' => $this->buildDefaultTemplateFromServicePoints($servicePoints),
            'isDefault' => true,
        ];
    }

    /**
     * @param  Collection<int, ServicePoint>  $servicePoints
     */
    public function buildDefaultTemplateFromServicePoints(Collection $servicePoints): string
    {
        $metricChunks = $servicePoints
            ->reject(fn (ServicePoint $point) => $this->isPingPoint($point))
            ->map(fn (ServicePoint $point) => '{'.$point->name.'}')
            ->implode("\n");

        if ($metricChunks === '') {
            return $this->defaultVendorTemplate();
        }

        return $this->wrapScriptTemplate($metricChunks);
    }

    private function wrapScriptTemplate(string $metricChunks): string
    {
        $template = <<<'TEMPLATE'
:local targetIP "{TARGET_IP}"
:local router [/system identity get name]
:local community "{COMMUNITY}"
:local nmsURL "{NMS_URL}"

# Interface indexes to monitor
:local ifIndexes {IF_INDEXES}

{PING_BLOCK}
    # ------------------------------
    # DEVICE METRICS
    # ------------------------------

    :local hostData ""
{METRIC_CHUNKS}

{INTERFACES_BLOCK}

    # ------------------------------
    # FINAL JSON
    # ------------------------------

    :local json "{"

    :set json ($json . "\"target_ip\":\"".$targetIP."\",")
    :set json ($json . "\"Router\":\"".$router."\",")
{JSON_METRIC_LINES}
    :set json ($json . "\"Ping_Status\":\"UP\",")
    :set json ($json . "\"interfaces\":" . $interfaces)

    :set json ($json . "}")

    :log info $json

    /tool fetch \
        url=$nmsURL \
        http-method=post \
        http-header-field="Content-Type: application/json" \
        http-data=$json \
        keep-result=no

    :log info ("NMS Data Sent: " . $targetIP)
}
TEMPLATE;

        return str_replace('{METRIC_CHUNKS}', $metricChunks, $template);
    }

    /**
     * @return array<int, string>
     */
    public function chunkTokens(ServicePoint $point): array
    {
        return array_values(array_unique([
            '{'.$point->name.'}',
            '{'.str_replace(' ', '_', $point->name).'}',
            '{'.$point->slug.'}',
        ]));
    }

    /**
     * @param  Collection<int, ServicePoint>  $servicePoints
     * @param  Collection<int, ServicePointCode>  $codesByPointId
     * @return array<string, string>
     */
    private function buildReplacements(array $config, Collection $servicePoints, Collection $codesByPointId): array
    {
        $replacements = [
            '{TARGET_IP}' => $this->escape($config['target_ip']),
            '{COMMUNITY}' => $this->escape($config['snmp_community']),
            '{NMS_URL}' => $this->escape($config['nms_url']),
            '{IF_INDEXES}' => $this->formatIfIndexes($config['interface_indexes']),
        ];

        foreach ($servicePoints as $point) {
            if ($this->isPingPoint($point)) {
                continue;
            }

            $code = $codesByPointId->get($point->id);
            $chunk = $code && $code->code
                ? $this->metricChunk($point, $code->code)
                : '';

            foreach ($this->chunkTokens($point) as $token) {
                $replacements[$token] = $chunk;
            }
        }

        return $replacements;
    }

    /**
     * @param  Collection<int, ServicePoint>  $servicePoints
     * @param  Collection<int, ServicePointCode>  $codesByPointId
     */
    private function jsonMetricLines(Collection $servicePoints, Collection $codesByPointId): string
    {
        $lines = [];

        foreach ($servicePoints as $point) {
            if ($this->isPingPoint($point)) {
                continue;
            }

            $code = $codesByPointId->get($point->id);
            if (! $code || ! $code->code) {
                continue;
            }

            $var = $this->variableName($point);
            $jsonKey = $this->jsonKey($point);
            $lines[] = '    :set json ($json . "\"'.$jsonKey.'\":\"".$'.$var.'."\",")';
        }

        return implode("\n", $lines);
    }

    private function metricChunk(ServicePoint $point, string $oid): string
    {
        $var = $this->variableName($point);
        $escapedOid = $this->escape($oid);

        return <<<CHUNK

    :do {
        :set {$var} (([/tool snmp-get address=\$targetIP community=\$community version=2c oid={$escapedOid} as-value])->"value")
    } on-error={
        :set {$var} "0"
    }
CHUNK;
    }

    private function pingBlock(): string
    {
        return <<<'BLOCK'
:local pingResult 0

:do {
    :set pingResult [/ping address=$targetIP count=2]
} on-error={
    :set pingResult 0
}

:if ($pingResult = 0) do={

    :local json ("{\"target_ip\":\"".$targetIP."\",\"Router\":\"".$router."\",\"Ping_Status\":\"DOWN\"}")

    /tool fetch \
        url=$nmsURL \
        http-method=post \
        http-header-field="Content-Type: application/json" \
        http-data=$json \
        keep-result=no

    :log warning ("NMS: Device Down - " . $targetIP)

} else={
BLOCK;
    }

    private function interfacesBlock(): string
    {
        return <<<'BLOCK'
    # ------------------------------
    # INTERFACE JSON ARRAY
    # ------------------------------

    :local interfaces "["
    :local first true

    :foreach idx in=$ifIndexes do={

        :do {

            :local ifName (([/tool snmp-get address=$targetIP community=$community version=2c oid=("1.3.6.1.2.1.2.2.1.2.".$idx) as-value])->"value")

            :local ifStatus (([/tool snmp-get address=$targetIP community=$community version=2c oid=("1.3.6.1.2.1.2.2.1.8.".$idx) as-value])->"value")

            :local rxBytes (([/tool snmp-get address=$targetIP community=$community version=2c oid=("1.3.6.1.2.1.2.2.1.10.".$idx) as-value])->"value")

            :local txBytes (([/tool snmp-get address=$targetIP community=$community version=2c oid=("1.3.6.1.2.1.2.2.1.16.".$idx) as-value])->"value")

            :local rxPackets (([/tool snmp-get address=$targetIP community=$community version=2c oid=("1.3.6.1.2.1.2.2.1.11.".$idx) as-value])->"value")

            :local txPackets (([/tool snmp-get address=$targetIP community=$community version=2c oid=("1.3.6.1.2.1.2.2.1.17.".$idx) as-value])->"value")

            :if (!$first) do={
                :set interfaces ($interfaces . ",")
            }

            :set first false

            :set interfaces ($interfaces . "{")
            :set interfaces ($interfaces . "\"if_index\":\"".$idx."\",")
            :set interfaces ($interfaces . "\"if_name\":\"".$ifName."\",")
            :set interfaces ($interfaces . "\"status\":\"".$ifStatus."\",")
            :set interfaces ($interfaces . "\"rx_bytes\":\"".$rxBytes."\",")
            :set interfaces ($interfaces . "\"tx_bytes\":\"".$txBytes."\",")
            :set interfaces ($interfaces . "\"rx_packets\":\"".$rxPackets."\",")
            :set interfaces ($interfaces . "\"tx_packets\":\"".$txPackets."\"")
            :set interfaces ($interfaces . "}")

        } on-error={
            :log warning ("Interface Poll Failed: " . $idx)
        }
    }

    :set interfaces ($interfaces . "]")
BLOCK;
    }

    /**
     * @return Collection<int, ServicePoint>
     */
    public function servicePointsForDevice(Device $device): Collection
    {
        if ($device->service) {
            return $device->service->points()
                ->where('status', ServicePoint::STATUS_ACTIVE)
                ->orderBy('name')
                ->get();
        }

        if ($device->vendor) {
            return ServicePoint::query()
                ->where('service_id', $device->vendor->service_id)
                ->where('status', ServicePoint::STATUS_ACTIVE)
                ->orderBy('name')
                ->get();
        }

        return collect();
    }

    /**
     * @param  Collection<int, ServicePoint>  $servicePoints
     * @param  Collection<int, ServicePointCode>  $codesByPointId
     * @return array<int, array{name: string, slug: string, code: string|null, chunks: array<int, string>, variable: string}>
     */
    public function servicePointRows(Collection $servicePoints, Collection $codesByPointId): array
    {
        return $servicePoints->map(function (ServicePoint $point) use ($codesByPointId) {
            $code = $codesByPointId->get($point->id);

            return [
                'name' => $point->name,
                'slug' => $point->slug,
                'code' => $code?->code,
                'chunks' => $this->chunkTokens($point),
                'variable' => $this->variableName($point),
            ];
        })->values()->all();
    }

    private function variableName(ServicePoint $point): string
    {
        if (isset($this->legacyVarMap[$point->slug])) {
            return $this->legacyVarMap[$point->slug];
        }

        if (isset($this->legacyVarMap[$point->name])) {
            return $this->legacyVarMap[$point->name];
        }

        return Str::of($point->slug)
            ->replace('-', '_')
            ->lower()
            ->toString();
    }

    private function jsonKey(ServicePoint $point): string
    {
        return $point->slug ?: Str::replace(' ', '_', $point->name);
    }

    private function isPingPoint(ServicePoint $point): bool
    {
        return in_array(strtolower($point->slug), ['ping_status', 'ping-status'], true)
            || strcasecmp($point->name, 'Ping Status') === 0;
    }

    /**
     * @param  array<int, string>  $indexes
     */
    public function formatIfIndexes(array $indexes): string
    {
        $parts = array_map(
            fn (string $index) => '"'.$this->escape($index).'"',
            $indexes
        );

        return '{'.implode(';', $parts).'}';
    }

    /**
     * @return array<int, string>
     */
    public function parseInterfaceIndexes(string $input): array
    {
        return collect(preg_split('/[\s,;]+/', trim($input)))
            ->filter(fn ($value) => $value !== '' && preg_match('/^\d+$/', $value))
            ->values()
            ->all();
    }

    public function publicRelativePath(int $deviceId): string
    {
        return 'scripts/device-'.$deviceId.'.rsc';
    }

    public function readPublishedScript(int $deviceId): ?string
    {
        $path = public_path($this->publicRelativePath($deviceId));

        return is_file($path) ? file_get_contents($path) : null;
    }

    private function cleanupScript(string $script): string
    {
        $script = preg_replace("/\r\n|\r/", "\n", $script) ?? $script;
        $script = preg_replace("/\n{3,}/", "\n\n", $script) ?? $script;

        return trim($script)."\n";
    }

    private function escape(string $value): string
    {
        return str_replace(['\\', '"'], ['\\\\', '\\"'], $value);
    }
}
