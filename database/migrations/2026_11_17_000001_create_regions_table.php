<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('regions', function (Blueprint $table) {
            $table->id();
            $table->string('province', 100);
            $table->string('regency', 100);

            $table->unique(['province', 'regency'], 'uq_regions');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('regions');
    }
};
