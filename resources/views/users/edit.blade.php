@extends('layouts.app')

@section('content')
@php
    $today = now()->format('Y-m-d');
    $oneYearLater = now()->addYear()->format('Y-m-d');
@endphp

<!-- Load Custom Form Styles -->
<link rel="stylesheet" href="{{ asset('css/user-form.css') }}">

<div class="page-header" style="margin-bottom: 1.5rem;">
    <div class="page-title">
        <div class="breadcrumb" style="font-size: 0.78rem; color: var(--text-muted); margin-bottom: 0.25rem;">
            <a href="{{ route('dashboard') }}" style="color: var(--text-muted);">Administration</a> &gt; 
            <a href="{{ route('users.index') }}" style="color: var(--text-muted);">Users</a> &gt; 
            <span style="color: var(--text-dark); font-weight: 500;">Edit Engineer User</span>
        </div>
        <h1 style="font-size: 1.75rem; font-weight: 800; color: var(--text-dark); display: flex; align-items: center; gap: 0.5rem;">
            Edit Engineer User: {{ $user->name }}
        </h1>
    </div>
</div>

<form action="{{ route('users.update', $user) }}" method="POST" enctype="multipart/form-data" id="engineerUserForm">
    @csrf
    @method('PUT')
    <input type="hidden" name="start_date" value="{{ old('start_date', $user->start_date?->format('Y-m-d') ?? $today) }}">
    <input type="hidden" name="expire_date" value="{{ old('expire_date', $user->expire_date?->format('Y-m-d') ?? $oneYearLater) }}">

    <div class="user-form-container">
        
        <!-- Left Side: Main Form Sections -->
        <div class="form-scroll-area">
            
            <!-- Section 1: Personal Information -->
            <div class="form-section">
                <h3 class="form-section-title">1. Personal Information</h3>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="name" class="required">Full Name</label>
                        <input type="text" id="name" name="name" class="form-control" placeholder="e.g. Vijay Kumar" required value="{{ old('name', $user->name) }}">
                    </div>
                    <div class="form-group">
                        <label for="email" class="required">Email ID</label>
                        <input type="email" id="email" name="email" class="form-control" placeholder="e.g. vijay.kumar@westernrail.in" required value="{{ old('email', $user->email) }}">
                    </div>
                    <div class="form-group">
                        <label for="mobile" class="required">Mobile Number</label>
                        <input type="text" id="mobile" name="mobile" class="form-control" placeholder="e.g. +91 98765 43210" required value="{{ old('mobile', $user->mobile) }}">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="dob">Date of Birth</label>
                        <input type="date" id="dob" name="dob" class="form-control" value="{{ old('dob', $user->dob?->format('Y-m-d')) }}">
                    </div>
                    <div class="form-group">
                        <label for="gender">Gender</label>
                        <select id="gender" name="gender" class="form-control">
                            <option value="">Select Gender</option>
                            <option value="Male" {{ old('gender', $user->gender) === 'Male' ? 'selected' : '' }}>Male</option>
                            <option value="Female" {{ old('gender', $user->gender) === 'Female' ? 'selected' : '' }}>Female</option>
                            <option value="Other" {{ old('gender', $user->gender) === 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Profile Photo</label>
                        <div class="file-upload-wrapper">
                            <div class="file-upload-preview" id="photoPreviewContainer">
                                @if($user->profile_photo)
                                    <img src="{{ asset($user->profile_photo) }}" alt="Preview">
                                @else
                                    <i class="fa-solid fa-user-tie fa-lg"></i>
                                @endif
                            </div>
                            <div class="file-upload-info">
                                <label for="profile_photo">Choose File</label>
                                <span id="photoFilename">{{ $user->profile_photo ? basename($user->profile_photo) : 'No file chosen' }}</span>
                                <span>(Max file size: 2MB)</span>
                            </div>
                            <input type="file" id="profile_photo" name="profile_photo" class="file-upload-input" accept="image/*">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Signature (Optional)</label>
                        <div class="file-upload-wrapper">
                            <div class="file-upload-preview" id="signaturePreviewContainer">
                                @if($user->signature)
                                    <img src="{{ asset($user->signature) }}" alt="Preview">
                                @else
                                    <i class="fa-solid fa-signature fa-lg"></i>
                                @endif
                            </div>
                            <div class="file-upload-info">
                                <label for="signature">Choose File</label>
                                <span id="signatureFilename">{{ $user->signature ? basename($user->signature) : 'No file chosen' }}</span>
                                <span>(Max file size: 2MB)</span>
                            </div>
                            <input type="file" id="signature" name="signature" class="file-upload-input" accept="image/*">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 2: Login & Access Information -->
            <div class="form-section">
                <h3 class="form-section-title">2. Login & Access Information</h3>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="role_id" class="required">Role</label>
                        <select id="role_id" name="role_id" class="form-control" required {{ $assignableRoles->count() <= 1 ? 'disabled' : '' }}>
                            @foreach($assignableRoles as $role)
                                <option value="{{ $role->id }}" {{ (string) old('role_id', $user->role_id) === (string) $role->id ? 'selected' : '' }}>
                                    {{ $role->name }}
                                </option>
                            @endforeach
                        </select>
                        @if($assignableRoles->count() <= 1)
                            <input type="hidden" name="role_id" value="{{ $user->role_id }}">
                        @endif
                    </div>
                    <div class="form-group">
                        <label for="status">Account Status</label>
                        <select id="status" name="status" class="form-control" required>
                            <option value="Active" {{ old('status', $user->status) === 'Active' ? 'selected' : '' }}>Active</option>
                            <option value="Inactive" {{ old('status', $user->status) === 'Inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Password <span style="font-weight: normal; color: var(--text-muted);">(leave blank to keep)</span></label>
                        <div style="position: relative;">
                            <input type="password" id="password" name="password" class="form-control" style="padding-right: 2.5rem;">
                            <i class="fa-regular fa-eye-slash show-password-toggle" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer; color: var(--text-muted);" onclick="togglePasswordVisibility('password', this)"></i>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="password_confirmation">Confirm Password</label>
                        <div style="position: relative;">
                            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" style="padding-right: 2.5rem;">
                            <i class="fa-regular fa-eye-slash show-password-toggle" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer; color: var(--text-muted);" onclick="togglePasswordVisibility('password_confirmation', this)"></i>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="password_expiry_days">Password Expiry (Days)</label>
                        <input type="number" id="password_expiry_days" name="password_expiry_days" class="form-control" min="0" value="{{ old('password_expiry_days', $user->password_expiry_days ?? 90) }}">
                    </div>
                    <div class="form-group">
                        <label for="failed_login_attempts">Failed Login Attempts</label>
                        <input type="number" id="failed_login_attempts" name="failed_login_attempts" class="form-control" min="0" value="{{ old('failed_login_attempts', $user->failed_login_attempts ?? 5) }}">
                    </div>
                    <div class="form-group">
                        <label for="lockout_minutes">Account Lockout (Minutes)</label>
                        <input type="number" id="lockout_minutes" name="lockout_minutes" class="form-control" min="0" value="{{ old('lockout_minutes', $user->lockout_minutes ?? 30) }}">
                    </div>
                </div>

                <div class="form-row" style="grid-template-columns: 1fr 1fr;">
                    <div class="form-group">
                        <div class="toggle-switch-wrapper">
                            <label class="toggle-switch">
                                <input type="checkbox" name="two_factor" value="1" {{ old('two_factor', $user->two_factor) ? 'checked' : '' }} id="two_factor">
                                <span class="toggle-slider"></span>
                            </label>
                            <span class="toggle-switch-label">Two Factor Authentication (2FA)</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="toggle-switch-wrapper">
                            <label class="toggle-switch">
                                <input type="checkbox" name="force_password_change" value="1" {{ old('force_password_change', $user->force_password_change) ? 'checked' : '' }} id="force_password_change">
                                <span class="toggle-slider"></span>
                            </label>
                            <span class="toggle-switch-label">Force Password Change on First Login</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 4: SLA Association -->
            <div class="form-section">
                <h3 class="form-section-title">3. SLA Association</h3>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="sla_policy_id" class="required">SLA Policy</label>
                        <select id="sla_policy_id" name="sla_policy_id" class="form-control" required>
                            <option value="">Select SLA Policy</option>
                            @foreach($slaPolicies as $policy)
                                <option value="{{ $policy->id }}" 
                                        data-desc="{{ $policy->description }}"
                                        data-response="{{ $policy->response_time_minutes }} Minutes"
                                        data-resolution="{{ $policy->resolution_time_minutes }} Hours"
                                        data-escalation="{{ $policy->escalation_time_minutes }} Minutes"
                                        data-tickets="{{ $policy->max_tickets_per_day }}"
                                        data-changes="{{ $policy->max_changes_per_week }}"
                                        {{ old('sla_policy_id', $user->sla_policy_id) == $policy->id ? 'selected' : '' }}>
                                    {{ $policy->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="business_unit">Business Unit</label>
                        <select id="business_unit" name="business_unit" class="form-control">
                            <option value="IT Operations" {{ old('business_unit', $user->business_unit) === 'IT Operations' ? 'selected' : '' }}>IT Operations</option>
                            <option value="Network Infrastructure" {{ old('business_unit', $user->business_unit) === 'Network Infrastructure' ? 'selected' : '' }}>Network Infrastructure</option>
                            <option value="Security Management" {{ old('business_unit', $user->business_unit) === 'Security Management' ? 'selected' : '' }}>Security Management</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="max_tickets_per_day">Max Tickets / Day</label>
                        <input type="number" id="max_tickets_per_day" name="max_tickets_per_day" class="form-control" min="0" value="{{ old('max_tickets_per_day', $user->max_tickets_per_day ?? 50) }}">
                    </div>
                    <div class="form-group">
                        <label for="max_changes_per_week">Max Changes / Week</label>
                        <input type="number" id="max_changes_per_week" name="max_changes_per_week" class="form-control" min="0" value="{{ old('max_changes_per_week', $user->max_changes_per_week ?? 10) }}">
                    </div>
                </div>
            </div>

            <!-- Section 6: Notifications & Preferences -->
            <div class="form-section">
                <h3 class="form-section-title">4. Notifications & Preferences</h3>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="required">Notification Method</label>
                        <div style="display: flex; gap: 1.5rem; margin-top: 0.5rem;">
                            @php
                                $notifMethods = old('notification_methods', $user->notification_methods ?? ['Email', 'SMS', 'In-App']);
                            @endphp
                            <label class="checkbox-card">
                                <input type="checkbox" name="notification_methods[]" value="Email" {{ in_array('Email', $notifMethods) ? 'checked' : '' }}>
                                Email
                            </label>
                            <label class="checkbox-card">
                                <input type="checkbox" name="notification_methods[]" value="SMS" {{ in_array('SMS', $notifMethods) ? 'checked' : '' }}>
                                SMS
                            </label>
                            <label class="checkbox-card">
                                <input type="checkbox" name="notification_methods[]" value="In-App" {{ in_array('In-App', $notifMethods) ? 'checked' : '' }}>
                                In-App
                            </label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="working_hours">Working Hours</label>
                        <select id="working_hours" name="working_hours" class="form-control">
                            <option value="24 x 7" {{ old('working_hours', $user->working_hours) === '24 x 7' ? 'selected' : '' }}>24 x 7</option>
                            <option value="8 x 5" {{ old('working_hours', $user->working_hours) === '8 x 5' ? 'selected' : '' }}>8 x 5 (Standard Business)</option>
                            <option value="12 x 5" {{ old('working_hours', $user->working_hours) === '12 x 5' ? 'selected' : '' }}>12 x 5</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="escalation_group">Escalation Group</label>
                        <select id="escalation_group" name="escalation_group" class="form-control">
                            <option value="Network Operations Team" {{ old('escalation_group', $user->escalation_group) === 'Network Operations Team' ? 'selected' : '' }}>Network Operations Team</option>
                            <option value="Security Operations Team" {{ old('escalation_group', $user->escalation_group) === 'Security Operations Team' ? 'selected' : '' }}>Security Operations Team</option>
                            <option value="Systems Ops Group" {{ old('escalation_group', $user->escalation_group) === 'Systems Ops Group' ? 'selected' : '' }}>Systems Ops Group</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="preferred_dashboard">Preferred Dashboard</label>
                        <select id="preferred_dashboard" name="preferred_dashboard" class="form-control">
                            <option value="Engineer Dashboard" {{ old('preferred_dashboard', $user->preferred_dashboard) === 'Engineer Dashboard' ? 'selected' : '' }}>Engineer Dashboard</option>
                            <option value="NMS Main Dashboard" {{ old('preferred_dashboard', $user->preferred_dashboard) === 'NMS Main Dashboard' ? 'selected' : '' }}>NMS Main Dashboard</option>
                            <option value="Service Desk View" {{ old('preferred_dashboard', $user->preferred_dashboard) === 'Service Desk View' ? 'selected' : '' }}>Service Desk View</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Section 7: Additional Information -->
            <div class="form-section">
                <h3 class="form-section-title">5. Additional Information</h3>
                
                <div class="form-row" style="grid-template-columns: 1fr 1fr;">
                    <div class="form-group">
                        <label>Skills / Expertise</label>
                        <div class="tags-input-container" id="skillsContainer">
                            @php
                                $skills = old('skills', $user->skills ?? ['Routing', 'Switching', 'WAN', 'VPN']);
                            @endphp
                            @foreach($skills as $skill)
                                <span class="tag-badge">{{ $skill }} <span class="remove-tag" onclick="removeTag(this)">&times;</span></span>
                            @endforeach
                            <input type="text" class="tags-input-field" placeholder="+ Add skill" onkeydown="handleTagInput(event, 'skills[]', this)">
                        </div>
                        <div id="skillsHiddenInputs">
                            @foreach($skills as $skill)
                                <input type="hidden" name="skills[]" value="{{ $skill }}">
                            @endforeach
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="certifications">Certifications (Optional)</label>
                        <input type="text" id="certifications" name="certifications" class="form-control" placeholder="e.g. CCNA, CCNP, CISSP" value="{{ old('certifications', $user->certifications) }}">
                    </div>
                </div>

                <div class="form-group">
                    <label for="notes">Notes</label>
                    <textarea id="notes" name="notes" class="form-control" rows="3" placeholder="e.g. Engineer for core network operations and incidents handling.">{{ old('notes', $user->notes) }}</textarea>
                </div>
            </div>

            <!-- Section 8: Attachments -->
            <div class="form-section">
                <h3 class="form-section-title">6. Attachments</h3>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>ID Proof</label>
                        <div class="file-upload-wrapper">
                            <div class="file-upload-preview" id="idProofPreview">
                                <i class="fa-solid fa-file-pdf fa-lg"></i>
                            </div>
                            <div class="file-upload-info">
                                <label for="id_proof">Choose New File</label>
                                <span id="idProofFilename">{{ $user->id_proof ? basename($user->id_proof) : 'No file chosen' }}</span>
                                <span>(Max file size: 5MB)</span>
                            </div>
                            <input type="file" id="id_proof" name="id_proof" class="file-upload-input" accept=".pdf,.doc,.docx,.jpg,.png">
                        </div>
                        @if($user->id_proof)
                            <div style="margin-top: 0.5rem; font-size: 0.8rem;">
                                <span style="color: var(--text-muted);">Current:</span>
                                <a href="{{ asset($user->id_proof) }}" target="_blank" style="color: var(--primary); font-weight: 600;">
                                    <i class="fa-solid fa-file-arrow-down"></i> View ID Proof
                                </a>
                            </div>
                        @endif
                    </div>

                    <div class="form-group">
                        <label>Offer Letter / Appointment Letter</label>
                        <div class="file-upload-wrapper">
                            <div class="file-upload-preview" id="offerLetterPreview">
                                <i class="fa-solid fa-file-pdf fa-lg"></i>
                            </div>
                            <div class="file-upload-info">
                                <label for="offer_letter">Choose New File</label>
                                <span id="offerLetterFilename">{{ $user->offer_letter ? basename($user->offer_letter) : 'No file chosen' }}</span>
                                <span>(Max file size: 5MB)</span>
                            </div>
                            <input type="file" id="offer_letter" name="offer_letter" class="file-upload-input" accept=".pdf,.doc,.docx,.jpg,.png">
                        </div>
                        @if($user->offer_letter)
                            <div style="margin-top: 0.5rem; font-size: 0.8rem;">
                                <span style="color: var(--text-muted);">Current:</span>
                                <a href="{{ asset($user->offer_letter) }}" target="_blank" style="color: var(--primary); font-weight: 600;">
                                    <i class="fa-solid fa-file-arrow-down"></i> View Offer Letter
                                </a>
                            </div>
                        @endif
                    </div>

                    <div class="form-group">
                        <label>Other Documents (Optional)</label>
                        <div class="file-upload-wrapper">
                            <div class="file-upload-preview" id="otherDocPreview">
                                <i class="fa-solid fa-file-pdf fa-lg"></i>
                            </div>
                            <div class="file-upload-info">
                                <label for="other_document">Choose New File</label>
                                <span id="otherDocFilename">{{ $user->other_document ? basename($user->other_document) : 'No file chosen' }}</span>
                                <span>(Max file size: 5MB)</span>
                            </div>
                            <input type="file" id="other_document" name="other_document" class="file-upload-input" accept=".pdf,.doc,.docx,.jpg,.png">
                        </div>
                        @if($user->other_document)
                            <div style="margin-top: 0.5rem; font-size: 0.8rem;">
                                <span style="color: var(--text-muted);">Current:</span>
                                <a href="{{ asset($user->other_document) }}" target="_blank" style="color: var(--primary); font-weight: 600;">
                                    <i class="fa-solid fa-file-arrow-down"></i> View Document
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Save and update actions -->
            <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 1.5rem;">
                <a href="{{ route('users.index') }}" class="btn-secondary" style="display: inline-flex; align-items: center; justify-content: center; padding: 0.75rem 1.5rem; text-decoration: none; border-radius: 8px;">Cancel</a>
                <button type="submit" class="btn-primary" style="width: auto; padding: 0.75rem 2rem; border-radius: 8px; font-weight: 700;">Update User</button>
            </div>

        </div>

        <!-- Right Side: Sticky User Summary Sidebar -->
        <div class="sticky-summary">
            
            <!-- Card 1: User Summary -->
            <div class="summary-card">
                <h3>User Summary</h3>
                <div class="summary-profile-header">
                    <div class="summary-avatar" id="summaryAvatar">
                        @if($user->profile_photo)
                            <img src="{{ asset($user->profile_photo) }}" alt="Preview">
                        @else
                            <i class="fa-solid fa-user-tie fa-2xl" style="color: #94a3b8;"></i>
                        @endif
                    </div>
                    <div class="summary-profile-info">
                        <h4 id="sidebarName">{{ $user->name }}</h4>
                        <p id="sidebarDesignation">{{ $user->roleLabel() }}</p>
                    </div>
                    <div style="margin-left: auto;">
                        <span class="{{ $user->status === 'Active' ? 'badge-active' : 'badge-inactive' }}" id="sidebarStatus">{{ $user->status }}</span>
                    </div>
                </div>
                
                <div class="summary-details-list">
                    <div class="summary-detail-item">
                        <span class="label">Full Name</span>
                        <span class="value" id="sidebarFullName">{{ $user->name }}</span>
                    </div>
                    <div class="summary-detail-item">
                        <span class="label">Email ID</span>
                        <span class="value" id="sidebarEmail">{{ $user->email }}</span>
                    </div>
                    <div class="summary-detail-item">
                        <span class="label">Mobile</span>
                        <span class="value" id="sidebarMobile">{{ $user->mobile ?? '—' }}</span>
                    </div>
                    <div class="summary-detail-item">
                        <span class="label">Role</span>
                        <span class="value" id="sidebarRole">{{ $user->roleLabel() }}</span>
                    </div>
                    <div class="summary-detail-item">
                        <span class="label">Access Level</span>
                        <span class="value" id="sidebarAccessLevel">{{ $user->roleLabel() }}</span>
                    </div>
                    <div class="summary-detail-item">
                        <span class="label">Status</span>
                        <span class="value {{ $user->status === 'Active' ? 'badge-active' : 'badge-inactive' }}" style="display:inline-block;" id="sidebarStatusDetail">{{ $user->status }}</span>
                    </div>
                </div>
            </div>

            <!-- Card 2: Roles Assigned -->
            <div class="summary-card">
                <h3>Roles Assigned</h3>
                <div class="assigned-roles-list" id="sidebarRolesList">
                    @foreach($userRoles as $uRole)
                        <span class="role-badge">{{ $uRole }}</span>
                    @endforeach
                </div>
            </div>

            <!-- Card 3: SLA Overview -->
            <div class="summary-card">
                <h3>SLA Overview <span style="float: right; font-size: 0.75rem; font-weight: normal; margin-top: 0.15rem;"><a href="#" style="color: var(--primary);">View SLA Policy</a></span></h3>
                <div class="summary-details-list" id="sidebarSlaDetails">
                    <div class="summary-detail-item">
                        <span class="label">SLA Policy</span>
                        <span class="value" id="sidebarSlaPolicy">{{ $user->slaPolicy?->name ?? '—' }}</span>
                    </div>
                    <div class="summary-detail-item">
                        <span class="label">Response SLA</span>
                        <span class="value" id="sidebarSlaResponse">{{ $user->slaPolicy?->response_time_minutes ?? '—' }} Minutes</span>
                    </div>
                    <div class="summary-detail-item">
                        <span class="label">Resolution SLA</span>
                        <span class="value" id="sidebarSlaResolution">{{ $user->slaPolicy?->resolution_time_minutes ?? '—' }} Hours</span>
                    </div>
                    <div class="summary-detail-item">
                        <span class="label">Escalation SLA</span>
                        <span class="value" id="sidebarSlaEscalation">{{ $user->slaPolicy?->escalation_time_minutes ?? '—' }} Minutes</span>
                    </div>
                    <div class="summary-detail-item">
                        <span class="label">Max Tickets / Day</span>
                        <span class="value" id="sidebarSlaMaxTickets">{{ $user->max_tickets_per_day ?? '50' }}</span>
                    </div>
                    <div class="summary-detail-item">
                        <span class="label">Max Changes / Week</span>
                        <span class="value" id="sidebarSlaMaxChanges">{{ $user->max_changes_per_week ?? '10' }}</span>
                    </div>
                </div>
            </div>

            <!-- Card 4: Module Access Summary -->
            <div class="summary-card">
                <h3>Module Access Summary</h3>
                <div class="progress-widget-list">
                    <div class="progress-widget-item">
                        <div class="progress-widget-header">
                            <span>Total Modules</span>
                            <span id="pbTotalCount">11</span>
                        </div>
                        <div class="progress-widget-bar-bg">
                            <div class="progress-widget-bar-fill bar-blue" style="width: 100%;" id="pbTotalFill"></div>
                        </div>
                    </div>
                    <div class="progress-widget-item">
                        <div class="progress-widget-header">
                            <span>Full Access</span>
                            <span id="pbFullCount">0</span>
                        </div>
                        <div class="progress-widget-bar-bg">
                            <div class="progress-widget-bar-fill bar-green" style="width: 0%;" id="pbFullFill"></div>
                        </div>
                    </div>
                    <div class="progress-widget-item">
                        <div class="progress-widget-header">
                            <span>Limited Access</span>
                            <span id="pbLimitedCount">0</span>
                        </div>
                        <div class="progress-widget-bar-bg">
                            <div class="progress-widget-bar-fill bar-orange" style="width: 0%;" id="pbLimitedFill"></div>
                        </div>
                    </div>
                    <div class="progress-widget-item">
                        <div class="progress-widget-header">
                            <span>No Access</span>
                            <span id="pbNoCount">11</span>
                        </div>
                        <div class="progress-widget-bar-bg">
                            <div class="progress-widget-bar-fill bar-red" style="width: 100%;" id="pbNoFill"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 5: Account Status -->
            <div class="summary-card">
                <h3>Account Status</h3>
                <div class="summary-details-list">
                    <div class="summary-detail-item">
                        <span class="label">Status</span>
                        <span class="value" id="sidebarAccountStatus">{{ $user->status }}</span>
                    </div>
                    <div class="summary-detail-item">
                        <span class="label">2FA Enabled</span>
                        <span class="value" id="sidebarAccount2FA">{{ $user->two_factor ? 'Yes' : 'No' }}</span>
                    </div>
                    <div class="summary-detail-item">
                        <span class="label">Password Expiry</span>
                        <span class="value" id="sidebarAccountExpiry">{{ $user->password_expiry_days ?? 90 }} Days</span>
                    </div>
                    <div class="summary-detail-item">
                        <span class="label">Last Login</span>
                        <span class="value">—</span>
                    </div>
                    <div class="summary-detail-item">
                        <span class="label">Failed Attempts</span>
                        <span class="value" id="sidebarAccountFailed">{{ $user->failed_login_attempts ?? 0 }}</span>
                    </div>
                </div>
            </div>

        </div>

    </div>
</form>

<script>
    // Toggle Password Visibility
    function togglePasswordVisibility(fieldId, iconEl) {
        const input = document.getElementById(fieldId);
        if (input.type === 'password') {
            input.type = 'text';
            iconEl.classList.remove('fa-eye-slash');
            iconEl.classList.add('fa-eye');
        } else {
            input.type = 'password';
            iconEl.classList.remove('fa-eye');
            iconEl.classList.add('fa-eye-slash');
        }
    }

    // Dynamic File Uploads Previewing & Filenames
    function setupFileUploader(inputId, previewId, filenameId, defaultIconHtml) {
        const fileInput = document.getElementById(inputId);
        const previewEl = document.getElementById(previewId);
        const filenameEl = document.getElementById(filenameId);

        if (!fileInput) return;

        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                filenameEl.textContent = file.name;
                
                // If it is an image, render preview
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(evt) {
                        previewEl.innerHTML = `<img src="${evt.target.result}" alt="Preview">`;
                        
                        // If profile photo uploader, also update sidebar avatar preview
                        if (inputId === 'profile_photo') {
                            document.getElementById('summaryAvatar').innerHTML = `<img src="${evt.target.result}" alt="Preview">`;
                        }
                    }
                    reader.readAsDataURL(file);
                } else {
                    // Document icons based on extension
                    const ext = file.name.split('.').pop().toLowerCase();
                    let iconClass = 'fa-file-pdf';
                    if (['doc', 'docx'].includes(ext)) iconClass = 'fa-file-word';
                    previewEl.innerHTML = `<i class="fa-solid ${iconClass} fa-lg" style="color: var(--primary);"></i>`;
                }
            } else {
                // If no file chosen, keep existing state
            }
        });
    }

    // Dynamic Tag Handling for Service Categories, Alert Emails, Skills, etc.
    function handleTagInput(e, name, inputEl, isEmail = false) {
        if (e.key === 'Enter' || e.key === ',') {
            e.preventDefault();
            let val = inputEl.value.trim().replace(/,/g, '');
            if (val === '') return;

            if (isEmail && !validateEmail(val)) {
                alert('Please enter a valid email address');
                return;
            }

            const container = inputEl.parentElement;
            const hiddenInputsContainer = document.getElementById(container.id.replace('Container', 'HiddenInputs'));

            // Create Badge
            const badge = document.createElement('span');
            badge.className = 'tag-badge';
            badge.innerHTML = `${val} <span class="remove-tag" onclick="removeTag(this)">&times;</span>`;
            container.insertBefore(badge, inputEl);

            // Create Hidden Input
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = name;
            hiddenInput.value = val;
            hiddenInputsContainer.appendChild(hiddenInput);

            inputEl.value = '';
        }
    }

    function removeTag(spanEl) {
        const badge = spanEl.parentElement;
        const container = badge.parentElement;
        const val = badge.textContent.trim().slice(0, -1).trim(); // Remove the 'x' character
        const hiddenInputsContainer = document.getElementById(container.id.replace('Container', 'HiddenInputs'));

        // Remove badge
        badge.remove();

        // Remove hidden input
        const inputs = hiddenInputsContainer.querySelectorAll('input');
        for (let input of inputs) {
            if (input.value === val) {
                input.remove();
                break;
            }
        }
    }

    function validateEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }

    // Core Real-Time Summary Syncing
    document.addEventListener('DOMContentLoaded', function() {
        // Setup File Uploaders
        setupFileUploader('profile_photo', 'photoPreviewContainer', 'photoFilename', '<i class="fa-solid fa-user-tie fa-lg"></i>');
        setupFileUploader('signature', 'signaturePreviewContainer', 'signatureFilename', '<i class="fa-solid fa-signature fa-lg"></i>');
        setupFileUploader('id_proof', 'idProofPreview', 'idProofFilename', '<i class="fa-solid fa-file-pdf fa-lg"></i>');
        setupFileUploader('offer_letter', 'offerLetterPreview', 'offerLetterFilename', '<i class="fa-solid fa-file-pdf fa-lg"></i>');
        setupFileUploader('other_document', 'otherDocPreview', 'otherDocFilename', '<i class="fa-solid fa-file-pdf fa-lg"></i>');

        // Inputs to monitor for simple textual changes
        const binds = [
            { sourceId: 'name', targetIds: ['sidebarName', 'sidebarFullName'] },
            { sourceId: 'email', targetIds: ['sidebarEmail'] },
            { sourceId: 'mobile', targetIds: ['sidebarMobile'] },
            { sourceId: 'role_id', targetIds: ['sidebarRole', 'sidebarAccessLevel', 'sidebarDesignation'] },
        ];

        binds.forEach(bind => {
            const input = document.getElementById(bind.sourceId);
            if (input) {
                const updateTargets = () => {
                    let val = input.value || '—';
                    if (bind.sourceId === 'role_id' && input.options && input.selectedIndex >= 0) {
                        val = input.options[input.selectedIndex].text || val;
                    }
                    bind.targetIds.forEach(targetId => {
                        const target = document.getElementById(targetId);
                        if (target) target.textContent = val;
                    });
                };
                input.addEventListener('input', updateTargets);
                input.addEventListener('change', updateTargets);
            }
        });

        // Account status monitoring
        const statusSelect = document.getElementById('status');
        if (statusSelect) {
            statusSelect.addEventListener('change', function() {
                const val = statusSelect.value;
                const badges = [document.getElementById('sidebarStatus'), document.getElementById('sidebarStatusDetail'), document.getElementById('sidebarAccountStatus')];
                badges.forEach(badge => {
                    if (badge) {
                        badge.textContent = val;
                        badge.className = val === 'Active' ? 'badge-active' : 'badge-inactive';
                    }
                });
            });
        }

        // SLA Expiry / 2FA monitoring
        const twoFaCheckbox = document.getElementById('two_factor');
        if (twoFaCheckbox) {
            twoFaCheckbox.addEventListener('change', function() {
                const text = twoFaCheckbox.checked ? 'Yes' : 'No';
                const el = document.getElementById('sidebarAccount2FA');
                if (el) el.textContent = text;
            });
        }

        const passExpiry = document.getElementById('password_expiry_days');
        if (passExpiry) {
            passExpiry.addEventListener('input', function() {
                const val = passExpiry.value ? `${passExpiry.value} Days` : 'Never';
                const el = document.getElementById('sidebarAccountExpiry');
                if (el) el.textContent = val;
            });
        }

        const failedAttempts = document.getElementById('failed_login_attempts');
        if (failedAttempts) {
            failedAttempts.addEventListener('input', function() {
                const val = failedAttempts.value || '0';
                const el = document.getElementById('sidebarAccountFailed');
                if (el) el.textContent = val;
            });
        }

        // SLA Policy dropdown changes
        const slaSelect = document.getElementById('sla_policy_id');
        if (slaSelect) {
            function updateSlaOverview() {
                const opt = slaSelect.options[slaSelect.selectedIndex];
                if (opt && opt.value) {
                    document.getElementById('sidebarSlaPolicy').textContent = opt.textContent.trim();
                    document.getElementById('sidebarSlaResponse').textContent = opt.getAttribute('data-response') || '—';
                    document.getElementById('sidebarSlaResolution').textContent = opt.getAttribute('data-resolution') || '—';
                    document.getElementById('sidebarSlaEscalation').textContent = opt.getAttribute('data-escalation') || '—';
                    
                    const maxTickets = opt.getAttribute('data-tickets') || '—';
                    const maxChanges = opt.getAttribute('data-changes') || '—';
                    document.getElementById('sidebarSlaMaxTickets').textContent = maxTickets;
                    document.getElementById('sidebarSlaMaxChanges').textContent = maxChanges;

                    // Sync values to form inputs in SLA section
                    if (maxTickets !== '—') document.getElementById('max_tickets_per_day').value = maxTickets;
                    if (maxChanges !== '—') document.getElementById('max_changes_per_week').value = maxChanges;
                } else {
                    document.getElementById('sidebarSlaPolicy').textContent = '—';
                    document.getElementById('sidebarSlaResponse').textContent = '—';
                    document.getElementById('sidebarSlaResolution').textContent = '—';
                    document.getElementById('sidebarSlaEscalation').textContent = '—';
                    document.getElementById('sidebarSlaMaxTickets').textContent = '—';
                    document.getElementById('sidebarSlaMaxChanges').textContent = '—';
                }
            }
            slaSelect.addEventListener('change', updateSlaOverview);
            // Don't auto-override initial DB values unless the user selects a new option
        }
    });
</script>
@endsection
