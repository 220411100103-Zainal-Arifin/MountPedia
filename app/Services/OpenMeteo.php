<?php

namespace App\Services;

use App\Models\Mountain;
use DateTimeImmutable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Prakiraan cuaca gunung dari Open-Meteo (https://open-meteo.com).
 * Tanpa API key (pemakaian non-komersial; atribusi wajib, ditampilkan di halaman).
 *
 * Satu permintaan memuat dua titik sekaligus (puncak + basecamp), masing-masing
 * dengan `elevation` sendiri agar suhu dikoreksi ke ketinggian titik itu.
 * Hasil disimpan di cache 30 menit per gunung, jadi jumlah panggilan ke Open-Meteo
 * ditentukan oleh jumlah gunung, bukan jumlah pengunjung.
 */
class OpenMeteo
{
    private const ENDPOINT = 'https://api.open-meteo.com/v1/forecast';

    private const TTL = 1800;      // detik: data berhasil

    private const FAIL_TTL = 60;   // detik: jangan menghujani API saat sedang gangguan

    private const DAYS = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];

    private const MONTHS = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

    /** Kode cuaca WMO => teks Indonesia. */
    private const CONDITIONS = [
        0 => 'Cerah', 1 => 'Cerah berawan', 2 => 'Berawan sebagian', 3 => 'Mendung',
        45 => 'Berkabut', 48 => 'Berkabut tebal',
        51 => 'Gerimis ringan', 53 => 'Gerimis', 55 => 'Gerimis lebat',
        56 => 'Gerimis beku', 57 => 'Gerimis beku lebat',
        61 => 'Hujan ringan', 63 => 'Hujan sedang', 65 => 'Hujan lebat',
        66 => 'Hujan beku', 67 => 'Hujan beku lebat',
        71 => 'Salju ringan', 73 => 'Salju', 75 => 'Salju lebat', 77 => 'Butiran salju',
        80 => 'Hujan sesaat ringan', 81 => 'Hujan sesaat', 82 => 'Hujan sesaat deras',
        85 => 'Hujan salju ringan', 86 => 'Hujan salju lebat',
        95 => 'Badai petir', 96 => 'Badai petir, es', 97 => 'Badai petir lebat', 99 => 'Badai petir, es lebat',
    ];

    /** Data siap tampil, atau null bila gunung tak punya koordinat / Open-Meteo sedang tak terjangkau. */
    public function forMountain(Mountain $mountain): ?array
    {
        $points = $this->points($mountain);
        if ($points === []) {
            return null;
        }

        // kunci memuat koordinat: koordinat diubah => cache lama otomatis tak terpakai
        $key = 'openmeteo:'.md5(json_encode($points));

        if (($hit = Cache::get($key)) !== null) {
            return $hit;
        }
        if (Cache::has($key.':down')) {
            return null;
        }

        $data = $this->fetch($points);
        if ($data === null) {
            Cache::put($key.':down', true, self::FAIL_TTL);

            return null;
        }

        Cache::put($key, $data, self::TTL);

        return $data;
    }

    /** Titik prakiraan: puncak (koordinat + mdpl gunung) dan satu basecamp bila datanya lengkap. */
    public function points(Mountain $mountain): array
    {
        if ($mountain->latitude === null || $mountain->longitude === null) {
            return [];
        }

        $points = [[
            'key' => 'summit',
            'label' => 'Puncak',
            'detail' => number_format($mountain->elevation_mdpl, 0, ',', '.').' mdpl',
            'lat' => (float) $mountain->latitude,
            'lng' => (float) $mountain->longitude,
            'elevation' => (int) $mountain->elevation_mdpl,
        ]];

        $mountain->loadMissing('trails');
        $trail = $mountain->trails->sortBy('name')->first(fn ($t) => $t->basecamp_latitude !== null
            && $t->basecamp_longitude !== null
            && $t->start_elevation_mdpl !== null);

        if ($trail) {
            $points[] = [
                'key' => 'basecamp',
                'label' => 'Basecamp',
                'detail' => ($trail->basecamp_name ?: $trail->name).', '.number_format($trail->start_elevation_mdpl, 0, ',', '.').' mdpl',
                'lat' => (float) $trail->basecamp_latitude,
                'lng' => (float) $trail->basecamp_longitude,
                'elevation' => (int) $trail->start_elevation_mdpl,
            ];
        }

        return $points;
    }

    private function fetch(array $points): ?array
    {
        try {
            $response = Http::acceptJson()
                ->timeout(6)
                ->retry(1, 300, throw: false)
                ->get(self::ENDPOINT, [
                    // beberapa titik: nilai dipisah koma, urutannya sama di tiga parameter
                    'latitude' => implode(',', array_column($points, 'lat')),
                    'longitude' => implode(',', array_column($points, 'lng')),
                    'elevation' => implode(',', array_column($points, 'elevation')),
                    'current' => 'temperature_2m,apparent_temperature,relative_humidity_2m,precipitation,weather_code,wind_speed_10m,wind_gusts_10m',
                    'daily' => 'weather_code,temperature_2m_max,temperature_2m_min,precipitation_probability_max,precipitation_sum,wind_speed_10m_max',
                    'timezone' => 'auto',
                    'forecast_days' => 5,
                    'wind_speed_unit' => 'kmh',
                ]);

            if ($response->failed()) {
                Log::warning('Open-Meteo gagal', ['status' => $response->status()]);

                return null;
            }

            return self::normalize($response->json(), $points);
        } catch (Throwable $e) {
            Log::warning('Open-Meteo tidak terjangkau', ['error' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * Ubah respons Open-Meteo menjadi bentuk yang dipakai halaman.
     * Satu titik => objek; beberapa titik => daftar objek (sama urutannya dengan $points).
     */
    public static function normalize(mixed $json, array $points): ?array
    {
        if (! is_array($json) || $json === []) {
            return null;
        }

        $locations = array_is_list($json) ? $json : [$json];
        if (count($locations) !== count($points)) {
            return null;
        }

        $out = [];
        foreach ($points as $i => $point) {
            $cur = $locations[$i]['current'] ?? null;
            $daily = $locations[$i]['daily'] ?? null;
            if (! is_array($cur) || ! is_array($daily) || empty($daily['time'])) {
                return null;
            }

            $days = [];
            foreach ($daily['time'] as $d => $date) {
                $days[] = [
                    'label' => self::dayLabel($date, $d),
                    'condition' => self::condition($daily['weather_code'][$d] ?? null),
                    'max' => self::whole($daily['temperature_2m_max'][$d] ?? null),
                    'min' => self::whole($daily['temperature_2m_min'][$d] ?? null),
                    'rain_pct' => self::whole($daily['precipitation_probability_max'][$d] ?? null),
                    'rain_mm' => isset($daily['precipitation_sum'][$d]) ? round((float) $daily['precipitation_sum'][$d], 1) : null,
                    'wind_max' => self::whole($daily['wind_speed_10m_max'][$d] ?? null),
                ];
            }

            $out[] = [
                'key' => $point['key'],
                'label' => $point['label'],
                'detail' => $point['detail'],
                'current' => [
                    'temp' => self::whole($cur['temperature_2m'] ?? null),
                    'feels' => self::whole($cur['apparent_temperature'] ?? null),
                    'humidity' => self::whole($cur['relative_humidity_2m'] ?? null),
                    'rain_mm' => isset($cur['precipitation']) ? round((float) $cur['precipitation'], 1) : null,
                    'wind' => self::whole($cur['wind_speed_10m'] ?? null),
                    'gust' => self::whole($cur['wind_gusts_10m'] ?? null),
                    'condition' => self::condition($cur['weather_code'] ?? null),
                ],
                'daily' => $days,
            ];
        }

        $first = $locations[0];

        return [
            'points' => $out,
            'observed_at' => self::stamp($first['current']['time'] ?? null, (int) ($first['utc_offset_seconds'] ?? 0)),
            'source' => 'Open-Meteo',
            'source_url' => 'https://open-meteo.com/',
        ];
    }

    private static function whole(mixed $v): ?int
    {
        return is_numeric($v) ? (int) round((float) $v) : null;
    }

    private static function condition(mixed $code): string
    {
        return self::CONDITIONS[(int) $code] ?? 'Kondisi tidak diketahui';
    }

    private static function dayLabel(string $date, int $index): string
    {
        if ($index === 0) {
            return 'Hari ini';
        }
        if ($index === 1) {
            return 'Besok';
        }

        $d = DateTimeImmutable::createFromFormat('Y-m-d', $date);

        return $d ? self::DAYS[(int) $d->format('w')].', '.$d->format('j').' '.self::MONTHS[(int) $d->format('n') - 1] : $date;
    }

    /** "24 Nov, 12.30 WIB" dari waktu lokal titik + selisih UTC-nya. */
    private static function stamp(?string $time, int $offset): ?string
    {
        if ($time === null) {
            return null;
        }
        try {
            $d = new DateTimeImmutable($time);
        } catch (Throwable) {
            return null;
        }

        $zone = [25200 => 'WIB', 28800 => 'WITA', 32400 => 'WIT'][$offset] ?? 'GMT'.($offset >= 0 ? '+' : '-').intdiv(abs($offset), 3600);

        return $d->format('j').' '.self::MONTHS[(int) $d->format('n') - 1].', '.$d->format('H.i').' '.$zone;
    }
}
