<?php

namespace App\Http\Controllers;

use App\Models\Mountain;
use App\Models\Region;
use App\Models\TrailStatusLog;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class MountainController extends Controller
{
    private const PER_PAGE = 12;

    /** kunci query (?mdpl=) => [min, max, label] */
    private const ELEVATION_RANGES = [
        '0-1499' => [0, 1499, 'Di bawah 1.500 mdpl'],
        '1500-2499' => [1500, 2499, '1.500 – 2.499 mdpl'],
        '2500-2999' => [2500, 2999, '2.500 – 2.999 mdpl'],
        '3000-9999' => [3000, 9999, '3.000 mdpl ke atas'],
    ];

    private const STATUSES = ['buka', 'dibatasi', 'tutup'];

    public function index(Request $request)
    {
        $q = $request->query('q');
        $province = $request->query('provinsi');
        $range = $request->query('mdpl');
        $status = $request->query('status');

        // Nilai yang tidak valid diabaikan, bukan menyebabkan error
        $filters = [
            'q' => is_string($q) ? mb_substr(trim($q), 0, 80) : '',
            'provinsi' => is_string($province) ? $province : '',
            'mdpl' => is_string($range) && isset(self::ELEVATION_RANGES[$range]) ? $range : '',
            'status' => is_string($status) && in_array($status, self::STATUSES, true) ? $status : '',
        ];

        // Status terkini tingkat gunung (periode mencakup hari ini, laporan terbaru).
        // Dipakai dua kali: sebagai kolom `current_status` dan sebagai syarat filter.
        $currentStatus = fn () => TrailStatusLog::query()
            ->select('status')
            ->whereColumn((new TrailStatusLog)->qualifyColumn('mountain_id'), (new Mountain)->qualifyColumn('id'))
            ->mountainLevel()
            ->activeToday()
            ->orderByDesc('reported_at')
            ->limit(1);

        $mountains = Mountain::query()
            ->with('region')
            ->withCount('trails')
            ->addSelect(['current_status' => $currentStatus()])
            ->when($filters['q'] !== '', function ($query) use ($filters) {
                $like = '%'.addcslashes($filters['q'], '%_\\').'%';

                $query->where(fn ($w) => $w
                    ->where('name', 'like', $like)
                    ->orWhereIn('region_id', Region::query()->select('id')->where('regency', 'like', $like)));
            })
            ->when($filters['provinsi'] !== '', fn ($query) => $query
                ->whereIn('region_id', Region::query()->select('id')->where('province', $filters['provinsi'])))
            ->when($filters['mdpl'] !== '', fn ($query) => $query
                ->whereBetween('elevation_mdpl', array_slice(self::ELEVATION_RANGES[$filters['mdpl']], 0, 2)))
            ->when($filters['status'] !== '', fn ($query) => $query
                ->where($currentStatus(), '=', $filters['status']))
            ->orderBy('name')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        // ?page= melebihi halaman terakhir: arahkan ke halaman terakhir
        if ($mountains->currentPage() > $mountains->lastPage()) {
            return redirect()->to($mountains->url($mountains->lastPage()));
        }

        $provinces = Region::query()
            ->whereIn('id', Mountain::query()->select('region_id'))
            ->distinct()
            ->orderBy('province')
            ->pluck('province');

        return view('gunung.index', [
            'mountains' => $mountains,
            'provinces' => $provinces,
            'elevationRanges' => self::ELEVATION_RANGES,
            'filters' => $filters,
            'hasFilters' => collect($filters)->contains(fn ($v) => $v !== ''),
        ]);
    }

    public function show(Mountain $mountain)
    {
        $mountain->load([
            'region',
            'trails' => fn ($q) => $q->where('is_active', true)->orderBy('name'),
            'trails.campsites',
            'campsites' => fn ($q) => $q->orderBy('elevation_mdpl'),
            'photos',
        ]);

        // Status terkini: tingkat gunung, dan tingkat jalur (menimpa status gunung untuk jalur itu)
        $status = $mountain->statusLogs()->mountainLevel()->activeToday()
            ->orderByDesc('reported_at')->first();

        $trailStatuses = $mountain->statusLogs()->whereNotNull('trail_id')->activeToday()
            ->orderByDesc('reported_at')->get()
            ->unique('trail_id')->keyBy('trail_id');

        // Prakiraan terdekat per titik (basecamp / puncak)
        $weather = $mountain->weatherForecasts()
            ->where('forecast_at', '>=', now()->subHours(3))
            ->orderBy('forecast_at')->get()
            ->groupBy('point')->map(fn (Collection $g) => $g->first());

        // Rekomendasi: provinsi yang sama lebih dulu, lalu ketinggian yang paling berdekatan
        $related = Mountain::query()
            ->with('region')
            ->withCurrentStatus()
            ->whereKeyNot($mountain->id)
            ->orderByRaw('CASE WHEN region_id IN (SELECT id FROM regions WHERE province = ?) THEN 0 ELSE 1 END', [$mountain->region->province])
            ->orderByRaw('ABS(CAST(elevation_mdpl AS SIGNED) - ?)', [$mountain->elevation_mdpl])
            ->limit(8)
            ->get();

        return view('gunung.show', [
            'mountain' => $mountain,
            'heroPhoto' => $this->realImage($mountain->image_url),
            'status' => $status,
            'trailStatuses' => $trailStatuses,
            'stats' => $this->stats($mountain),
            'overview' => $this->overview($mountain->description),
            'permit' => $this->permit($mountain),
            'weather' => $weather,
            'mapPoints' => $this->mapPoints($mountain),
            'sharedCamps' => $mountain->campsites->whereNull('trail_id')->values(),
            'related' => $related,
            'jsonLd' => $this->jsonLd($mountain),
        ]);
    }

    /** image_url dari seeder hanya placeholder; abaikan agar halaman memakai ilustrasi bawaan. */
    private function realImage(?string $url): ?string
    {
        return filled($url) && ! str_contains($url, 'placehold.co') ? $url : null;
    }

    /** Angka ringkas untuk bento grid. Semua nilai dihitung dari database, null bila datanya belum ada. */
    private function stats(Mountain $mountain): array
    {
        $trails = $mountain->trails;
        $levels = ['mudah' => 'Mudah', 'sedang' => 'Sedang', 'sulit' => 'Sulit', 'ekstrem' => 'Ekstrem'];
        $order = array_keys($levels);

        $idx = $trails->pluck('difficulty')->filter()
            ->map(fn ($d) => array_search($d, $order, true))->filter(fn ($i) => $i !== false);

        $difficulty = null;
        if ($idx->isNotEmpty()) {
            $lo = $levels[$order[$idx->min()]];
            $hi = $levels[$order[$idx->max()]];
            $difficulty = $lo === $hi ? $lo : "{$lo} – {$hi}";
        }

        $ups = $trails->pluck('duration_up_hours')->filter(fn ($v) => $v !== null)->map(fn ($v) => (float) $v);
        $climb = null;
        if ($ups->isNotEmpty()) {
            $lo = $this->hours($ups->min());
            $hi = $this->hours($ups->max());
            $climb = ($lo === $hi ? $lo : "{$lo}–{$hi}").' jam';
        }

        return [
            'rank' => Mountain::where('elevation_mdpl', '>', $mountain->elevation_mdpl)->count() + 1,
            'total' => Mountain::count(),
            'elevation_pct' => round($mountain->elevation_mdpl / max(1, Mountain::max('elevation_mdpl')) * 100),
            'trail_count' => $trails->count(),
            'camp_count' => $mountain->campsites->count(),
            'difficulty' => $difficulty,
            'climb' => $climb,
        ];
    }

    private function hours(float $v): string
    {
        return rtrim(rtrim(number_format($v, 1, ',', '.'), '0'), ',');
    }

    /** Paragraf pembuka (lead) dan sisa paragraf, untuk tata letak editorial. */
    private function overview(?string $text): array
    {
        $text = trim((string) $text);
        if ($text === '') {
            return ['lead' => null, 'body' => []];
        }

        $paragraphs = array_values(array_filter(preg_split('/\R{2,}/', $text)));
        if (count($paragraphs) === 1) {
            // satu paragraf: kalimat pertama jadi lead, sisanya isi
            $parts = preg_split('/(?<=[.!?])\s+/', $paragraphs[0], 2);

            return ['lead' => $parts[0], 'body' => isset($parts[1]) ? [$parts[1]] : []];
        }

        return ['lead' => array_shift($paragraphs), 'body' => $paragraphs];
    }

    /** Tabel tarif simaksi (tingkat gunung) yang masih berlaku hari ini. Null bila belum ada data. */
    private function permit(Mountain $mountain): ?array
    {
        $today = today();

        $fees = $mountain->permitFees()->with('tripType')->whereNull('trail_id')
            ->where(fn ($q) => $q->whereNull('valid_from')->orWhereDate('valid_from', '<=', $today))
            ->where(fn ($q) => $q->whereNull('valid_until')->orWhereDate('valid_until', '>=', $today))
            ->orderBy('trip_type_id')->get();

        if ($fees->isEmpty()) {
            return null;
        }

        $rows = [];
        foreach ($fees->groupBy('trip_type_id') as $group) {
            $unit = $group->first()->fee_unit === 'per_orang_per_malam' ? 'per orang per malam' : 'per orang';

            foreach (['weekday' => 'Hari biasa', 'weekend_libur' => 'Akhir pekan & libur'] as $day => $label) {
                $cell = fn (string $visitor) => $group
                    ->first(fn ($f) => $f->day_type === $day && $f->visitor_type === $visitor)?->fee_idr;

                $rows[] = [
                    'trip' => $group->first()->tripType->name,
                    'day' => $label,
                    'unit' => $unit,
                    'wni' => $cell('wni'),
                    'wna' => $cell('wna'),
                ];
            }
        }

        return [
            'rows' => $rows,
            'valid_until' => $fees->pluck('valid_until')->filter()->min(),
            'notes' => $fees->pluck('notes')->filter()->unique()->values()->all(),
            'source_url' => $fees->pluck('source_url')->filter()->first(),
        ];
    }

    /** Titik untuk peta Leaflet: puncak, basecamp tiap jalur, dan camp. Hanya yang punya koordinat. */
    private function mapPoints(Mountain $mountain): array
    {
        $points = [];

        if ($mountain->latitude !== null && $mountain->longitude !== null) {
            $points[] = [
                'key' => 'summit', 'type' => 'summit', 'label' => 'Puncak',
                'name' => $mountain->name,
                'detail' => number_format($mountain->elevation_mdpl, 0, ',', '.').' mdpl',
                'lat' => (float) $mountain->latitude, 'lng' => (float) $mountain->longitude,
            ];
        }

        foreach ($mountain->trails as $i => $trail) {
            if ($trail->basecamp_latitude === null || $trail->basecamp_longitude === null) {
                continue;
            }
            $points[] = [
                'key' => 'trail-'.$trail->id, 'type' => 'basecamp', 'label' => 'Basecamp',
                'name' => $trail->basecamp_name ?: $trail->name,
                'detail' => $trail->name,
                'order' => $i + 1,
                'lat' => (float) $trail->basecamp_latitude, 'lng' => (float) $trail->basecamp_longitude,
            ];
        }

        foreach ($mountain->campsites as $camp) {
            if ($camp->latitude === null || $camp->longitude === null) {
                continue;
            }
            $bits = array_filter([
                $camp->elevation_mdpl ? number_format($camp->elevation_mdpl, 0, ',', '.').' mdpl' : null,
                $camp->has_water_source ? 'ada sumber air' : 'tanpa sumber air',
            ]);
            $points[] = [
                'key' => 'camp-'.$camp->id, 'type' => 'camp', 'label' => 'Area camp',
                'name' => $camp->name,
                'detail' => implode(' · ', $bits),
                'lat' => (float) $camp->latitude, 'lng' => (float) $camp->longitude,
            ];
        }

        return $points;
    }

    private function jsonLd(Mountain $mountain): array
    {
        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'Mountain',
            'name' => $mountain->name,
            'url' => route('mountains.show', $mountain),
            'description' => Str::limit(strip_tags((string) $mountain->description), 200),
            'containedInPlace' => [
                '@type' => 'AdministrativeArea',
                'name' => $mountain->region->regency.', '.$mountain->region->province,
            ],
        ];

        if ($mountain->latitude !== null && $mountain->longitude !== null) {
            $data['geo'] = [
                '@type' => 'GeoCoordinates',
                'latitude' => (float) $mountain->latitude,
                'longitude' => (float) $mountain->longitude,
                'elevation' => $mountain->elevation_mdpl,
            ];
        }

        return $data;
    }
}