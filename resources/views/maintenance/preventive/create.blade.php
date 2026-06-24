@extends('layouts.app')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1>Schedule Preventive Maintenance</h1>
        <p>Schedule network outages, firmware patches, or power drills while configuring SLA exemptions.</p>
    </div>
    <a href="{{ route('maintenance.preventive.index') }}" class="btn-secondary" style="height: 38px; display: inline-flex; align-items: center; padding: 0 1rem; border-radius: 6px; text-decoration: none; border: 1px solid var(--border-color); color: var(--text-muted); font-size: 0.85rem;">
        <i class="fa-solid fa-arrow-left" style="margin-right: 0.5rem;"></i> Back to Schedules
    </a>
</div>

<form action="{{ route('maintenance.preventive.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem;">
        
        <!-- Left Column -->
        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
            
            <!-- Section 1: Maintenance Information -->
            <div style="background: white; border-radius: 12px; padding: 1.75rem; box-shadow: var(--card-shadow);">
                <h3 style="font-size: 1.05rem; font-weight: 700; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem; color: var(--text-dark); display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fa-solid fa-screwdriver-wrench" style="color: var(--primary);"></i> 1. Maintenance Details
                </h3>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.25rem;">
                    <div class="form-group">
                        <label for="type" style="font-weight: 600;">Maintenance Type</label>
                        <select name="type" id="type" class="form-control">
                            <option value="Preventive">Preventive Maintenance</option>
                            <option value="Corrective">Corrective Maintenance</option>
                            <option value="Emergency">Emergency Downtime</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="category" style="font-weight: 600;">Category</label>
                        <select name="category" id="category" class="form-control">
                            <option value="Network">Network Interface / Core</option>
                            <option value="Power">UPS & Power Drill</option>
                            <option value="Hardware">Server Chassis</option>
                            <option value="Security">Firewall Policies</option>
                        </select>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 1.25rem;">
                    <label for="title" style="font-weight: 600;">Maintenance Title <span style="color: var(--status-down);">*</span></label>
                    <input type="text" name="title" id="title" class="form-control" placeholder="e.g. Core Switch Redundant PSU Swap" required>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.25rem;">
                    <div class="form-group">
                        <label for="purpose" style="font-weight: 600;">Purpose / Reason</label>
                        <input type="text" name="purpose" id="purpose" class="form-control" placeholder="Swap failing primary power supply module">
                    </div>
                    <div class="form-group">
                        <label for="policy" style="font-weight: 600;">Maintenance Policy Rule</label>
                        <input type="text" name="policy" id="policy" class="form-control" placeholder="Standard Off-Peak Policy">
                    </div>
                </div>
            </div>

            <!-- Section 2: Assets & Services Affected -->
            <div style="background: white; border-radius: 12px; padding: 1.75rem; box-shadow: var(--card-shadow);">
                <h3 style="font-size: 1.05rem; font-weight: 700; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem; color: var(--text-dark); display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fa-solid fa-server" style="color: #3b82f6;"></i> 2. Impacted Assets & Sites
                </h3>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.25rem;">
                    <div class="form-group">
                        <label for="primary_device_id" style="font-weight: 600;">Primary Asset (Device) <span style="color: var(--status-down);">*</span></label>
                        <select name="primary_device_id" id="primary_device_id" class="form-control" required>
                            <option value="">Select Primary Device</option>
                            @foreach($devices as $dev)
                                <option value="{{ $dev->id }}">{{ $dev->name }} ({{ $dev->ip_address }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="additional_assets" style="font-weight: 600;">Additional Assets (Comma separated IDs)</label>
                        <input type="text" name="additional_assets" id="additional_assets" class="form-control" placeholder="AST-092, AST-120">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.25rem;">
                    <div class="form-group">
                        <label for="affected_services" style="font-weight: 600;">Affected Services</label>
                        <input type="text" name="affected_services" id="affected_services" class="form-control" placeholder="Central VPN, ERP Access">
                    </div>
                    <div class="form-group">
                        <label for="affected_users" style="font-weight: 600;">Affected Users / Sites</label>
                        <input type="text" name="affected_users" id="affected_users" class="form-control" placeholder="approx. 150 users at Mumbai DC">
                    </div>
                </div>

                <div class="form-group">
                    <label for="business_impact" style="font-weight: 600;">Business Impact / Revenue Impact</label>
                    <textarea name="business_impact" id="business_impact" class="form-control" rows="2" placeholder="Temporary loss of link redundancy during module swap..."></textarea>
                </div>
            </div>

            <!-- Section 3: Implementation & Rollback -->
            <div style="background: white; border-radius: 12px; padding: 1.75rem; box-shadow: var(--card-shadow);">
                <h3 style="font-size: 1.05rem; font-weight: 700; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem; color: var(--text-dark); display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fa-solid fa-list-check" style="color: #8b5cf6;"></i> 3. Steps & Rollback Plan
                </h3>

                <div class="form-group" style="margin-bottom: 1.25rem;">
                    <label for="implementation_steps" style="font-weight: 600;">Implementation Steps</label>
                    <textarea name="implementation_steps" id="implementation_steps" class="form-control" rows="3" placeholder="1. Power down PSU module A&#10;2. Extract module from chassis&#10;3. Slide in replacement..."></textarea>
                </div>

                <div class="form-group">
                    <label for="rollback_plan" style="font-weight: 600;">Rollback Plan</label>
                    <textarea name="rollback_plan" id="rollback_plan" class="form-control" rows="2" placeholder="Slide back original functional module, re-engage locks..."></textarea>
                </div>
            </div>
            
        </div>

        <!-- Right Column -->
        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
            
            <!-- Window & Timing -->
            <div style="background: white; border-radius: 12px; padding: 1.5rem; box-shadow: var(--card-shadow); border-top: 4px solid var(--primary);">
                <h3 style="font-size: 1rem; font-weight: 700; margin-bottom: 1.25rem; color: var(--text-dark); display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fa-solid fa-clock" style="color: var(--primary);"></i> Timing & Windows
                </h3>

                <div class="form-group" style="margin-bottom: 1rem;">
                    <label for="start_time" style="font-weight: 600;">Planned Start Time <span style="color: var(--status-down);">*</span></label>
                    <input type="datetime-local" name="start_time" id="start_time" class="form-control" required>
                </div>

                <div class="form-group" style="margin-bottom: 1rem;">
                    <label for="end_time" style="font-weight: 600;">Planned End Time <span style="color: var(--status-down);">*</span></label>
                    <input type="datetime-local" name="end_time" id="end_time" class="form-control" required>
                </div>

                <div class="form-group" style="margin-bottom: 1rem;">
                    <label for="expected_downtime_minutes" style="font-weight: 600;">Expected Downtime (Minutes)</label>
                    <input type="number" name="expected_downtime_minutes" id="expected_downtime_minutes" class="form-control" value="60">
                </div>

                <div class="form-group" style="margin-bottom: 1rem;">
                    <label for="downtime_type" style="font-weight: 600;">Downtime Coverage</label>
                    <select name="downtime_type" id="downtime_type" class="form-control">
                        <option value="Partial Outage">Partial Outage (Degraded Performance)</option>
                        <option value="Full Outage">Full Outage (Complete Link Down)</option>
                        <option value="No Downtime">No Outage (Redundancy Active)</option>
                    </select>
                </div>
            </div>

            <!-- SLA Handling -->
            <div style="background: white; border-radius: 12px; padding: 1.5rem; box-shadow: var(--card-shadow); border-top: 4px solid #3b82f6;">
                <h3 style="font-size: 1rem; font-weight: 700; margin-bottom: 1.25rem; color: var(--text-dark); display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fa-solid fa-shield-halved" style="color: #3b82f6;"></i> SLA Configuration
                </h3>

                <div class="form-group" style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem;">
                    <input type="checkbox" name="exclude_sla" id="exclude_sla" value="1" checked>
                    <label for="exclude_sla" style="font-weight: 600; margin-bottom: 0; cursor: pointer;">Exclude from SLA Clocks?</label>
                </div>

                <div class="form-group" style="margin-bottom: 1rem;">
                    <label for="sla_impact" style="font-weight: 600;">SLA Impact Status</label>
                    <input type="text" name="sla_impact" id="sla_impact" class="form-control" value="No Breach (Maintenance)">
                </div>

                <div class="form-group" style="margin-bottom: 1rem;">
                    <label for="sla_policy" style="font-weight: 600;">Mapped SLA Policy</label>
                    <select name="sla_policy" id="sla_policy" class="form-control">
                        @foreach($policies as $pol)
                            <option value="{{ $pol->name }}">{{ $pol->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label for="notify_before_minutes" style="font-weight: 600;">Pre-Notification Advance (Mins)</label>
                    <input type="number" name="notify_before_minutes" id="notify_before_minutes" class="form-control" value="120">
                </div>
            </div>

            <!-- NOC Sign-off -->
            <div style="background: white; border-radius: 12px; padding: 1.5rem; box-shadow: var(--card-shadow); border-top: 4px solid #8b5cf6;">
                <h3 style="font-size: 1rem; font-weight: 700; margin-bottom: 1.25rem; color: var(--text-dark); display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fa-solid fa-file-signature" style="color: #8b5cf6;"></i> Sign-off Approvals
                </h3>

                <div class="form-group" style="margin-bottom: 1rem;">
                    <label for="requested_by" style="font-weight: 600;">Requested By</label>
                    <input type="text" name="requested_by" id="requested_by" class="form-control" value="{{ auth()->user()->name ?? 'NMS Admin' }}">
                </div>

                <div class="form-group" style="margin-bottom: 1rem;">
                    <label for="approved_noc_manager" style="font-weight: 600;">Approved NOC Manager</label>
                    <input type="text" name="approved_noc_manager" id="approved_noc_manager" class="form-control" placeholder="Vijay Kumar">
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label for="approved_it_head" style="font-weight: 600;">Approved IT Head</label>
                    <input type="text" name="approved_it_head" id="approved_it_head" class="form-control" placeholder="Rajesh Sharma">
                </div>
            </div>

            <!-- Submit -->
            <div style="background: white; border-radius: 12px; padding: 1.5rem; box-shadow: var(--card-shadow); text-align: center;">
                <button type="submit" class="btn-add" style="width: 100%; height: 42px; display: inline-flex; align-items: center; justify-content: center; font-weight: 700;">
                    Schedule Window
                </button>
            </div>
            
        </div>
        
    </div>
</form>
@endsection
