<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // This table lives in the sange_control database!
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // Petronas is one entity globally
            $table->string('registration_number')->nullable();
            $table->string('logo_path')->nullable();

            // Global HQ Contact
            $table->string('hq_contact_person')->nullable();
            $table->string('hq_contact_email')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
