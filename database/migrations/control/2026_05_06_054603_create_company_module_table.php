<?php

// database/migrations/control/2026_05_06_000002_create_company_modules_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'control';

    public function up(): void
    {
        Schema::connection($this->connection)->create('company_modules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('module_id');
            $table->timestamp('enabled_at')->nullable();
            $table->jsonb('settings_json')->nullable();
            $table->timestamps();

            $table->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->onDelete('cascade');

            $table->foreign('module_id')
                ->references('id')
                ->on('modules')
                ->onDelete('cascade');

            $table->unique(['company_id', 'module_id']);
            $table->index(['company_id', 'module_id']);
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('company_modules');
    }
};
