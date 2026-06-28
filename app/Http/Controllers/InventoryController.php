<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ValidatesApplicationMasterFields;
use App\Models\Device;
use App\Models\SlaPolicy;
use App\Models\Asset;
use App\Models\User;
use App\Models\DeviceVendor;
use Carbon\Carbon;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    use ValidatesApplicationMasterFields;

    public function assetsIndex(Request $request)
    {
        $user = auth()->user();
        if ($user->isAdmin()) {
            $query = Asset::query();
        } else {
            $query = Asset::where('customer_id', $user->id);
        }

        // Sorting
        $sort = $request->query('sort', 'created_at');
        $dir = $request->query('direction', 'desc');
        if (in_array($sort, ['asset_id_auto', 'asset_name', 'management_ip', 'manufacturer', 'model_number', 'serial_number', 'status', 'created_at'])) {
            $query->orderBy($sort, $dir === 'asc' ? 'asc' : 'desc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // Filtering
        if ($request->filled('status')) {
            $statusId = is_numeric($request->query('status'))
                ? (int) $request->query('status')
                : \App\Support\ApplicationMasterHelper::resolveId('asset_status', $request->query('status'));

            if ($statusId) {
                $query->where('status_id', $statusId);
            }
        }

        $assets = $query->get();

        return view('inventory.assets.index', compact('assets', 'sort', 'dir'));
    }

    public function assetsCreate()
    {
        $user = auth()->user();
        if ($user->isAdmin()) {
            $users = User::all();
        } else {
            $users = User::where('id', $user->id)->get();
        }
        $vendors = DeviceVendor::with('service')->where('status', DeviceVendor::STATUS_ACTIVE)->orderBy('name')->get();
        return view('inventory.assets.create', compact('users', 'vendors'));
    }

    public function assetsStore(Request $request)
    {
        $user = auth()->user();
        if (!$user->isAdmin()) {
            $request->merge(['customer_id' => $user->id]);
        }

        $request->validate(
            array_merge([
                'asset_name' => 'required|string|max:255',
                'model_number' => 'required|string|max:255',
                'serial_number' => 'required|string|max:255|unique:assets,serial_number',
                'management_ip' => 'required|ip',
                'customer_id' => 'required|exists:users,id',
                'attachment' => 'nullable|file|max:20480',
                'backup_file' => 'nullable|file|max:20480',
            ], $this->applicationMasterRules([
                'asset_type_id',
                'asset_category_id',
                'status_id',
                'criticality_id',
                'manufacturer_id',
                'site_location_id',
                'sla_policy_id',
                'service_name_id',
            ])),
            [],
            [
                'asset_name' => 'asset name',
                'model_number' => 'model number',
                'serial_number' => 'serial number',
                'management_ip' => 'management IP',
                'customer_id' => 'customer',
                'asset_type_id' => 'asset type',
                'asset_category_id' => 'asset category',
                'status_id' => 'status',
                'criticality_id' => 'criticality',
                'manufacturer_id' => 'manufacturer',
                'site_location_id' => 'site / location',
                'sla_policy_id' => 'SLA policy',
                'service_name_id' => 'service name',
            ]
        );

        $data = $request->except(['attachment', 'backup_file', 'ssh_enabled', 'telnet_enabled', 'auto_discover_snmp', 'auto_import_interfaces', 'auto_import_software', 'auto_import_config_backup', 'health_monitoring', 'health_score_calculation']);

        // Checkbox toggles mapping
        $data['ssh_enabled'] = $request->has('ssh_enabled');
        $data['telnet_enabled'] = $request->has('telnet_enabled');
        $data['auto_discover_snmp'] = $request->has('auto_discover_snmp');
        $data['auto_import_interfaces'] = $request->has('auto_import_interfaces');
        $data['auto_import_software'] = $request->has('auto_import_software');
        $data['auto_import_config_backup'] = $request->has('auto_import_config_backup');
        $data['health_monitoring'] = $request->has('health_monitoring');
        $data['health_score_calculation'] = $request->has('health_score_calculation');

        // Autogenerate Asset ID (e.g. AST-2026-0001)
        $year = date('Y');
        $count = Asset::whereYear('created_at', $year)->count() + 1;
        $data['asset_id_auto'] = sprintf('AST-%s-%04d', $year, $count);

        // Attachment file upload
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/attachments'), $filename);
            $data['attachment_path'] = 'uploads/attachments/' . $filename;
        }

        // Backup file upload
        if ($request->hasFile('backup_file')) {
            $file = $request->file('backup_file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/backups'), $filename);
            $data['backup_path'] = 'uploads/backups/' . $filename;
        }

        Asset::create($data);

        return redirect()->route('inventory.assets.index')->with('success', 'Asset created successfully.');
    }

    public function assetsEdit($id)
    {
        $user = auth()->user();
        if ($user->isAdmin()) {
            $asset = Asset::findOrFail($id);
            $users = User::all();
        } else {
            $asset = Asset::where('customer_id', $user->id)->findOrFail($id);
            $users = User::where('id', $user->id)->get();
        }
        $vendors = DeviceVendor::with('service')->where('status', DeviceVendor::STATUS_ACTIVE)->orderBy('name')->get();
        return view('inventory.assets.edit', compact('asset', 'users', 'vendors'));
    }

    public function assetsUpdate(Request $request, $id)
    {
        $user = auth()->user();
        if ($user->isAdmin()) {
            $asset = Asset::findOrFail($id);
        } else {
            $asset = Asset::where('customer_id', $user->id)->findOrFail($id);
            $request->merge(['customer_id' => $user->id]);
        }

        $request->validate(array_merge([
            'asset_name' => 'required|string|max:255',
            'model_number' => 'required|string|max:255',
            'serial_number' => 'required|string|max:255|unique:assets,serial_number,'.$asset->id,
            'management_ip' => 'required|ip',
            'customer_id' => 'required|exists:users,id',
            'attachment' => 'nullable|file|max:20480',
            'backup_file' => 'nullable|file|max:20480',
        ], $this->applicationMasterRules([
            'asset_type_id',
            'asset_category_id',
            'status_id',
            'criticality_id',
            'manufacturer_id',
            'site_location_id',
            'sla_policy_id',
            'service_name_id',
        ])));

        $data = $request->except(['attachment', 'backup_file', 'ssh_enabled', 'telnet_enabled', 'auto_discover_snmp', 'auto_import_interfaces', 'auto_import_software', 'auto_import_config_backup', 'health_monitoring', 'health_score_calculation']);

        // Checkbox toggles mapping
        $data['ssh_enabled'] = $request->has('ssh_enabled');
        $data['telnet_enabled'] = $request->has('telnet_enabled');
        $data['auto_discover_snmp'] = $request->has('auto_discover_snmp');
        $data['auto_import_interfaces'] = $request->has('auto_import_interfaces');
        $data['auto_import_software'] = $request->has('auto_import_software');
        $data['auto_import_config_backup'] = $request->has('auto_import_config_backup');
        $data['health_monitoring'] = $request->has('health_monitoring');
        $data['health_score_calculation'] = $request->has('health_score_calculation');

        // Attachment file upload
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/attachments'), $filename);

            // Delete old attachment if it exists
            if ($asset->attachment_path && file_exists(public_path($asset->attachment_path))) {
                @unlink(public_path($asset->attachment_path));
            }

            $data['attachment_path'] = 'uploads/attachments/' . $filename;
        }

        // Backup file upload
        if ($request->hasFile('backup_file')) {
            $file = $request->file('backup_file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/backups'), $filename);

            // Delete old backup if it exists
            if ($asset->backup_path && file_exists(public_path($asset->backup_path))) {
                @unlink(public_path($asset->backup_path));
            }

            $data['backup_path'] = 'uploads/backups/' . $filename;
        }

        $asset->update($data);

        return redirect()->route('inventory.assets.index')->with('success', 'Asset updated successfully.');
    }

    public function assetsDestroy($id)
    {
        $user = auth()->user();
        if ($user->isAdmin()) {
            $asset = Asset::findOrFail($id);
        } else {
            $asset = Asset::where('customer_id', $user->id)->findOrFail($id);
        }

        // Delete attachment if it exists
        if ($asset->attachment_path && file_exists(public_path($asset->attachment_path))) {
            @unlink(public_path($asset->attachment_path));
        }

        // Delete backup if it exists
        if ($asset->backup_path && file_exists(public_path($asset->backup_path))) {
            @unlink(public_path($asset->backup_path));
        }

        $asset->delete();

        return redirect()->route('inventory.assets.index')->with('success', 'Asset deleted successfully.');
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
