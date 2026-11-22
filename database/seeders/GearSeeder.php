<?php

namespace Database\Seeders;

use App\Models\GearCategory;
use App\Models\GearItem;
use App\Models\GearRecommendation;
use App\Models\Mountain;
use App\Models\TripType;
use Illuminate\Database\Seeder;

class GearSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedCatalog();
        $this->seedRecommendations();
    }

    
    private function catalog(): array
    {
        return [
            'Shelter & Tidur' => [
                ['Tenda dome', true, 'Tenda tahan angin dan hujan sesuai jumlah orang'],
                ['Sleeping bag', true, 'Kantong tidur dengan rating suhu sesuai ketinggian'],
                ['Matras', true, 'Alas tidur penahan dingin dari tanah'],
                ['Hammock', true, 'Opsional untuk bersantai di area camp'],
            ],
            'Tas & Pembawa' => [
                ['Carrier 40-60 L', true, 'Tas besar untuk pendakian bermalam'],
                ['Daypack 20-30 L', true, 'Tas kecil untuk tektok atau summit attack'],
                ['Rain cover tas', true, 'Pelindung tas dari hujan'],
                ['Kantong sampah', false, 'Bawa turun seluruh sampah'],
            ],
            'Pakaian' => [
                ['Jaket gunung', true, 'Jaket penahan angin dan dingin'],
                ['Jas hujan / ponco', true, 'Pelindung hujan'],
                ['Baju ganti', false, 'Pakaian kering untuk di camp'],
                ['Kaos kaki cadangan', false, 'Kaos kaki kering'],
                ['Sarung tangan', true, 'Penghangat tangan dan pelindung saat memegang batu'],
                ['Kupluk', true, 'Penutup kepala penahan dingin'],
                ['Buff / masker', false, 'Pelindung debu dan dingin'],
            ],
            'Alas Kaki' => [
                ['Sepatu gunung / trail', true, 'Sepatu dengan grip baik'],
                ['Sandal gunung', true, 'Alas kaki cadangan di camp'],
            ],
            'Penerangan & Navigasi' => [
                ['Headlamp', true, 'Lampu kepala'],
                ['Baterai cadangan', false, 'Baterai untuk headlamp'],
                ['Powerbank', true, 'Sumber daya cadangan'],
                ['Peta / GPS offline', false, 'Navigasi tanpa sinyal'],
                ['Peluit', false, 'Sinyal darurat'],
            ],
            'Masak & Makan' => [
                ['Kompor portable', true, 'Kompor gas untuk memasak di gunung'],
                ['Gas kaleng', false, 'Bahan bakar kompor'],
                ['Nesting / panci', true, 'Set alat masak'],
                ['Alat makan', false, 'Sendok, garpu, piring/mug'],
                ['Korek api / lighter', false, 'Pemantik api'],
            ],
            'Hidrasi & Logistik' => [
                ['Botol minum 1,5 L', true, 'Wadah air minum'],
                ['Logistik makanan', false, 'Makanan sesuai jumlah malam'],
                ['Snack energi', false, 'Camilan penambah tenaga'],
            ],
            'P3K & Keselamatan' => [
                ['Kotak P3K', false, 'Perlengkapan pertolongan pertama'],
                ['Obat pribadi', false, 'Obat rutin masing-masing pendaki'],
                ['Emergency blanket', false, 'Selimut darurat penahan panas tubuh'],
                ['Trekking pole', true, 'Tongkat bantu jalan di tanjakan dan turunan'],
            ],
            'Lain-lain' => [
                ['Tisu basah', false, 'Kebersihan pribadi'],
                ['Sunblock', false, 'Pelindung kulit dari sinar matahari'],
            ],
        ];
    }

    private function seedCatalog(): void
    {
        $order = 1;
        foreach ($this->catalog() as $category => $items) {
            $cat = GearCategory::updateOrCreate(['name' => $category], ['sort_order' => $order++]);

            foreach ($items as [$name, $rentable, $desc]) {
                GearItem::updateOrCreate(['name' => $name], [
                    'gear_category_id' => $cat->id,
                    'description' => $desc,
                    'is_rentable' => $rentable,
                ]);
            }
        }
    }

    private function seedRecommendations(): void
    {
        $tektok = TripType::where('code', 'tektok')->firstOrFail();
        $camping = TripType::where('code', 'camping')->firstOrFail();

        
        $tektokRules = [
            ['Daypack 20-30 L', 'wajib'], ['Botol minum 1,5 L', 'wajib'], ['Jaket gunung', 'wajib'],
            ['Jas hujan / ponco', 'wajib'], ['Sepatu gunung / trail', 'wajib'], ['Headlamp', 'wajib'],
            ['Kotak P3K', 'wajib'], ['Snack energi', 'wajib'], ['Kantong sampah', 'wajib'],
            ['Obat pribadi', 'disarankan'], ['Powerbank', 'disarankan'], ['Peta / GPS offline', 'disarankan'],
            ['Sarung tangan', 'disarankan'], ['Buff / masker', 'disarankan'], ['Peluit', 'disarankan'],
            ['Trekking pole', 'opsional'], ['Sunblock', 'opsional'],
        ];
        foreach ($tektokRules as [$item, $priority]) {
            $this->rule($tektok, $item, 0, null, $priority);
        }

        
        $campingAll = [
            ['Carrier 40-60 L', 'wajib'], ['Tenda dome', 'wajib'], ['Sleeping bag', 'wajib'], ['Matras', 'wajib'],
            ['Jaket gunung', 'wajib'], ['Jas hujan / ponco', 'wajib'], ['Sepatu gunung / trail', 'wajib'],
            ['Headlamp', 'wajib'], ['Kompor portable', 'wajib'], ['Nesting / panci', 'wajib'],
            ['Alat makan', 'wajib'], ['Korek api / lighter', 'wajib'], ['Botol minum 1,5 L', 'wajib'],
            ['Kotak P3K', 'wajib'], ['Kantong sampah', 'wajib'],
            ['Baterai cadangan', 'disarankan'], ['Rain cover tas', 'disarankan'], ['Sarung tangan', 'disarankan'],
            ['Kupluk', 'disarankan'], ['Obat pribadi', 'disarankan'], ['Emergency blanket', 'disarankan'],
            ['Powerbank', 'disarankan'], ['Peta / GPS offline', 'disarankan'], ['Peluit', 'disarankan'],
            ['Tisu basah', 'disarankan'], ['Buff / masker', 'disarankan'],
            ['Daypack 20-30 L', 'opsional'], ['Trekking pole', 'opsional'], ['Hammock', 'opsional'], ['Sunblock', 'opsional'],
        ];
        foreach ($campingAll as [$item, $priority]) {
            $this->rule($camping, $item, 1, null, $priority);
        }

        
        $byNights = [
            ['Logistik makanan', 'wajib', [[1, 1, '3 kali makan + snack'], [2, 2, '5 kali makan + snack'], [3, null, '8 kali makan + snack, tambah cadangan 1 hari']]],
            ['Gas kaleng', 'wajib', [[1, 2, '1 kaleng per 4 orang'], [3, null, '2 kaleng per 4 orang']]],
            ['Baju ganti', 'wajib', [[1, 1, '1 set'], [2, null, '2 set']]],
            ['Kaos kaki cadangan', 'wajib', [[1, 1, '1 pasang'], [2, null, '2-3 pasang']]],
        ];
        foreach ($byNights as [$item, $priority, $ranges]) {
            foreach ($ranges as [$min, $max, $qty]) {
                $this->rule($camping, $item, $min, $max, $priority, $qty);
            }
        }

        
        $this->mountainRule('semeru', $tektok, 'Buff / masker', 0, null, 'wajib', 'Debu dan gas vulkanik di jalur Kalimati-Mahameru');
        $this->mountainRule('semeru', $camping, 'Buff / masker', 1, null, 'wajib', 'Debu dan gas vulkanik di jalur Kalimati-Mahameru');
        $this->mountainRule('rinjani', $camping, 'Trekking pole', 1, null, 'disarankan', 'Tanjakan panjang dan medan pasir');
        $this->mountainRule('rinjani', $camping, 'Sleeping bag', 1, null, 'wajib', 'Malam di Plawangan sangat dingin; gunakan sleeping bag hangat');
        $this->mountainRule('lawu', $camping, 'Jaket gunung', 1, null, 'wajib', 'Suhu malam dingin dan berangin');
    }

    
    private function hint(string $item): array
    {
        $hints = [
            'Tenda dome' => ['1 tenda per 2-3 orang', 'Pilih tenda double layer yang tahan angin'],
            'Sleeping bag' => ['1 per orang', 'Pilih rating suhu sesuai ketinggian'],
            'Matras' => ['1 per orang', 'Menahan dingin dari tanah'],
            'Carrier 40-60 L' => ['1 per orang', 'Kapasitas menyesuaikan durasi'],
            'Daypack 20-30 L' => ['1 per orang', 'Untuk summit attack atau tektok'],
            'Jaket gunung' => ['1 buah', 'Tahan angin dan dingin'],
            'Jas hujan / ponco' => ['1 per orang', 'Cuaca gunung cepat berubah'],
            'Sepatu gunung / trail' => ['1 pasang', 'Grip baik dan sudah dipakai sebelumnya'],
            'Headlamp' => ['1 per orang', 'Cek baterai sebelum berangkat'],
            'Kompor portable' => ['1 per kelompok', 'Pastikan sambungan gas aman'],
            'Botol minum 1,5 L' => ['2 botol per orang', 'Isi penuh sebelum naik'],
            'Kotak P3K' => ['1 per kelompok', 'Cek isi dan tanggal kedaluwarsa'],
            'Kantong sampah' => ['2-3 lembar', 'Semua sampah dibawa turun'],
        ];

        return $hints[$item] ?? ['1 per orang', 'Periksa kondisi sebelum berangkat'];
    }

    private function rule(TripType $trip, string $itemName, int $min, ?int $max, string $priority, ?string $qty = null, ?int $mountainId = null, ?string $notes = null): void
    {
        $item = GearItem::where('name', $itemName)->firstOrFail();
        [$defaultQty, $defaultNote] = $this->hint($itemName);

        GearRecommendation::updateOrCreate(
            [
                'trip_type_id' => $trip->id,
                'gear_item_id' => $item->id,
                'mountain_id' => $mountainId,
                'min_nights' => $min,
            ],
            ['max_nights' => $max, 'priority' => $priority, 'quantity_note' => $qty ?? $defaultQty, 'notes' => $notes ?? $defaultNote]
        );
    }

    private function mountainRule(string $slug, TripType $trip, string $item, int $min, ?int $max, string $priority, string $notes): void
    {
        $mountain = Mountain::where('slug', $slug)->first();

        if ($mountain) {
            $this->rule($trip, $item, $min, $max, $priority, null, $mountain->id, $notes);
        }
    }
}
