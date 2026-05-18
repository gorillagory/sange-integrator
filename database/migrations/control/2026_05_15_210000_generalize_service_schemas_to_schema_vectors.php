<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::connection('control')->hasTable('service_schemas') && ! Schema::connection('control')->hasTable('schema_vectors')) {
            Schema::connection('control')->rename('service_schemas', 'schema_vectors');
        }

        if (Schema::connection('control')->hasTable('schema_vectors') && ! Schema::connection('control')->hasColumn('schema_vectors', 'service_group_key')) {
            Schema::connection('control')->table('schema_vectors', function (Blueprint $table) {
                $table->string('service_group_key')->nullable()->after('industry')->index();
            });
        }

        if (Schema::connection('control')->hasTable('schema_vectors') && Schema::connection('control')->hasColumn('schema_vectors', 'service_group_key')) {
            DB::connection('control')
                ->table('schema_vectors')
                ->whereNull('service_group_key')
                ->update([
                    'service_group_key' => DB::raw("industry || '.services'"),
                ]);
        }
    }

    public function down(): void
    {
        if (Schema::connection('control')->hasTable('schema_vectors') && Schema::connection('control')->hasColumn('schema_vectors', 'service_group_key')) {
            Schema::connection('control')->table('schema_vectors', function (Blueprint $table) {
                $table->dropColumn('service_group_key');
            });
        }

        if (Schema::connection('control')->hasTable('schema_vectors') && ! Schema::connection('control')->hasTable('service_schemas')) {
            Schema::connection('control')->rename('schema_vectors', 'service_schemas');
        }
    }
};
