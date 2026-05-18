<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('booking_services', function (Blueprint $table) {
            if (! Schema::hasColumn('booking_services', 'service_code')) {
                $table->string('service_code')->nullable()->after('service_type');
            }

            if (! Schema::hasColumn('booking_services', 'schema_version')) {
                $table->unsignedInteger('schema_version')->nullable()->after('service_code');
            }

            if (! Schema::hasColumn('booking_services', 'service_details_extra')) {
                $table->jsonb('service_details_extra')->nullable()->after('service_details');
            }

            if (! Schema::hasColumn('booking_services', 'payload_snapshot')) {
                $table->jsonb('payload_snapshot')->nullable()->after('payload');
            }
        });
    }

    public function down(): void
    {
        Schema::table('booking_services', function (Blueprint $table) {
            $columns = [
                'payload_snapshot',
                'service_details_extra',
                'schema_version',
                'service_code',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('booking_services', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

