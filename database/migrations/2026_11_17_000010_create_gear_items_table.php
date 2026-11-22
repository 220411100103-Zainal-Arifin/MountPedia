<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gear_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gear_category_id')->constrained('gear_categories');
            $table->string('name', 120)->unique();
            $table->string('description')->nullable();
            $table->boolean('is_rentable')->default(true);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gear_items');
    }
};
