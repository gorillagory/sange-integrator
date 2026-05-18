<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('operation_projections') && ! Schema::hasTable('service_record_projections')) {
            Schema::rename('operation_projections', 'service_record_projections');
        }

        if (
            Schema::hasTable('service_record_projections')
            && Schema::hasColumn('service_record_projections', 'handler_key')
            && ! Schema::hasColumn('service_record_projections', 'service_group_key')
        ) {
            Schema::table('service_record_projections', function (Blueprint $table) {
                $table->renameColumn('handler_key', 'service_group_key');
            });
        }

        if (
            Schema::hasTable('service_record_projections')
            && Schema::hasColumn('service_record_projections', 'operation_id')
            && ! Schema::hasColumn('service_record_projections', 'service_record_id')
        ) {
            Schema::table('service_record_projections', function (Blueprint $table) {
                $table->renameColumn('operation_id', 'service_record_id');
            });
        }

        if (
            Schema::hasTable('service_record_projections')
            && Schema::hasColumn('service_record_projections', 'service_instance_id')
            && ! Schema::hasColumn('service_record_projections', 'service_record_row_id')
        ) {
            Schema::table('service_record_projections', function (Blueprint $table) {
                $table->renameColumn('service_instance_id', 'service_record_row_id');
            });
        }

        if (
            Schema::hasTable('service_record_projections')
            && Schema::hasColumn('service_record_projections', 'service_schema_id')
            && ! Schema::hasColumn('service_record_projections', 'schema_vector_id')
        ) {
            Schema::table('service_record_projections', function (Blueprint $table) {
                $table->renameColumn('service_schema_id', 'schema_vector_id');
            });
        }
    }

    public function down(): void
    {
        if (
            Schema::hasTable('service_record_projections')
            && Schema::hasColumn('service_record_projections', 'schema_vector_id')
            && ! Schema::hasColumn('service_record_projections', 'service_schema_id')
        ) {
            Schema::table('service_record_projections', function (Blueprint $table) {
                $table->renameColumn('schema_vector_id', 'service_schema_id');
            });
        }

        if (
            Schema::hasTable('service_record_projections')
            && Schema::hasColumn('service_record_projections', 'service_record_row_id')
            && ! Schema::hasColumn('service_record_projections', 'service_instance_id')
        ) {
            Schema::table('service_record_projections', function (Blueprint $table) {
                $table->renameColumn('service_record_row_id', 'service_instance_id');
            });
        }

        if (
            Schema::hasTable('service_record_projections')
            && Schema::hasColumn('service_record_projections', 'service_record_id')
            && ! Schema::hasColumn('service_record_projections', 'operation_id')
        ) {
            Schema::table('service_record_projections', function (Blueprint $table) {
                $table->renameColumn('service_record_id', 'operation_id');
            });
        }

        if (
            Schema::hasTable('service_record_projections')
            && Schema::hasColumn('service_record_projections', 'service_group_key')
            && ! Schema::hasColumn('service_record_projections', 'handler_key')
        ) {
            Schema::table('service_record_projections', function (Blueprint $table) {
                $table->renameColumn('service_group_key', 'handler_key');
            });
        }

        if (Schema::hasTable('service_record_projections') && ! Schema::hasTable('operation_projections')) {
            Schema::rename('service_record_projections', 'operation_projections');
        }
    }
};
