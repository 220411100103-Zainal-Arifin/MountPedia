<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shop_mountains', function (Blueprint $table) {
            $table->foreignId('rental_shop_id')->constrained('rental_shops')->cascadeOnDelete();
            $table->foreignId('mountain_id')->constrained('mountains')->cascadeOnDelete();
            $table->decimal('distance_km_to_basecamp', 5, 1)->nullable();
            $table->string('note', 100)->nullable();

            $table->primary(['rental_shop_id', 'mountain_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_mountains');
    }
};
