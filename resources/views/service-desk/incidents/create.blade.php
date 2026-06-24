@extends('layouts.app')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1>Create New Incident</h1>
        <p>Report and log a service degradation, outage, or hardware failure.</p>
    </div>
    <a href="{{ route('incidents.index') }}" class="btn-secondary" style="height: 38px; display: inline-flex; align-items: center; padding: 0 1rem; border-radius: 6px; text-decoration: none; border: 1px solid var(--border-color); color: var(--text-muted); font-size: 0.85rem;">
        <i class="fa-solid fa-arrow-left" style="margin-right: 0.5rem;"></i> Back to Incidents
    </a>
</div>

<form action="{{ route('incidents.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem;">
        
        <!-- Main Form Column -->
        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
            
            <!-- Section 1: Incident Information -->
            <div style="background: white; border-radius: 12px; padding: 1.75rem; box-shadow: var(--card-shadow);">
                <h3 style="font-size: 1.05rem; font-weight: 700; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem; color: var(--text-dark); display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fa-solid fa-circle-info" style="color: var(--primary);"></i> 1. Incident Information
                </h3>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.25rem;">
                    <div class="form-group">
                        <label for="customer_id" style="font-weight: 600;">Customer / Organization <span style="color: var(--status-down);">*</span></label>
                        <select name="customer_id" id="customer_id" class="form-control" required>
                            <option value="">Select Customer</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ $user->id === auth()->id() ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ $user->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="contact_person" style="font-weight: 600;">Contact Person</label>
                        <input type="text" name="contact_person" id="contact_person" class="form-control" placeholder="John Doe">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.25rem;">
                    <div class="form-group">
                        <label for="contact_number" style="font-weight: 600;">Contact Number</label>
                        <input type="text" name="contact_number" id="contact_number" class="form-control" placeholder="+91 9999999999">
                    </div>
                    <div class="form-group">
                        <label for="category" style="font-weight: 600;">Ticket Type / Category</label>
                        <select name="category" id="category" class="form-control">
                            <option value="Network">Network</option>
                            <option value="Hardware">Hardware</option>
                            <option value="Software">Software</option>
                            <option value="Security">Security</option>
                        </select>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.25rem;">
                    <div class="form-group">
                        <label for="sub_category" style="font-weight: 600;">Sub Category</label>
                        <input type="text" name="sub_category" id="sub_category" class="form-control" placeholder="VPN Failure, Packet Loss, etc.">
                    </div>
                    <div class="form-group">
                        <label for="source" style="font-weight: 600;">Source</label>
                        <select name="source" id="source" class="form-control">
                            <option value="Manual">Manual Entry</option>
                            <option value="Email">Email Request</option>
                            <option value="NMS Alarm">NMS Alarm (Automated)</option>
                            <option value="Call">Phone Call</option>
                        </select>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 1.25rem;">
                    <label for="title" style="font-weight: 600;">Short Description (Summary) <span style="color: var(--status-down);">*</span></label>
                    <input type="text" name="title" id="title" class="form-control" placeholder="e.g. Core Router VPN is down" required>
                </div>

                <div class="form-group">
                    <label for="description" style="font-weight: 600;">Detailed Description</label>
                    <textarea name="description" id="description" class="form-control" rows="4" placeholder="Describe the failure, error messages, and troubleshooting done..."></textarea>
                </div>
            </div>

            <!-- Section 2: Affected Service / Asset -->
            <div style="background: white; border-radius: 12px; padding: 1.75rem; box-shadow: var(--card-shadow);">
                <h3 style="font-size: 1.05rem; font-weight: 700; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem; color: var(--text-dark); display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fa-solid fa-server" style="color: #3b82f6;"></i> 2. Affected Service & Assets
                </h3>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.25rem;">
                    <div class="form-group">
                        <label for="service_impacted" style="font-weight: 600;">Service Impacted</label>
                        <input type="text" name="service_impacted" id="service_impacted" class="form-control" placeholder="Corporate Internet, VPN Portal, ERP">
                    </div>
                    <div class="form-group">
                        <label for="ci_service" style="font-weight: 600;">Configuration Item (CI) / Service</label>
                        <input type="text" name="ci_service" id="ci_service" class="form-control" placeholder="e.g. Cisco-2911-GW">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.25rem;">
                    <div class="form-group">
                        <label for="affected_users" style="font-weight: 600;">Affected Users</label>
                        <input type="number" name="affected_users" id="affected_users" class="form-control" min="0" value="0">
                    </div>
                    <div class="form-group">
                        <label for="business_impact" style="font-weight: 600;">Business Impact</label>
                        <input type="text" name="business_impact" id="business_impact" class="form-control" placeholder="All employees at Mumbai branch offline">
                    </div>
                </div>

                <div class="form-group">
                    <label for="device_id" style="font-weight: 600;">Affected Asset (Device Mapping)</label>
                    <select name="device_id" id="device_id" class="form-control">
                        <option value="">Select Affected NMS Device</option>
                        @foreach($devices as $device)
                            <option value="{{ $device->id }}">{{ $device->name }} ({{ $device->ip_address }})</option>
                        @endforeach
                    </select>
                    <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">
                        Mapping a device will populate serial number, warranty status, and AMC indicators.
                    </p>
                </div>
            </div>

            <!-- Section 3: Additional Details & NOC Actions -->
            <div style="background: white; border-radius: 12px; padding: 1.75rem; box-shadow: var(--card-shadow);">
                <h3 style="font-size: 1.05rem; font-weight: 700; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem; color: var(--text-dark); display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fa-solid fa-clipboard-list" style="color: #8b5cf6;"></i> 3. Additional & NOC Actions
                </h3>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.25rem;">
                    <div class="form-group">
                        <label for="alarm_alert_id" style="font-weight: 600;">Alarm / Alert ID (If automated)</label>
                        <input type="text" name="alarm_alert_id" id="alarm_alert_id" class="form-control" placeholder="AL-9082">
                    </div>
                    <div class="form-group">
                        <label for="detected_time" style="font-weight: 600;">Detected Time</label>
                        <input type="datetime-local" name="detected_time" id="detected_time" class="form-control">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.25rem;">
                    <div class="form-group">
                        <label for="incident_start_time" style="font-weight: 600;">Incident Start Time</label>
                        <input type="datetime-local" name="incident_start_time" id="incident_start_time" class="form-control">
                    </div>
                    <div class="form-group" style="display: flex; align-items: center; gap: 0.5rem; margin-top: 2rem;">
                        <input type="checkbox" name="planned_outage" id="planned_outage" value="1">
                        <label for="planned_outage" style="font-weight: 600; margin-bottom: 0; cursor: pointer;">Is Planned Outage?</label>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.25rem;">
                    <div class="form-group">
                        <label for="assign_group" style="font-weight: 600;">Assignment Group</label>
                        <select name="assign_group" id="assign_group" class="form-control">
                            <option value="Network NOC Team">Network NOC Team</option>
                            <option value="Security NOC Team">Security NOC Team</option>
                            <option value="Server Support Team">Server Support Team</option>
                            <option value="Desktop Support Team">Desktop Support Team</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="assigned_to" style="font-weight: 600;">Assign To Engineer</label>
                        <select name="assigned_to" id="assigned_to" class="form-control">
                            <option value="">Assign Later</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="attachments" style="font-weight: 600;">Attachments (Logs, Screenshots)</label>
                    <input type="file" name="attachments[]" id="attachments" class="form-control" multiple>
                </div>
            </div>
            
        </div>

        <!-- Right Side Priority & SLA Panel -->
        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
            
            <!-- SLA & Impact Matrix -->
            <div style="background: white; border-radius: 12px; padding: 1.5rem; box-shadow: var(--card-shadow); border-top: 4px solid var(--primary);">
                <h3 style="font-size: 1rem; font-weight: 700; margin-bottom: 1.25rem; color: var(--text-dark); display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fa-solid fa-bolt" style="color: var(--primary);"></i> SLA Priority Matrix
                </h3>

                <div class="form-group" style="margin-bottom: 1rem;">
                    <label for="priority" style="font-weight: 600;">Priority <span style="color: var(--status-down);">*</span></label>
                    <select name="priority" id="priority" class="form-control" required>
                        <option value="critical">Critical (P1)</option>
                        <option value="high">High (P2)</option>
                        <option value="medium" selected>Medium (P3)</option>
                        <option value="low">Low (P4)</option>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 1rem;">
                    <label for="impact" style="font-weight: 600;">Impact</label>
                    <select name="impact" id="impact" class="form-control">
                        <option value="critical">Critical (Enterprise-wide)</option>
                        <option value="high">High (Department/Site)</option>
                        <option value="medium" selected>Medium (Multiple Users)</option>
                        <option value="low">Low (Single User)</option>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label for="urgency" style="font-weight: 600;">Urgency</label>
                    <select name="urgency" id="urgency" class="form-control">
                        <option value="critical">Critical (Immediate Outage)</option>
                        <option value="high">High (Severely Degraded)</option>
                        <option value="medium" selected>Medium (Workaround Available)</option>
                        <option value="low">Low (Cosmetic/Enhancement)</option>
                    </select>
                </div>

                <div style="background: var(--bg-up); border: 1px solid rgba(116, 198, 43, 0.2); border-radius: 8px; padding: 1rem;">
                    <h4 style="font-size: 0.85rem; font-weight: 700; color: var(--status-up); margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.25rem;">
                        <i class="fa-solid fa-clock"></i> Target Timelines
                    </h4>
                    <ul style="list-style: none; font-size: 0.8rem; line-height: 1.5; color: var(--text-dark); padding: 0;">
                        <li>• Response SLA: <strong>15 mins</strong></li>
                        <li>• Resolution SLA: <strong>120 mins</strong></li>
                        <li>• Escalation SLA: <strong>30 mins</strong></li>
                    </ul>
                </div>
            </div>

            <!-- Submit Section -->
            <div style="background: white; border-radius: 12px; padding: 1.5rem; box-shadow: var(--card-shadow); text-align: center;">
                <button type="submit" class="btn-add" style="width: 100%; height: 42px; display: inline-flex; align-items: center; justify-content: center; font-weight: 700;">
                    Log Incident
                </button>
            </div>
            
        </div>
        
    </div>
</form>
@endsection
