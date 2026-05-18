<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('operation_projections')) {
            return;
        }

        Schema::create('operation_projections', function (Blueprint $table) {
            $table->id();
            $table->string('handler_key');
            $table->unsignedBigInteger('operation_id');
            $table->unsignedBigInteger('service_instance_id')->unique();
            $table->unsignedBigInteger('service_schema_id')->nullable();
            $table->string('service_code')->nullable();
            $table->unsignedInteger('schema_version')->default(1);
            $table->timestamp('captured_at')->nullable();
            $table->json('dimensions');
            $table->json('metrics');
            $table->json('raw_payload')->nullable();
            $table->timestamps();

            $table->index(['handler_key', 'captured_at']);
            $table->index(['operation_id', 'service_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operation_projections');
    }
};
