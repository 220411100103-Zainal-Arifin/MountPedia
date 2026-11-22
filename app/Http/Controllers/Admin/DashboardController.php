<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mountain;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index()
    {
        
        $count = fn (string $table) => Schema::hasTable($table) ? DB::table($table)->count() : null;

        $monthStart = now()->startOfMonth();
        $newThisMonth = fn (string $table) => Schema::hasTable($table) && Schema::hasColumn($table, 'created_at')
            ? DB::table($table)->where('created_at', '>=', $monthStart)->count()
            : null;

        $provinces = Schema::hasTable('mountains') && Schema::hasTable('regions')
            ? DB::table('regions')->whereIn('id', DB::table('mountains')->select('region_id'))->distinct()->count('province')
            : null;

        $activeTrails = Schema::hasTable('trails') && Schema::hasColumn('trails', 'is_active')
            ? DB::table('trails')->where('is_active', true)->count()
            : null;

        $stats = [
            [
                'label' => 'Total Gunung',
                'icon' => 'mountain',
                'value' => $count('mountains'),
                'meta' => $provinces !== null ? "Tersebar di {$provinces} provinsi" : null,
                'trend' => $newThisMonth('mountains'),
            ],
            [
                'label' => 'Total Jalur Pendakian',
                'icon' => 'route',
                'value' => $count('trails'),
                'meta' => $activeTrails !== null ? "{$activeTrails} jalur aktif" : null,
                'trend' => $newThisMonth('trails'),
            ],
            [
                'label' => 'Total Tempat Sewa',
                'icon' => 'store',
                'value' => $count('rental_shops'),
                'meta' => ($n = $count('rental_items')) !== null ? number_format($n, 0, ',', '.').' barang sewa terdaftar' : null,
                'trend' => $newThisMonth('rental_shops'),
            ],
            [
                'label' => 'Total Gear',
                'icon' => 'backpack',
                'value' => $count('gear_items'),
                'meta' => ($n = $count('gear_categories')) !== null ? "Dalam {$n} kategori" : null,
                'trend' => $newThisMonth('gear_items'),
            ],
            [
                'label' => 'Total Pengguna',
                'icon' => 'users',
                'value' => $count('users'),
                'meta' => Schema::hasTable('users') ? null : 'Tabel users belum dibuat',
                'trend' => $newThisMonth('users'),
            ],
        ];

        $recentMountains = Mountain::query()->with('region')->orderByDesc('id')->limit(5)->get();

        return view('admin.dashboard', compact('stats', 'recentMountains'));
    }
}
