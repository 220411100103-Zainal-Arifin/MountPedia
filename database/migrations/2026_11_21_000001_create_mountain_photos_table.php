<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mountain_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mountain_id')->constrained('mountains')->cascadeOnDelete();
            // URL penuh (https://...) atau path relatif di disk "public" (mis. mountains/semeru/1.jpg)
            $table->string('path');
            $table->string('caption', 200)->nullable();
            // kredit fotografer/sumber: wajib diisi bila foto bukan milik sendiri
            $table->string('credit', 150)->nullable();
            $table->string('credit_url')->nullable();
            // ukuran asli, dipakai untuk mencegah layout bergeser saat gambar dimuat
            $table->unsignedSmallInteger('width')->nullable();
            $table->unsignedSmallInteger('height')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['mountain_id', 'sort_order'], 'idx_photos_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mountain_photos');
    }
};
