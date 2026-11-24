<?php

namespace Database\Seeders;

use App\Models\Campsite;
use App\Models\Mountain;
use App\Models\Region;
use App\Models\Trail;
use Illuminate\Database\Seeder;

class MountainSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->data() as $row) {
            $region = Region::where('province', $row['region'][0])
                ->where('regency', $row['region'][1])
                ->firstOrFail();

            $mountain = Mountain::updateOrCreate(['slug' => $row['slug']], [
                'region_id' => $region->id,
                'name' => $row['name'],
                'elevation_mdpl' => $row['elevation'],
                'latitude' => $row['lat'],
                'longitude' => $row['lng'],
                'managing_authority' => $row['authority'],
                'description' => $row['description'],
                'image_url' => 'https://placehold.co/1200x630?text='.urlencode($row['name']),

                // status gunung; 'tutup' menutup seluruh jalurnya (lihat Trail::effective_status)
                'status' => $row['status'] ?? 'buka',
                'status_reason' => $row['status_reason'] ?? null,
                'status_source_url' => isset($row['status_reason']) ? 'https://example.com/pengumuman-pengelola' : null,
                'status_updated_at' => now(),
            ]);

            $trailIds = [];
            foreach ($row['trails'] as $i => $t) {
                $trail = Trail::updateOrCreate(
                    ['mountain_id' => $mountain->id, 'slug' => $t['slug']],
                    [
                        'name' => $t['name'],
                        'basecamp_name' => $t['basecamp'],

                        'basecamp_latitude' => round($row['lat'] - 0.04 * ($i + 1), 6),
                        'basecamp_longitude' => round($row['lng'] + 0.03 * ($i + 1), 6),
                        'start_elevation_mdpl' => $t['start'],
                        'distance_km' => $t['km'],
                        'duration_up_hours' => $t['up'],
                        'duration_down_hours' => $t['down'],
                        'difficulty' => $t['difficulty'],
                        'description' => $t['description'] ?? sprintf(
                            'Jalur via %s dengan estimasi %s jam naik dan %s jam turun (data contoh).',
                            $t['basecamp'], $t['up'], $t['down']
                        ),
                        // status jalur sendiri; berlaku bila gunungnya buka
                        'status' => $t['status'] ?? 'buka',
                        'status_reason' => $t['status_reason'] ?? null,
                    ]
                );
                $trailIds[$t['slug']] = $trail->id;
            }

