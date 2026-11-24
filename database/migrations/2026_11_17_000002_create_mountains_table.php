<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mountains', function (Blueprint $table) {
            $table->id();
            $table->foreignId('region_id')->constrained('regions');
            $table->string('name', 120);
            $table->string('slug', 140)->unique();
            $table->unsignedSmallInteger('elevation_mdpl');
            $table->decimal('latitude', 9, 6)->nullable();
            $table->decimal('longitude', 9, 6)->nullable();
            $table->string('managing_authority', 150)->nullable();
            $table->text('description')->nullable();
            $table->string('image_url')->nullable();

            // Status pendakian tingkat gunung. 'tutup' otomatis menutup seluruh jalur.
            $table->enum('status', ['buka', 'tutup'])->default('buka');
            $table->string('status_reason')->nullable();
            $table->string('status_source_url')->nullable();
            $table->timestamp('status_updated_at')->nullable();

            $table->timestamps();

            if (in_array(Schema::getConnection()->getDriverName(), ['mysql', 'mariadb', 'pgsql'])) {
                $table->fullText('name', 'ft_mountains_name');
            }
            $table->index('elevation_mdpl', 'idx_mountains_elevation');
            $table->index('status', 'idx_mountains_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mountains');
    }
};
