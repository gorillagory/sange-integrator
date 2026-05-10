<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // This table ONLY exists in sange_control
        Schema::connection('control')->create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('subdomain')->unique();
            $table->string('db_name')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes(); // Required for ISO 27001 / HIPAA
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('control')->dropIfExists('companies');
    }
};
