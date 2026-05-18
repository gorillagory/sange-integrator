<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('bookings') && ! Schema::hasTable('operations')) {
            Schema::rename('bookings', 'operations');
        }

        if (Schema::hasTable('booking_services') && ! Schema::hasTable('service_instances')) {
            Schema::rename('booking_services', 'service_instances');
        }

        if (
            Schema::hasTable('service_instances')
            && Schema::hasColumn('service_instances', 'booking_id')
            && ! Schema::hasColumn('service_instances', 'operation_id')
        ) {
            Schema::table('service_instances', function (Blueprint $table) {
                $table->renameColumn('booking_id', 'operation_id');
            });
        }

        if (Schema::hasTable('operations') && ! Schema::hasColumn('operations', 'handler_key')) {
            Schema::table('operations', function (Blueprint $table) {
                $table->string('handler_key')->nullable()->after('reference_no');
            });
        }

        if (Schema::hasTable('operations') && Schema::hasColumn('operations', 'handler_key')) {
            DB::connection('tenant')
                ->table('operations')
                ->whereNull('handler_key')
                ->update(['handler_key' => 'travel.services']);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('operations') && Schema::hasColumn('operations', 'handler_key')) {
            Schema::table('operations', function (Blueprint $table) {
                $table->dropColumn('handler_key');
            });
        }

        if (
            Schema::hasTable('service_instances')
            && Schema::hasColumn('service_instances', 'operation_id')
            && ! Schema::hasColumn('service_instances', 'booking_id')
        ) {
            Schema::table('service_instances', function (Blueprint $table) {
                $table->renameColumn('operation_id', 'booking_id');
            });
        }

        if (Schema::hasTable('service_instances') && ! Schema::hasTable('booking_services')) {
            Schema::rename('service_instances', 'booking_services');
        }

        if (Schema::hasTable('operations') && ! Schema::hasTable('bookings')) {
            Schema::rename('operations', 'bookings');
        }
    }
};
