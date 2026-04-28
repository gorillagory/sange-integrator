<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id')->nullable();
            $table->string('contract_no')->nullable();
            $table->string('invoice_no')->nullable();
            $table->string('reference_no')->unique()->nullable();
            $table->jsonb('cart_payload')->nullable();
            $table->jsonb('passenger_details')->nullable();
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->string('status')->default('Draft');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
