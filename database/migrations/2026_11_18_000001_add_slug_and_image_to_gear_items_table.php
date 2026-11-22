<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Halaman Perlengkapan butuh slug (URL detail) dan gambar per perlengkapan.
 * Aman dijalankan bila kolomnya sudah ada.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gear_items', function (Blueprint $table) {
            if (! Schema::hasColumn('gear_items', 'slug')) {
                $table->string('slug', 140)->nullable()->unique();
            }
            if (! Schema::hasColumn('gear_items', 'image_url')) {
                $table->string('image_url')->nullable();
            }
        });

        // isi slug untuk baris yang sudah ada
        DB::table('gear_items')->whereNull('slug')->orderBy('id')->each(function ($row) {
            DB::table('gear_items')->where('id', $row->id)->update(['slug' => Str::slug($row->name)]);
        });
    }

    public function down(): void
    {
        Schema::table('gear_items', function (Blueprint $table) {
            if (Schema::hasColumn('gear_items', 'slug')) {
                $table->dropUnique(['slug']);
                $table->dropColumn('slug');
            }
            if (Schema::hasColumn('gear_items', 'image_url')) {
                $table->dropColumn('image_url');
            }
        });
    }
};
