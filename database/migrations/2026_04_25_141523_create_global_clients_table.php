<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 🟢 Explicitly connection('control')
        Schema::connection('control')->create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('registration_number')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('hq_contact_person')->nullable();
            $table->string('hq_contact_email')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('control')->dropIfExists('clients');
    }
};
