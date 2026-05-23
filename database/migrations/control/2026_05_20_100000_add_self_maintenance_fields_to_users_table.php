<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('control')->table('users', function (Blueprint $table) {
            if (! Schema::connection('control')->hasColumn('users', 'username')) {
                $table->string('username')->nullable()->unique()->after('name');
            }

            if (! Schema::connection('control')->hasColumn('users', 'digital_id')) {
                $table->string('digital_id')->nullable()->unique()->after('email');
            }

            if (! Schema::connection('control')->hasColumn('users', 'image_path')) {
                $table->string('image_path')->nullable()->after('digital_id');
            }
        });
    }

    public function down(): void
    {
        Schema::connection('control')->table('users', function (Blueprint $table) {
            if (Schema::connection('control')->hasColumn('users', 'image_path')) {
                $table->dropColumn('image_path');
            }

            if (Schema::connection('control')->hasColumn('users', 'digital_id')) {
                $table->dropColumn('digital_id');
            }

            if (Schema::connection('control')->hasColumn('users', 'username')) {
                $table->dropColumn('username');
            }
        });
    }
};
