<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('operations') && ! Schema::hasTable('service_records')) {
            Schema::rename('operations', 'service_records');
        }

        if (Schema::hasTable('service_instances') && ! Schema::hasTable('service_record_rows')) {
            Schema::rename('service_instances', 'service_record_rows');
        }

        if (
            Schema::hasTable('service_record_rows')
            && Schema::hasColumn('service_record_rows', 'operation_id')
            && ! Schema::hasColumn('service_record_rows', 'service_record_id')
        ) {
            Schema::table('service_record_rows', function (Blueprint $table) {
                $table->renameColumn('operation_id', 'service_record_id');
            });
        }

        if (
            Schema::hasTable('service_record_rows')
            && Schema::hasColumn('service_record_rows', 'service_schema_id')
            && ! Schema::hasColumn('service_record_rows', 'schema_vector_id')
        ) {
            Schema::table('service_record_rows', function (Blueprint $table) {
                $table->renameColumn('service_schema_id', 'schema_vector_id');
            });
        }

        if (
            Schema::hasTable('service_records')
            && Schema::hasColumn('service_records', 'handler_key')
            && ! Schema::hasColumn('service_records', 'service_group_key')
        ) {
            Schema::table('service_records', function (Blueprint $table) {
                $table->renameColumn('handler_key', 'service_group_key');
            });
        }

        if (Schema::hasTable('service_record_rows')) {
            Schema::table('service_record_rows', function (Blueprint $table) {
                if (! Schema::hasColumn('service_record_rows', 'unit_name')) {
                    $table->string('unit_name')->nullable()->after('qty');
                }

                if (! Schema::hasColumn('service_record_rows', 'base_cost')) {
                    $table->decimal('base_cost', 12, 2)->default(0)->after('unit_name');
                }

                if (! Schema::hasColumn('service_record_rows', 'supplier_cost')) {
                    $table->decimal('supplier_cost', 12, 2)->nullable()->after('base_cost');
                }

                if (! Schema::hasColumn('service_record_rows', 'discount_type')) {
                    $table->string('discount_type', 10)->default('RM')->after('supplier_cost');
                }

                if (! Schema::hasColumn('service_record_rows', 'discount_value')) {
                    $table->decimal('discount_value', 12, 2)->default(0)->after('discount_type');
                }

                if (! Schema::hasColumn('service_record_rows', 'discount_amount')) {
                    $table->decimal('discount_amount', 12, 2)->default(0)->after('discount_value');
                }

                if (! Schema::hasColumn('service_record_rows', 'sell_price')) {
                    $table->decimal('sell_price', 12, 2)->default(0)->after('discount_amount');
                }
            });
        }

        if (Schema::hasTable('service_records') && Schema::hasColumn('service_records', 'service_group_key')) {
            DB::connection('tenant')
                ->table('service_records')
                ->whereNull('service_group_key')
                ->update(['service_group_key' => 'travel.services']);
        }

        if (Schema::hasTable('service_record_rows')) {
            DB::connection('tenant')
                ->table('service_record_rows')
                ->whereNull('supplier_cost')
                ->update(['supplier_cost' => DB::raw('unit_fare')]);

            DB::connection('tenant')
                ->table('service_record_rows')
                ->update([
                    'base_cost' => DB::raw('unit_fare'),
                    'sell_price' => DB::raw('client_price'),
                ]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('service_record_rows')) {
            Schema::table('service_record_rows', function (Blueprint $table) {
                foreach (['sell_price', 'discount_amount', 'discount_value', 'discount_type', 'supplier_cost', 'base_cost', 'unit_name'] as $column) {
                    if (Schema::hasColumn('service_record_rows', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (
            Schema::hasTable('service_records')
            && Schema::hasColumn('service_records', 'service_group_key')
            && ! Schema::hasColumn('service_records', 'handler_key')
        ) {
            Schema::table('service_records', function (Blueprint $table) {
                $table->renameColumn('service_group_key', 'handler_key');
            });
        }

        if (
            Schema::hasTable('service_record_rows')
            && Schema::hasColumn('service_record_rows', 'schema_vector_id')
            && ! Schema::hasColumn('service_record_rows', 'service_schema_id')
        ) {
            Schema::table('service_record_rows', function (Blueprint $table) {
                $table->renameColumn('schema_vector_id', 'service_schema_id');
            });
        }

        if (
            Schema::hasTable('service_record_rows')
            && Schema::hasColumn('service_record_rows', 'service_record_id')
            && ! Schema::hasColumn('service_record_rows', 'operation_id')
        ) {
            Schema::table('service_record_rows', function (Blueprint $table) {
                $table->renameColumn('service_record_id', 'operation_id');
            });
        }

        if (Schema::hasTable('service_record_rows') && ! Schema::hasTable('service_instances')) {
            Schema::rename('service_record_rows', 'service_instances');
        }

        if (Schema::hasTable('service_records') && ! Schema::hasTable('operations')) {
            Schema::rename('service_records', 'operations');
        }
    }
};
