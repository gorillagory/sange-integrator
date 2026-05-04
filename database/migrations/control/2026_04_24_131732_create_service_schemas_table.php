<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('control')->create('service_schemas', function (Blueprint $table) {
            $table->id();
            $table->string('industry')->index(); // e.g., 'travel', 'medical'
            $table->string('service_type')->unique(); // e.g., 'flight_ticket', 'hotel'
            $table->string('display_name'); // e.g., 'Air Ticket'
            $table->jsonb('schema_payload'); // 👈 The magic lives here
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('control')->dropIfExists('service_schemas');
    }
};
