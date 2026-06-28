<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. SLA Policies Table
        Schema::create('sla_policies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('response_time_minutes')->default(15);
            $table->integer('resolution_time_minutes')->default(120);
            $table->integer('escalation_time_minutes')->default(30);
            $table->integer('max_tickets_per_day')->default(50);
            $table->integer('max_changes_per_week')->default(10);
            $table->timestamps();
        });

        // 2. Tickets Table (Unified for Tickets, Incidents, Problems, Changes)
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique();
            $table->enum('type', ['ticket', 'incident', 'problem', 'change'])->default('ticket');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('status', ['new', 'assigned', 'in_progress', 'resolved', 'closed'])->default('new');
            $table->enum('priority', ['critical', 'high', 'medium', 'low'])->default('medium');
            $table->enum('impact', ['critical', 'high', 'medium', 'low'])->default('medium');
            $table->enum('urgency', ['critical', 'high', 'medium', 'low'])->default('medium');
            $table->string('source')->nullable();
            
            $table->foreignId('customer_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('device_id')->nullable()->constrained('assets')->onDelete('set null');
            $table->foreignId('sla_policy_id')->nullable()->constrained('sla_policies')->onDelete('set null');
            
            // SLA Target timelines
            $table->timestamp('response_sla_deadline')->nullable();
            $table->timestamp('resolution_sla_deadline')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            
            // Incident specific details
            $table->string('contact_person')->nullable();
            $table->string('contact_number')->nullable();
            $table->string('sub_category')->nullable();
            $table->string('service_impacted')->nullable();
            $table->string('ci_service')->nullable();
            $table->integer('affected_users')->default(0);
            $table->string('business_impact')->nullable();
            $table->string('alarm_alert_id')->nullable();
            $table->timestamp('detected_time')->nullable();
            $table->timestamp('incident_start_time')->nullable();
            $table->boolean('planned_outage')->default(false);
            $table->string('assign_group')->nullable();
            
            // Change specific details
            $table->string('change_category')->nullable();
            $table->text('risk_description')->nullable();
            $table->boolean('impact_on_sla')->default(true);
            $table->text('rollback_plan')->nullable();
            $table->integer('backout_time_minutes')->default(30);
            $table->timestamp('change_planned_start')->nullable();
            $table->timestamp('change_planned_end')->nullable();
            $table->boolean('planned_downtime')->default(false);
            $table->string('change_window')->nullable();
            $table->text('implementation_steps')->nullable();
            
            $table->timestamps();
        });

        // 3. SLA Breaches Table
        Schema::create('sla_breaches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('tickets')->onDelete('cascade');
            $table->enum('type', ['response', 'resolution']);
            $table->timestamp('breached_at');
            $table->timestamps();
        });

        // 4. Maintenance Windows Table (Preventive Downtime)
        Schema::create('maintenance_windows', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('maintenance_id')->unique();
            $table->string('type')->default('Preventive');
            $table->string('category')->default('Network');
            $table->foreignId('primary_device_id')->constrained('assets')->onDelete('cascade');
            $table->timestamp('start_time');
            $table->timestamp('end_time')->nullable();
            $table->integer('expected_downtime_minutes')->default(60);
            $table->boolean('exclude_sla')->default(true);
            $table->string('sla_impact')->default('No Breach (Maintenance)');
            $table->string('sla_policy')->default('Standard Incident SLA');
            $table->integer('notify_before_minutes')->default(120);
            $table->text('notification_recipients')->nullable();
            $table->text('implementation_steps')->nullable();
            $table->text('rollback_plan')->nullable();
            $table->boolean('notify_users')->default(true);
            $table->string('notification_method')->default('Email');
            $table->text('notification_message')->nullable();
            $table->string('requested_by')->nullable();
            $table->string('approved_noc_manager')->nullable();
            $table->string('approved_it_head')->nullable();
            $table->string('customer_approval')->default('Pending');
            $table->enum('status', ['scheduled', 'approved', 'in_progress', 'completed', 'cancelled'])->default('scheduled');
            $table->timestamps();
        });

        // 5. Extend users table for Engineer profile details
        Schema::table('users', function (Blueprint $table) {
            $table->string('employee_id')->nullable();
            $table->string('alternate_number')->nullable();
            $table->string('alternate_email')->nullable();
            $table->date('dob')->nullable();
            $table->string('gender')->nullable();
            $table->string('language')->nullable();
            $table->string('profile_photo')->nullable();
            $table->string('signature')->nullable();
            
            $table->string('department')->nullable();
            $table->string('designation')->nullable();
            $table->string('reporting_manager')->nullable();
            $table->string('office_location')->nullable();
            $table->string('work_location')->nullable();
            $table->string('timezone')->nullable();
            $table->string('address')->nullable();
            $table->string('landline')->nullable();
            $table->string('extension')->nullable();
            
            $table->string('auth_type')->default('Local Authentication');
            $table->integer('failed_login_attempts')->default(0);
            $table->integer('lockout_minutes')->default(30);
            $table->boolean('two_factor')->default(false);
            $table->boolean('force_password_change')->default(false);
            
            $table->text('skills')->nullable();
            $table->text('certifications')->nullable();
            $table->text('notes')->nullable();
        });

        // 6. Extend devices table for Warranty & AMC tracking
        Schema::table('devices', function (Blueprint $table) {
            $table->string('asset_id')->nullable();
            $table->string('asset_name')->nullable();
            $table->string('manufacturer')->nullable();
            $table->string('model_number')->nullable();
            $table->string('serial_number')->nullable();
            
            // Warranty
            $table->string('warranty_type')->nullable();
            $table->string('warranty_provider')->nullable();
            $table->string('warranty_support_level')->nullable();
            $table->string('warranty_status')->nullable();
            $table->date('warranty_start_date')->nullable();
            $table->date('warranty_end_date')->nullable();
            $table->integer('warranty_duration_years')->nullable();
            $table->boolean('warranty_onsite_support')->default(false);
            $table->string('warranty_parts_coverage')->nullable();
            $table->string('warranty_labor_coverage')->nullable();
            $table->boolean('warranty_transferable')->default(false);
            $table->text('warranty_terms')->nullable();
            
            // AMC
            $table->boolean('amc_available')->default(false);
            $table->string('amc_type')->nullable();
            $table->string('amc_provider')->nullable();
            $table->string('amc_support_level')->nullable();
            $table->date('amc_start_date')->nullable();
            $table->date('amc_end_date')->nullable();
            $table->integer('amc_duration_years')->nullable();
            $table->string('amc_response_time')->nullable();
            $table->string('amc_resolution_time')->nullable();
            $table->string('amc_escalation_time')->nullable();
            $table->string('amc_coverage')->nullable();
            $table->text('amc_terms')->nullable();
            
            // Financials
            $table->string('purchase_order_no')->nullable();
            $table->string('invoice_no')->nullable();
            $table->date('purchase_date')->nullable();
            $table->date('invoice_date')->nullable();
            $table->decimal('warranty_cost', 15, 2)->default(0.00);
            $table->decimal('amc_cost', 15, 2)->default(0.00);
            $table->string('currency')->default('INR');
            $table->decimal('tax', 15, 2)->default(0.00);
            $table->decimal('total_amc_cost', 15, 2)->default(0.00);
            
            // SLA
            $table->string('customer_sla_policy')->nullable();
            $table->decimal('availability_sla', 5, 2)->default(99.95);
            $table->string('response_sla')->nullable();
            $table->string('resolution_sla')->nullable();
            
            // Renewal
            $table->string('renewal_reminder')->nullable();
            $table->string('amc_renewal_reminder')->nullable();
            $table->boolean('warranty_expiry_alert')->default(true);
            $table->boolean('amc_expiry_alert')->default(true);
            $table->text('notification_recipients')->nullable();
            
            // Ownership
            $table->string('asset_owner')->nullable();
            $table->string('custodian')->nullable();
            $table->string('responsible_person')->nullable();
            $table->string('contact_number')->nullable();
            $table->text('additional_notes')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sla_breaches');
        Schema::dropIfExists('tickets');
        Schema::dropIfExists('sla_policies');
        Schema::dropIfExists('maintenance_windows');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'employee_id', 'alternate_number', 'alternate_email', 'dob', 'gender', 'language',
                'profile_photo', 'signature', 'department', 'designation', 'reporting_manager',
                'office_location', 'work_location', 'timezone', 'address', 'landline', 'extension',
                'auth_type', 'failed_login_attempts', 'lockout_minutes', 'two_factor', 'force_password_change',
                'skills', 'certifications', 'notes'
            ]);
        });

        Schema::table('devices', function (Blueprint $table) {
            $table->dropColumn([
                'asset_id', 'asset_name', 'manufacturer', 'model_number', 'serial_number',
                'warranty_type', 'warranty_provider', 'warranty_support_level', 'warranty_status',
                'warranty_start_date', 'warranty_end_date', 'warranty_duration_years', 'warranty_onsite_support',
                'warranty_parts_coverage', 'warranty_labor_coverage', 'warranty_transferable', 'warranty_terms',
                'amc_available', 'amc_type', 'amc_provider', 'amc_support_level', 'amc_start_date', 'amc_end_date',
                'amc_duration_years', 'amc_response_time', 'amc_resolution_time', 'amc_escalation_time',
                'amc_coverage', 'amc_terms', 'purchase_order_no', 'invoice_no', 'purchase_date', 'invoice_date',
                'warranty_cost', 'amc_cost', 'currency', 'tax', 'total_amc_cost', 'customer_sla_policy',
                'availability_sla', 'response_sla', 'resolution_sla', 'renewal_reminder', 'amc_renewal_reminder',
                'warranty_expiry_alert', 'amc_expiry_alert', 'notification_recipients', 'asset_owner',
                'custodian', 'responsible_person', 'contact_number', 'additional_notes'
            ]);
        });
    }
};
