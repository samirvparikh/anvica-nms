<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\SlaPolicy;
use Carbon\Carbon;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function assetsIndex(Request $request)
    {
        $query = Device::query();

        // Sorting
        $sort = $request->query('sort', 'created_at');
        $dir = $request->query('direction', 'desc');
        if (in_array($sort, ['asset_id', 'name', 'ip_address', 'status', 'created_at'])) {
            $query->orderBy($sort, $dir === 'asc' ? 'asc' : 'desc');
        }

        // Filtering
        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        $assets = $query->get();

        return view('inventory.assets.index', compact('assets', 'sort', 'dir'));
    }

    public function assetGroupsIndex()
    {
        $groups = [
            (object)['name' => 'Core Network Devices', 'count' => Device::count(), 'description' => 'Core routers, switches, and edge gateways.'],
            (object)['name' => 'Security Devices', 'count' => 0, 'description' => 'Next-gen Firewalls, IPS, and VPN concentrators.'],
            (object)['name' => 'Servers & Virtualization', 'count' => 0, 'description' => 'Physical hypervisors, standard baremetal nodes.']
        ];
        return view('inventory.asset-groups.index', compact('groups'));
    }

    public function softwareIndex()
    {
        $softwares = [
            (object)['name' => 'Cisco IOS-XE Firmware', 'version' => '17.9.4a', 'vendor' => 'Cisco Systems', 'count' => Device::count(), 'license' => 'Enterprise License Agreement'],
            (object)['name' => 'Palo Alto PAN-OS', 'version' => '10.2.4-h2', 'vendor' => 'Palo Alto Networks', 'count' => 0, 'license' => 'Subscription (Expires Dec 2026)']
        ];
        return view('inventory.software.index', compact('softwares'));
    }

    public function warrantyIndex(Request $request)
    {
        $devices = Device::all();
        $policies = SlaPolicy::all();
        return view('inventory.warranty.index', compact('devices', 'policies'));
    }

    public function warrantyStore(Request $request)
    {
        $request->validate([
            'device_id' => 'required|exists:devices,id',
            'warranty_type' => 'nullable|string',
            'warranty_provider' => 'nullable|string',
        ]);

        $device = Device::findOrFail($request->input('device_id'));

        $device->update([
            'asset_id' => $request->input('asset_id', $device->asset_id ?? 'AST-' . mt_rand(10000, 99999)),
            'asset_name' => $request->input('asset_name', $device->name),
            'manufacturer' => $request->input('manufacturer'),
            'model_number' => $request->input('model_number'),
            'serial_number' => $request->input('serial_number'),
            
            // Warranty
            'warranty_type' => $request->input('warranty_type'),
            'warranty_provider' => $request->input('warranty_provider'),
            'warranty_support_level' => $request->input('warranty_support_level'),
            'warranty_status' => $request->input('warranty_status'),
            'warranty_start_date' => $request->filled('warranty_start_date') ? Carbon::parse($request->input('warranty_start_date')) : null,
            'warranty_end_date' => $request->filled('warranty_end_date') ? Carbon::parse($request->input('warranty_end_date')) : null,
            'warranty_duration_years' => (int) $request->input('warranty_duration_years'),
            'warranty_onsite_support' => $request->has('warranty_onsite_support'),
            'warranty_parts_coverage' => $request->input('warranty_parts_coverage'),
            'warranty_labor_coverage' => $request->input('warranty_labor_coverage'),
            'warranty_transferable' => $request->has('warranty_transferable'),
            'warranty_terms' => $request->input('warranty_terms'),
            
            // AMC
            'amc_available' => $request->has('amc_available'),
            'amc_type' => $request->input('amc_type'),
            'amc_provider' => $request->input('amc_provider'),
            'amc_support_level' => $request->input('amc_support_level'),
            'amc_start_date' => $request->filled('amc_start_date') ? Carbon::parse($request->input('amc_start_date')) : null,
            'amc_end_date' => $request->filled('amc_end_date') ? Carbon::parse($request->input('amc_end_date')) : null,
            'amc_duration_years' => (int) $request->input('amc_duration_years'),
            'amc_response_time' => $request->input('amc_response_time'),
            'amc_resolution_time' => $request->input('amc_resolution_time'),
            'amc_escalation_time' => $request->input('amc_escalation_time'),
            'amc_coverage' => $request->input('amc_coverage'),
            'amc_terms' => $request->input('amc_terms'),
            
            // Financials
            'purchase_order_no' => $request->input('purchase_order_no'),
            'invoice_no' => $request->input('invoice_no'),
            'purchase_date' => $request->filled('purchase_date') ? Carbon::parse($request->input('purchase_date')) : null,
            'invoice_date' => $request->filled('invoice_date') ? Carbon::parse($request->input('invoice_date')) : null,
            'warranty_cost' => (float) $request->input('warranty_cost', 0.0),
            'amc_cost' => (float) $request->input('amc_cost', 0.0),
            'currency' => $request->input('currency', 'INR'),
            'tax' => (float) $request->input('tax', 0.0),
            'total_amc_cost' => (float) $request->input('total_amc_cost', 0.0),
            
            // SLA
            'customer_sla_policy' => $request->input('customer_sla_policy'),
            'availability_sla' => (float) $request->input('availability_sla', 99.95),
            'response_sla' => $request->input('response_sla'),
            'resolution_sla' => $request->input('resolution_sla'),
            
            // Renewal
            'renewal_reminder' => $request->input('renewal_reminder'),
            'amc_renewal_reminder' => $request->input('amc_renewal_reminder'),
            'warranty_expiry_alert' => $request->has('warranty_expiry_alert'),
            'amc_expiry_alert' => $request->has('amc_expiry_alert'),
            'notification_recipients' => $request->input('notification_recipients'),
            
            // Ownership
            'asset_owner' => $request->input('asset_owner'),
            'custodian' => $request->input('custodian'),
            'responsible_person' => $request->input('responsible_person'),
            'contact_number' => $request->input('contact_number'),
            'additional_notes' => $request->input('additional_notes'),
        ]);

        return redirect()->route('inventory.warranty.index')->with('success', 'Warranty & AMC information updated successfully for device ' . $device->name . '.');
    }
}
