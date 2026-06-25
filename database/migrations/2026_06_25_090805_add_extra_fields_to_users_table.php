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
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->unique()->after('email');
            $table->string('access_level')->nullable()->after('role');
            $table->integer('password_expiry_days')->nullable()->after('force_password_change');
            $table->json('assigned_roles')->nullable()->after('notes');
            $table->json('module_access')->nullable()->after('assigned_roles');
            $table->foreignId('sla_policy_id')->nullable()->after('module_access')->constrained('sla_policies')->nullOnDelete();
            $table->string('business_unit')->nullable()->after('sla_policy_id');
            $table->json('service_categories')->nullable()->after('business_unit');
            $table->integer('max_tickets_per_day')->nullable()->after('service_categories');
            $table->integer('max_changes_per_week')->nullable()->after('max_tickets_per_day');
            $table->json('notification_methods')->nullable()->after('max_changes_per_week');
            $table->json('alert_emails')->nullable()->after('notification_methods');
            $table->string('working_hours')->nullable()->after('alert_emails');
            $table->string('escalation_group')->nullable()->after('working_hours');
            $table->string('preferred_dashboard')->nullable()->after('escalation_group');
            $table->string('id_proof')->nullable()->after('signature');
            $table->string('offer_letter')->nullable()->after('id_proof');
            $table->string('other_document')->nullable()->after('offer_letter');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (DB::getDriverName() !== 'sqlite') {
                $table->dropForeign(['sla_policy_id']);
            }
            $table->dropColumn([
                'username',
                'access_level',
                'password_expiry_days',
                'assigned_roles',
                'module_access',
                'sla_policy_id',
                'business_unit',
                'service_categories',
                'max_tickets_per_day',
                'max_changes_per_week',
                'notification_methods',
                'alert_emails',
                'working_hours',
                'escalation_group',
                'preferred_dashboard',
                'id_proof',
                'offer_letter',
                'other_document'
            ]);
        });
    }
};
