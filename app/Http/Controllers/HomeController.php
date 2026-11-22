<?php

namespace App\Http\Controllers;

use App\Models\Mountain;
use Illuminate\Support\Str;

class HomeController extends Controller
{
    /** Gunung yang tampil di hero (sementara dipilih manual; nanti bisa dari kolom "unggulan"). */
    private const FEATURED = ['semeru', 'arjuno', 'panderman', 'penanggungan', 'rinjani'];

    public function index()
    {
        $slides = Mountain::with('region')
            ->withCount('trails')
            ->whereIn('slug', self::FEATURED)
            ->get()
            ->sortBy(fn (Mountain $m) => array_search($m->slug, self::FEATURED))
            ->values()
            ->map(fn (Mountain $m) => [
                'slug' => $m->slug,
                'name' => $m->name,
                'place' => Str::replaceFirst('Kabupaten ', '', $m->region->regency).', '.$m->region->province,
                'elevation' => $m->elevation_mdpl,
                'trails' => $m->trails_count,
                // status terkini tingkat gunung: periode mencakup hari ini, laporan terbaru
                'status' => $m->statusLogs()->mountainLevel()->activeToday()->orderByDesc('reported_at')->value('status'),
                'description' => Str::limit((string) $m->description, 150),
                // image_url dari seeder hanya placeholder; abaikan agar hero memakai ilustrasi bawaan
                'image' => str_contains((string) $m->image_url, 'placehold.co') ? null : $m->image_url,
                'url' => url('/gunung/'.$m->slug),
            ]);

        abort_if($slides->isEmpty(), 503, 'Data gunung belum tersedia. Jalankan: php artisan migrate:fresh --seed');

        return view('home', ['slides' => $slides, 'first' => $slides->first()]);
    }
}
