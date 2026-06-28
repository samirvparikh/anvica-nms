<?php

namespace Database\Seeders;

use App\Models\ApplicationMaster;
use Illuminate\Database\Seeder;

class ApplicationMasterSeeder extends Seeder
{
    public function run(): void
    {
        $masters = [
            'manufacturer' => ['Cisco', 'HP', 'Dell', 'Palo Alto', 'Fortinet', 'Juniper', 'MikroTik', 'Huawei'],
            'criticality' => ['Critical', 'High', 'Medium', 'Low'],
            'asset_type' => ['Router', 'Switch', 'Server', 'Firewall', 'Access Point', 'Controller'],
            'asset_category' => ['Network Infrastructure', 'Security Infrastructure', 'Server Infrastructure'],
            'asset_status' => ['Active', 'Inactive', 'Maintenance'],
            'asset_group' => ['Core Network', 'Distribution Network', 'Access Network'],
            'availability_requirement' => ['24 x 7', '9 x 5'],
            'snmp_version' => ['v1', 'v2c', 'v3'],
            'region' => ['West Zone', 'East Zone', 'North Zone', 'South Zone'],
            'state' => ['Gujarat', 'Maharashtra', 'Delhi', 'Karnataka'],
            'city' => ['Ahmedabad', 'Mumbai', 'Pune', 'Bangalore'],
            'site_location' => ['Ahmedabad DC', 'Mumbai DC', 'Bangalore DC'],
            'rack' => ['Rack-01', 'Rack-02', 'Rack-03'],
            'rack_unit' => ['U-12', 'U-13', 'U-14', 'U-15'],
            'zone' => ['DC Network', 'DMZ', 'LAN', 'WAN'],
            'sla_policy' => ['Gold SLA', 'Silver SLA', 'Bronze SLA', 'Standard SLA'],
            'service_name' => ['Corporate WAN', 'Internet Leased Line', 'MPLS Link'],
            'business_unit' => ['IT Operations', 'Security Operations', 'Finance'],
            'sla_availability' => ['99.99%', '99.95%', '99.9%'],
            'response_sla' => ['15 Minutes', '30 Minutes', '1 Hour', '2 Hours', '4 Hours'],
            'resolution_sla' => ['2 Hours', '4 Hours', '8 Hours', '24 Hours'],
            'escalation_sla' => ['30 Minutes', '1 Hour', '2 Hours', '4 Hours'],
            'warranty_status' => ['Active', 'Expired'],
            'amc_status' => ['Active', 'Expired'],
            'ticket_priority' => ['Critical', 'High', 'Medium', 'Low'],
            'incident_impact' => ['Critical', 'High', 'Medium', 'Low'],
            'incident_urgency' => ['Critical', 'High', 'Medium', 'Low'],
        ];

        foreach ($masters as $type => $values) {
            foreach (array_values($values) as $index => $value) {
                ApplicationMaster::updateOrCreate(
                    [
                        'type' => $type,
                        'value' => $value,
                    ],
                    [
                        'name' => $value,
                        'sort_order' => $index + 1,
                        'status' => ApplicationMaster::STATUS_ACTIVE,
                    ]
                );
            }
        }
    }
}
