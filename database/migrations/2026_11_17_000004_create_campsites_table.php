<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campsites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mountain_id')->constrained('mountains')->cascadeOnDelete();
            $table->foreignId('trail_id')->nullable()->constrained('trails')->nullOnDelete();
            $table->string('name', 120);
            $table->unsignedSmallInteger('elevation_mdpl')->nullable();
            $table->unsignedSmallInteger('capacity_tents')->nullable();
            $table->boolean('has_water_source')->default(false);
            $table->string('water_note')->nullable();
            $table->decimal('latitude', 9, 6)->nullable();
            $table->decimal('longitude', 9, 6)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campsites');
    }
};
