<?php

namespace Database\Seeders;

use App\Models\Mountain;
use App\Models\Trail;
use App\Models\TrailStatusLog;
use Illuminate\Database\Seeder;

class TrailStatusSeeder extends Seeder
{
    public function run(): void
    {
        TrailStatusLog::query()->delete();

        
        foreach (Mountain::all() as $mountain) {
            TrailStatusLog::create([
                'mountain_id' => $mountain->id,
                'trail_id' => null,
                'status' => 'buka',
                'reason' => 'Jalur dibuka normal (data contoh)',
                'starts_on' => now()->subDays(30)->toDateString(),
                'ends_on' => null,
                'source_name' => 'Data contoh (belum diverifikasi)',
                'source_url' => 'https://example.com/pengumuman-pengelola',
                'reported_at' => now(),
            ]);
        }

        
        if ($rinjani = Mountain::where('slug', 'rinjani')->first()) {
            TrailStatusLog::create([
                'mountain_id' => $rinjani->id,
                'status' => 'tutup',
                'reason' => 'Contoh riwayat penutupan (data contoh)',
                'starts_on' => now()->subDays(90)->toDateString(),
                'ends_on' => now()->subDays(40)->toDateString(),
                'source_name' => 'Data contoh',
                'source_url' => 'https://example.com/pengumuman-pengelola',
                'reported_at' => now()->subDays(95),
            ]);
        }

        
        $lawu = Mountain::where('slug', 'lawu')->first();
        $trail = $lawu ? Trail::where('mountain_id', $lawu->id)->where('slug', 'cemoro-kandang')->first() : null;

        if ($lawu && $trail) {
            TrailStatusLog::create([
                'mountain_id' => $lawu->id,
                'trail_id' => $trail->id,
                'status' => 'dibatasi',
                'reason' => 'Contoh pengujian: status jalur menimpa status gunung (data contoh)',
                'starts_on' => now()->subDays(2)->toDateString(),
                'ends_on' => now()->addDays(14)->toDateString(),
                'source_name' => 'Data contoh',
                'source_url' => 'https://example.com/pengumuman-pengelola',
                'reported_at' => now()->subDay(),
            ]);
        }
    }
}
