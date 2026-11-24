<?php

namespace App\Http\Controllers;

use App\Models\Mountain;
use Illuminate\Support\Str;

class HomeController extends Controller
{
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

                'status' => $m->status,
                'description' => Str::limit((string) $m->description, 150),

                'image' => str_contains((string) $m->image_url, 'placehold.co') ? null : $m->image_url,
                'url' => url('/gunung/'.$m->slug),
            ]);

        abort_if($slides->isEmpty(), 503, 'Data gunung belum tersedia. Jalankan: php artisan migrate:fresh --seed');

        return view('home', ['slides' => $slides, 'first' => $slides->first()]);
    }
}