            foreach ($row['camps'] as $j => $c) {
                Campsite::updateOrCreate(
                    ['mountain_id' => $mountain->id, 'name' => $c['name']],
                    [
                        'trail_id' => isset($c['trail']) ? ($trailIds[$c['trail']] ?? null) : null,
                        'elevation_mdpl' => $c['elevation'],
                        'capacity_tents' => $c['tents'],
                        'has_water_source' => $c['water'],
                        'water_note' => $c['water_note'],

                        'latitude' => round($row['lat'] - 0.01 * ($j + 1), 6),
                        'longitude' => round($row['lng'] + 0.01 * ($j + 1), 6),
                        'notes' => $c['notes'] ?? sprintf('Area camp di ketinggian %s mdpl (data contoh).', $c['elevation']),
                    ]
                );
            }
        }
    }

    private function data(): array
    {
        return [
            [
                'name' => 'Gunung Semeru', 'slug' => 'semeru', 'elevation' => 3676,
                'region' => ['Jawa Timur', 'Kabupaten Lumajang'],
                'lat' => -8.108000, 'lng' => 112.922000,
                'authority' => 'Balai Besar Taman Nasional Bromo Tengger Semeru',
                'description' => 'Gunung tertinggi di Pulau Jawa dengan puncak Mahameru. Pendakian terkenal melewati Ranu Pani dan danau Ranu Kumbolo.',
                'trails' => [
                    ['name' => 'Jalur Ranu Pani', 'slug' => 'ranu-pani', 'basecamp' => 'Ranu Pani', 'start' => 2100, 'km' => 15.0, 'up' => 10.0, 'down' => 7.0, 'difficulty' => 'sulit'],
                ],
                'camps' => [
                    ['name' => 'Ranu Kumbolo', 'elevation' => 2400, 'tents' => 200, 'water' => true, 'water_note' => 'Air danau, perlu dimasak/disaring', 'trail' => 'ranu-pani'],
                    ['name' => 'Kalimati', 'elevation' => 2700, 'tents' => 100, 'water' => true, 'water_note' => 'Sumber Mani, ±1 jam pulang-pergi dari camp', 'trail' => 'ranu-pani'],
                    ['name' => 'Arcopodo', 'elevation' => 2900, 'tents' => 20, 'water' => false, 'water_note' => 'Tidak ada sumber air', 'trail' => 'ranu-pani'],
                ],
            ],
            [
                'name' => 'Gunung Arjuno', 'slug' => 'arjuno', 'elevation' => 3339,
                'region' => ['Jawa Timur', 'Kabupaten Malang'],
                'lat' => -7.765000, 'lng' => 112.580000,
                'authority' => 'Tahura R. Soerjo',
                'description' => 'Gunung berapi di kawasan Arjuno-Welirang yang berdekatan dengan Gunung Welirang; populer lewat jalur Lawang dan Tretes.',
                'trails' => [
                    ['name' => 'Jalur Lawang', 'slug' => 'lawang', 'basecamp' => 'Lawang', 'start' => 1000, 'km' => 12.0, 'up' => 9.0, 'down' => 6.0, 'difficulty' => 'sulit'],
                    ['name' => 'Jalur Tretes', 'slug' => 'tretes', 'basecamp' => 'Tretes', 'start' => 900, 'km' => 14.0, 'up' => 10.0, 'down' => 7.0, 'difficulty' => 'sulit'],
                ],
                'camps' => [
                    ['name' => 'Pondokan', 'elevation' => 2000, 'tents' => 30, 'water' => true, 'water_note' => 'Aliran air dekat lokasi', 'trail' => 'tretes'],
                    ['name' => 'Kokopan', 'elevation' => 2700, 'tents' => 25, 'water' => true, 'water_note' => 'Mata air terdekat; cek ketersediaan musim kemarau', 'trail' => 'tretes'],
                ],
            ],
            [
                'name' => 'Gunung Welirang', 'slug' => 'welirang', 'elevation' => 3156,
                'region' => ['Jawa Timur', 'Kabupaten Pasuruan'],
                'lat' => -7.734000, 'lng' => 112.589000,
                'authority' => 'Tahura R. Soerjo',
                'description' => 'Gunung belerang di kawasan Arjuno-Welirang; jalur pendakian umumnya dimulai dari Tretes.',
                'trails' => [
                    ['name' => 'Jalur Tretes', 'slug' => 'tretes', 'basecamp' => 'Tretes', 'start' => 900, 'km' => 11.0, 'up' => 8.0, 'down' => 5.5, 'difficulty' => 'sulit'],
                ],
                'camps' => [
                    ['name' => 'Pondokan Welirang', 'elevation' => 2800, 'tents' => 20, 'water' => true, 'water_note' => 'Mata air terbatas', 'trail' => 'tretes'],
                ],
            ],
            [
                'name' => 'Gunung Panderman', 'slug' => 'panderman', 'elevation' => 2045,
                'region' => ['Jawa Timur', 'Kota Batu'],
                'lat' => -7.870000, 'lng' => 112.520000,
                'authority' => 'Pengelola basecamp setempat',
                'description' => 'Gunung ringan di Kota Batu yang cocok untuk pemula dan pendakian tektok.',
                'trails' => [
                    ['name' => 'Jalur Toyomerto', 'slug' => 'toyomerto', 'basecamp' => 'Basecamp Panderman (Toyomerto)', 'start' => 1000, 'km' => 5.5, 'up' => 3.5, 'down' => 2.5, 'difficulty' => 'sedang'],
                ],
                'camps' => [],
            ],
            [
                'name' => 'Gunung Penanggungan', 'slug' => 'penanggungan', 'elevation' => 1653,
                'region' => ['Jawa Timur', 'Kabupaten Mojokerto'],
                'lat' => -7.605000, 'lng' => 112.630000,
                'authority' => 'Tahura R. Soerjo',
                'description' => 'Gunung dengan banyak situs peninggalan purbakala di lerengnya; cocok untuk tektok.',
                'trails' => [
                    ['name' => 'Jalur Tamiajeng', 'slug' => 'tamiajeng', 'basecamp' => 'Tamiajeng, Trawas', 'start' => 700, 'km' => 4.0, 'up' => 3.0, 'down' => 2.0, 'difficulty' => 'sedang'],
                    ['name' => 'Jalur Jolotundo', 'slug' => 'jolotundo', 'basecamp' => 'Jolotundo', 'start' => 600, 'km' => 4.5, 'up' => 3.5, 'down' => 2.5, 'difficulty' => 'sedang'],
                ],
                'camps' => [
                    ['name' => 'Puncak Bayangan', 'elevation' => 1500, 'tents' => 20, 'water' => false, 'water_note' => 'Tidak ada sumber air; bawa dari bawah', 'trail' => 'tamiajeng'],
                ],
            ],
            [
                'name' => 'Gunung Merbabu', 'slug' => 'merbabu', 'elevation' => 3145,
                'region' => ['Jawa Tengah', 'Kabupaten Boyolali'],
                'lat' => -7.455000, 'lng' => 110.440000,
                'authority' => 'Balai Taman Nasional Gunung Merbabu',
                'description' => 'Gunung dengan sabana luas dan pemandangan Merapi; jalur Selo dan Suwanting populer.',
                'trails' => [
                    ['name' => 'Jalur Selo', 'slug' => 'selo', 'basecamp' => 'Selo', 'start' => 1600, 'km' => 9.0, 'up' => 6.0, 'down' => 4.0, 'difficulty' => 'sedang'],
                    ['name' => 'Jalur Suwanting', 'slug' => 'suwanting', 'basecamp' => 'Suwanting', 'start' => 1400, 'km' => 10.0, 'up' => 7.0, 'down' => 4.5, 'difficulty' => 'sedang'],
                ],
                'camps' => [
                    ['name' => 'Sabana 1', 'elevation' => 2800, 'tents' => 60, 'water' => false, 'water_note' => 'Tidak ada air; bawa secukupnya', 'trail' => 'selo'],
                    ['name' => 'Sabana 2', 'elevation' => 2950, 'tents' => 40, 'water' => false, 'water_note' => 'Tidak ada air', 'trail' => 'selo'],
                ],
            ],
            [
                'name' => 'Gunung Lawu', 'slug' => 'lawu', 'elevation' => 3265,
                'region' => ['Jawa Timur', 'Kabupaten Magetan'],
                'lat' => -7.626000, 'lng' => 111.193000,
                'authority' => 'Perhutani dan pengelola basecamp setempat',
                'description' => 'Gunung di perbatasan Jawa Tengah dan Jawa Timur dengan jalur Cemoro Sewu dan Cemoro Kandang.',
                'trails' => [
                    ['name' => 'Jalur Cemoro Sewu', 'slug' => 'cemoro-sewu', 'basecamp' => 'Cemoro Sewu', 'start' => 1800, 'km' => 9.0, 'up' => 7.0, 'down' => 5.0, 'difficulty' => 'sedang'],
                    ['name' => 'Jalur Cemoro Kandang', 'slug' => 'cemoro-kandang', 'basecamp' => 'Cemoro Kandang', 'start' => 1900, 'km' => 10.0, 'up' => 7.5, 'down' => 5.0, 'difficulty' => 'sedang', 'status' => 'tutup', 'status_reason' => 'Contoh: perbaikan jalur, jalur lain tetap dibuka (data contoh)'],
                ],
                'camps' => [
                    ['name' => 'Sendang Drajat', 'elevation' => 3100, 'tents' => 15, 'water' => true, 'water_note' => 'Sumber air dekat puncak', 'trail' => 'cemoro-sewu'],
                ],
            ],
            [
                'name' => 'Gunung Rinjani', 'slug' => 'rinjani', 'elevation' => 3726,
                'status' => 'tutup', 'status_reason' => 'Contoh penutupan sementara; seluruh jalur ikut ditutup (data contoh)',
                'region' => ['Nusa Tenggara Barat', 'Kabupaten Lombok Timur'],
                'lat' => -8.411500, 'lng' => 116.457000,
                'authority' => 'Balai Taman Nasional Gunung Rinjani',
                'description' => 'Gunung tertinggi kedua di Indonesia untuk gunung berapi; terkenal dengan Danau Segara Anak. Umumnya didaki 2–4 hari.',
                'trails' => [
                    ['name' => 'Jalur Sembalun', 'slug' => 'sembalun', 'basecamp' => 'Sembalun', 'start' => 1150, 'km' => 14.0, 'up' => 9.0, 'down' => 7.0, 'difficulty' => 'sulit'],
                    ['name' => 'Jalur Senaru', 'slug' => 'senaru', 'basecamp' => 'Senaru', 'start' => 600, 'km' => 13.0, 'up' => 9.0, 'down' => 7.0, 'difficulty' => 'sulit'],
                ],
                'camps' => [
                    ['name' => 'Plawangan Sembalun', 'elevation' => 2639, 'tents' => 150, 'water' => false, 'water_note' => 'Air perlu dibawa/dibeli dari porter', 'trail' => 'sembalun'],
                    ['name' => 'Danau Segara Anak', 'elevation' => 2000, 'tents' => 100, 'water' => true, 'water_note' => 'Air danau, perlu dimasak/disaring'],
                ],
            ],

            [
                'name' => 'Gunung Kawi', 'slug' => 'kawi', 'elevation' => 2651,
                'region' => ['Jawa Timur', 'Kabupaten Malang'],
                'lat' => -7.925000, 'lng' => 112.458000,
                'authority' => 'Perhutani dan pengelola basecamp setempat',
                'description' => 'Gunung di perbatasan Malang dan Blitar yang dikenal lewat kawasan wisata ziarah di lerengnya; jalur pendakian populer lewat Wonosari.',
                'trails' => [
                    ['name' => 'Jalur Wonosari', 'slug' => 'wonosari', 'basecamp' => 'Wonosari', 'start' => 1000, 'km' => 9.0, 'up' => 6.0, 'down' => 4.0, 'difficulty' => 'sedang'],
                ],
                'camps' => [],
            ],
            [
                'name' => 'Gunung Butak', 'slug' => 'butak', 'elevation' => 2868,
                'region' => ['Jawa Timur', 'Kota Batu'],
                'lat' => -7.914000, 'lng' => 112.488000,
                'authority' => 'Tahura R. Soerjo',
                'description' => 'Puncak di jajaran Kawi-Butak dengan hutan pegunungan yang rapat dan sering berkabut.',
                'trails' => [
                    ['name' => 'Jalur Sumber Brantas', 'slug' => 'sumber-brantas', 'basecamp' => 'Sumber Brantas', 'start' => 1600, 'km' => 8.0, 'up' => 6.0, 'down' => 4.0, 'difficulty' => 'sulit'],
                ],
                'camps' => [
                    ['name' => 'Pos Pertigaan', 'elevation' => 2400, 'tents' => 15, 'water' => false, 'water_note' => 'Tidak ada sumber air; bawa dari bawah', 'trail' => 'sumber-brantas'],
                ],
            ],
            [
                'name' => 'Gunung Merapi', 'slug' => 'merapi', 'elevation' => 2930,
                'region' => ['Jawa Tengah', 'Kabupaten Boyolali'],
                'lat' => -7.540700, 'lng' => 110.445700,
                'authority' => 'Balai Taman Nasional Gunung Merapi',
                'description' => 'Salah satu gunung berapi paling aktif di Indonesia; jalur Selo membawa pendaki ke Pasar Bubrah sebelum puncak.',
                'trails' => [
                    ['name' => 'Jalur Selo', 'slug' => 'selo', 'basecamp' => 'Selo', 'start' => 1600, 'km' => 6.0, 'up' => 5.0, 'down' => 3.5, 'difficulty' => 'sedang'],
                ],
                'camps' => [
                    ['name' => 'Pasar Bubrah', 'elevation' => 2700, 'tents' => 80, 'water' => false, 'water_note' => 'Tidak ada air; bawa dari bawah', 'trail' => 'selo'],
                ],
            ],
            [
                'name' => 'Gunung Bromo', 'slug' => 'bromo', 'elevation' => 2329,
                'region' => ['Jawa Timur', 'Kabupaten Pasuruan'],
                'lat' => -7.942500, 'lng' => 112.953000,
                'authority' => 'Balai Besar Taman Nasional Bromo Tengger Semeru',
                'description' => 'Gunung berapi aktif di tengah lautan pasir Tengger; kawahnya dicapai dengan berjalan kaki singkat dari kawasan wisata.',
                'trails' => [
                    ['name' => 'Jalur Wonokitri', 'slug' => 'wonokitri', 'basecamp' => 'Wonokitri, Tosari', 'start' => 1750, 'km' => 5.0, 'up' => 2.0, 'down' => 1.5, 'difficulty' => 'sedang'],
                ],
                'camps' => [],
            ],
            [
                'name' => 'Gunung Lamongan', 'slug' => 'lamongan', 'elevation' => 1651,
                'region' => ['Jawa Timur', 'Kabupaten Lumajang'],
                'lat' => -7.977800, 'lng' => 113.341700,
                'authority' => 'Pengelola basecamp setempat',
                'description' => 'Gunung berapi kecil yang dikelilingi telaga-telaga vulkanik (maar), cocok untuk pendakian singkat.',
                'trails' => [
                    ['name' => 'Jalur Tegalrejo', 'slug' => 'tegalrejo', 'basecamp' => 'Tegalrejo', 'start' => 600, 'km' => 6.0, 'up' => 4.0, 'down' => 3.0, 'difficulty' => 'sedang'],
                ],
                'camps' => [],
            ],
        ];
    }
}
