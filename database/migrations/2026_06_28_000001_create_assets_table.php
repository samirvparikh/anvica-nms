<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            
            // 1. Asset Information
            $table->string('asset_name');
            $table->string('asset_type');
            $table->string('asset_category');
            $table->string('status');
            $table->string('asset_id_auto')->unique();
            $table->string('asset_group')->nullable();
            $table->string('criticality');
            $table->string('availability_requirement')->nullable();

            // 2. Asset Identification
            $table->string('manufacturer');
            $table->string('model_number');
            $table->string('serial_number')->unique();
            $table->string('part_number')->nullable();
            $table->string('firmware_version')->nullable();
            $table->string('hardware_version')->nullable();
            $table->string('mac_address')->nullable();
            $table->string('ean_imei')->nullable();

            // 3. Network Information
            $table->string('management_ip');
            $table->string('hostname')->nullable();
            $table->string('snmp_version')->nullable();
            $table->string('snmp_community_user')->nullable();
            $table->string('read_community')->nullable();
            $table->string('write_community')->nullable();
            $table->boolean('ssh_enabled')->default(false);
            $table->boolean('telnet_enabled')->default(false);
            $table->boolean('auto_discover_snmp')->default(false);
            $table->boolean('auto_import_interfaces')->default(false);
            $table->boolean('auto_import_software')->default(false);
            $table->boolean('auto_import_config_backup')->default(false);

            // 4. Location Information
            $table->foreignId('customer_id')->constrained('users')->onDelete('cascade');
            $table->string('region')->nullable();
            $table->string('state')->nullable();
            $table->string('city')->nullable();
            $table->string('site_location')->nullable();
            $table->string('building_floor')->nullable();
            $table->string('rack')->nullable();
            $table->string('rack_unit')->nullable();
            $table->text('address')->nullable();
            $table->string('gps_coordinates')->nullable();
            $table->string('zone')->nullable();

            // 5. Vendor & Purchase Information
            $table->string('vendor')->nullable();
            $table->string('supplier_reseller')->nullable();
            $table->string('purchase_order_no')->nullable();
            $table->string('invoice_no')->nullable();
            $table->date('purchase_date')->nullable();
            $table->date('installation_date')->nullable();
            $table->date('commissioning_date')->nullable();
            $table->decimal('cost', 15, 2)->nullable();

            // 6. Warranty & AMC Information
            $table->string('warranty_status')->nullable();
            $table->date('warranty_start_date')->nullable();
            $table->date('warranty_end_date')->nullable();
            $table->string('amc_status')->nullable();
            $table->date('amc_start_date')->nullable();
            $table->date('amc_end_date')->nullable();

            // 7. SLA & Business Mapping
            $table->string('sla_policy')->nullable();
            $table->string('service_name')->nullable();
            $table->string('business_unit')->nullable();
            $table->string('sla_availability')->nullable();
            $table->string('response_sla')->nullable();
            $table->string('resolution_sla')->nullable();
            $table->string('escalation_sla')->nullable();
            $table->string('business_impact')->nullable();

            // 8. Monitoring & Health Configuration
            $table->integer('cpu_utilization_threshold')->default(85);
            $table->integer('memory_utilization_threshold')->default(80);
            $table->integer('packet_loss_threshold')->default(2);
            $table->integer('temperature_threshold')->default(70);
            $table->boolean('health_monitoring')->default(true);
            $table->boolean('health_score_calculation')->default(true);
            $table->string('polling_interval')->default('5 Minutes');
            $table->string('alert_profile')->default('Default');

            // 9. Ownership & Responsibility
            $table->string('asset_owner')->nullable();
            $table->string('custodian_department')->nullable();
            $table->string('responsible_person')->nullable();
            $table->string('contact_number')->nullable();
            $table->string('email_id')->nullable();
            $table->string('escalation_group')->nullable();
            $table->string('notification_group')->nullable();

            // 10. Attachments & Notes
            $table->string('attachment_path')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
