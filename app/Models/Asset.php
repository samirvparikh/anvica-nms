<?php

namespace App\Models;

use App\Models\Concerns\ResolvesApplicationMasters;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Asset extends Model
{
    use HasFactory;
    use ResolvesApplicationMasters;

    protected $fillable = [
        'asset_name',
        'asset_type_id',
        'asset_category_id',
        'status_id',
        'asset_id_auto',
        'asset_group_id',
        'criticality_id',
        'availability_requirement_id',
        'model_number',
        'serial_number',
        'part_number',
        'firmware_version',
        'hardware_version',
        'mac_address',
        'ean_imei',
        'manufacturer_id',
        'management_ip',
        'hostname',
        'snmp_version_id',
        'snmp_community_user',
        'read_community',
        'write_community',
        'snmp_port',
        'service_id',
        'vendor_id',
        'api_url',
        'api_username',
        'api_password',
        'health_status',
        'last_seen',
        'ssh_enabled',
        'telnet_enabled',
        'auto_discover_snmp',
        'auto_import_interfaces',
        'auto_import_software',
        'auto_import_config_backup',
        'customer_id',
        'region_id',
        'state_id',
        'city_id',
        'site_location_id',
        'building_floor',
        'rack_id',
        'rack_unit_id',
        'address',
        'gps_coordinates',
        'zone_id',
        'vendor',
        'supplier_reseller',
        'purchase_order_no',
        'invoice_no',
        'purchase_date',
        'installation_date',
        'commissioning_date',
        'cost',
        'warranty_status_id',
        'warranty_start_date',
        'warranty_end_date',
        'amc_status_id',
        'amc_start_date',
        'amc_end_date',
        'sla_policy_id',
        'service_name_id',
        'business_unit_id',
        'sla_availability_id',
        'response_sla_id',
        'resolution_sla_id',
        'escalation_sla_id',
        'business_impact',
        'cpu_utilization_threshold',
        'memory_utilization_threshold',
        'packet_loss_threshold',
        'temperature_threshold',
        'health_monitoring',
        'health_score_calculation',
        'polling_interval',
        'alert_profile',
        'asset_owner',
        'custodian_department',
        'responsible_person',
        'contact_number',
        'email_id',
        'escalation_group',
        'notification_group',
        'attachment_path',
        'backup_path',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'ssh_enabled' => 'boolean',
            'telnet_enabled' => 'boolean',
            'auto_discover_snmp' => 'boolean',
            'auto_import_interfaces' => 'boolean',
            'auto_import_software' => 'boolean',
            'auto_import_config_backup' => 'boolean',
            'health_monitoring' => 'boolean',
            'health_score_calculation' => 'boolean',
            'purchase_date' => 'date',
            'installation_date' => 'date',
            'commissioning_date' => 'date',
            'warranty_start_date' => 'date',
            'warranty_end_date' => 'date',
            'amc_start_date' => 'date',
            'amc_end_date' => 'date',
            'cost' => 'decimal:2',
            'snmp_port' => 'integer',
            'last_seen' => 'datetime',
            'cpu_utilization_threshold' => 'integer',
            'memory_utilization_threshold' => 'integer',
            'packet_loss_threshold' => 'integer',
            'temperature_threshold' => 'integer',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function deviceVendor(): BelongsTo
    {
        return $this->belongsTo(DeviceVendor::class, 'vendor_id');
    }
}
