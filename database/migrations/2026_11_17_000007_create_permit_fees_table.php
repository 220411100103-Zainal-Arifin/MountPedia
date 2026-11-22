<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permit_fees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mountain_id')->constrained('mountains')->cascadeOnDelete();
            $table->foreignId('trail_id')->nullable()->constrained('trails')->nullOnDelete();
            $table->foreignId('trip_type_id')->constrained('trip_types');
            $table->enum('visitor_type', ['wni', 'wna'])->default('wni');
            $table->enum('day_type', ['weekday', 'weekend_libur'])->default('weekday');
            $table->unsignedInteger('fee_idr'); 
            $table->enum('fee_unit', ['per_orang', 'per_orang_per_malam'])->default('per_orang');
            $table->date('valid_from')->nullable();
            $table->date('valid_until')->nullable();
            $table->string('source_url')->nullable();
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->index(['mountain_id', 'trip_type_id', 'visitor_type', 'day_type'], 'idx_permit_lookup');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permit_fees');
    }
};
