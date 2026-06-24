@extends('layouts.app')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1>Raise Change Request (RFC)</h1>
        <p>Propose and plan network modifications, hardware replacements, or firmware updates.</p>
    </div>
    <a href="{{ route('changes.index') }}" class="btn-secondary" style="height: 38px; display: inline-flex; align-items: center; padding: 0 1rem; border-radius: 6px; text-decoration: none; border: 1px solid var(--border-color); color: var(--text-muted); font-size: 0.85rem;">
        <i class="fa-solid fa-arrow-left" style="margin-right: 0.5rem;"></i> Back to Changes
    </a>
</div>

<form action="{{ route('changes.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem;">
        
        <!-- Left Column -->
        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
            
            <!-- Section 1: Change Information -->
            <div style="background: white; border-radius: 12px; padding: 1.75rem; box-shadow: var(--card-shadow);">
                <h3 style="font-size: 1.05rem; font-weight: 700; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem; color: var(--text-dark); display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fa-solid fa-file-signature" style="color: var(--primary);"></i> 1. Change Information
                </h3>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.25rem;">
                    <div class="form-group">
                        <label for="customer_id" style="font-weight: 600;">Raised By Customer/Org <span style="color: var(--status-down);">*</span></label>
                        <select name="customer_id" id="customer_id" class="form-control" required>
                            <option value="">Select Customer</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ $user->id === auth()->id() ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="change_type" style="font-weight: 600;">Change Type</label>
                        <select name="change_type" id="change_type" class="form-control">
                            <option value="Standard">Standard (Low Risk, Pre-approved)</option>
                            <option value="Normal">Normal (Requires CAB Review)</option>
                            <option value="Emergency">Emergency (Immediate Outage Resolution)</option>
                        </select>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.25rem;">
                    <div class="form-group">
                        <label for="change_category" style="font-weight: 600;">Change Category</label>
                        <select name="change_category" id="change_category" class="form-control">
                            <option value="Firmware Upgrade">Firmware Upgrade</option>
                            <option value="Hardware Replacement">Hardware Replacement</option>
                            <option value="Route Modification">Route Modification</option>
                            <option value="Firewall Policy Update">Firewall Policy Update</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="reason" style="font-weight: 600;">Reason for Change</label>
                        <input type="text" name="reason" id="reason" class="form-control" placeholder="Fix vulnerability, expand bandwidth">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 1.25rem;">
                    <label for="title" style="font-weight: 600;">Change Title (RFC Summary) <span style="color: var(--status-down);">*</span></label>
                    <input type="text" name="title" id="title" class="form-control" placeholder="e.g. Upgrade Core Switch Firmware to v17.9.4" required>
                </div>

                <div class="form-group">
                    <label for="description" style="font-weight: 600;">Description</label>
                    <textarea name="description" id="description" class="form-control" rows="4" placeholder="Detail the change objective, affected systems, and expected benefits..."></textarea>
                </div>
            </div>

            <!-- Section 2: Related Items & Affected Assets -->
            <div style="background: white; border-radius: 12px; padding: 1.75rem; box-shadow: var(--card-shadow);">
                <h3 style="font-size: 1.05rem; font-weight: 700; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem; color: var(--text-dark); display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fa-solid fa-link" style="color: #3b82f6;"></i> 2. Related Items & Affected Assets
                </h3>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.25rem;">
                    <div class="form-group">
                        <label for="related_problem" style="font-weight: 600;">Related Problem ID</label>
                        <input type="text" name="related_problem" id="related_problem" class="form-control" placeholder="PRB-2026-001">
                    </div>
                    <div class="form-group">
                        <label for="related_incidents" style="font-weight: 600;">Related Incident(s)</label>
                        <input type="text" name="related_incidents" id="related_incidents" class="form-control" placeholder="INC-1024, INC-1021">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.25rem;">
                    <div class="form-group">
                        <label for="device_id" style="font-weight: 600;">Primary Asset Affected</label>
                        <select name="device_id" id="device_id" class="form-control">
                            <option value="">Select Affected Device</option>
                            @foreach($devices as $device)
                                <option value="{{ $device->id }}">{{ $device->name }} ({{ $device->ip_address }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="affected_services" style="font-weight: 600;">Affected Services</label>
                        <input type="text" name="affected_services" id="affected_services" class="form-control" placeholder="Internet Gateway, Core VPN">
                    </div>
                </div>
            </div>

            <!-- Section 3: Risk Assessment & Rollback -->
            <div style="background: white; border-radius: 12px; padding: 1.75rem; box-shadow: var(--card-shadow);">
                <h3 style="font-size: 1.05rem; font-weight: 700; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem; color: var(--text-dark); display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fa-solid fa-triangle-exclamation" style="color: var(--status-warning);"></i> 3. Risk & Rollback Assessment
                </h3>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.25rem;">
                    <div class="form-group">
                        <label for="risk_level" style="font-weight: 600;">Risk Level</label>
                        <select name="risk_level" id="risk_level" class="form-control">
                            <option value="low">Low (No downtime)</option>
                            <option value="medium" selected>Medium (Short degradation)</option>
                            <option value="high">High (Outage expected)</option>
                        </select>
                    </div>
                    <div class="form-group" style="display: flex; align-items: center; gap: 0.5rem; margin-top: 2rem;">
                        <input type="checkbox" name="impact_on_sla" id="impact_on_sla" value="1" checked>
                        <label for="impact_on_sla" style="font-weight: 600; margin-bottom: 0; cursor: pointer;">Impact on SLA?</label>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 1.25rem;">
                    <label for="risk_description" style="font-weight: 600;">Risk Description</label>
                    <textarea name="risk_description" id="risk_description" class="form-control" rows="2" placeholder="Detail any risks, e.g. config parsing error on reload"></textarea>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.25rem;">
                    <div class="form-group">
                        <label for="rollback_plan" style="font-weight: 600;">Rollback Plan</label>
                        <textarea name="rollback_plan" id="rollback_plan" class="form-control" rows="2" placeholder="Steps to restore original configuration..."></textarea>
                    </div>
                    <div class="form-group">
                        <label for="backout_time_minutes" style="font-weight: 600;">Estimated Backout Time (Minutes)</label>
                        <input type="number" name="backout_time_minutes" id="backout_time_minutes" class="form-control" value="30">
                    </div>
                </div>
            </div>

            <!-- Section 4: Implementation Steps -->
            <div style="background: white; border-radius: 12px; padding: 1.75rem; box-shadow: var(--card-shadow);">
                <h3 style="font-size: 1.05rem; font-weight: 700; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem; color: var(--text-dark); display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fa-solid fa-list-check" style="color: #8b5cf6;"></i> 4. Implementation Steps
                </h3>
                <div class="form-group">
                    <label for="implementation_steps" style="font-weight: 600;">Execution / Implementation Steps</label>
                    <textarea name="implementation_steps" id="implementation_steps" class="form-control" rows="4" placeholder="1. Save configuration&#10;2. TFTP flash update&#10;3. Reboot core switch..."></textarea>
                </div>
            </div>
            
        </div>

        <!-- Right Column -->
        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
            
            <!-- Timing & Outage Panel -->
            <div style="background: white; border-radius: 12px; padding: 1.5rem; box-shadow: var(--card-shadow); border-top: 4px solid var(--primary);">
                <h3 style="font-size: 1rem; font-weight: 700; margin-bottom: 1.25rem; color: var(--text-dark); display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fa-solid fa-clock" style="color: var(--primary);"></i> Timing & Outages
                </h3>

                <div class="form-group" style="margin-bottom: 1rem;">
                    <label for="priority" style="font-weight: 600;">Change Priority</label>
                    <select name="priority" id="priority" class="form-control">
                        <option value="critical">Critical (Emergency)</option>
                        <option value="high">High</option>
                        <option value="medium" selected>Medium</option>
                        <option value="low">Low</option>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 1rem;">
                    <label for="change_planned_start" style="font-weight: 600;">Planned Start Time</label>
                    <input type="datetime-local" name="change_planned_start" id="change_planned_start" class="form-control">
                </div>

                <div class="form-group" style="margin-bottom: 1rem;">
                    <label for="change_planned_end" style="font-weight: 600;">Planned End Time</label>
                    <input type="datetime-local" name="change_planned_end" id="change_planned_end" class="form-control">
                </div>

                <div class="form-group" style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem;">
                    <input type="checkbox" name="planned_downtime" id="planned_downtime" value="1">
                    <label for="planned_downtime" style="font-weight: 600; margin-bottom: 0; cursor: pointer;">Planned Downtime Required?</label>
                </div>

                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label for="change_window" style="font-weight: 600;">Maintenance Window Category</label>
                    <select name="change_window" id="change_window" class="form-control">
                        <option value="Off-Peak Weekend">Off-Peak Weekend</option>
                        <option value="Night Window (22:00 - 02:00)">Night Window (22:00 - 02:00)</option>
                        <option value="Business Hours (Risk Approved)">Business Hours (Risk Approved)</option>
                    </select>
                </div>
            </div>

            <!-- CAB Approvers Panel -->
            <div style="background: white; border-radius: 12px; padding: 1.5rem; box-shadow: var(--card-shadow); border-top: 4px solid #3b82f6;">
                <h3 style="font-size: 1rem; font-weight: 700; margin-bottom: 1.25rem; color: var(--text-dark); display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fa-solid fa-users-check" style="color: #3b82f6;"></i> CAB Approvals
                </h3>

                <div class="form-group" style="margin-bottom: 1rem;">
                    <label for="assigned_to" style="font-weight: 600;">Assigned CAB Reviewer</label>
                    <select name="assigned_to" id="assigned_to" class="form-control">
                        <option value="">Assign CAB Leader</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 1rem;">
                    <label for="approved_by" style="font-weight: 600;">Approved By (NOC Lead)</label>
                    <input type="text" name="approved_by" id="approved_by" class="form-control" placeholder="Vijay Kumar">
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label for="customer_approval" style="font-weight: 600;">Customer Consent</label>
                    <select name="customer_approval" id="customer_approval" class="form-control">
                        <option value="Pending">Pending Customer Approval</option>
                        <option value="Approved">Consent Obtained</option>
                        <option value="Not Required">Consent Not Required</option>
                    </select>
                </div>
            </div>

            <!-- Submit -->
            <div style="background: white; border-radius: 12px; padding: 1.5rem; box-shadow: var(--card-shadow); text-align: center;">
                <button type="submit" class="btn-add" style="width: 100%; height: 42px; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; background-color: #3b82f6; border-color: #2563eb;">
                    Submit Change (RFC)
                </button>
            </div>
            
        </div>
        
    </div>
</form>
@endsection
