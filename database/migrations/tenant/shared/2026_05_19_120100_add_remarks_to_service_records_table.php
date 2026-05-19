<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_records', function (Blueprint $table) {
            if (! Schema::hasColumn('service_records', 'client_remark_preset_id')) {
                $table->unsignedBigInteger('client_remark_preset_id')->nullable()->after('contract_no');
            }

            if (! Schema::hasColumn('service_records', 'remarks')) {
                $table->text('remarks')->nullable()->after('client_remark_preset_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('service_records', function (Blueprint $table) {
            foreach (['remarks', 'client_remark_preset_id'] as $column) {
                if (Schema::hasColumn('service_records', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
