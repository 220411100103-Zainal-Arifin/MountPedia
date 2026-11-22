<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rental_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rental_shop_id')->constrained('rental_shops')->cascadeOnDelete();
            $table->foreignId('gear_item_id')->nullable()->constrained('gear_items')->nullOnDelete();
            $table->string('item_name', 150);
            $table->unsignedInteger('price_per_day_idr');
            $table->string('stock_note', 100)->nullable();
            $table->string('notes')->nullable();

            $table->index('gear_item_id', 'idx_rental_gear');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rental_items');
    }
};
