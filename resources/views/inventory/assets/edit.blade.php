@extends('layouts.app')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1>Edit Asset</h1>
        <p>Modify and update information for asset: <strong>{{ $asset->asset_name }}</strong> ({{ $asset->asset_id_auto }})</p>
    </div>
    <a href="{{ route('inventory.assets.index') }}" class="btn-secondary" style="height: 38px; display: inline-flex; align-items: center; padding: 0 1rem; border-radius: 6px; text-decoration: none; border: 1px solid var(--border-color); color: var(--text-muted); font-size: 0.85rem;">
        <i class="fa-solid fa-arrow-left" style="margin-right: 0.5rem;"></i> Back to Assets
    </a>
</div>

<form action="{{ route('inventory.assets.update', $asset->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div style="display: flex; flex-direction: column; gap: 1.5rem; max-width: 1200px; margin: 0 auto;">
        
        <!-- 1. Asset Information -->
        <div style="background: white; border-radius: 12px; padding: 1.75rem; box-shadow: var(--card-shadow);">
            <h3 style="font-size: 1.05rem; font-weight: 700; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem; color: var(--text-dark); display: flex; align-items: center; gap: 0.5rem;">
                <i class="fa-solid fa-circle-info" style="color: var(--primary);"></i> 1. Asset Information
            </h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem;">
                <div class="form-group">
                    <label for="asset_name" style="font-weight: 600;">Asset Name <span style="color: var(--status-down);">*</span></label>
                    <input type="text" name="asset_name" id="asset_name" class="form-control" placeholder="e.g. Core-Router-01" required value="{{ old('asset_name', $asset->asset_name) }}">
                </div>
                <div class="form-group">
                    <label for="asset_type" style="font-weight: 600;">Asset Type <span style="color: var(--status-down);">*</span></label>
                    <select name="asset_type" id="asset_type" class="form-control" required>
                        <option value="">Select Type</option>
                        <option value="Router" {{ old('asset_type', $asset->asset_type) == 'Router' ? 'selected' : '' }}>Router</option>
                        <option value="Switch" {{ old('asset_type', $asset->asset_type) == 'Switch' ? 'selected' : '' }}>Switch</option>
                        <option value="Server" {{ old('asset_type', $asset->asset_type) == 'Server' ? 'selected' : '' }}>Server</option>
                        <option value="Firewall" {{ old('asset_type', $asset->asset_type) == 'Firewall' ? 'selected' : '' }}>Firewall</option>
                        <option value="Access Point" {{ old('asset_type', $asset->asset_type) == 'Access Point' ? 'selected' : '' }}>Access Point</option>
                        <option value="Controller" {{ old('asset_type', $asset->asset_type) == 'Controller' ? 'selected' : '' }}>Controller</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="asset_category" style="font-weight: 600;">Asset Category <span style="color: var(--status-down);">*</span></label>
                    <select name="asset_category" id="asset_category" class="form-control" required>
                        <option value="">Select Category</option>
                        <option value="Network Infrastructure" {{ old('asset_category', $asset->asset_category) == 'Network Infrastructure' ? 'selected' : '' }}>Network Infrastructure</option>
                        <option value="Security Infrastructure" {{ old('asset_category', $asset->asset_category) == 'Security Infrastructure' ? 'selected' : '' }}>Security Infrastructure</option>
                        <option value="Server Infrastructure" {{ old('asset_category', $asset->asset_category) == 'Server Infrastructure' ? 'selected' : '' }}>Server Infrastructure</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="status" style="font-weight: 600;">Status <span style="color: var(--status-down);">*</span></label>
                    <select name="status" id="status" class="form-control" required>
                        <option value="Active" {{ old('status', $asset->status) == 'Active' ? 'selected' : '' }}>Active</option>
                        <option value="Inactive" {{ old('status', $asset->status) == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="Maintenance" {{ old('status', $asset->status) == 'Maintenance' ? 'selected' : '' }}>Maintenance</option>
                    </select>
                </div>
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem; margin-top: 1.25rem;">
                <div class="form-group">
                    <label style="font-weight: 600;">Asset ID (Auto)</label>
                    <input type="text" class="form-control" readonly disabled value="{{ $asset->asset_id_auto }}" style="background-color: var(--border-color); cursor: not-allowed;">
                </div>
                <div class="form-group">
                    <label for="asset_group" style="font-weight: 600;">Asset Group</label>
                    <select name="asset_group" id="asset_group" class="form-control">
                        <option value="Core Network" {{ old('asset_group', $asset->asset_group) == 'Core Network' ? 'selected' : '' }}>Core Network</option>
                        <option value="Distribution Network" {{ old('asset_group', $asset->asset_group) == 'Distribution Network' ? 'selected' : '' }}>Distribution Network</option>
                        <option value="Access Network" {{ old('asset_group', $asset->asset_group) == 'Access Network' ? 'selected' : '' }}>Access Network</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="criticality" style="font-weight: 600;">Criticality <span style="color: var(--status-down);">*</span></label>
                    <select name="criticality" id="criticality" class="form-control" required>
                        <option value="Critical" {{ old('criticality', $asset->criticality) == 'Critical' ? 'selected' : '' }}>Critical</option>
                        <option value="High" {{ old('criticality', $asset->criticality) == 'High' ? 'selected' : '' }}>High</option>
                        <option value="Medium" {{ old('criticality', $asset->criticality) == 'Medium' ? 'selected' : '' }}>Medium</option>
                        <option value="Low" {{ old('criticality', $asset->criticality) == 'Low' ? 'selected' : '' }}>Low</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="availability_requirement" style="font-weight: 600;">Availability Requirement</label>
                    <select name="availability_requirement" id="availability_requirement" class="form-control">
                        <option value="24 x 7" {{ old('availability_requirement', $asset->availability_requirement) == '24 x 7' ? 'selected' : '' }}>24 x 7</option>
                        <option value="9 x 5" {{ old('availability_requirement', $asset->availability_requirement) == '9 x 5' ? 'selected' : '' }}>9 x 5</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- 2. Asset Identification -->
        <div style="background: white; border-radius: 12px; padding: 1.75rem; box-shadow: var(--card-shadow);">
            <h3 style="font-size: 1.05rem; font-weight: 700; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem; color: var(--text-dark); display: flex; align-items: center; gap: 0.5rem;">
                <i class="fa-solid fa-id-card" style="color: #3b82f6;"></i> 2. Asset Identification
            </h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem;">
                <div class="form-group">
                    <label for="manufacturer" style="font-weight: 600;">Manufacturer <span style="color: var(--status-down);">*</span></label>
                    <select name="manufacturer" id="manufacturer" class="form-control" required>
                        <option value="">Select Manufacturer</option>
                        <option value="Cisco" {{ old('manufacturer', $asset->manufacturer) == 'Cisco' ? 'selected' : '' }}>Cisco</option>
                        <option value="HP" {{ old('manufacturer', $asset->manufacturer) == 'HP' ? 'selected' : '' }}>HP</option>
                        <option value="Dell" {{ old('manufacturer', $asset->manufacturer) == 'Dell' ? 'selected' : '' }}>Dell</option>
                        <option value="Palo Alto" {{ old('manufacturer', $asset->manufacturer) == 'Palo Alto' ? 'selected' : '' }}>Palo Alto</option>
                        <option value="Fortinet" {{ old('manufacturer', $asset->manufacturer) == 'Fortinet' ? 'selected' : '' }}>Fortinet</option>
                        <option value="Juniper" {{ old('manufacturer', $asset->manufacturer) == 'Juniper' ? 'selected' : '' }}>Juniper</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="model_number" style="font-weight: 600;">Model Number <span style="color: var(--status-down);">*</span></label>
                    <input type="text" name="model_number" id="model_number" class="form-control" placeholder="e.g. ISR 4331" required value="{{ old('model_number', $asset->model_number) }}">
                </div>
                <div class="form-group">
                    <label for="serial_number" style="font-weight: 600;">Serial Number <span style="color: var(--status-down);">*</span></label>
                    <input type="text" name="serial_number" id="serial_number" class="form-control" placeholder="e.g. FGL2148X2YZ" required value="{{ old('serial_number', $asset->serial_number) }}">
                </div>
                <div class="form-group">
                    <label for="part_number" style="font-weight: 600;">Part Number</label>
                    <input type="text" name="part_number" id="part_number" class="form-control" placeholder="e.g. ISR4331/K9" value="{{ old('part_number', $asset->part_number) }}">
                </div>
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem; margin-top: 1.25rem;">
                <div class="form-group">
                    <label for="firmware_version" style="font-weight: 600;">Firmware Version</label>
                    <input type="text" name="firmware_version" id="firmware_version" class="form-control" placeholder="e.g. 17.06.04" value="{{ old('firmware_version', $asset->firmware_version) }}">
                </div>
                <div class="form-group">
                    <label for="hardware_version" style="font-weight: 600;">Hardware Version</label>
                    <input type="text" name="hardware_version" id="hardware_version" class="form-control" placeholder="e.g. V01" value="{{ old('hardware_version', $asset->hardware_version) }}">
                </div>
                <div class="form-group">
                    <label for="mac_address" style="font-weight: 600;">MAC Address</label>
                    <input type="text" name="mac_address" id="mac_address" class="form-control" placeholder="e.g. 00:11:22:33:44:55" value="{{ old('mac_address', $asset->mac_address) }}">
                </div>
                <div class="form-group">
                    <label for="ean_imei" style="font-weight: 600;">EAN / IMEI (if any)</label>
                    <input type="text" name="ean_imei" id="ean_imei" class="form-control" placeholder="Enter EAN / IMEI" value="{{ old('ean_imei', $asset->ean_imei) }}">
                </div>
            </div>
        </div>

        <!-- 3. Network Information -->
        <div style="background: white; border-radius: 12px; padding: 1.75rem; box-shadow: var(--card-shadow);">
            <h3 style="font-size: 1.05rem; font-weight: 700; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem; color: var(--text-dark); display: flex; align-items: center; gap: 0.5rem;">
                <i class="fa-solid fa-network-wired" style="color: #10b981;"></i> 3. Network Information
            </h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem;">
                <div class="form-group">
                    <label for="management_ip" style="font-weight: 600;">Management IP <span style="color: var(--status-down);">*</span></label>
                    <input type="text" name="management_ip" id="management_ip" class="form-control" placeholder="e.g. 10.10.1.1" required value="{{ old('management_ip', $asset->management_ip) }}">
                </div>
                <div class="form-group">
                    <label for="hostname" style="font-weight: 600;">Hostname</label>
                    <input type="text" name="hostname" id="hostname" class="form-control" placeholder="e.g. Core-Router-01" value="{{ old('hostname', $asset->hostname) }}">
                </div>
                <div class="form-group">
                    <label for="snmp_version" style="font-weight: 600;">SNMP Version</label>
                    <select name="snmp_version" id="snmp_version" class="form-control">
                        <option value="">Select SNMP Version</option>
                        <option value="v1" {{ old('snmp_version', $asset->snmp_version) == 'v1' ? 'selected' : '' }}>v1</option>
                        <option value="v2c" {{ old('snmp_version', $asset->snmp_version) == 'v2c' ? 'selected' : '' }}>v2c</option>
                        <option value="v3" {{ old('snmp_version', $asset->snmp_version) == 'v3' ? 'selected' : '' }}>v3</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="snmp_community_user" style="font-weight: 600;">SNMP Community / User</label>
                    <input type="text" name="snmp_community_user" id="snmp_community_user" class="form-control" placeholder="e.g. Anvica_NMS" value="{{ old('snmp_community_user', $asset->snmp_community_user) }}">
                </div>
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem; margin-top: 1.25rem;">
                <div class="form-group">
                    <label for="read_community" style="font-weight: 600;">Read Community</label>
                    <input type="text" name="read_community" id="read_community" class="form-control" placeholder="e.g. Anvica_NMS" value="{{ old('read_community', $asset->read_community) }}">
                </div>
                <div class="form-group">
                    <label for="write_community" style="font-weight: 600;">Write Community</label>
                    <div style="position: relative; display: flex; align-items: center;">
                        <input type="password" name="write_community" id="write_community" class="form-control" placeholder="••••••••••••" value="{{ old('write_community', $asset->write_community) }}" style="padding-right: 2.5rem; width: 100%;">
                        <i class="fa-solid fa-eye" id="toggleWriteCommunity" style="position: absolute; right: 0.75rem; cursor: pointer; color: var(--text-muted);"></i>
                    </div>
                </div>
                <div class="form-group" style="display: flex; flex-direction: column; justify-content: center; gap: 0.25rem;">
                    <label style="font-weight: 600;">SSH Enabled</label>
                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-top: 0.25rem;">
                        <input type="checkbox" name="ssh_enabled" id="ssh_enabled" value="1" {{ old('ssh_enabled', $asset->ssh_enabled) ? 'checked' : '' }}>
                        <span style="font-size: 0.85rem; color: var(--text-muted);">Enable SSH Connection</span>
                    </div>
                </div>
                <div class="form-group" style="display: flex; flex-direction: column; justify-content: center; gap: 0.25rem;">
                    <label style="font-weight: 600;">Telnet Enabled</label>
                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-top: 0.25rem;">
                        <input type="checkbox" name="telnet_enabled" id="telnet_enabled" value="1" {{ old('telnet_enabled', $asset->telnet_enabled) ? 'checked' : '' }}>
                        <span style="font-size: 0.85rem; color: var(--text-muted);">Enable Telnet Connection</span>
                    </div>
                </div>
            </div>

            <!-- Toggles row -->
            <div style="background-color: var(--bg-up); border: 1px solid rgba(59, 130, 246, 0.1); border-radius: 8px; padding: 1rem; margin-top: 1.5rem; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <input type="checkbox" name="auto_discover_snmp" id="auto_discover_snmp" value="1" {{ old('auto_discover_snmp', $asset->auto_discover_snmp) ? 'checked' : '' }}>
                    <label for="auto_discover_snmp" style="font-weight: 600; margin-bottom: 0; cursor: pointer; font-size: 0.85rem;">Auto Discover via SNMP</label>
                </div>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <input type="checkbox" name="auto_import_interfaces" id="auto_import_interfaces" value="1" {{ old('auto_import_interfaces', $asset->auto_import_interfaces) ? 'checked' : '' }}>
                    <label for="auto_import_interfaces" style="font-weight: 600; margin-bottom: 0; cursor: pointer; font-size: 0.85rem;">Auto Import Interfaces</label>
                </div>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <input type="checkbox" name="auto_import_software" id="auto_import_software" value="1" {{ old('auto_import_software', $asset->auto_import_software) ? 'checked' : '' }}>
                    <label for="auto_import_software" style="font-weight: 600; margin-bottom: 0; cursor: pointer; font-size: 0.85rem;">Auto Import Software</label>
                </div>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <input type="checkbox" name="auto_import_config_backup" id="auto_import_config_backup" value="1" {{ old('auto_import_config_backup', $asset->auto_import_config_backup) ? 'checked' : '' }}>
                    <label for="auto_import_config_backup" style="font-weight: 600; margin-bottom: 0; cursor: pointer; font-size: 0.85rem;">Auto Import Config Backup</label>
                </div>
            </div>
        </div>

        <!-- 4. Location Information -->
        <div style="background: white; border-radius: 12px; padding: 1.75rem; box-shadow: var(--card-shadow);">
            <h3 style="font-size: 1.05rem; font-weight: 700; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem; color: var(--text-dark); display: flex; align-items: center; gap: 0.5rem;">
                <i class="fa-solid fa-map-location-dot" style="color: #f59e0b;"></i> 4. Location Information
            </h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem;">
                <div class="form-group">
                    <label for="customer_id" style="font-weight: 600;">Customer / Organization <span style="color: var(--status-down);">*</span></label>
                    @if(auth()->user()->isAdmin())
                        <select name="customer_id" id="customer_id" class="form-control" required>
                            <option value="">Select Customer</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('customer_id', $asset->customer_id) == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    @else
                        <input type="hidden" name="customer_id" id="customer_id" value="{{ auth()->id() }}">
                        <input type="text" class="form-control" value="{{ auth()->user()->name }}" readonly disabled style="cursor: not-allowed; background-color: var(--border-color); opacity: 0.85;">
                    @endif
                </div>
                <div class="form-group">
                    <label for="region" style="font-weight: 600;">Region</label>
                    <select name="region" id="region" class="form-control">
                        <option value="West Zone" {{ old('region', $asset->region) == 'West Zone' ? 'selected' : '' }}>West Zone</option>
                        <option value="East Zone" {{ old('region', $asset->region) == 'East Zone' ? 'selected' : '' }}>East Zone</option>
                        <option value="North Zone" {{ old('region', $asset->region) == 'North Zone' ? 'selected' : '' }}>North Zone</option>
                        <option value="South Zone" {{ old('region', $asset->region) == 'South Zone' ? 'selected' : '' }}>South Zone</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="state" style="font-weight: 600;">State</label>
                    <select name="state" id="state" class="form-control">
                        <option value="Gujarat" {{ old('state', $asset->state) == 'Gujarat' ? 'selected' : '' }}>Gujarat</option>
                        <option value="Maharashtra" {{ old('state', $asset->state) == 'Maharashtra' ? 'selected' : '' }}>Maharashtra</option>
                        <option value="Delhi" {{ old('state', $asset->state) == 'Delhi' ? 'selected' : '' }}>Delhi</option>
                        <option value="Karnataka" {{ old('state', $asset->state) == 'Karnataka' ? 'selected' : '' }}>Karnataka</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="city" style="font-weight: 600;">City</label>
                    <select name="city" id="city" class="form-control">
                        <option value="Ahmedabad" {{ old('city', $asset->city) == 'Ahmedabad' ? 'selected' : '' }}>Ahmedabad</option>
                        <option value="Mumbai" {{ old('city', $asset->city) == 'Mumbai' ? 'selected' : '' }}>Mumbai</option>
                        <option value="Pune" {{ old('city', $asset->city) == 'Pune' ? 'selected' : '' }}>Pune</option>
                        <option value="Bangalore" {{ old('city', $asset->city) == 'Bangalore' ? 'selected' : '' }}>Bangalore</option>
                    </select>
                </div>
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem; margin-top: 1.25rem;">
                <div class="form-group">
                    <label for="site_location" style="font-weight: 600;">Site / Location <span style="color: var(--status-down);">*</span></label>
                    <select name="site_location" id="site_location" class="form-control" required>
                        <option value="Ahmedabad DC" {{ old('site_location', $asset->site_location) == 'Ahmedabad DC' ? 'selected' : '' }}>Ahmedabad DC</option>
                        <option value="Mumbai DC" {{ old('site_location', $asset->site_location) == 'Mumbai DC' ? 'selected' : '' }}>Mumbai DC</option>
                        <option value="Bangalore DC" {{ old('site_location', $asset->site_location) == 'Bangalore DC' ? 'selected' : '' }}>Bangalore DC</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="building_floor" style="font-weight: 600;">Building / Floor</label>
                    <input type="text" name="building_floor" id="building_floor" class="form-control" placeholder="e.g. Data Center - 1" value="{{ old('building_floor', $asset->building_floor) }}">
                </div>
                <div class="form-group">
                    <label for="rack" style="font-weight: 600;">Rack</label>
                    <select name="rack" id="rack" class="form-control">
                        <option value="Rack-01" {{ old('rack', $asset->rack) == 'Rack-01' ? 'selected' : '' }}>Rack-01</option>
                        <option value="Rack-02" {{ old('rack', $asset->rack) == 'Rack-02' ? 'selected' : '' }}>Rack-02</option>
                        <option value="Rack-03" {{ old('rack', $asset->rack) == 'Rack-03' ? 'selected' : '' }}>Rack-03</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="rack_unit" style="font-weight: 600;">Rack Unit (U)</label>
                    <select name="rack_unit" id="rack_unit" class="form-control">
                        <option value="U-12" {{ old('rack_unit', $asset->rack_unit) == 'U-12' ? 'selected' : '' }}>U-12</option>
                        <option value="U-13" {{ old('rack_unit', $asset->rack_unit) == 'U-13' ? 'selected' : '' }}>U-13</option>
                        <option value="U-14" {{ old('rack_unit', $asset->rack_unit) == 'U-14' ? 'selected' : '' }}>U-14</option>
                        <option value="U-15" {{ old('rack_unit', $asset->rack_unit) == 'U-15' ? 'selected' : '' }}>U-15</option>
                    </select>
                </div>
            </div>
            <div style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 1.25rem; margin-top: 1.25rem;">
                <div class="form-group">
                    <label for="address" style="font-weight: 600;">Address</label>
                    <input type="text" name="address" id="address" class="form-control" placeholder="Address..." value="{{ old('address', $asset->address) }}">
                </div>
                <div class="form-group">
                    <label for="gps_coordinates" style="font-weight: 600;">GPS Coordinates</label>
                    <input type="text" name="gps_coordinates" id="gps_coordinates" class="form-control" placeholder="23.0225, 72.5714" value="{{ old('gps_coordinates', $asset->gps_coordinates) }}">
                </div>
                <div class="form-group">
                    <label for="zone" style="font-weight: 600;">Zone</label>
                    <select name="zone" id="zone" class="form-control">
                        <option value="DC Network" {{ old('zone', $asset->zone) == 'DC Network' ? 'selected' : '' }}>DC Network</option>
                        <option value="DMZ" {{ old('zone', $asset->zone) == 'DMZ' ? 'selected' : '' }}>DMZ</option>
                        <option value="LAN" {{ old('zone', $asset->zone) == 'LAN' ? 'selected' : '' }}>LAN</option>
                        <option value="WAN" {{ old('zone', $asset->zone) == 'WAN' ? 'selected' : '' }}>WAN</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- 5. Vendor & Purchase Information -->
        <div style="background: white; border-radius: 12px; padding: 1.75rem; box-shadow: var(--card-shadow);">
            <h3 style="font-size: 1.05rem; font-weight: 700; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem; color: var(--text-dark); display: flex; align-items: center; gap: 0.5rem;">
                <i class="fa-solid fa-money-bill-wave" style="color: #6366f1;"></i> 5. Vendor & Purchase Information
            </h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem;">
                <div class="form-group">
                    <label for="vendor" style="font-weight: 600;">Vendor <span style="color: var(--status-down);">*</span></label>
                    <select name="vendor" id="vendor" class="form-control" required>
                        <option value="">Select Vendor</option>
                        @foreach($vendors as $vendor)
                            <option value="{{ $vendor->name }}" {{ old('vendor', $asset->vendor) == $vendor->name ? 'selected' : '' }}>
                                {{ $vendor->name }}@if($vendor->service) ({{ $vendor->service->name }})@endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="supplier_reseller" style="font-weight: 600;">Supplier / Reseller</label>
                    <input type="text" name="supplier_reseller" id="supplier_reseller" class="form-control" placeholder="e.g. ABC Technologies Pvt. Ltd." value="{{ old('supplier_reseller', $asset->supplier_reseller) }}">
                </div>
                <div class="form-group">
                    <label for="purchase_order_no" style="font-weight: 600;">Purchase Order No.</label>
                    <input type="text" name="purchase_order_no" id="purchase_order_no" class="form-control" placeholder="PO-2026-1001" value="{{ old('purchase_order_no', $asset->purchase_order_no) }}">
                </div>
                <div class="form-group">
                    <label for="invoice_no" style="font-weight: 600;">Invoice No.</label>
                    <input type="text" name="invoice_no" id="invoice_no" class="form-control" placeholder="INV-2026-101" value="{{ old('invoice_no', $asset->invoice_no) }}">
                </div>
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem; margin-top: 1.25rem;">
                <div class="form-group">
                    <label for="purchase_date" style="font-weight: 600;">Purchase Date</label>
                    <input type="date" name="purchase_date" id="purchase_date" class="form-control" value="{{ old('purchase_date', $asset->purchase_date ? $asset->purchase_date->format('Y-m-d') : '') }}">
                </div>
                <div class="form-group">
                    <label for="installation_date" style="font-weight: 600;">Installation Date</label>
                    <input type="date" name="installation_date" id="installation_date" class="form-control" value="{{ old('installation_date', $asset->installation_date ? $asset->installation_date->format('Y-m-d') : '') }}">
                </div>
                <div class="form-group">
                    <label for="commissioning_date" style="font-weight: 600;">Commissioning Date</label>
                    <input type="date" name="commissioning_date" id="commissioning_date" class="form-control" value="{{ old('commissioning_date', $asset->commissioning_date ? $asset->commissioning_date->format('Y-m-d') : '') }}">
                </div>
                <div class="form-group">
                    <label for="cost" style="font-weight: 600;">Cost (INR)</label>
                    <input type="number" name="cost" id="cost" class="form-control" min="0" step="0.01" placeholder="3,45,000.00" value="{{ old('cost', $asset->cost) }}">
                </div>
            </div>
        </div>

        <!-- 6. Warranty & AMC Information -->
        <div style="background: white; border-radius: 12px; padding: 1.75rem; box-shadow: var(--card-shadow);">
            <h3 style="font-size: 1.05rem; font-weight: 700; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem; color: var(--text-dark); display: flex; align-items: center; gap: 0.5rem;">
                <i class="fa-solid fa-shield-halved" style="color: #ec4899;"></i> 6. Warranty & AMC Information
            </h3>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                <!-- Warranty sub-column -->
                <div style="border-right: 1px solid var(--border-color); padding-right: 2rem;">
                    <h4 style="font-size: 0.95rem; font-weight: 700; color: var(--status-up); margin-bottom: 1rem;">Warranty</h4>
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label for="warranty_status" style="font-weight: 600;">Warranty Status</label>
                        <select name="warranty_status" id="warranty_status" class="form-control">
                            <option value="Active" {{ old('warranty_status', $asset->warranty_status) == 'Active' ? 'selected' : '' }}>Active</option>
                            <option value="Expired" {{ old('warranty_status', $asset->warranty_status) == 'Expired' ? 'selected' : '' }}>Expired</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label for="warranty_start_date" style="font-weight: 600;">Start Date</label>
                        <input type="date" name="warranty_start_date" id="warranty_start_date" class="form-control" value="{{ old('warranty_start_date', $asset->warranty_start_date ? $asset->warranty_start_date->format('Y-m-d') : '') }}">
                    </div>
                    <div class="form-group">
                        <label for="warranty_end_date" style="font-weight: 600;">End Date</label>
                        <input type="date" name="warranty_end_date" id="warranty_end_date" class="form-control" value="{{ old('warranty_end_date', $asset->warranty_end_date ? $asset->warranty_end_date->format('Y-m-d') : '') }}">
                    </div>
                </div>

                <!-- AMC sub-column -->
                <div>
                    <h4 style="font-size: 0.95rem; font-weight: 700; color: #8b5cf6; margin-bottom: 1rem;">AMC / Support Contract</h4>
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label for="amc_status" style="font-weight: 600;">AMC Status</label>
                        <select name="amc_status" id="amc_status" class="form-control">
                            <option value="Active" {{ old('amc_status', $asset->amc_status) == 'Active' ? 'selected' : '' }}>Active</option>
                            <option value="Expired" {{ old('amc_status', $asset->amc_status) == 'Expired' ? 'selected' : '' }}>Expired</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label for="amc_start_date" style="font-weight: 600;">Start Date</label>
                        <input type="date" name="amc_start_date" id="amc_start_date" class="form-control" value="{{ old('amc_start_date', $asset->amc_start_date ? $asset->amc_start_date->format('Y-m-d') : '') }}">
                    </div>
                    <div class="form-group">
                        <label for="amc_end_date" style="font-weight: 600;">End Date</label>
                        <input type="date" name="amc_end_date" id="amc_end_date" class="form-control" value="{{ old('amc_end_date', $asset->amc_end_date ? $asset->amc_end_date->format('Y-m-d') : '') }}">
                    </div>
                </div>
            </div>
        </div>

        <!-- 7. SLA & Business Mapping -->
        <div style="background: white; border-radius: 12px; padding: 1.75rem; box-shadow: var(--card-shadow);">
            <h3 style="font-size: 1.05rem; font-weight: 700; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem; color: var(--text-dark); display: flex; align-items: center; gap: 0.5rem;">
                <i class="fa-solid fa-briefcase" style="color: #6366f1;"></i> 7. SLA & Business Mapping
            </h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem;">
                <div class="form-group">
                    <label for="sla_policy" style="font-weight: 600;">SLA Policy <span style="color: var(--status-down);">*</span></label>
                    <select name="sla_policy" id="sla_policy" class="form-control" required>
                        <option value="Gold SLA" {{ old('sla_policy', $asset->sla_policy) == 'Gold SLA' ? 'selected' : '' }}>Gold SLA</option>
                        <option value="Silver SLA" {{ old('sla_policy', $asset->sla_policy) == 'Silver SLA' ? 'selected' : '' }}>Silver SLA</option>
                        <option value="Bronze SLA" {{ old('sla_policy', $asset->sla_policy) == 'Bronze SLA' ? 'selected' : '' }}>Bronze SLA</option>
                        <option value="Standard SLA" {{ old('sla_policy', $asset->sla_policy) == 'Standard SLA' ? 'selected' : '' }}>Standard SLA</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="service_name" style="font-weight: 600;">Service Name <span style="color: var(--status-down);">*</span></label>
                    <select name="service_name" id="service_name" class="form-control" required>
                        <option value="Corporate WAN" {{ old('service_name', $asset->service_name) == 'Corporate WAN' ? 'selected' : '' }}>Corporate WAN</option>
                        <option value="Internet Leased Line" {{ old('service_name', $asset->service_name) == 'Internet Leased Line' ? 'selected' : '' }}>Internet Leased Line</option>
                        <option value="MPLS Link" {{ old('service_name', $asset->service_name) == 'MPLS Link' ? 'selected' : '' }}>MPLS Link</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="business_unit" style="font-weight: 600;">Business Unit</label>
                    <select name="business_unit" id="business_unit" class="form-control">
                        <option value="IT Operations" {{ old('business_unit', $asset->business_unit) == 'IT Operations' ? 'selected' : '' }}>IT Operations</option>
                        <option value="Security Operations" {{ old('business_unit', $asset->business_unit) == 'Security Operations' ? 'selected' : '' }}>Security Operations</option>
                        <option value="Finance" {{ old('business_unit', $asset->business_unit) == 'Finance' ? 'selected' : '' }}>Finance</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="sla_availability" style="font-weight: 600;">SLA Availability</label>
                    <select name="sla_availability" id="sla_availability" class="form-control">
                        <option value="99.99%" {{ old('sla_availability', $asset->sla_availability) == '99.99%' ? 'selected' : '' }}>99.99%</option>
                        <option value="99.95%" {{ old('sla_availability', $asset->sla_availability) == '99.95%' ? 'selected' : '' }}>99.95%</option>
                        <option value="99.9%" {{ old('sla_availability', $asset->sla_availability) == '99.9%' ? 'selected' : '' }}>99.9%</option>
                    </select>
                </div>
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem; margin-top: 1.25rem;">
                <div class="form-group">
                    <label for="response_sla" style="font-weight: 600;">Response SLA</label>
                    <select name="response_sla" id="response_sla" class="form-control">
                        <option value="15 Minutes" {{ old('response_sla', $asset->response_sla) == '15 Minutes' ? 'selected' : '' }}>15 Minutes</option>
                        <option value="30 Minutes" {{ old('response_sla', $asset->response_sla) == '30 Minutes' ? 'selected' : '' }}>30 Minutes</option>
                        <option value="1 Hour" {{ old('response_sla', $asset->response_sla) == '1 Hour' ? 'selected' : '' }}>1 Hour</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="resolution_sla" style="font-weight: 600;">Resolution SLA</label>
                    <select name="resolution_sla" id="resolution_sla" class="form-control">
                        <option value="2 Hours" {{ old('resolution_sla', $asset->resolution_sla) == '2 Hours' ? 'selected' : '' }}>2 Hours</option>
                        <option value="4 Hours" {{ old('resolution_sla', $asset->resolution_sla) == '4 Hours' ? 'selected' : '' }}>4 Hours</option>
                        <option value="8 Hours" {{ old('resolution_sla', $asset->resolution_sla) == '8 Hours' ? 'selected' : '' }}>8 Hours</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="escalation_sla" style="font-weight: 600;">Escalation SLA</label>
                    <select name="escalation_sla" id="escalation_sla" class="form-control">
                        <option value="30 Minutes" {{ old('escalation_sla', $asset->escalation_sla) == '30 Minutes' ? 'selected' : '' }}>30 Minutes</option>
                        <option value="1 Hour" {{ old('escalation_sla', $asset->escalation_sla) == '1 Hour' ? 'selected' : '' }}>1 Hour</option>
                        <option value="2 Hours" {{ old('escalation_sla', $asset->escalation_sla) == '2 Hours' ? 'selected' : '' }}>2 Hours</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="business_impact" style="font-weight: 600;">Business Impact</label>
                    <select name="business_impact" id="business_impact" class="form-control">
                        <option value="High" {{ old('business_impact', $asset->business_impact) == 'High' ? 'selected' : '' }}>High</option>
                        <option value="Medium" {{ old('business_impact', $asset->business_impact) == 'Medium' ? 'selected' : '' }}>Medium</option>
                        <option value="Low" {{ old('business_impact', $asset->business_impact) == 'Low' ? 'selected' : '' }}>Low</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- 8. Monitoring & Health Configuration -->
        <div style="background: white; border-radius: 12px; padding: 1.75rem; box-shadow: var(--card-shadow);">
            <h3 style="font-size: 1.05rem; font-weight: 700; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem; color: var(--text-dark); display: flex; align-items: center; gap: 0.5rem;">
                <i class="fa-solid fa-heart-pulse" style="color: var(--primary);"></i> 8. Monitoring & Health Configuration
            </h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem;">
                <div class="form-group">
                    <label for="cpu_utilization_threshold" style="font-weight: 600;">CPU Utilization Threshold (%)</label>
                    <input type="number" name="cpu_utilization_threshold" id="cpu_utilization_threshold" class="form-control" min="0" max="100" value="{{ old('cpu_utilization_threshold', $asset->cpu_utilization_threshold) }}">
                </div>
                <div class="form-group">
                    <label for="memory_utilization_threshold" style="font-weight: 600;">Memory Utilization Threshold (%)</label>
                    <input type="number" name="memory_utilization_threshold" id="memory_utilization_threshold" class="form-control" min="0" max="100" value="{{ old('memory_utilization_threshold', $asset->memory_utilization_threshold) }}">
                </div>
                <div class="form-group">
                    <label for="packet_loss_threshold" style="font-weight: 600;">Packet Loss Threshold (%)</label>
                    <input type="number" name="packet_loss_threshold" id="packet_loss_threshold" class="form-control" min="0" max="100" value="{{ old('packet_loss_threshold', $asset->packet_loss_threshold) }}">
                </div>
                <div class="form-group">
                    <label for="temperature_threshold" style="font-weight: 600;">Temperature Threshold (°C)</label>
                    <input type="number" name="temperature_threshold" id="temperature_threshold" class="form-control" min="0" max="150" value="{{ old('temperature_threshold', $asset->temperature_threshold) }}">
                </div>
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem; margin-top: 1.25rem;">
                <div class="form-group" style="display: flex; flex-direction: column; justify-content: center;">
                    <label style="font-weight: 600;">Health Monitoring</label>
                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-top: 0.25rem;">
                        <input type="checkbox" name="health_monitoring" id="health_monitoring" value="1" {{ old('health_monitoring', $asset->health_monitoring) ? 'checked' : '' }}>
                        <span style="font-size: 0.85rem; color: var(--text-muted);">Enable Health Monitoring</span>
                    </div>
                </div>
                <div class="form-group" style="display: flex; flex-direction: column; justify-content: center;">
                    <label style="font-weight: 600;">Health Score Calculation</label>
                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-top: 0.25rem;">
                        <input type="checkbox" name="health_score_calculation" id="health_score_calculation" value="1" {{ old('health_score_calculation', $asset->health_score_calculation) ? 'checked' : '' }}>
                        <span style="font-size: 0.85rem; color: var(--text-muted);">Calculate Health Score</span>
                    </div>
                </div>
                <div class="form-group">
                    <label for="polling_interval" style="font-weight: 600;">Polling Interval</label>
                    <select name="polling_interval" id="polling_interval" class="form-control">
                        <option value="1 Minute" {{ old('polling_interval', $asset->polling_interval) == '1 Minute' ? 'selected' : '' }}>1 Minute</option>
                        <option value="5 Minutes" {{ old('polling_interval', $asset->polling_interval) == '5 Minutes' ? 'selected' : '' }}>5 Minutes</option>
                        <option value="15 Minutes" {{ old('polling_interval', $asset->polling_interval) == '15 Minutes' ? 'selected' : '' }}>15 Minutes</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="alert_profile" style="font-weight: 600;">Alert Profile</label>
                    <select name="alert_profile" id="alert_profile" class="form-control">
                        <option value="Default" {{ old('alert_profile', $asset->alert_profile) == 'Default' ? 'selected' : '' }}>Default</option>
                        <option value="Critical Only" {{ old('alert_profile', $asset->alert_profile) == 'Critical Only' ? 'selected' : '' }}>Critical Only</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- 9. Ownership & Responsibility -->
        <div style="background: white; border-radius: 12px; padding: 1.75rem; box-shadow: var(--card-shadow);">
            <h3 style="font-size: 1.05rem; font-weight: 700; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem; color: var(--text-dark); display: flex; align-items: center; gap: 0.5rem;">
                <i class="fa-solid fa-users" style="color: #6366f1;"></i> 9. Ownership & Responsibility
            </h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem;">
                <div class="form-group">
                    <label for="asset_owner" style="font-weight: 600;">Asset Owner <span style="color: var(--status-down);">*</span></label>
                    <select name="asset_owner" id="asset_owner" class="form-control" required>
                        <option value="IT Head" {{ old('asset_owner', $asset->asset_owner) == 'IT Head' ? 'selected' : '' }}>IT Head</option>
                        <option value="VP Infrastructure" {{ old('asset_owner', $asset->asset_owner) == 'VP Infrastructure' ? 'selected' : '' }}>VP Infrastructure</option>
                        <option value="CIO" {{ old('asset_owner', $asset->asset_owner) == 'CIO' ? 'selected' : '' }}>CIO</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="custodian_department" style="font-weight: 600;">Custodian / Department <span style="color: var(--status-down);">*</span></label>
                    <select name="custodian_department" id="custodian_department" class="form-control" required>
                        <option value="Network Operations" {{ old('custodian_department', $asset->custodian_department) == 'Network Operations' ? 'selected' : '' }}>Network Operations</option>
                        <option value="IT Support" {{ old('custodian_department', $asset->custodian_department) == 'IT Support' ? 'selected' : '' }}>IT Support</option>
                        <option value="Security Operations" {{ old('custodian_department', $asset->custodian_department) == 'Security Operations' ? 'selected' : '' }}>Security Operations</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="responsible_person" style="font-weight: 600;">Responsible Person <span style="color: var(--status-down);">*</span></label>
                    <select name="responsible_person" id="responsible_person" class="form-control" required>
                        <option value="Vijay Kumar" {{ old('responsible_person', $asset->responsible_person) == 'Vijay Kumar' ? 'selected' : '' }}>Vijay Kumar</option>
                        <option value="Rohan Shah" {{ old('responsible_person', $asset->responsible_person) == 'Rohan Shah' ? 'selected' : '' }}>Rohan Shah</option>
                        <option value="Sanjay Mehta" {{ old('responsible_person', $asset->responsible_person) == 'Sanjay Mehta' ? 'selected' : '' }}>Sanjay Mehta</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="contact_number" style="font-weight: 600;">Contact Number</label>
                    <input type="text" name="contact_number" id="contact_number" class="form-control" placeholder="+91 98765 43210" value="{{ old('contact_number', $asset->contact_number) }}">
                </div>
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem; margin-top: 1.25rem;">
                <div class="form-group">
                    <label for="email_id" style="font-weight: 600;">Email ID</label>
                    <input type="email" name="email_id" id="email_id" class="form-control" placeholder="e.g. vijay.kumar@westernrail.in" value="{{ old('email_id', $asset->email_id) }}">
                </div>
                <div class="form-group">
                    <label Hoff="escalation_group" style="font-weight: 600;">Escalation Group</label>
                    <select name="escalation_group" id="escalation_group" class="form-control">
                        <option value="Network Operations Team" {{ old('escalation_group', $asset->escalation_group) == 'Network Operations Team' ? 'selected' : '' }}>Network Operations Team</option>
                        <option value="Desktop Support Team" {{ old('escalation_group', $asset->escalation_group) == 'Desktop Support Team' ? 'selected' : '' }}>Desktop Support Team</option>
                        <option value="Server Team" {{ old('escalation_group', $asset->escalation_group) == 'Server Team' ? 'selected' : '' }}>Server Team</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="notification_group" style="font-weight: 600;">Notification Group</label>
                    <select name="notification_group" id="notification_group" class="form-control">
                        <option value="NOC Team" {{ old('notification_group', $asset->notification_group) == 'NOC Team' ? 'selected' : '' }}>NOC Team</option>
                        <option value="SOC Team" {{ old('notification_group', $asset->notification_group) == 'SOC Team' ? 'selected' : '' }}>SOC Team</option>
                        <option value="Support Helpdesk" {{ old('notification_group', $asset->notification_group) == 'Support Helpdesk' ? 'selected' : '' }}>Support Helpdesk</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- 10. Attachments & Notes -->
        <div style="background: white; border-radius: 12px; padding: 1.75rem; box-shadow: var(--card-shadow);">
            <h3 style="font-size: 1.05rem; font-weight: 700; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem; color: var(--text-dark); display: flex; align-items: center; gap: 0.5rem;">
                <i class="fa-solid fa-paperclip" style="color: #64748b;"></i> 10. Attachments & Notes
            </h3>
            <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 2rem;">
                <div class="form-group">
                    <label for="attachment" style="font-weight: 600;">Attachments</label>
                    <input type="file" name="attachment" id="attachment" class="form-control">
                    @if($asset->attachment_path)
                        <div style="margin-top: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
                            <i class="fa-solid fa-file-arrow-down" style="color: var(--primary);"></i>
                            <a href="{{ asset($asset->attachment_path) }}" target="_blank" style="font-size: 0.85rem; color: var(--primary); text-decoration: none; font-weight: 600;">
                                View current attachment
                            </a>
                        </div>
                    @endif
                    <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.5rem;">
                        Max file size: 20MB. Accepted files: images, PDF, logs.
                    </p>
                </div>
                <div class="form-group">
                    <label for="notes" style="font-weight: 600;">Notes</label>
                    <textarea name="notes" id="notes" class="form-control" rows="4" placeholder="Enter any additional notes about this asset...">{{ old('notes', $asset->notes) }}</textarea>
                </div>
            </div>
        </div>

        <!-- Submit actions -->
        <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 1rem; margin-bottom: 3rem;">
            <a href="{{ route('inventory.assets.index') }}" class="btn-secondary" style="height: 42px; display: inline-flex; align-items: center; padding: 0 1.5rem; border-radius: 6px; text-decoration: none; border: 1px solid var(--border-color); color: var(--text-muted); font-weight: 600;">
                Cancel
            </a>
            <button type="submit" class="btn-add" style="height: 42px; padding: 0 2rem; font-weight: 700; border-radius: 6px;">
                Update Asset
            </button>
        </div>

    </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Toggle write community password visibility
        const toggleBtn = document.getElementById('toggleWriteCommunity');
        const passwordInput = document.getElementById('write_community');
        if (toggleBtn && passwordInput) {
            toggleBtn.addEventListener('click', function () {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                this.classList.toggle('fa-eye');
                this.classList.toggle('fa-eye-slash');
            });
        }
    });
</script>
@endsection
