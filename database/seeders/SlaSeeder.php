<?php

namespace Database\Seeders;

use App\Models\Device;
use App\Models\SlaPolicy;
use App\Models\Ticket;
use App\Models\SlaBreach;
use App\Models\MaintenanceWindow;
use App\Models\Role;
use App\Models\User;
use App\Support\ApplicationMasterHelper;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class SlaSeeder extends Seeder
{
    public function run(): void
    {
        // 1. SLA Policies
        $gold = SlaPolicy::updateOrCreate(
            ['name' => 'Gold SLA Policy'],
            [
                'description' => 'SLA for Critical and High priority systems',
                'response_time_minutes' => 15,
                'resolution_time_minutes' => 120,
                'escalation_time_minutes' => 30,
                'max_tickets_per_day' => 100,
                'max_changes_per_week' => 20
            ]
        );

        $standard = SlaPolicy::updateOrCreate(
            ['name' => 'Standard SLA Policy'],
            [
                'description' => 'SLA for Medium and Low priority systems',
                'response_time_minutes' => 60,
                'resolution_time_minutes' => 480,
                'escalation_time_minutes' => 120,
                'max_tickets_per_day' => 50,
                'max_changes_per_week' => 10
            ]
        );

        // Fetch users
        $admin = User::query()
            ->whereHas('assignedRole', fn ($query) => $query->whereIn('slug', [Role::SLUG_ADMIN, Role::SLUG_SUPERADMIN]))
            ->first();
        $user = User::query()
            ->whereHas('assignedRole', fn ($query) => $query->where('is_staff', true))
            ->first() ?? $admin;

        // 2. Enrich devices with warranty, AMC, and SLA demo data (current assets schema).
        $devices = Device::all();
        $i = 1;
        foreach ($devices as $device) {
            $payload = array_filter([
                'model_number' => $i % 2 === 0 ? 'ISR-4331/K9' : 'Catalyst-3850',
                'site_location_id' => ApplicationMasterHelper::resolveId('site_location', 'Mumbai DC'),
                'rack_id' => ApplicationMasterHelper::resolveId('rack', 'Rack-0'.min($i, 3)),
                'status_id' => ApplicationMasterHelper::resolveId('asset_status', 'Active'),
                'manufacturer_id' => ApplicationMasterHelper::resolveId('manufacturer', 'Cisco'),
                'warranty_status_id' => ApplicationMasterHelper::resolveId('warranty_status', 'Active'),
                'warranty_start_date' => Carbon::now()->subYears(2),
                'warranty_end_date' => Carbon::now()->addYear(),
                'amc_status_id' => ApplicationMasterHelper::resolveId('amc_status', 'Active'),
                'amc_start_date' => Carbon::now()->subYear(),
                'amc_end_date' => Carbon::now()->addYears(2),
                'purchase_order_no' => 'PO-2026-90'.$i,
                'invoice_no' => 'INV-098'.$i,
                'purchase_date' => Carbon::now()->subYears(2),
                'cost' => 150000.00,
                'sla_policy_id' => ApplicationMasterHelper::resolveId('sla_policy', 'Gold SLA'),
                'sla_availability_id' => ApplicationMasterHelper::resolveId('sla_availability', '99.95%'),
                'response_sla_id' => ApplicationMasterHelper::resolveId('response_sla', '15 Minutes'),
                'resolution_sla_id' => ApplicationMasterHelper::resolveId('resolution_sla', '2 Hours'),
                'escalation_sla_id' => ApplicationMasterHelper::resolveId('escalation_sla', '30 Minutes'),
                'asset_owner' => 'Anvica Networks',
                'custodian_department' => 'IT Ops Mumbai',
                'responsible_person' => 'Vijay Kumar',
                'contact_number' => '+91 9888877777',
            ], static fn ($value) => $value !== null && $value !== '');

            $device->update($payload);
            $i++;
        }

        // Fetch a primary device
        $primaryDevice = Device::first();

        if ($primaryDevice) {
            // 3. Seed Tickets / Incidents
            $inc1 = Ticket::updateOrCreate(
                ['ticket_number' => 'INC-1024'],
                [
                    'type' => 'incident',
                    'title' => 'VPN Connectivity Issue - Mumbai DC',
                    'description' => 'Persistent tunnel reset alerts on core IPSec tunnels connecting Mumbai to Chennai.',
                    'status' => 'in_progress',
                    'priority' => 'critical',
                    'impact' => 'critical',
                    'urgency' => 'critical',
                    'source' => 'NMS Alarm',
                    'customer_id' => $user->id,
                    'assigned_to' => $admin->id,
                    'device_id' => $primaryDevice->id,
                    'sla_policy_id' => $gold->id,
                    'response_sla_deadline' => Carbon::now()->addMinutes($gold->response_time_minutes),
                    'resolution_sla_deadline' => Carbon::now()->addMinutes($gold->resolution_time_minutes),
                    'contact_person' => 'Vijay Kumar',
                    'contact_number' => '+91 9888877777',
                    'sub_category' => 'VPN Tunnel Down',
                    'service_impacted' => 'Corporate VPN Portal',
                    'ci_service' => 'Cisco-ISR-4331',
                    'affected_users' => 120,
                    'business_impact' => 'Remote engineers unable to access dev environments.',
                ]
            );

            $inc2 = Ticket::updateOrCreate(
                ['ticket_number' => 'INC-1021'],
                [
                    'type' => 'incident',
                    'title' => 'High Packet Loss - Delhi DC',
                    'description' => 'Ping statistics show over 18% packet drops on WAN interface of Delhi perimeter firewall.',
                    'status' => 'new',
                    'priority' => 'high',
                    'impact' => 'high',
                    'urgency' => 'high',
                    'source' => 'Manual',
                    'customer_id' => $user->id,
                    'assigned_to' => null,
                    'device_id' => $primaryDevice->id,
                    'sla_policy_id' => $gold->id,
                    'response_sla_deadline' => Carbon::now()->addMinutes($gold->response_time_minutes),
                    'resolution_sla_deadline' => Carbon::now()->addMinutes($gold->resolution_time_minutes),
                    'contact_person' => 'Samir Patel',
                    'contact_number' => '+91 9999988888',
                    'sub_category' => 'WAN Packet Drop',
                    'service_impacted' => 'Perimeter Internet',
                    'ci_service' => 'PaloAlto-5220',
                    'affected_users' => 350,
                    'business_impact' => 'Slow database sync between Delhi and Mumbai.',
                ]
            );

            // 4. Seed SLA Breaches
            SlaBreach::updateOrCreate(
                ['ticket_id' => $inc1->id, 'type' => 'response'],
                [
                    'breached_at' => Carbon::now()->subMinutes(10),
                ]
            );

            // 5. Seed Problems
            Ticket::updateOrCreate(
                ['ticket_number' => 'PRB-2026-001'],
                [
                    'type' => 'problem',
                    'title' => 'Repeated Core-Router-01 Connectivity Failures',
                    'description' => 'Investigate recurring interface flap incidents on interface GigabitEthernet0/0/1.',
                    'status' => 'assigned',
                    'priority' => 'high',
                    'customer_id' => $user->id,
                    'assigned_to' => $admin->id,
                    'device_id' => $primaryDevice->id,
                    'sla_policy_id' => $standard->id,
                ]
            );

            // 6. Seed Changes (RFC)
            Ticket::updateOrCreate(
                ['ticket_number' => 'CHG-2026-0001'],
                [
                    'type' => 'change',
                    'title' => 'Core Router-01 Firmware Upgrade',
                    'description' => 'Upgrade Cisco IOS-XE firmware to version 17.9.4a to resolve critical security vulnerability.',
                    'status' => 'new',
                    'priority' => 'high',
                    'customer_id' => $user->id,
                    'assigned_to' => $admin->id,
                    'device_id' => $primaryDevice->id,
                    'sla_policy_id' => $standard->id,
                    'change_category' => 'Firmware Upgrade',
                    'risk_description' => 'Minimal risk; switch redundancy active during swap.',
                    'impact_on_sla' => true,
                    'rollback_plan' => 'TFTP restore previous IOS image from flash.',
                    'change_planned_start' => Carbon::now()->addDays(2)->setTime(23, 0, 0),
                    'change_planned_end' => Carbon::now()->addDays(3)->setTime(1, 0, 0),
                    'planned_downtime' => true,
                    'change_window' => 'Night Window (22:00 - 02:00)',
                ]
            );

            // 7. Seed Maintenance Windows
            MaintenanceWindow::updateOrCreate(
                ['maintenance_id' => 'PM-2026-00045'],
                [
                    'title' => 'Core Router Firmware Upgrade',
                    'type' => 'Preventive',
                    'category' => 'Network',
                    'primary_device_id' => $primaryDevice->id,
                    'start_time' => Carbon::now()->addDays(2)->setTime(23, 0, 0),
                    'end_time' => Carbon::now()->addDays(3)->setTime(1, 0, 0),
                    'expected_downtime_minutes' => 120,
                    'exclude_sla' => true,
                    'sla_impact' => 'No Breach (Maintenance)',
                    'sla_policy' => $gold->name,
                    'notify_before_minutes' => 120,
                    'requested_by' => 'NMS Admin',
                    'approved_noc_manager' => 'Vijay Kumar',
                    'approved_it_head' => 'Rajesh Sharma',
                    'customer_approval' => 'Approved',
                    'status' => 'scheduled',
                ]
            );

            MaintenanceWindow::updateOrCreate(
                ['maintenance_id' => 'PM-2026-00048'],
                [
                    'title' => 'Firewall Rules Clean-up & Sync',
                    'type' => 'Preventive',
                    'category' => 'Security',
                    'primary_device_id' => $primaryDevice->id,
                    'start_time' => Carbon::now()->addDays(5)->setTime(22, 0, 0),
                    'end_time' => Carbon::now()->addDays(5)->setTime(23, 0, 0),
                    'expected_downtime_minutes' => 60,
                    'exclude_sla' => true,
                    'sla_impact' => 'No Breach (Maintenance)',
                    'sla_policy' => $standard->name,
                    'notify_before_minutes' => 120,
                    'requested_by' => 'NMS Admin',
                    'approved_noc_manager' => 'Vijay Kumar',
                    'approved_it_head' => 'Rajesh Sharma',
                    'customer_approval' => 'Pending',
                    'status' => 'scheduled',
                ]
            );
        }
    }
}
