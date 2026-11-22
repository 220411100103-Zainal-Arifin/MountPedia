<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Foto toko untuk kartu di halaman /sewa. Kosong = kartu memakai warna + ikon. */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('rental_shops', 'image_url')) {
            Schema::table('rental_shops', function (Blueprint $table) {
                $table->string('image_url')->nullable()->after('description');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('rental_shops', 'image_url')) {
            Schema::table('rental_shops', function (Blueprint $table) {
                $table->dropColumn('image_url');
            });
        }
    }
};
