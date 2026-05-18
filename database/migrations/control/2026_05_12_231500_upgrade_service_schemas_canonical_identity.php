<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $schema = Schema::connection('control');

        $schema->table('service_schemas', function (Blueprint $table) use ($schema) {
            if (! $schema->hasColumn('service_schemas', 'service_code')) {
                $table->string('service_code')->nullable()->after('industry');
            }

            if (! $schema->hasColumn('service_schemas', 'service_name')) {
                $table->string('service_name')->nullable()->after('display_name');
            }

            if (! $schema->hasColumn('service_schemas', 'version')) {
                $table->unsignedInteger('version')->default(1)->after('service_name');
            }

            if (! $schema->hasColumn('service_schemas', 'status')) {
                $table->string('status', 30)->default('active')->after('version');
            }

            if (! $schema->hasColumn('service_schemas', 'is_default')) {
                $table->boolean('is_default')->default(true)->after('status');
            }
        });

        DB::connection('control')->table('service_schemas')
            ->whereNull('service_code')
            ->orWhere('service_code', '')
            ->update(['service_code' => DB::raw('service_type')]);

        DB::connection('control')->table('service_schemas')
            ->whereNull('service_name')
            ->orWhere('service_name', '')
            ->update(['service_name' => DB::raw('display_name')]);

        DB::connection('control')->table('service_schemas')
            ->whereNull('version')
            ->update(['version' => 1]);

        DB::connection('control')->table('service_schemas')
            ->whereNull('status')
            ->update(['status' => 'active']);

        DB::connection('control')->table('service_schemas')
            ->whereNull('is_default')
            ->update(['is_default' => true]);

        DB::connection('control')->statement(
            'ALTER TABLE service_schemas DROP CONSTRAINT IF EXISTS service_schemas_service_type_unique'
        );
        DB::connection('control')->statement('DROP INDEX IF EXISTS service_schemas_service_type_unique');

        DB::connection('control')->statement(
            'CREATE UNIQUE INDEX IF NOT EXISTS service_schemas_industry_service_code_version_unique
            ON service_schemas (industry, service_code, version)'
        );
        DB::connection('control')->statement(
            'CREATE INDEX IF NOT EXISTS service_schemas_industry_service_code_index
            ON service_schemas (industry, service_code)'
        );
        DB::connection('control')->statement(
            "CREATE UNIQUE INDEX IF NOT EXISTS service_schemas_active_default_unique
            ON service_schemas (industry, service_code)
            WHERE status = 'active' AND is_default = true"
        );
    }

    public function down(): void
    {
        DB::connection('control')->statement('DROP INDEX IF EXISTS service_schemas_active_default_unique');
        DB::connection('control')->statement('DROP INDEX IF EXISTS service_schemas_industry_service_code_index');
        DB::connection('control')->statement('DROP INDEX IF EXISTS service_schemas_industry_service_code_version_unique');
        DB::connection('control')->statement(
            'CREATE UNIQUE INDEX IF NOT EXISTS service_schemas_service_type_unique
            ON service_schemas (service_type)'
        );

        $schema = Schema::connection('control');

        $schema->table('service_schemas', function (Blueprint $table) use ($schema) {
            if ($schema->hasColumn('service_schemas', 'is_default')) {
                $table->dropColumn('is_default');
            }

            if ($schema->hasColumn('service_schemas', 'status')) {
                $table->dropColumn('status');
            }

            if ($schema->hasColumn('service_schemas', 'version')) {
                $table->dropColumn('version');
            }

            if ($schema->hasColumn('service_schemas', 'service_name')) {
                $table->dropColumn('service_name');
            }

            if ($schema->hasColumn('service_schemas', 'service_code')) {
                $table->dropColumn('service_code');
            }
        });
    }
};

