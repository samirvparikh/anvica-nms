<?php

namespace App\Support;

use App\Models\Asset;
use Illuminate\Support\Str;

class DeviceAssetMapper
{
    /**
     * @return array<string, mixed>
     */
    public static function fromLegacyArray(array $device): array
    {
        $name = $device['name'] ?? $device['asset_name'] ?? 'Unnamed Asset';
        $ip = $device['ip_address'] ?? $device['management_ip'] ?? null;
        $type = $device['device_type'] ?? $device['type'] ?? $device['asset_type'] ?? 'Router';

        return self::buildAssetPayload(
            name: $name,
            ip: $ip,
            type: $type,
            customerId: $device['user_id'] ?? $device['customer_id'] ?? null,
            extras: array_merge($device, [
                'hostname' => $device['hostname'] ?? $name,
                'location' => $device['location'] ?? $device['site_location'] ?? null,
                'snmp_community_user' => $device['snmp_community'] ?? $device['snmp_community_user'] ?? null,
                'service_id' => $device['service_id'] ?? null,
                'vendor_id' => $device['vendor_id'] ?? null,
                'api_url' => $device['api_url'] ?? null,
                'api_username' => $device['api_username'] ?? null,
                'api_password' => $device['api_password'] ?? null,
                'snmp_version' => $device['snmp_version'] ?? '2c',
                'snmp_port' => $device['snmp_port'] ?? 161,
                'health_status' => $device['health_status'] ?? 'Up',
                'last_seen' => $device['last_seen'] ?? null,
                'status' => self::normalizeStatus($device['status'] ?? 'Active'),
            ])
        );
    }

    /**
     * @return array<string, mixed>
     */
    public static function fromDeviceRow(object $row): array
    {
        return self::fromLegacyArray([
            'name' => $row->name ?? null,
            'asset_name' => $row->asset_name ?? null,
            'hostname' => $row->hostname ?? null,
            'type' => $row->type ?? null,
            'device_type' => $row->device_type ?? null,
            'ip_address' => $row->ip_address ?? null,
            'management_ip' => $row->management_ip ?? null,
            'user_id' => $row->user_id ?? null,
            'customer_id' => $row->customer_id ?? null,
            'location' => $row->location ?? null,
            'site_location' => $row->site_location ?? null,
            'service_id' => $row->service_id ?? null,
            'vendor_id' => $row->vendor_id ?? null,
            'api_url' => $row->api_url ?? null,
            'api_username' => $row->api_username ?? null,
            'api_password' => $row->api_password ?? null,
            'snmp_version' => $row->snmp_version ?? '2c',
            'snmp_port' => $row->snmp_port ?? 161,
            'snmp_community' => $row->snmp_community ?? null,
            'snmp_community_user' => $row->snmp_community_user ?? null,
            'status' => $row->status ?? 'active',
            'health_status' => $row->health_status ?? 'Up',
            'last_seen' => $row->last_seen ?? null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public static function fromDeviceRowForLegacySchema(object $row): array
    {
        $name = $row->name ?? $row->asset_name ?? 'Unnamed Asset';
        $ip = $row->ip_address ?? $row->management_ip ?? '127.0.0.1';
        $type = $row->device_type ?? $row->type ?? 'Router';
        $year = date('Y');
        $sequence = Asset::whereYear('created_at', $year)->count() + 1;

        return array_filter([
            'asset_name' => $name,
            'asset_type' => $type,
            'asset_category' => 'Network Infrastructure',
            'status' => self::normalizeStatus($row->status ?? 'Active'),
            'asset_id_auto' => sprintf('AST-%s-%04d', $year, $sequence),
            'criticality' => 'Medium',
            'manufacturer' => 'Cisco',
            'model_number' => 'Generic',
            'serial_number' => 'SN-'.Str::upper(Str::random(10)),
            'management_ip' => $ip,
            'hostname' => $row->hostname ?? $name,
            'snmp_version' => $row->snmp_version ?? '2c',
            'snmp_port' => (int) ($row->snmp_port ?? 161),
            'snmp_community_user' => $row->snmp_community_user ?? $row->snmp_community ?? null,
            'customer_id' => $row->user_id ?? $row->customer_id ?? null,
            'site_location' => $row->site_location ?? $row->location ?? null,
            'service_id' => $row->service_id ?? null,
            'vendor_id' => $row->vendor_id ?? null,
            'api_url' => $row->api_url ?? null,
            'api_username' => $row->api_username ?? null,
            'api_password' => $row->api_password ?? null,
            'health_status' => $row->health_status ?? 'Up',
            'last_seen' => $row->last_seen ?? null,
            'health_monitoring' => true,
            'health_score_calculation' => true,
        ], static fn ($value) => $value !== null);
    }

    public static function resolveMasterId(string $type, ?string $value): ?int
    {
        return ApplicationMasterHelper::resolveId($type, $value);
    }

    /**
     * @param  array<string, mixed>  $extras
     * @return array<string, mixed>
     */
    protected static function buildAssetPayload(
        string $name,
        ?string $ip,
        string $type,
        ?int $customerId,
        array $extras = [],
    ): array {
        $year = date('Y');
        $sequence = Asset::whereYear('created_at', $year)->count() + 1;

        $payload = [
            'asset_name' => $name,
            'asset_type_id' => ApplicationMasterHelper::resolveId('asset_type', $type),
            'asset_category_id' => ApplicationMasterHelper::resolveId('asset_category', $extras['asset_category'] ?? 'Network Infrastructure'),
            'status_id' => ApplicationMasterHelper::resolveId('asset_status', self::normalizeStatus($extras['status'] ?? 'Active')),
            'asset_id_auto' => $extras['asset_id_auto'] ?? sprintf('AST-%s-%04d', $year, $sequence),
            'criticality_id' => ApplicationMasterHelper::resolveId('criticality', $extras['criticality'] ?? 'Medium'),
            'manufacturer_id' => ApplicationMasterHelper::resolveId('manufacturer', $extras['manufacturer'] ?? 'Cisco'),
            'model_number' => $extras['model_number'] ?? 'Generic',
            'serial_number' => $extras['serial_number'] ?? 'SN-'.Str::upper(Str::random(10)),
            'management_ip' => $ip ?? '127.0.0.1',
            'hostname' => $extras['hostname'] ?? $name,
            'snmp_version_id' => ApplicationMasterHelper::resolveId('snmp_version', $extras['snmp_version'] ?? '2c'),
            'snmp_port' => (int) ($extras['snmp_port'] ?? 161),
            'snmp_community_user' => $extras['snmp_community_user'] ?? $extras['snmp_community'] ?? null,
            'customer_id' => $customerId,
            'site_location_id' => ApplicationMasterHelper::resolveId('site_location', $extras['site_location'] ?? $extras['location'] ?? null),
            'service_id' => $extras['service_id'] ?? null,
            'vendor_id' => $extras['vendor_id'] ?? null,
            'api_url' => $extras['api_url'] ?? null,
            'api_username' => $extras['api_username'] ?? null,
            'api_password' => $extras['api_password'] ?? null,
            'health_status' => $extras['health_status'] ?? 'Up',
            'last_seen' => $extras['last_seen'] ?? null,
            'health_monitoring' => $extras['health_monitoring'] ?? true,
            'health_score_calculation' => $extras['health_score_calculation'] ?? true,
        ];

        return array_filter($payload, static fn ($value) => $value !== null);
    }

    public static function normalizeStatus(mixed $status): string
    {
        $value = strtolower((string) $status);

        return in_array($value, ['inactive', 'down'], true) ? 'Inactive' : 'Active';
    }
}
