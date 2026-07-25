<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('alarms')) {
            return;
        }

        Schema::table('alarms', function (Blueprint $table) {
            if (! Schema::hasColumn('alarms', 'remarks')) {
                $table->text('remarks')->nullable()->after('status');
            }

            if (! Schema::hasColumn('alarms', 'ticket_id')) {
                $table->foreignId('ticket_id')->nullable()->after('remarks')->constrained('tickets')->nullOnDelete();
            }

            if (! Schema::hasColumn('alarms', 'resolved_at')) {
                $table->timestamp('resolved_at')->nullable()->after('ticket_id');
            }
        });

        // Allow Resolved (and future statuses) beyond Open/Acknowledged.
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE alarms MODIFY status VARCHAR(50) NOT NULL DEFAULT 'Open'");
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('alarms')) {
            return;
        }

        Schema::table('alarms', function (Blueprint $table) {
            if (Schema::hasColumn('alarms', 'ticket_id')) {
                $table->dropConstrainedForeignId('ticket_id');
            }

            $columns = array_filter(['remarks', 'resolved_at'], fn (string $column) => Schema::hasColumn('alarms', $column));
            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
