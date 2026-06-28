<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicationMaster extends Model
{
    public const STATUS_ACTIVE = 'Active';

    public const STATUS_INACTIVE = 'Inactive';

    protected $fillable = [
        'type',
        'name',
        'value',
        'description',
        'sort_order',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function typeLabels(): array
    {
        return [
            'manufacturer' => 'Manufacturer',
            'criticality' => 'Criticality',
            'asset_type' => 'Asset Type',
            'asset_category' => 'Asset Category',
            'asset_status' => 'Asset Status',
            'asset_group' => 'Asset Group',
            'availability_requirement' => 'Availability Requirement',
            'snmp_version' => 'SNMP Version',
            'region' => 'Region',
            'state' => 'State',
            'city' => 'City',
            'site_location' => 'Site Location',
            'rack' => 'Rack',
            'rack_unit' => 'Rack Unit',
            'zone' => 'Network Zone',
            'sla_policy' => 'SLA Policy',
            'service_name' => 'Service Name',
            'business_unit' => 'Business Unit',
            'sla_availability' => 'SLA Availability',
            'response_sla' => 'Response SLA',
            'resolution_sla' => 'Resolution SLA',
            'escalation_sla' => 'Escalation SLA',
            'warranty_status' => 'Warranty Status',
            'amc_status' => 'AMC Status',
            'ticket_priority' => 'Ticket Priority',
            'incident_impact' => 'Incident Impact',
            'incident_urgency' => 'Incident Urgency',
        ];
    }

    /**
     * Master types sorted by display label (A–Z).
     *
     * @return array<string, string>
     */
    public static function sortedTypeLabels(): array
    {
        $labels = self::typeLabels();
        asort($labels, SORT_NATURAL | SORT_FLAG_CASE);

        return $labels;
    }

    public static function typeLabel(string $type): string
    {
        return self::typeLabels()[$type] ?? ucwords(str_replace('_', ' ', $type));
    }
}
