<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mountains', function (Blueprint $table) {
            $table->id();
            $table->foreignId('region_id')->constrained('regions');
            $table->string('name', 120);
            $table->string('slug', 140)->unique();
            $table->unsignedSmallInteger('elevation_mdpl');
            $table->decimal('latitude', 9, 6)->nullable();
            $table->decimal('longitude', 9, 6)->nullable();
            $table->string('managing_authority', 150)->nullable();
            $table->text('description')->nullable();
            $table->string('weather_area_code', 30)->nullable();
            $table->string('image_url')->nullable();
            $table->timestamps();

            if (in_array(Schema::getConnection()->getDriverName(), ['mysql', 'mariadb', 'pgsql'])) {
                $table->fullText('name', 'ft_mountains_name');
            }
            $table->index('elevation_mdpl', 'idx_mountains_elevation');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mountains');
    }
};
