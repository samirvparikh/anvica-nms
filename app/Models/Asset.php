<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Asset extends Model
{
    use HasFactory;

    protected $fillable = [
        // 1. Asset Information
        'asset_name',
        'asset_type',
        'asset_category',
        'status',
        'asset_id_auto',
        'asset_group',
        'criticality',
        'availability_requirement',

        // 2. Asset Identification
        'manufacturer',
        'model_number',
        'serial_number',
        'part_number',
        'firmware_version',
        'hardware_version',
        'mac_address',
        'ean_imei',

        // 3. Network Information
        'management_ip',
        'hostname',
        'snmp_version',
        'snmp_community_user',
        'read_community',
        'write_community',
        'ssh_enabled',
        'telnet_enabled',
        'auto_discover_snmp',
        'auto_import_interfaces',
        'auto_import_software',
        'auto_import_config_backup',

        // 4. Location Information
        'customer_id',
        'region',
        'state',
        'city',
        'site_location',
        'building_floor',
        'rack',
        'rack_unit',
        'address',
        'gps_coordinates',
        'zone',

        // 5. Vendor & Purchase Information
        'vendor',
        'supplier_reseller',
        'purchase_order_no',
        'invoice_no',
        'purchase_date',
        'installation_date',
        'commissioning_date',
        'cost',

        // 6. Warranty & AMC Information
        'warranty_status',
        'warranty_start_date',
        'warranty_end_date',
        'amc_status',
        'amc_start_date',
        'amc_end_date',

        // 7. SLA & Business Mapping
        'sla_policy',
        'service_name',
        'business_unit',
        'sla_availability',
        'response_sla',
        'resolution_sla',
        'escalation_sla',
        'business_impact',

        // 8. Monitoring & Health Configuration
        'cpu_utilization_threshold',
        'memory_utilization_threshold',
        'packet_loss_threshold',
        'temperature_threshold',
        'health_monitoring',
        'health_score_calculation',
        'polling_interval',
        'alert_profile',

        // 9. Ownership & Responsibility
        'asset_owner',
        'custodian_department',
        'responsible_person',
        'contact_number',
        'email_id',
        'escalation_group',
        'notification_group',

        // 10. Attachments & Notes
        'attachment_path',
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
}
