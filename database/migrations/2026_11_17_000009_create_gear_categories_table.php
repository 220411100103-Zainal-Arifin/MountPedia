<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gear_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80)->unique();
            $table->unsignedTinyInteger('sort_order')->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gear_categories');
    }
};
