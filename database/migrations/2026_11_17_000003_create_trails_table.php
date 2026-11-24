<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trails', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mountain_id')->constrained('mountains')->cascadeOnDelete();
            $table->string('name', 120);
            $table->string('slug', 140);
            $table->string('basecamp_name', 150)->nullable();
            $table->decimal('basecamp_latitude', 9, 6)->nullable();
            $table->decimal('basecamp_longitude', 9, 6)->nullable();
            $table->unsignedSmallInteger('start_elevation_mdpl')->nullable();
            $table->decimal('distance_km', 5, 2)->nullable();
            $table->decimal('duration_up_hours', 4, 1)->nullable();
            $table->decimal('duration_down_hours', 4, 1)->nullable();
            $table->enum('difficulty', ['mudah', 'sedang', 'sulit', 'ekstrem'])->nullable();
            $table->text('description')->nullable();

            // Status jalur sendiri. Dipakai hanya bila gunungnya 'buka';
            // bila gunung 'tutup', jalur dianggap tutup (lihat Trail::effective_status).
            $table->enum('status', ['buka', 'tutup'])->default('buka');
            $table->string('status_reason')->nullable();

            $table->timestamps();

            $table->unique(['mountain_id', 'slug'], 'uq_trails_slug');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trails');
    }
};
