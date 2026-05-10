<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Strictly built inside the Control DB
        Schema::connection('control')->create('company_user', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('company_id');
            $table->string('role')->default('employee');
            $table->timestamps();

            // Foreign Key Constraints
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');

            // Prevent duplicate assignments
            $table->unique(['user_id', 'company_id']);
        });
    }

    public function down(): void
    {
        Schema::connection('control')->dropIfExists('company_user');
    }
};
