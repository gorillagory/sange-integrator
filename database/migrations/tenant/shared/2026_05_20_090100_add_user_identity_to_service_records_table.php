<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('service_records')) {
            return;
        }

        Schema::table('service_records', function (Blueprint $table) {
            if (! Schema::hasColumn('service_records', 'created_by_user_id')) {
                $table->unsignedBigInteger('created_by_user_id')->nullable()->after('remarks');
            }

            if (! Schema::hasColumn('service_records', 'assigned_user_id')) {
                $table->unsignedBigInteger('assigned_user_id')->nullable()->after('created_by_user_id');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('service_records')) {
            return;
        }

        Schema::table('service_records', function (Blueprint $table) {
            foreach (['assigned_user_id', 'created_by_user_id'] as $column) {
                if (Schema::hasColumn('service_records', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
