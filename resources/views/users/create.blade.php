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
            <span style="color: var(--text-dark); font-weight: 500;">Create Engineer User</span>
        </div>
        <h1 style="font-size: 1.75rem; font-weight: 800; color: var(--text-dark); display: flex; align-items: center; gap: 0.5rem;">
            Create Engineer User
        </h1>
    </div>
</div>

<form action="{{ route('users.store') }}" method="POST" enctype="multipart/form-data" id="engineerUserForm">
    @csrf
    <input type="hidden" name="device_limit" value="50">
    <input type="hidden" name="start_date" value="{{ $today }}">
    <input type="hidden" name="expire_date" value="{{ $oneYearLater }}">

    <div class="user-form-container">
        
        <!-- Left Side: Main Form Sections -->
        <div class="form-scroll-area">
            
            <!-- Section 1: Personal Information -->
            <div class="form-section">
                <h3 class="form-section-title">1. Personal Information</h3>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="name" class="required">Full Name</label>
                        <input type="text" id="name" name="name" class="form-control" placeholder="e.g. Vijay Kumar" required value="{{ old('name') }}">
                    </div>
                    <div class="form-group">
                        <label for="username" class="required">User ID</label>
                        <input type="text" id="username" name="username" class="form-control" placeholder="e.g. vijay.kumar" required value="{{ old('username') }}">
                    </div>
                    <div class="form-group">
                        <label for="employee_id">Employee ID</label>
                        <input type="text" id="employee_id" name="employee_id" class="form-control" placeholder="e.g. EMP-1025" value="{{ old('employee_id') }}">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="email" class="required">Email ID</label>
                        <input type="email" id="email" name="email" class="form-control" placeholder="e.g. vijay.kumar@westernrail.in" required value="{{ old('email') }}">
                    </div>
                    <div class="form-group">
                        <label for="mobile" class="required">Mobile Number</label>
                        <input type="text" id="mobile" name="mobile" class="form-control" placeholder="e.g. +91 98765 43210" required value="{{ old('mobile') }}">
                    </div>
                    <div class="form-group">
                        <label for="alternate_number">Alternate Number</label>
                        <input type="text" id="alternate_number" name="alternate_number" class="form-control" placeholder="e.g. +91 91234 56789" value="{{ old('alternate_number') }}">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="dob">Date of Birth</label>
                        <input type="date" id="dob" name="dob" class="form-control" value="{{ old('dob') }}">
                    </div>
                    <div class="form-group">
                        <label for="gender">Gender</label>
                        <select id="gender" name="gender" class="form-control">
                            <option value="">Select Gender</option>
                            <option value="Male" {{ old('gender') === 'Male' ? 'selected' : '' }}>Male</option>
                            <option value="Female" {{ old('gender') === 'Female' ? 'selected' : '' }}>Female</option>
                            <option value="Other" {{ old('gender') === 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="language">Language Preference</label>
                        <select id="language" name="language" class="form-control">
                            <option value="English" {{ old('language') === 'English' ? 'selected' : '' }}>English</option>
                            <option value="Hindi" {{ old('language') === 'Hindi' ? 'selected' : '' }}>Hindi</option>
                            <option value="Gujarati" {{ old('language') === 'Gujarati' ? 'selected' : '' }}>Gujarati</option>
                            <option value="Spanish" {{ old('language') === 'Spanish' ? 'selected' : '' }}>Spanish</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Profile Photo</label>
                        <div class="file-upload-wrapper">
                            <div class="file-upload-preview" id="photoPreviewContainer">
                                <i class="fa-solid fa-user-tie fa-lg"></i>
                            </div>
                            <div class="file-upload-info">
                                <label for="profile_photo">Choose File</label>
                                <span id="photoFilename">No file chosen</span>
                                <span>(Max file size: 2MB)</span>
                            </div>
                            <input type="file" id="profile_photo" name="profile_photo" class="file-upload-input" accept="image/*">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Signature (Optional)</label>
                        <div class="file-upload-wrapper">
                            <div class="file-upload-preview" id="signaturePreviewContainer">
                                <i class="fa-solid fa-signature fa-lg"></i>
                            </div>
                            <div class="file-upload-info">
                                <label for="signature">Choose File</label>
                                <span id="signatureFilename">No file chosen</span>
                                <span>(Max file size: 2MB)</span>
                            </div>
                            <input type="file" id="signature" name="signature" class="file-upload-input" accept="image/*">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 2: Organization & Contact Information -->
            <div class="form-section">
                <h3 class="form-section-title">2. Organization & Contact Information</h3>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="department" class="required">Department</label>
                        <select id="department" name="department" class="form-control" required>
                            <option value="">Select Department</option>
                            <option value="Network Operations" {{ old('department') === 'Network Operations' ? 'selected' : '' }}>Network Operations</option>
                            <option value="Security Operations" {{ old('department') === 'Security Operations' ? 'selected' : '' }}>Security Operations</option>
                            <option value="System Administration" {{ old('department') === 'System Administration' ? 'selected' : '' }}>System Administration</option>
                            <option value="IT Service Desk" {{ old('department') === 'IT Service Desk' ? 'selected' : '' }}>IT Service Desk</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="designation" class="required">Designation</label>
                        <select id="designation" name="designation" class="form-control" required>
                            <option value="">Select Designation</option>
                            <option value="Network Engineer" {{ old('designation') === 'Network Engineer' ? 'selected' : '' }}>Network Engineer</option>
                            <option value="Security Analyst" {{ old('designation') === 'Security Analyst' ? 'selected' : '' }}>Security Analyst</option>
                            <option value="Systems Administrator" {{ old('designation') === 'Systems Administrator' ? 'selected' : '' }}>Systems Administrator</option>
                            <option value="Support Desk Lead" {{ old('designation') === 'Support Desk Lead' ? 'selected' : '' }}>Support Desk Lead</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="reporting_manager" class="required">Reporting Manager</label>
                        <select id="reporting_manager" name="reporting_manager" class="form-control" required>
                            <option value="">Select Manager</option>
                            <option value="Rakesh Singh" {{ old('reporting_manager') === 'Rakesh Singh' ? 'selected' : '' }}>Rakesh Singh</option>
                            <option value="Sanjay Patel" {{ old('reporting_manager') === 'Sanjay Patel' ? 'selected' : '' }}>Sanjay Patel</option>
                            <option value="Amisha Mehta" {{ old('reporting_manager') === 'Amisha Mehta' ? 'selected' : '' }}>Amisha Mehta</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="office_location" class="required">Office Location</label>
                        <select id="office_location" name="office_location" class="form-control" required>
                            <option value="">Select Location</option>
                            <option value="Ahmedabad DC" {{ old('office_location') === 'Ahmedabad DC' ? 'selected' : '' }}>Ahmedabad DC</option>
                            <option value="Mumbai DC" {{ old('office_location') === 'Mumbai DC' ? 'selected' : '' }}>Mumbai DC</option>
                            <option value="Delhi Head Office" {{ old('office_location') === 'Delhi Head Office' ? 'selected' : '' }}>Delhi Head Office</option>
                            <option value="Bangalore Branch" {{ old('office_location') === 'Bangalore Branch' ? 'selected' : '' }}>Bangalore Branch</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="work_location">Work Location / Site</label>
                        <select id="work_location" name="work_location" class="form-control">
                            <option value="">Select Work Location</option>
                            <option value="Ahmedabad DC" {{ old('work_location') === 'Ahmedabad DC' ? 'selected' : '' }}>Ahmedabad DC</option>
                            <option value="Mumbai DC" {{ old('work_location') === 'Mumbai DC' ? 'selected' : '' }}>Mumbai DC</option>
                            <option value="On-Site Support" {{ old('work_location') === 'On-Site Support' ? 'selected' : '' }}>On-Site Support</option>
                            <option value="Remote Work" {{ old('work_location') === 'Remote Work' ? 'selected' : '' }}>Remote Work</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="timezone" class="required">Time Zone</label>
                        <select id="timezone" name="timezone" class="form-control" required>
                            <option value="Asia/Kolkata" {{ old('timezone') === 'Asia/Kolkata' ? 'selected' : '' }}>Asia/Kolkata (IST)</option>
                            <option value="UTC" {{ old('timezone') === 'UTC' ? 'selected' : '' }}>UTC</option>
                            <option value="Europe/London" {{ old('timezone') === 'Europe/London' ? 'selected' : '' }}>Europe/London (GMT)</option>
                            <option value="America/New_York" {{ old('timezone') === 'America/New_York' ? 'selected' : '' }}>America/New_York (EST)</option>
                        </select>
                    </div>
                </div>

                <div class="form-row" style="grid-template-columns: 2fr 1fr 1fr;">
                    <div class="form-group">
                        <label for="address">Address</label>
                        <textarea id="address" name="address" class="form-control" rows="2" placeholder="e.g. Ahmedabad Data Center, Near Kalupur Station, Ahmedabad, Gujarat - 380001">{{ old('address') }}</textarea>
                    </div>
                    <div class="form-group">
                        <label for="landline">Landline Number</label>
                        <input type="text" id="landline" name="landline" class="form-control" placeholder="e.g. 079-26876543" value="{{ old('landline') }}">
                    </div>
                    <div class="form-group">
                        <label for="extension">Extension</label>
                        <input type="text" id="extension" name="extension" class="form-control" placeholder="e.g. 1025" value="{{ old('extension') }}">
                    </div>
                </div>
            </div>

            <!-- Section 3: Login & Access Information -->
            <div class="form-section">
                <h3 class="form-section-title">3. Login & Access Information</h3>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="role_id" class="required">Role</label>
                        <select id="role_id" name="role_id" class="form-control" required>
                            <option value="">Select Role</option>
                            @foreach($assignableRoles as $role)
                                <option value="{{ $role->id }}" {{ (string) old('role_id', $assignableRoles->first()?->id) === (string) $role->id ? 'selected' : '' }}>
                                    {{ $role->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="status">Account Status</label>
                        <select id="status" name="status" class="form-control" required>
                            <option value="Active" {{ old('status') === 'Active' ? 'selected' : '' }}>Active</option>
                            <option value="Inactive" {{ old('status') === 'Inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="password" class="required">Password</label>
                        <div style="position: relative;">
                            <input type="password" id="password" name="password" class="form-control" required style="padding-right: 2.5rem;">
                            <i class="fa-regular fa-eye-slash show-password-toggle" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer; color: var(--text-muted);" onclick="togglePasswordVisibility('password', this)"></i>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="password_confirmation" class="required">Confirm Password</label>
                        <div style="position: relative;">
                            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required style="padding-right: 2.5rem;">
                            <i class="fa-regular fa-eye-slash show-password-toggle" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer; color: var(--text-muted);" onclick="togglePasswordVisibility('password_confirmation', this)"></i>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="password_expiry_days">Password Expiry (Days)</label>
                        <input type="number" id="password_expiry_days" name="password_expiry_days" class="form-control" min="0" value="90">
                    </div>
                    <div class="form-group">
                        <label for="failed_login_attempts">Failed Login Attempts</label>
                        <input type="number" id="failed_login_attempts" name="failed_login_attempts" class="form-control" min="0" value="5">
                    </div>
                    <div class="form-group">
                        <label for="lockout_minutes">Account Lockout (Minutes)</label>
                        <input type="number" id="lockout_minutes" name="lockout_minutes" class="form-control" min="0" value="30">
                    </div>
                </div>

                <div class="form-row" style="grid-template-columns: 1fr 1fr;">
                    <div class="form-group">
                        <div class="toggle-switch-wrapper">
                            <label class="toggle-switch">
                                <input type="checkbox" name="two_factor" value="1" {{ old('two_factor') ? 'checked' : '' }} id="two_factor">
                                <span class="toggle-slider"></span>
                            </label>
                            <span class="toggle-switch-label">Two Factor Authentication (2FA)</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="toggle-switch-wrapper">
                            <label class="toggle-switch">
                                <input type="checkbox" name="force_password_change" value="1" {{ old('force_password_change') ? 'checked' : '' }} id="force_password_change">
                                <span class="toggle-slider"></span>
                            </label>
                            <span class="toggle-switch-label">Force Password Change on First Login</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 4: SLA Association -->
            <div class="form-section">
                <h3 class="form-section-title">4. SLA Association</h3>
                
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
                                        {{ old('sla_policy_id') == $policy->id || $policy->name === 'Gold SLA Policy' ? 'selected' : '' }}>
                                    {{ $policy->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="business_unit">Business Unit</label>
                        <select id="business_unit" name="business_unit" class="form-control">
                            <option value="IT Operations" {{ old('business_unit') === 'IT Operations' ? 'selected' : '' }}>IT Operations</option>
                            <option value="Network Infrastructure" {{ old('business_unit') === 'Network Infrastructure' ? 'selected' : '' }}>Network Infrastructure</option>
                            <option value="Security Management" {{ old('business_unit') === 'Security Management' ? 'selected' : '' }}>Security Management</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Service Categories (Allowed)</label>
                        <div class="tags-input-container" id="serviceCategoriesContainer">
                            <span class="tag-badge">Network <span class="remove-tag" onclick="removeTag(this)">&times;</span></span>
                            <span class="tag-badge">Connectivity <span class="remove-tag" onclick="removeTag(this)">&times;</span></span>
                            <span class="tag-badge">Security <span class="remove-tag" onclick="removeTag(this)">&times;</span></span>
                            <input type="text" class="tags-input-field" placeholder="+ Add category" onkeydown="handleTagInput(event, 'service_categories[]', this)">
                        </div>
                        <!-- Hidden inputs generated dynamically -->
                        <div id="serviceCategoriesHiddenInputs">
                            <input type="hidden" name="service_categories[]" value="Network">
                            <input type="hidden" name="service_categories[]" value="Connectivity">
                            <input type="hidden" name="service_categories[]" value="Security">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="max_tickets_per_day">Max Tickets / Day</label>
                        <input type="number" id="max_tickets_per_day" name="max_tickets_per_day" class="form-control" min="0" value="50">
                    </div>
                    <div class="form-group">
                        <label for="max_changes_per_week">Max Changes / Week</label>
                        <input type="number" id="max_changes_per_week" name="max_changes_per_week" class="form-control" min="0" value="10">
                    </div>
                </div>
            </div>

            <!-- Section 6: Notifications & Preferences -->
            <div class="form-section">
                <h3 class="form-section-title">6. Notifications & Preferences</h3>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="required">Notification Method</label>
                        <div style="display: flex; gap: 1.5rem; margin-top: 0.5rem;">
                            <label class="checkbox-card">
                                <input type="checkbox" name="notification_methods[]" value="Email" checked>
                                Email
                            </label>
                            <label class="checkbox-card">
                                <input type="checkbox" name="notification_methods[]" value="SMS" checked>
                                SMS
                            </label>
                            <label class="checkbox-card">
                                <input type="checkbox" name="notification_methods[]" value="In-App" checked>
                                In-App
                            </label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="working_hours">Working Hours</label>
                        <select id="working_hours" name="working_hours" class="form-control">
                            <option value="24 x 7" {{ old('working_hours') === '24 x 7' ? 'selected' : '' }}>24 x 7</option>
                            <option value="8 x 5" {{ old('working_hours') === '8 x 5' ? 'selected' : '' }}>8 x 5 (Standard Business)</option>
                            <option value="12 x 5" {{ old('working_hours') === '12 x 5' ? 'selected' : '' }}>12 x 5</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="required">Email for Alerts</label>
                        <div class="tags-input-container" id="alertEmailsContainer">
                            <span class="tag-badge">vijay.kumar@westernrail.in <span class="remove-tag" onclick="removeTag(this)">&times;</span></span>
                            <input type="text" class="tags-input-field" placeholder="+ Add email" onkeydown="handleTagInput(event, 'alert_emails[]', this, true)">
                        </div>
                        <div id="alertEmailsHiddenInputs">
                            <input type="hidden" name="alert_emails[]" value="vijay.kumar@westernrail.in">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="escalation_group">Escalation Group</label>
                        <select id="escalation_group" name="escalation_group" class="form-control">
                            <option value="Network Operations Team" {{ old('escalation_group') === 'Network Operations Team' ? 'selected' : '' }}>Network Operations Team</option>
                            <option value="Security Operations Team" {{ old('escalation_group') === 'Security Operations Team' ? 'selected' : '' }}>Security Operations Team</option>
                            <option value="Systems Ops Group" {{ old('escalation_group') === 'Systems Ops Group' ? 'selected' : '' }}>Systems Ops Group</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="preferred_dashboard">Preferred Dashboard</label>
                        <select id="preferred_dashboard" name="preferred_dashboard" class="form-control">
                            <option value="Engineer Dashboard" {{ old('preferred_dashboard') === 'Engineer Dashboard' ? 'selected' : '' }}>Engineer Dashboard</option>
                            <option value="NMS Main Dashboard" {{ old('preferred_dashboard') === 'NMS Main Dashboard' ? 'selected' : '' }}>NMS Main Dashboard</option>
                            <option value="Service Desk View" {{ old('preferred_dashboard') === 'Service Desk View' ? 'selected' : '' }}>Service Desk View</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Section 7: Additional Information -->
            <div class="form-section">
                <h3 class="form-section-title">7. Additional Information</h3>
                
                <div class="form-row" style="grid-template-columns: 1fr 1fr;">
                    <div class="form-group">
                        <label>Skills / Expertise</label>
                        <div class="tags-input-container" id="skillsContainer">
                            <span class="tag-badge">Routing <span class="remove-tag" onclick="removeTag(this)">&times;</span></span>
                            <span class="tag-badge">Switching <span class="remove-tag" onclick="removeTag(this)">&times;</span></span>
                            <span class="tag-badge">WAN <span class="remove-tag" onclick="removeTag(this)">&times;</span></span>
                            <span class="tag-badge">VPN <span class="remove-tag" onclick="removeTag(this)">&times;</span></span>
                            <input type="text" class="tags-input-field" placeholder="+ Add skill" onkeydown="handleTagInput(event, 'skills[]', this)">
                        </div>
                        <div id="skillsHiddenInputs">
                            <input type="hidden" name="skills[]" value="Routing">
                            <input type="hidden" name="skills[]" value="Switching">
                            <input type="hidden" name="skills[]" value="WAN">
                            <input type="hidden" name="skills[]" value="VPN">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="certifications">Certifications (Optional)</label>
                        <input type="text" id="certifications" name="certifications" class="form-control" placeholder="e.g. CCNA, CCNP, CISSP" value="{{ old('certifications') }}">
                    </div>
                </div>

                <div class="form-group">
                    <label for="notes">Notes</label>
                    <textarea id="notes" name="notes" class="form-control" rows="3" placeholder="e.g. Engineer for core network operations and incidents handling.">{{ old('notes') }}</textarea>
                </div>
            </div>

            <!-- Section 8: Attachments -->
            <div class="form-section">
                <h3 class="form-section-title">8. Attachments</h3>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>ID Proof</label>
                        <div class="file-upload-wrapper">
                            <div class="file-upload-preview" id="idProofPreview">
                                <i class="fa-solid fa-file-pdf fa-lg"></i>
                            </div>
                            <div class="file-upload-info">
                                <label for="id_proof">Choose File</label>
                                <span id="idProofFilename">No file chosen</span>
                                <span>(Max file size: 5MB)</span>
                            </div>
                            <input type="file" id="id_proof" name="id_proof" class="file-upload-input" accept=".pdf,.doc,.docx,.jpg,.png">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Offer Letter / Appointment Letter</label>
                        <div class="file-upload-wrapper">
                            <div class="file-upload-preview" id="offerLetterPreview">
                                <i class="fa-solid fa-file-pdf fa-lg"></i>
                            </div>
                            <div class="file-upload-info">
                                <label for="offer_letter">Choose File</label>
                                <span id="offerLetterFilename">No file chosen</span>
                                <span>(Max file size: 5MB)</span>
                            </div>
                            <input type="file" id="offer_letter" name="offer_letter" class="file-upload-input" accept=".pdf,.doc,.docx,.jpg,.png">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Other Documents (Optional)</label>
                        <div class="file-upload-wrapper">
                            <div class="file-upload-preview" id="otherDocPreview">
                                <i class="fa-solid fa-file-pdf fa-lg"></i>
                            </div>
                            <div class="file-upload-info">
                                <label for="other_document">Choose File</label>
                                <span id="otherDocFilename">No file chosen</span>
                                <span>(Max file size: 5MB)</span>
                            </div>
                            <input type="file" id="other_document" name="other_document" class="file-upload-input" accept=".pdf,.doc,.docx,.jpg,.png">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Services Association Section (Original services from DB) -->
            <div class="form-section">
                <h3 class="form-section-title">NMS Services Access</h3>
                <div class="form-group">
                    <label>Assign Services</label>
                    <div class="checkbox-grid">
                        @forelse($services as $service)
                            <label class="checkbox-card">
                                <input type="checkbox" name="services[]" value="{{ $service->id }}" checked>
                                {{ $service->name }}
                            </label>
                        @empty
                            <p style="color: var(--text-muted); font-size: 0.85rem;">No services available. Create services first.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Save and reset actions -->
            <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 1.5rem;">
                <a href="{{ route('users.index') }}" class="btn-secondary" style="display: inline-flex; align-items: center; justify-content: center; padding: 0.75rem 1.5rem; text-decoration: none; border-radius: 8px;">Cancel</a>
                <!-- <button type="button" class="btn-sidebar-action" style="padding: 0.75rem 1.5rem; border-radius: 8px;" onclick="window.history.back()">Save as Draft</button> -->
                <button type="submit" class="btn-primary" style="width: auto; padding: 0.75rem 2rem; border-radius: 8px; font-weight: 700;">Create User</button>
            </div>

        </div>

        <!-- Right Side: Sticky User Summary Sidebar -->
        <!--
        <div class="sticky-summary">
            
            <div class="summary-card">
                <h3>User Summary</h3>
                <div class="summary-profile-header">
                    <div class="summary-avatar" id="summaryAvatar">
                        <i class="fa-solid fa-user-tie fa-2xl" style="color: #94a3b8;"></i>
                    </div>
                    <div class="summary-profile-info">
                        <h4 id="sidebarName">Vijay Kumar</h4>
                        <p id="sidebarDesignation">Network Engineer</p>
                    </div>
                    <div style="margin-left: auto;">
                        <span class="badge-active" id="sidebarStatus">Active</span>
                    </div>
                </div>
                
                <div class="summary-details-list">
                    <div class="summary-detail-item">
                        <span class="label">User ID</span>
                        <span class="value" id="sidebarUserId">vijay.kumar</span>
                    </div>
                    <div class="summary-detail-item">
                        <span class="label">Full Name</span>
                        <span class="value" id="sidebarFullName">Vijay Kumar</span>
                    </div>
                    <div class="summary-detail-item">
                        <span class="label">Designation</span>
                        <span class="value" id="sidebarDesignationDetail">Network Engineer</span>
                    </div>
                    <div class="summary-detail-item">
                        <span class="label">Department</span>
                        <span class="value" id="sidebarDepartment">Network Operations</span>
                    </div>
                    <div class="summary-detail-item">
                        <span class="label">Email ID</span>
                        <span class="value" id="sidebarEmail">vijay.kumar@westernrail.in</span>
                    </div>
                    <div class="summary-detail-item">
                        <span class="label">Mobile</span>
                        <span class="value" id="sidebarMobile">+91 98765 43210</span>
                    </div>
                    <div class="summary-detail-item">
                        <span class="label">Location</span>
                        <span class="value" id="sidebarLocation">Ahmedabad DC</span>
                    </div>
                    <div class="summary-detail-item">
                        <span class="label">Role</span>
                        <span class="value" id="sidebarRole">Network Engineer</span>
                    </div>
                    <div class="summary-detail-item">
                        <span class="label">Access Level</span>
                        <span class="value" id="sidebarAccessLevel">Engineer</span>
                    </div>
                    <div class="summary-detail-item">
                        <span class="label">Time Zone</span>
                        <span class="value" id="sidebarTimezone">Asia/Kolkata (IST)</span>
                    </div>
                    <div class="summary-detail-item">
                        <span class="label">Status</span>
                        <span class="value badge-active" style="display:inline-block;" id="sidebarStatusDetail">Active</span>
                    </div>
                </div>
            </div>

            <div class="summary-card">
                <h3>Roles Assigned</h3>
                <div class="assigned-roles-list" id="sidebarRolesList">
                    <span class="role-badge">Network Engineer</span>
                </div>
            </div>

            <div class="summary-card">
                <h3>SLA Overview <span style="float: right; font-size: 0.75rem; font-weight: normal; margin-top: 0.15rem;"><a href="#" style="color: var(--primary);">View SLA Policy</a></span></h3>
                <div class="summary-details-list" id="sidebarSlaDetails">
                    <div class="summary-detail-item">
                        <span class="label">SLA Policy</span>
                        <span class="value" id="sidebarSlaPolicy">Gold SLA Policy</span>
                    </div>
                    <div class="summary-detail-item">
                        <span class="label">Response SLA</span>
                        <span class="value" id="sidebarSlaResponse">15 Minutes</span>
                    </div>
                    <div class="summary-detail-item">
                        <span class="label">Resolution SLA</span>
                        <span class="value" id="sidebarSlaResolution">2 Hours</span>
                    </div>
                    <div class="summary-detail-item">
                        <span class="label">Escalation SLA</span>
                        <span class="value" id="sidebarSlaEscalation">30 Minutes</span>
                    </div>
                    <div class="summary-detail-item">
                        <span class="label">Max Tickets / Day</span>
                        <span class="value" id="sidebarSlaMaxTickets">50</span>
                    </div>
                    <div class="summary-detail-item">
                        <span class="label">Max Changes / Week</span>
                        <span class="value" id="sidebarSlaMaxChanges">10</span>
                    </div>
                </div>
            </div>

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
                            <span id="pbFullCount">2</span>
                        </div>
                        <div class="progress-widget-bar-bg">
                            <div class="progress-widget-bar-fill bar-green" style="width: 18.18%;" id="pbFullFill"></div>
                        </div>
                    </div>
                    <div class="progress-widget-item">
                        <div class="progress-widget-header">
                            <span>Limited Access</span>
                            <span id="pbLimitedCount">8</span>
                        </div>
                        <div class="progress-widget-bar-bg">
                            <div class="progress-widget-bar-fill bar-orange" style="width: 72.72%;" id="pbLimitedFill"></div>
                        </div>
                    </div>
                    <div class="progress-widget-item">
                        <div class="progress-widget-header">
                            <span>No Access</span>
                            <span id="pbNoCount">1</span>
                        </div>
                        <div class="progress-widget-bar-bg">
                            <div class="progress-widget-bar-fill bar-red" style="width: 9.09%;" id="pbNoFill"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="summary-card">
                <h3>Account Status</h3>
                <div class="summary-details-list">
                    <div class="summary-detail-item">
                        <span class="label">Status</span>
                        <span class="value" id="sidebarAccountStatus">Active</span>
                    </div>
                    <div class="summary-detail-item">
                        <span class="label">2FA Enabled</span>
                        <span class="value" id="sidebarAccount2FA">No</span>
                    </div>
                    <div class="summary-detail-item">
                        <span class="label">Password Expiry</span>
                        <span class="value" id="sidebarAccountExpiry">90 Days</span>
                    </div>
                    <div class="summary-detail-item">
                        <span class="label">Last Login</span>
                        <span class="value">—</span>
                    </div>
                    <div class="summary-detail-item">
                        <span class="label">Failed Attempts</span>
                        <span class="value" id="sidebarAccountFailed">0</span>
                    </div>
                </div>
            </div>

            <div class="summary-card">
                <h3>Quick Actions</h3>
                <div class="action-grid">
                    <button type="button" class="btn-sidebar-action">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 15V17M12 7V13M12 21C16.9706 21 21 16.9706 21 12C21 7.02944 16.9706 3 12 3C7.02944 3 3 7.02944 3 12C3 16.9706 7.02944 21 12 21Z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        Reset Pass
                    </button>
                    <button type="button" class="btn-sidebar-action">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M8 7V3C8 2.44772 8.44772 2 9 2H19C19.5523 2 20 2.44772 20 3V17C20 17.5523 19.5523 18 19 18H15M15 6H5C4.44772 6 4 6.44772 4 7V21C4 21.5523 4.44772 22 5 22H15C15.5523 22 16 21.5523 16 21V7C16 6.44772 15.5523 6 15 6Z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        Clone User
                    </button>
                    <button type="button" class="btn-sidebar-action">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18.364 18.364C21.8787 14.8492 21.8787 9.15076 18.364 5.63604C14.8492 2.12132 9.15076 2.12132 5.63604 5.63604C2.12132 9.15076 2.12132 14.8492 5.63604 18.364C9.15076 21.8787 14.8492 21.8787 18.364 18.364ZM18.364 18.364L5.63604 5.63604" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        Deactivate
                    </button>
                    <button type="button" class="btn-sidebar-action">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 8V12L15 15M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        History
                    </button>
                </div>
            </div>

        </div>
        -->
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
                filenameEl.textContent = 'No file chosen';
                previewEl.innerHTML = defaultIconHtml;
                if (inputId === 'profile_photo') {
                    document.getElementById('summaryAvatar').innerHTML = `<i class="fa-solid fa-user-tie fa-2xl" style="color: #94a3b8;"></i>`;
                }
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
            { sourceId: 'username', targetIds: ['sidebarUserId'] },
            { sourceId: 'email', targetIds: ['sidebarEmail'] },
            { sourceId: 'mobile', targetIds: ['sidebarMobile'] },
            { sourceId: 'department', targetIds: ['sidebarDepartment'] },
            { sourceId: 'designation', targetIds: ['sidebarDesignation', 'sidebarDesignationDetail'] },
            { sourceId: 'role_id', targetIds: ['sidebarRole', 'sidebarAccessLevel'] },
            { sourceId: 'office_location', targetIds: ['sidebarLocation'] },
            { sourceId: 'timezone', targetIds: ['sidebarTimezone'] },
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
            updateSlaOverview();
        }

        const roleSelect = document.getElementById('role_id');
        const staffRoleIds = @json($staffRoleIds);
        const staffSectionPrefixes = ['2.', '4.', '6.', '7.', '8.', '9.', '10.', '11.', 'NMS Services'];

        function toggleCreateStaffSections() {
            if (!roleSelect) {
                return;
            }

            const isStaff = staffRoleIds.map(String).includes(String(roleSelect.value));
            document.querySelectorAll('.form-section').forEach(section => {
                const title = section.querySelector('.form-section-title')?.textContent?.trim() || '';
                const isStaffSection = staffSectionPrefixes.some(prefix => title.startsWith(prefix));
                if (isStaffSection) {
                    section.style.display = isStaff ? '' : 'none';
                }
            });

            document.querySelectorAll('#engineerUserForm [required]').forEach(field => {
                if (['role_id', 'name', 'email', 'mobile', 'password', 'password_confirmation', 'status'].includes(field.id)) {
                    return;
                }

                field.required = isStaff;
            });
        }

        roleSelect?.addEventListener('change', toggleCreateStaffSections);
        toggleCreateStaffSections();
    });
</script>
@endsection
