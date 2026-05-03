<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Standard Schema call. Your IdentifyTenant middleware
        // will ensure this runs on the correct company database.
        Schema::create('document_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('document_type')->default('invoice'); // invoice, receipt, quote, itinerary
            $table->json('layout_vector'); // The nested grid DNA
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_templates');
    }
};
