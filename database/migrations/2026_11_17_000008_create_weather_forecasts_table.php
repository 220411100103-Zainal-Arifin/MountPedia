<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weather_forecasts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mountain_id')->constrained('mountains')->cascadeOnDelete();
            $table->enum('point', ['basecamp', 'puncak'])->default('basecamp');
            $table->dateTime('forecast_at');
            $table->decimal('temp_c', 4, 1)->nullable();
            $table->unsignedTinyInteger('humidity_pct')->nullable();
            $table->decimal('wind_kmh', 4, 1)->nullable();
            $table->unsignedTinyInteger('rain_probability_pct')->nullable();
            $table->string('condition', 60)->nullable();
            $table->string('source', 60)->nullable();
            $table->dateTime('fetched_at');

            $table->unique(['mountain_id', 'point', 'forecast_at'], 'uq_weather');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weather_forecasts');
    }
};
