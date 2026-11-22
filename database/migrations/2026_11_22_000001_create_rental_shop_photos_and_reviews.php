<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pelengkap halaman detail tempat sewa:
 *  - rental_shops.terms        : ketentuan sewa (satu ketentuan per baris)
 *  - rental_shop_photos        : galeri foto toko
 *  - rental_shop_reviews       : ulasan pengunjung (rating dihitung dari sini)
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('rental_shops', 'terms')) {
            Schema::table('rental_shops', function (Blueprint $table) {
                $table->text('terms')->nullable();
            });
        }

        if (! Schema::hasTable('rental_shop_photos')) {
            Schema::create('rental_shop_photos', function (Blueprint $table) {
                $table->id();
                $table->foreignId('rental_shop_id')->constrained()->cascadeOnDelete();
                $table->string('url');
                $table->string('caption', 150)->nullable();
                $table->unsignedSmallInteger('sort_order')->default(0);

                $table->index(['rental_shop_id', 'sort_order']);
            });
        }

        if (! Schema::hasTable('rental_shop_reviews')) {
            Schema::create('rental_shop_reviews', function (Blueprint $table) {
                $table->id();
                $table->foreignId('rental_shop_id')->constrained()->cascadeOnDelete();
                $table->string('reviewer_name', 80);
                $table->unsignedTinyInteger('rating'); // 1-5
                $table->text('comment')->nullable();
                $table->date('visited_on')->nullable();
                $table->boolean('is_published')->default(true);
                $table->timestamps();

                $table->index(['rental_shop_id', 'is_published']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('rental_shop_reviews');
        Schema::dropIfExists('rental_shop_photos');

        if (Schema::hasColumn('rental_shops', 'terms')) {
            Schema::table('rental_shops', function (Blueprint $table) {
                $table->dropColumn('terms');
            });
        }
    }
};
