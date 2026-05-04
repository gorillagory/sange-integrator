<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'control';

    public function up(): void
    {
        Schema::connection($this->connection)->table('companies', function (Blueprint $table) {
            if (! Schema::connection($this->connection)->hasColumn('companies', 'main_group_company_id')) {
                $table->unsignedBigInteger('main_group_company_id')->nullable()->after('id');
            }

            if (! Schema::connection($this->connection)->hasColumn('companies', 'registration_number')) {
                $table->string('registration_number')->nullable()->after('name');
            }

            if (! Schema::connection($this->connection)->hasColumn('companies', 'address')) {
                $table->json('address')->nullable()->after('industry');
            }

            if (! Schema::connection($this->connection)->hasColumn('companies', 'phones')) {
                $table->json('phones')->nullable()->after('address');
            }

            if (! Schema::connection($this->connection)->hasColumn('companies', 'enterprise_types')) {
                $table->json('enterprise_types')->nullable()->after('phones');
            }

            if (! Schema::connection($this->connection)->hasColumn('companies', 'logo_path')) {
                $table->string('logo_path')->nullable()->after('enterprise_types');
            }

            if (! Schema::connection($this->connection)->hasColumn('companies', 'theme_color')) {
                $table->string('theme_color')->nullable()->after('logo_path');
            }
        });

        Schema::connection($this->connection)->table('companies', function (Blueprint $table) {
            $table->foreign('main_group_company_id')
                ->references('id')
                ->on('main_group_companies')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->table('companies', function (Blueprint $table) {
            $table->dropForeign(['main_group_company_id']);
        });

        Schema::connection($this->connection)->table('companies', function (Blueprint $table) {
            if (Schema::connection($this->connection)->hasColumn('companies', 'main_group_company_id')) {
                $table->dropColumn('main_group_company_id');
            }

            if (Schema::connection($this->connection)->hasColumn('companies', 'registration_number')) {
                $table->dropColumn('registration_number');
            }

            if (Schema::connection($this->connection)->hasColumn('companies', 'address')) {
                $table->dropColumn('address');
            }

            if (Schema::connection($this->connection)->hasColumn('companies', 'phones')) {
                $table->dropColumn('phones');
            }

            if (Schema::connection($this->connection)->hasColumn('companies', 'enterprise_types')) {
                $table->dropColumn('enterprise_types');
            }

            if (Schema::connection($this->connection)->hasColumn('companies', 'logo_path')) {
                $table->dropColumn('logo_path');
            }

            if (Schema::connection($this->connection)->hasColumn('companies', 'theme_color')) {
                $table->dropColumn('theme_color');
            }
        });
    }
};
