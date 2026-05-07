<?php

// database/migrations/tenant/travel/2026_05_06_000100_upgrade_booking_module_tables.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (! Schema::hasColumn('bookings', 'company_id')) {
                $table->unsignedBigInteger('company_id')->nullable()->after('id')->index();
            }
        });

        Schema::table('passengers', function (Blueprint $table) {
            if (! Schema::hasColumn('passengers', 'booking_id')) {
                $table->unsignedBigInteger('booking_id')->nullable()->after('id')->index();
            }

            if (! Schema::hasColumn('passengers', 'company_id')) {
                $table->unsignedBigInteger('company_id')->nullable()->after('booking_id')->index();
            }

            if (! Schema::hasColumn('passengers', 'full_name')) {
                $table->string('full_name')->nullable()->after('company_id');
            }

            if (! Schema::hasColumn('passengers', 'passenger_type')) {
                $table->string('passenger_type')->nullable()->after('full_name');
            }

            if (! Schema::hasColumn('passengers', 'passport_no')) {
                $table->string('passport_no')->nullable()->after('passenger_type');
            }

            if (! Schema::hasColumn('passengers', 'nationality')) {
                $table->string('nationality')->nullable()->after('passport_no');
            }

            if (! Schema::hasColumn('passengers', 'date_of_birth')) {
                $table->date('date_of_birth')->nullable()->after('nationality');
            }

            if (! Schema::hasColumn('passengers', 'meta')) {
                $table->jsonb('meta')->nullable()->after('date_of_birth');
            }
        });

        Schema::table('booking_services', function (Blueprint $table) {
            if (! Schema::hasColumn('booking_services', 'booking_id')) {
                $table->unsignedBigInteger('booking_id')->nullable()->after('id')->index();
            }

            if (! Schema::hasColumn('booking_services', 'company_id')) {
                $table->unsignedBigInteger('company_id')->nullable()->after('booking_id')->index();
            }

            if (! Schema::hasColumn('booking_services', 'service_schema_id')) {
                $table->unsignedBigInteger('service_schema_id')->nullable()->after('company_id');
            }

            if (! Schema::hasColumn('booking_services', 'service_type')) {
                $table->string('service_type')->nullable()->after('service_schema_id');
            }

            if (! Schema::hasColumn('booking_services', 'service_name')) {
                $table->string('service_name')->nullable()->after('service_type');
            }

            if (! Schema::hasColumn('booking_services', 'service_details')) {
                $table->jsonb('service_details')->nullable()->after('service_name');
            }

            if (! Schema::hasColumn('booking_services', 'qty')) {
                $table->unsignedInteger('qty')->default(1)->after('service_details');
            }

            if (! Schema::hasColumn('booking_services', 'unit_fare')) {
                $table->decimal('unit_fare', 12, 2)->default(0)->after('qty');
            }

            if (! Schema::hasColumn('booking_services', 'tax_type')) {
                $table->string('tax_type', 10)->default('RM')->after('unit_fare');
            }

            if (! Schema::hasColumn('booking_services', 'tax_value')) {
                $table->decimal('tax_value', 12, 2)->default(0)->after('tax_type');
            }

            if (! Schema::hasColumn('booking_services', 'tax_amount')) {
                $table->decimal('tax_amount', 12, 2)->default(0)->after('tax_value');
            }

            if (! Schema::hasColumn('booking_services', 'client_price')) {
                $table->decimal('client_price', 12, 2)->default(0)->after('tax_amount');
            }

            if (! Schema::hasColumn('booking_services', 'line_total')) {
                $table->decimal('line_total', 12, 2)->default(0)->after('client_price');
            }

            if (! Schema::hasColumn('booking_services', 'sort_order')) {
                $table->unsignedInteger('sort_order')->default(0)->after('line_total');
            }

            if (! Schema::hasColumn('booking_services', 'payload')) {
                $table->jsonb('payload')->nullable()->after('sort_order');
            }
        });
    }

    public function down(): void
    {
        Schema::table('booking_services', function (Blueprint $table) {
            $columns = [
                'booking_id',
                'company_id',
                'service_schema_id',
                'service_type',
                'service_name',
                'service_details',
                'qty',
                'unit_fare',
                'tax_type',
                'tax_value',
                'tax_amount',
                'client_price',
                'line_total',
                'sort_order',
                'payload',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('booking_services', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('passengers', function (Blueprint $table) {
            $columns = [
                'booking_id',
                'company_id',
                'full_name',
                'passenger_type',
                'passport_no',
                'nationality',
                'date_of_birth',
                'meta',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('passengers', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bookings', 'company_id')) {
                $table->dropColumn('company_id');
            }
        });
    }
};
