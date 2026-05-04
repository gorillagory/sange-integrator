<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 🟢 Explicitly connection('control')
        Schema::connection('control')->create('airports', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->string('iata_code', 3)->nullable()->index();
            $table->string('icao_code', 4)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->timestamps();

            $table->index('city');
            $table->index('name');
        });
    }

    public function down()
    {
        Schema::connection('control')->dropIfExists('airports');
    }
};
