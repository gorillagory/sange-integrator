<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::connection('control')->hasTable('clients')) {
            return;
        }

        Schema::connection('control')->table('clients', function (Blueprint $table) {
            if (! Schema::connection('control')->hasColumn('clients', 'address')) {
                $table->text('address')->nullable()->after('hq_contact_email');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::connection('control')->hasTable('clients')) {
            return;
        }

        Schema::connection('control')->table('clients', function (Blueprint $table) {
            if (Schema::connection('control')->hasColumn('clients', 'address')) {
                $table->dropColumn('address');
            }
        });
    }
};
