<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // This table lives inside sange_tenant_bt (or whatever tenant is active)
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id'); // Links to the Global Client ID

            $table->string('contract_no')->unique(); // e.g. BAYAM 323
            $table->string('title'); // e.g. Provision of Travel Management

            // Billing specifics for THIS specific contract
            $table->text('billing_address')->nullable();
            $table->string('payment_terms')->default('30 Days');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
