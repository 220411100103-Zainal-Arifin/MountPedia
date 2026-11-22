<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pelengkap halaman detail barang (/perlengkapan/{slug}):
 *  - brand        : merek
 *  - body         : deskripsi lengkap (pisahkan paragraf dengan baris kosong)
 *  - features     : fitur utama, satu fitur per baris
 *  - usage_notes  : rekomendasi penggunaan (teks bebas)
 *  - specs        : spesifikasi teknis, JSON objek {"Kapasitas": "60 L", "Berat": "1,9 kg"}
 *  - gear_item_photos : galeri foto barang (foto pertama = foto utama)
 * Aman dijalankan ulang: setiap kolom/tabel dicek lebih dulu.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gear_items', function (Blueprint $table) {
            if (! Schema::hasColumn('gear_items', 'brand')) {
                $table->string('brand', 80)->nullable();
            }
            if (! Schema::hasColumn('gear_items', 'body')) {
                $table->text('body')->nullable();
            }
            if (! Schema::hasColumn('gear_items', 'features')) {
                $table->text('features')->nullable();
            }
            if (! Schema::hasColumn('gear_items', 'usage_notes')) {
                $table->text('usage_notes')->nullable();
            }
            if (! Schema::hasColumn('gear_items', 'specs')) {
                $table->json('specs')->nullable();
            }
        });

        if (! Schema::hasTable('gear_item_photos')) {
            Schema::create('gear_item_photos', function (Blueprint $table) {
                $table->id();
                $table->foreignId('gear_item_id')->constrained('gear_items')->cascadeOnDelete();
                $table->string('url');
                $table->string('caption', 150)->nullable();
                $table->unsignedSmallInteger('sort_order')->default(0);

                $table->index(['gear_item_id', 'sort_order']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('gear_item_photos');

        Schema::table('gear_items', function (Blueprint $table) {
            foreach (['brand', 'body', 'features', 'usage_notes', 'specs'] as $col) {
                if (Schema::hasColumn('gear_items', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
