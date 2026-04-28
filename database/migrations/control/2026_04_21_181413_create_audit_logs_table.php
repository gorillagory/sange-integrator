<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Force this table to live in the Control database
    protected $connection = 'control';

    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // The Matrix Context
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();

            // The Taxonomy
            $table->string('category')->index(); // e.g., 'USER', 'SCANNER'
            $table->string('action')->index();   // e.g., 'CREATED', 'AUTH.LOGIN'
            $table->string('resource_type')->nullable(); // e.g., 'App\Models\Patient'
            $table->string('resource_id')->nullable();

            // The Payload (Before & After states)
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();

            // The Cryptographic Seal
            $table->string('signature', 64);

            // Timestamps (Only created_at, because they are immutable!)
            $table->timestamp('created_at')->useCurrent()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
