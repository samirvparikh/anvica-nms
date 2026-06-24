@extends('layouts.app')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1>Asset Warranty & AMC Contracts</h1>
        <p>Configure support coverage, financial details, alert recipients, and SLA metrics for network assets.</p>
    </div>
</div>

<form action="{{ route('inventory.warranty.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div style="background: white; border-radius: 12px; padding: 1.75rem; box-shadow: var(--card-shadow); margin-bottom: 2rem;">
        <h3 style="font-size: 1.05rem; font-weight: 700; margin-bottom: 1.25rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem; color: var(--text-dark);">
            Select Asset to Configure
        </h3>
        <div class="form-group" style="max-width: 500px;">
            <label for="device_id" style="font-weight: 600;">NMS Device Mapping <span style="color: var(--status-down);">*</span></label>
            <select name="device_id" id="device_id" class="form-control" required>
                <option value="">-- Choose a Device --</option>
                @foreach($devices as $dev)
                    <option value="{{ $dev->id }}" 
                        data-asset-id="{{ $dev->asset_id ?? 'AST-' . (10000 + $dev->id) }}"
                        data-name="{{ $dev->name }}"
                        data-manufacturer="{{ $dev->manufacturer }}"
                        data-model="{{ $dev->model_number }}"
                        data-serial="{{ $dev->serial_number }}"
                        data-warranty-type="{{ $dev->warranty_type }}"
                        data-warranty-provider="{{ $dev->warranty_provider }}"
                        data-warranty-support="{{ $dev->warranty_support_level }}"
                        data-warranty-status="{{ $dev->warranty_status }}"
                        data-warranty-start="{{ $dev->warranty_start_date?->format('Y-m-d') }}"
                        data-warranty-end="{{ $dev->warranty_end_date?->format('Y-m-d') }}"
                        data-warranty-duration="{{ $dev->warranty_duration_years }}"
                        data-warranty-onsite="{{ $dev->warranty_onsite_support ? 1 : 0 }}"
                        data-warranty-parts="{{ $dev->warranty_parts_coverage }}"
                        data-warranty-labor="{{ $dev->warranty_labor_coverage }}"
                        data-warranty-transferable="{{ $dev->warranty_transferable ? 1 : 0 }}"
                        data-warranty-terms="{{ $dev->warranty_terms }}"
                        data-amc-available="{{ $dev->amc_available ? 1 : 0 }}"
                        data-amc-type="{{ $dev->amc_type }}"
                        data-amc-provider="{{ $dev->amc_provider }}"
                        data-amc-support="{{ $dev->amc_support_level }}"
                        data-amc-start="{{ $dev->amc_start_date?->format('Y-m-d') }}"
                        data-amc-end="{{ $dev->amc_end_date?->format('Y-m-d') }}"
                        data-amc-duration="{{ $dev->amc_duration_years }}"
                        data-amc-response="{{ $dev->amc_response_time }}"
                        data-amc-resolution="{{ $dev->amc_resolution_time }}"
                        data-amc-escalation="{{ $dev->amc_escalation_time }}"
                        data-amc-coverage="{{ $dev->amc_coverage }}"
                        data-amc-terms="{{ $dev->amc_terms }}"
                        data-po="{{ $dev->purchase_order_no }}"
                        data-invoice-no="{{ $dev->invoice_no }}"
                        data-purchase-date="{{ $dev->purchase_date?->format('Y-m-d') }}"
                        data-invoice-date="{{ $dev->invoice_date?->format('Y-m-d') }}"
                        data-warranty-cost="{{ $dev->warranty_cost }}"
                        data-amc-cost="{{ $dev->amc_cost }}"
                        data-currency="{{ $dev->currency }}"
                        data-tax="{{ $dev->tax }}"
                        data-total-amc-cost="{{ $dev->total_amc_cost }}"
                        data-sla-policy="{{ $dev->customer_sla_policy }}"
                        data-sla-availability="{{ $dev->availability_sla }}"
                        data-sla-response="{{ $dev->response_sla }}"
                        data-sla-resolution="{{ $dev->resolution_sla }}"
                        data-renewal-reminder="{{ $dev->renewal_reminder }}"
                        data-amc-renewal="{{ $dev->amc_renewal_reminder }}"
                        data-expiry-alert="{{ $dev->warranty_expiry_alert ? 1 : 0 }}"
                        data-amc-alert="{{ $dev->amc_expiry_alert ? 1 : 0 }}"
                        data-recipients="{{ $dev->notification_recipients }}"
                        data-owner="{{ $dev->asset_owner }}"
                        data-custodian="{{ $dev->custodian }}"
                        data-responsible="{{ $dev->responsible_person }}"
                        data-contact="{{ $dev->contact_number }}"
                        data-notes="{{ $dev->additional_notes }}"
                        {{ request('device_id') == $dev->id ? 'selected' : '' }}
                    >
                        {{ $dev->name }} ({{ $dev->ip_address }})
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Main Form Fields Grid -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
        
        <!-- Column 1: Asset Info & Warranty -->
        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
            
            <!-- Block 1: Asset Details -->
            <div style="background: white; border-radius: 12px; padding: 1.5rem; box-shadow: var(--card-shadow);">
                <h3 style="font-size: 1rem; font-weight: 700; margin-bottom: 1.25rem; color: var(--text-dark); border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fa-solid fa-server" style="color: var(--primary);"></i> Asset Profile Details
                </h3>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                    <div class="form-group">
                        <label for="asset_id">Asset ID</label>
                        <input type="text" name="asset_id" id="asset_id" class="form-control" placeholder="AST-10254">
                    </div>
                    <div class="form-group">
                        <label for="asset_name">Asset Name</label>
                        <input type="text" name="asset_name" id="asset_name" class="form-control" placeholder="Core-Router">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                    <div class="form-group">
                        <label for="manufacturer">Manufacturer</label>
                        <input type="text" name="manufacturer" id="manufacturer" class="form-control" placeholder="Cisco Systems">
                    </div>
                    <div class="form-group">
                        <label for="model_number">Model Number</label>
                        <input type="text" name="model_number" id="model_number" class="form-control" placeholder="ISR-4331/K9">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="serial_number">Serial Number</label>
                        <input type="text" name="serial_number" id="serial_number" class="form-control" placeholder="FDO22409Z3">
                    </div>
                    <div class="form-group">
                        <label for="location">Location / Rack</label>
                        <input type="text" name="location" id="location" class="form-control" placeholder="Mumbai DC, Rack B4">
                    </div>
                </div>
            </div>

            <!-- Block 2: Warranty Information -->
            <div style="background: white; border-radius: 12px; padding: 1.5rem; box-shadow: var(--card-shadow);">
                <h3 style="font-size: 1rem; font-weight: 700; margin-bottom: 1.25rem; color: var(--text-dark); border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fa-solid fa-shield-halved" style="color: #3b82f6;"></i> Warranty Information
                </h3>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                    <div class="form-group">
                        <label for="warranty_type">Warranty Type</label>
                        <select name="warranty_type" id="warranty_type" class="form-control" style="height: 38px;">
                            <option value="OEM Warranty">OEM Standard Warranty</option>
                            <option value="Extended Warranty">Extended OEM Warranty</option>
                            <option value="None">No Warranty Coverage</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="warranty_provider">Warranty Provider</label>
                        <input type="text" name="warranty_provider" id="warranty_provider" class="form-control" placeholder="Cisco Services Ltd">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                    <div class="form-group">
                        <label for="warranty_support_level">Support Level</label>
                        <input type="text" name="warranty_support_level" id="warranty_support_level" class="form-control" placeholder="8x5 NBD, 24x7x4hr">
                    </div>
                    <div class="form-group">
                        <label for="warranty_status">Warranty Status</label>
                        <select name="warranty_status" id="warranty_status" class="form-control" style="height: 38px;">
                            <option value="Active">Active</option>
                            <option value="Expired">Expired</option>
                            <option value="Voided">Voided</option>
                        </select>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                    <div class="form-group">
                        <label for="warranty_start_date">Warranty Start Date</label>
                        <input type="date" name="warranty_start_date" id="warranty_start_date" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="warranty_end_date">Warranty End Date</label>
                        <input type="date" name="warranty_end_date" id="warranty_end_date" class="form-control">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                    <div class="form-group">
                        <label for="warranty_duration_years">Warranty Duration (Years)</label>
                        <input type="number" name="warranty_duration_years" id="warranty_duration_years" class="form-control" min="0" value="3">
                    </div>
                    <div class="form-group" style="display: flex; align-items: center; gap: 0.5rem; margin-top: 1.5rem;">
                        <input type="checkbox" name="warranty_onsite_support" id="warranty_onsite_support" value="1" checked>
                        <label for="warranty_onsite_support" style="margin-bottom:0; font-weight:600; cursor:pointer;">Onsite Support Available?</label>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                    <div class="form-group">
                        <label for="warranty_parts_coverage">Parts Covered</label>
                        <input type="text" name="warranty_parts_coverage" id="warranty_parts_coverage" class="form-control" placeholder="All replacement units">
                    </div>
                    <div class="form-group">
                        <label for="warranty_labor_coverage">Labor Covered</label>
                        <input type="text" name="warranty_labor_coverage" id="warranty_labor_coverage" class="form-control" placeholder="NOC/Vendor dispatch engineer">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr; gap: 1rem; margin-bottom: 1rem;">
                    <div class="form-group" style="display: flex; align-items: center; gap: 0.5rem;">
                        <input type="checkbox" name="warranty_transferable" id="warranty_transferable" value="1">
                        <label for="warranty_transferable" style="margin-bottom:0; font-weight:600; cursor:pointer;">Warranty Transferable?</label>
                    </div>
                </div>

                <div class="form-group">
                    <label for="warranty_terms">Warranty Terms & Conditions / Notes</label>
                    <textarea name="warranty_terms" id="warranty_terms" class="form-control" rows="2"></textarea>
                </div>
            </div>

            <!-- Block 3: Financial Details -->
            <div style="background: white; border-radius: 12px; padding: 1.5rem; box-shadow: var(--card-shadow);">
                <h3 style="font-size: 1rem; font-weight: 700; margin-bottom: 1.25rem; color: var(--text-dark); border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fa-solid fa-indian-rupee-sign" style="color: #8b5cf6;"></i> Financial Information
                </h3>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                    <div class="form-group">
                        <label for="purchase_order_no">Purchase Order No.</label>
                        <input type="text" name="purchase_order_no" id="purchase_order_no" class="form-control" placeholder="PO-2026-902">
                    </div>
                    <div class="form-group">
                        <label for="invoice_no">Invoice Number</label>
                        <input type="text" name="invoice_no" id="invoice_no" class="form-control" placeholder="INV-0982">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                    <div class="form-group">
                        <label for="purchase_date">Purchase Date</label>
                        <input type="date" name="purchase_date" id="purchase_date" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="invoice_date">Invoice Date</label>
                        <input type="date" name="invoice_date" id="invoice_date" class="form-control">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                    <div class="form-group">
                        <label for="warranty_cost">Warranty Cost</label>
                        <input type="number" step="0.01" name="warranty_cost" id="warranty_cost" class="form-control" value="0.00">
                    </div>
                    <div class="form-group">
                        <label for="amc_cost">Base AMC Cost</label>
                        <input type="number" step="0.01" name="amc_cost" id="amc_cost" class="form-control" value="0.00">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="currency">Currency</label>
                        <input type="text" name="currency" id="currency" class="form-control" value="INR">
                    </div>
                    <div class="form-group">
                        <label for="tax">Tax Code / Cost</label>
                        <input type="number" step="0.01" name="tax" id="tax" class="form-control" value="0.00">
                    </div>
                    <div class="form-group">
                        <label for="total_amc_cost">Total Cost</label>
                        <input type="number" step="0.01" name="total_amc_cost" id="total_amc_cost" class="form-control" value="0.00">
                    </div>
                </div>
            </div>
        </div>

        <!-- Column 2: AMC, SLA, & Notifications -->
        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
            
            <!-- Block 4: AMC / Support Contract -->
            <div style="background: white; border-radius: 12px; padding: 1.5rem; box-shadow: var(--card-shadow);">
                <h3 style="font-size: 1rem; font-weight: 700; margin-bottom: 1.25rem; color: var(--text-dark); border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fa-solid fa-file-contract" style="color: var(--status-warning);"></i> AMC & Support Contract Details
                </h3>

                <div class="form-group" style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem;">
                    <input type="checkbox" name="amc_available" id="amc_available" value="1" checked>
                    <label for="amc_available" style="margin-bottom:0; font-weight:600; cursor:pointer;">Is AMC Contract Available?</label>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                    <div class="form-group">
                        <label for="amc_type">AMC Type</label>
                        <select name="amc_type" id="amc_type" class="form-control" style="height: 38px;">
                            <option value="Comprehensive">Comprehensive (Parts & Labor)</option>
                            <option value="Non-Comprehensive">Non-Comprehensive (Labor only)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="amc_provider">Support Vendor / Provider</label>
                        <input type="text" name="amc_provider" id="amc_provider" class="form-control" placeholder="Network Solutions Pvt Ltd">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                    <div class="form-group">
                        <label for="amc_support_level">Support Priority Level</label>
                        <input type="text" name="amc_support_level" id="amc_support_level" class="form-control" placeholder="Critical 24x7 L3 Support">
                    </div>
                    <div class="form-group">
                        <label for="amc_duration_years">Contract Duration (Years)</label>
                        <input type="number" name="amc_duration_years" id="amc_duration_years" class="form-control" value="2">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                    <div class="form-group">
                        <label for="amc_start_date">Contract Start Date</label>
                        <input type="date" name="amc_start_date" id="amc_start_date" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="amc_end_date">Contract End Date</label>
                        <input type="date" name="amc_end_date" id="amc_end_date" class="form-control">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                    <div class="form-group">
                        <label for="amc_response_time">Response SLA</label>
                        <input type="text" name="amc_response_time" id="amc_response_time" class="form-control" placeholder="e.g. 15 Mins">
                    </div>
                    <div class="form-group">
                        <label for="amc_resolution_time">Resolution SLA</label>
                        <input type="text" name="amc_resolution_time" id="amc_resolution_time" class="form-control" placeholder="e.g. 2 Hours">
                    </div>
                    <div class="form-group">
                        <label for="amc_escalation_time">Escalation SLA</label>
                        <input type="text" name="amc_escalation_time" id="amc_escalation_time" class="form-control" placeholder="e.g. 30 Mins">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 1rem;">
                    <label for="amc_coverage">SLA Coverage Hours</label>
                    <input type="text" name="amc_coverage" id="amc_coverage" class="form-control" placeholder="24x7x365 coverage including public holidays">
                </div>

                <div class="form-group">
                    <label for="amc_terms">AMC Terms & Conditions / Notes</label>
                    <textarea name="amc_terms" id="amc_terms" class="form-control" rows="2"></textarea>
                </div>
            </div>

            <!-- Block 5: SLA Mapping -->
            <div style="background: white; border-radius: 12px; padding: 1.5rem; box-shadow: var(--card-shadow); border-top: 4px solid var(--primary);">
                <h3 style="font-size: 1rem; font-weight: 700; margin-bottom: 1.25rem; color: var(--text-dark); border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fa-solid fa-shield-halved" style="color: var(--primary);"></i> SLA Target Parameters
                </h3>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                    <div class="form-group">
                        <label for="customer_sla_policy">Customer SLA Policy</label>
                        <select name="customer_sla_policy" id="customer_sla_policy" class="form-control" style="height: 38px;">
                            @foreach($policies as $p)
                                <option value="{{ $p->name }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="availability_sla">Availability target (%)</label>
                        <input type="number" step="0.01" name="availability_sla" id="availability_sla" class="form-control" value="99.95">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="response_sla">Target Response SLA</label>
                        <input type="text" name="response_sla" id="response_sla" class="form-control" placeholder="e.g. 15 mins">
                    </div>
                    <div class="form-group">
                        <label for="resolution_sla">Target Resolution SLA</label>
                        <input type="text" name="resolution_sla" id="resolution_sla" class="form-control" placeholder="e.g. 120 mins">
                    </div>
                </div>
            </div>

            <!-- Block 6: Alerts & Reminders -->
            <div style="background: white; border-radius: 12px; padding: 1.5rem; box-shadow: var(--card-shadow); border-top: 4px solid #06b6d4;">
                <h3 style="font-size: 1rem; font-weight: 700; margin-bottom: 1.25rem; color: var(--text-dark); border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fa-solid fa-bell" style="color: #06b6d4;"></i> Renewal & Expiry Alerts
                </h3>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                    <div class="form-group">
                        <label for="renewal_reminder">Warranty Expiry Alert Window</label>
                        <select name="renewal_reminder" id="renewal_reminder" class="form-control" style="height: 38px;">
                            <option value="30 Days Before">30 Days Prior</option>
                            <option value="60 Days Before">60 Days Prior</option>
                            <option value="90 Days Before">90 Days Prior</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="amc_renewal_reminder">AMC Renewal Alert Window</label>
                        <select name="amc_renewal_reminder" id="amc_renewal_reminder" class="form-control" style="height: 38px;">
                            <option value="30 Days Before">30 Days Prior</option>
                            <option value="60 Days Before">60 Days Prior</option>
                            <option value="90 Days Before">90 Days Prior</option>
                        </select>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                    <div class="form-group" style="display: flex; align-items: center; gap: 0.5rem;">
                        <input type="checkbox" name="warranty_expiry_alert" id="warranty_expiry_alert" value="1" checked>
                        <label for="warranty_expiry_alert" style="margin-bottom:0; font-weight:600; cursor:pointer;">Alert on Warranty Expiry?</label>
                    </div>
                    <div class="form-group" style="display: flex; align-items: center; gap: 0.5rem;">
                        <input type="checkbox" name="amc_expiry_alert" id="amc_expiry_alert" value="1" checked>
                        <label for="amc_expiry_alert" style="margin-bottom:0; font-weight:600; cursor:pointer;">Alert on AMC Expiry?</label>
                    </div>
                </div>

                <div class="form-group">
                    <label for="notification_recipients">Notification Recipients (Emails)</label>
                    <input type="text" name="notification_recipients" id="notification_recipients" class="form-control" placeholder="noc-leads@anvica.com, admin@anvica.com">
                </div>
            </div>

            <!-- Block 7: Ownership & Responsibility -->
            <div style="background: white; border-radius: 12px; padding: 1.5rem; box-shadow: var(--card-shadow); border-top: 4px solid #4b5563;">
                <h3 style="font-size: 1rem; font-weight: 700; margin-bottom: 1.25rem; color: var(--text-dark); border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fa-solid fa-user-shield" style="color: #4b5563;"></i> Asset Ownership
                </h3>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                    <div class="form-group">
                        <label for="asset_owner">Asset Owner</label>
                        <input type="text" name="asset_owner" id="asset_owner" class="form-control" placeholder="Anvica Networks">
                    </div>
                    <div class="form-group">
                        <label for="custodian">Asset Custodian</label>
                        <input type="text" name="custodian" id="custodian" class="form-control" placeholder="IT Ops Mumbai">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                    <div class="form-group">
                        <label for="responsible_person">Responsible Person</label>
                        <input type="text" name="responsible_person" id="responsible_person" class="form-control" placeholder="Vijay Kumar">
                    </div>
                    <div class="form-group">
                        <label for="contact_number">Custodian Contact Number</label>
                        <input type="text" name="contact_number" id="contact_number" class="form-control" placeholder="+91 9888877777">
                    </div>
                </div>

                <div class="form-group">
                    <label for="additional_notes">Additional Ownership / Asset Notes</label>
                    <textarea name="additional_notes" id="additional_notes" class="form-control" rows="2"></textarea>
                </div>
            </div>

            <!-- Submit -->
            <div style="background: white; border-radius: 12px; padding: 1.5rem; box-shadow: var(--card-shadow); text-align: center;">
                <button type="submit" class="btn-add" style="width: 100%; height: 42px; display: inline-flex; align-items: center; justify-content: center; font-weight: 700;">
                    Save Warranty & AMC Details
                </button>
            </div>
        </div>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const deviceSelect = document.getElementById('device_id');
    
    deviceSelect.addEventListener('change', function () {
        const option = this.options[this.selectedIndex];
        if (!option.value) {
            // Reset fields
            document.getElementById('asset_id').value = '';
            document.getElementById('asset_name').value = '';
            document.getElementById('manufacturer').value = '';
            document.getElementById('model_number').value = '';
            document.getElementById('serial_number').value = '';
            document.getElementById('warranty_provider').value = '';
            document.getElementById('warranty_support_level').value = '';
            document.getElementById('warranty_start_date').value = '';
            document.getElementById('warranty_end_date').value = '';
            document.getElementById('warranty_duration_years').value = '3';
            document.getElementById('warranty_onsite_support').checked = true;
            document.getElementById('warranty_parts_coverage').value = '';
            document.getElementById('warranty_labor_coverage').value = '';
            document.getElementById('warranty_transferable').checked = false;
            document.getElementById('warranty_terms').value = '';
            document.getElementById('amc_available').checked = true;
            document.getElementById('amc_provider').value = '';
            document.getElementById('amc_support_level').value = '';
            document.getElementById('amc_duration_years').value = '2';
            document.getElementById('amc_start_date').value = '';
            document.getElementById('amc_end_date').value = '';
            document.getElementById('amc_response_time').value = '';
            document.getElementById('amc_resolution_time').value = '';
            document.getElementById('amc_escalation_time').value = '';
            document.getElementById('amc_coverage').value = '';
            document.getElementById('amc_terms').value = '';
            document.getElementById('purchase_order_no').value = '';
            document.getElementById('invoice_no').value = '';
            document.getElementById('purchase_date').value = '';
            document.getElementById('invoice_date').value = '';
            document.getElementById('warranty_cost').value = '0.00';
            document.getElementById('amc_cost').value = '0.00';
            document.getElementById('tax').value = '0.00';
            document.getElementById('total_amc_cost').value = '0.00';
            document.getElementById('availability_sla').value = '99.95';
            document.getElementById('response_sla').value = '';
            document.getElementById('resolution_sla').value = '';
            document.getElementById('notification_recipients').value = '';
            document.getElementById('asset_owner').value = '';
            document.getElementById('custodian').value = '';
            document.getElementById('responsible_person').value = '';
            document.getElementById('contact_number').value = '';
            document.getElementById('additional_notes').value = '';
            return;
        }

        document.getElementById('asset_id').value = option.getAttribute('data-asset-id') || '';
        document.getElementById('asset_name').value = option.getAttribute('data-name') || '';
        document.getElementById('manufacturer').value = option.getAttribute('data-manufacturer') || '';
        document.getElementById('model_number').value = option.getAttribute('data-model') || '';
        document.getElementById('serial_number').value = option.getAttribute('data-serial') || '';
        
        document.getElementById('warranty_type').value = option.getAttribute('data-warranty-type') || 'OEM Warranty';
        document.getElementById('warranty_provider').value = option.getAttribute('data-warranty-provider') || '';
        document.getElementById('warranty_support_level').value = option.getAttribute('data-warranty-support') || '';
        document.getElementById('warranty_status').value = option.getAttribute('data-warranty-status') || 'Active';
        document.getElementById('warranty_start_date').value = option.getAttribute('data-warranty-start') || '';
        document.getElementById('warranty_end_date').value = option.getAttribute('data-warranty-end') || '';
        document.getElementById('warranty_duration_years').value = option.getAttribute('data-warranty-duration') || '3';
        document.getElementById('warranty_onsite_support').checked = option.getAttribute('data-warranty-onsite') === '1';
        document.getElementById('warranty_parts_coverage').value = option.getAttribute('data-warranty-parts') || '';
        document.getElementById('warranty_labor_coverage').value = option.getAttribute('data-warranty-labor') || '';
        document.getElementById('warranty_transferable').checked = option.getAttribute('data-warranty-transferable') === '1';
        document.getElementById('warranty_terms').value = option.getAttribute('data-warranty-terms') || '';

        document.getElementById('amc_available').checked = option.getAttribute('data-amc-available') === '1';
        document.getElementById('amc_type').value = option.getAttribute('data-amc-type') || 'Comprehensive';
        document.getElementById('amc_provider').value = option.getAttribute('data-amc-provider') || '';
        document.getElementById('amc_support_level').value = option.getAttribute('data-amc-support') || '';
        document.getElementById('amc_duration_years').value = option.getAttribute('data-amc-duration') || '2';
        document.getElementById('amc_start_date').value = option.getAttribute('data-amc-start') || '';
        document.getElementById('amc_end_date').value = option.getAttribute('data-amc-end') || '';
        document.getElementById('amc_response_time').value = option.getAttribute('data-amc-response') || '';
        document.getElementById('amc_resolution_time').value = option.getAttribute('data-amc-resolution') || '';
        document.getElementById('amc_escalation_time').value = option.getAttribute('data-amc-escalation') || '';
        document.getElementById('amc_coverage').value = option.getAttribute('data-amc-coverage') || '';
        document.getElementById('amc_terms').value = option.getAttribute('data-amc-terms') || '';

        document.getElementById('purchase_order_no').value = option.getAttribute('data-po') || '';
        document.getElementById('invoice_no').value = option.getAttribute('data-invoice-no') || '';
        document.getElementById('purchase_date').value = option.getAttribute('data-purchase-date') || '';
        document.getElementById('invoice_date').value = option.getAttribute('data-invoice-date') || '';
        document.getElementById('warranty_cost').value = option.getAttribute('data-warranty-cost') || '0.00';
        document.getElementById('amc_cost').value = option.getAttribute('data-amc-cost') || '0.00';
        document.getElementById('currency').value = option.getAttribute('data-currency') || 'INR';
        document.getElementById('tax').value = option.getAttribute('data-tax') || '0.00';
        document.getElementById('total_amc_cost').value = option.getAttribute('data-total-amc-cost') || '0.00';

        document.getElementById('customer_sla_policy').value = option.getAttribute('data-sla-policy') || 'Standard Incident SLA';
        document.getElementById('availability_sla').value = option.getAttribute('data-sla-availability') || '99.95';
        document.getElementById('response_sla').value = option.getAttribute('data-sla-response') || '';
        document.getElementById('resolution_sla').value = option.getAttribute('data-sla-resolution') || '';

        document.getElementById('renewal_reminder').value = option.getAttribute('data-renewal-reminder') || '30 Days Before';
        document.getElementById('amc_renewal_reminder').value = option.getAttribute('data-amc-renewal') || '30 Days Before';
        document.getElementById('warranty_expiry_alert').checked = option.getAttribute('data-expiry-alert') === '1';
        document.getElementById('amc_expiry_alert').checked = option.getAttribute('data-amc-alert') === '1';
        document.getElementById('notification_recipients').value = option.getAttribute('data-recipients') || '';

        document.getElementById('asset_owner').value = option.getAttribute('data-owner') || '';
        document.getElementById('custodian').value = option.getAttribute('data-custodian') || '';
        document.getElementById('responsible_person').value = option.getAttribute('data-responsible') || '';
        document.getElementById('contact_number').value = option.getAttribute('data-contact') || '';
        document.getElementById('additional_notes').value = option.getAttribute('data-notes') || '';
    });

    // Auto-trigger change if preset query param exists
    if (deviceSelect.value) {
        deviceSelect.dispatchEvent(new Event('change'));
    }
});
</script>
@endsection
