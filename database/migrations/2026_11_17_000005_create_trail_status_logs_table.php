<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trail_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mountain_id')->constrained('mountains')->cascadeOnDelete();
            
            $table->foreignId('trail_id')->nullable()->constrained('trails')->cascadeOnDelete();
            $table->enum('status', ['buka', 'tutup', 'dibatasi']);
            $table->string('reason')->nullable();
            $table->date('starts_on');
            $table->date('ends_on')->nullable();
            $table->string('source_name', 150)->nullable();
            $table->string('source_url')->nullable();
            $table->dateTime('reported_at');
            $table->timestamps();

            $table->index(['mountain_id', 'trail_id', 'reported_at'], 'idx_status_current');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trail_status_logs');
    }
};
