<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gear_recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_type_id')->constrained('trip_types');
            $table->foreignId('gear_item_id')->constrained('gear_items')->cascadeOnDelete();
            // NULL = aturan umum; terisi = kekhususan gunung tertentu
            $table->foreignId('mountain_id')->nullable()->constrained('mountains')->cascadeOnDelete();
            $table->unsignedTinyInteger('min_nights')->default(0);
            $table->unsignedTinyInteger('max_nights')->nullable();
            $table->enum('priority', ['wajib', 'disarankan', 'opsional'])->default('disarankan');
            $table->string('quantity_note', 100)->nullable();
            $table->string('notes')->nullable();

            $table->index(['trip_type_id', 'mountain_id', 'min_nights'], 'idx_gear_rec');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gear_recommendations');
    }
};
