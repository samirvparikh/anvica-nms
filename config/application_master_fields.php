<?php

/**
 * Maps model columns to application_masters.type keys.
 * Column names use *_id suffix; values are stored as application_masters.id.
 */
return [
    'assets' => [
        'asset_type_id' => 'asset_type',
        'asset_category_id' => 'asset_category',
        'status_id' => 'asset_status',
        'asset_group_id' => 'asset_group',
        'criticality_id' => 'criticality',
        'availability_requirement_id' => 'availability_requirement',
        'manufacturer_id' => 'manufacturer',
        'snmp_version_id' => 'snmp_version',
        'region_id' => 'region',
        'state_id' => 'state',
        'city_id' => 'city',
        'site_location_id' => 'site_location',
        'rack_id' => 'rack',
        'rack_unit_id' => 'rack_unit',
        'zone_id' => 'zone',
        'warranty_status_id' => 'warranty_status',
        'amc_status_id' => 'amc_status',
        'sla_policy_id' => 'sla_policy',
        'service_name_id' => 'service_name',
        'business_unit_id' => 'business_unit',
        'sla_availability_id' => 'sla_availability',
        'response_sla_id' => 'response_sla',
        'resolution_sla_id' => 'resolution_sla',
        'escalation_sla_id' => 'escalation_sla',
    ],
];
