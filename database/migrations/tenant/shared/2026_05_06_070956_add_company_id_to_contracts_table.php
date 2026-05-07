<?php

// database/migrations/tenant/shared/2026_05_06_000200_add_company_id_to_contracts_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            if (! Schema::hasColumn('contracts', 'company_id')) {
                $table->unsignedBigInteger('company_id')->nullable()->after('id')->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            if (Schema::hasColumn('contracts', 'company_id')) {
                $table->dropColumn('company_id');
            }
        });
    }
};
