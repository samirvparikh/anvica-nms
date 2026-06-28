<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'username')) {
                $table->string('username')->nullable()->unique()->after('email');
            }

            if (! Schema::hasColumn('users', 'access_level')) {
                $anchor = Schema::hasColumn('users', 'role_id') ? 'role_id' : 'mobile';
                $table->string('access_level')->nullable()->after($anchor);
            }

            if (! Schema::hasColumn('users', 'password_expiry_days')) {
                $table->integer('password_expiry_days')->nullable()->after('force_password_change');
            }

            if (! Schema::hasColumn('users', 'assigned_roles')) {
                $table->json('assigned_roles')->nullable()->after('notes');
            }

            if (! Schema::hasColumn('users', 'module_access')) {
                $table->json('module_access')->nullable()->after('assigned_roles');
            }

            if (! Schema::hasColumn('users', 'sla_policy_id')) {
                $table->foreignId('sla_policy_id')->nullable()->after('module_access')->constrained('sla_policies')->nullOnDelete();
            }

            if (! Schema::hasColumn('users', 'business_unit')) {
                $table->string('business_unit')->nullable()->after('sla_policy_id');
            }

            if (! Schema::hasColumn('users', 'service_categories')) {
                $table->json('service_categories')->nullable()->after('business_unit');
            }

            if (! Schema::hasColumn('users', 'max_tickets_per_day')) {
                $table->integer('max_tickets_per_day')->nullable()->after('service_categories');
            }

            if (! Schema::hasColumn('users', 'max_changes_per_week')) {
                $table->integer('max_changes_per_week')->nullable()->after('max_tickets_per_day');
            }

            if (! Schema::hasColumn('users', 'notification_methods')) {
                $table->json('notification_methods')->nullable()->after('max_changes_per_week');
            }

            if (! Schema::hasColumn('users', 'alert_emails')) {
                $table->json('alert_emails')->nullable()->after('notification_methods');
            }

            if (! Schema::hasColumn('users', 'working_hours')) {
                $table->string('working_hours')->nullable()->after('alert_emails');
            }

            if (! Schema::hasColumn('users', 'escalation_group')) {
                $table->string('escalation_group')->nullable()->after('working_hours');
            }

            if (! Schema::hasColumn('users', 'preferred_dashboard')) {
                $table->string('preferred_dashboard')->nullable()->after('escalation_group');
            }

            if (! Schema::hasColumn('users', 'id_proof')) {
                $table->string('id_proof')->nullable()->after('signature');
            }

            if (! Schema::hasColumn('users', 'offer_letter')) {
                $table->string('offer_letter')->nullable()->after('id_proof');
            }

            if (! Schema::hasColumn('users', 'other_document')) {
                $table->string('other_document')->nullable()->after('offer_letter');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'sla_policy_id') && DB::getDriverName() !== 'sqlite') {
                $table->dropForeign(['sla_policy_id']);
            }

            $columns = array_filter([
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
                'other_document',
            ], fn (string $column) => Schema::hasColumn('users', $column));

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
