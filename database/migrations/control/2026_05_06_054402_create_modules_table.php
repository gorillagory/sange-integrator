<?php

// database/migrations/control/2026_05_06_000001_create_modules_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'control';

    public function up(): void
    {
        Schema::connection($this->connection)->create('modules', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // travel.booking
            $table->string('industry')->index(); // travel, medical, enterprise
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_core')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('modules');
    }
};
