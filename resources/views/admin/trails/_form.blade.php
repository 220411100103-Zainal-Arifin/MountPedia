@if ($errors->any())
    <div class="flash flash--error" role="alert">Data belum bisa disimpan. Periksa kembali isian yang ditandai merah.</div>
@endif

<section class="panel form-panel">
    <header class="panel__head"><h2>Informasi jalur</h2><span>Gunung dan identitas jalur</span></header>
    <div class="form-grid">
        <x-admin.select label="Gunung" name="mountain_id" required span>
            <option value="">Pilih gunung</option>
            @foreach ($mountains as $m)
                <option value="{{ $m->id }}" @selected((string) old('mountain_id', $trail->mountain_id) === (string) $m->id)>{{ $m->name }}</option>
            @endforeach
        </x-admin.select>

        <x-admin.field label="Nama jalur" name="name" :value="$trail->name" required maxlength="120"
                       placeholder="Contoh: Jalur Ranu Pani" data-slug-source />
        <x-admin.field label="Slug (alamat jalur)" name="slug" :value="$trail->slug" maxlength="140"
                       placeholder="otomatis dari nama" data-slug-target hint="Kosongkan agar dibuat otomatis." />

        <x-admin.select label="Tingkat kesulitan" name="difficulty">
            <option value="">Belum ditentukan</option>
            @foreach (['mudah', 'sedang', 'sulit', 'ekstrem'] as $d)
                <option value="{{ $d }}" @selected(old('difficulty', $trail->difficulty) === $d)>{{ ucfirst($d) }}</option>
            @endforeach
        </x-admin.select>

        <x-admin.select label="Status jalur" name="status" required>
            <option value="buka" @selected(old('status', $trail->status ?? 'buka') === 'buka')>Dibuka</option>
            <option value="tutup" @selected(old('status', $trail->status ?? 'buka') === 'tutup')>Ditutup</option>
        </x-admin.select>

        <x-admin.field label="Alasan status (opsional)" name="status_reason" :value="old('status_reason', $trail->status_reason)"
                       maxlength="255" span
                       placeholder="Contoh: Sedang dalam perbaikan jalur" hint="Diisi bila jalur ditutup atau ada kondisi khusus." />
    </div>
</section>

<section class="panel form-panel">
    <header class="panel__head"><h2>Rute &amp; waktu tempuh</h2><span>Opsional, ditampilkan sebagai ringkasan jalur</span></header>
    <div class="form-grid">
        <x-admin.field label="Nama basecamp" name="basecamp_name" :value="$trail->basecamp_name" maxlength="150" span />
        <x-admin.field label="Ketinggian awal (mdpl)" name="start_elevation_mdpl" type="number" :value="$trail->start_elevation_mdpl"
                       min="0" max="9000" step="1" inputmode="numeric" />
        <x-admin.field label="Jarak (km)" name="distance_km" type="number" :value="$trail->distance_km"
                       min="0" max="999.99" step="0.01" inputmode="decimal" />
        <x-admin.field label="Durasi naik (jam)" name="duration_up_hours" type="number" :value="$trail->duration_up_hours"
                       min="0" max="99.9" step="0.1" inputmode="decimal" />
        <x-admin.field label="Durasi turun (jam)" name="duration_down_hours" type="number" :value="$trail->duration_down_hours"
                       min="0" max="99.9" step="0.1" inputmode="decimal" />

        <x-admin.map-picker
            lat-name="basecamp_latitude"  :lat-value="$trail->basecamp_latitude"
            lng-name="basecamp_longitude" :lng-value="$trail->basecamp_longitude"
            label="Lokasi basecamp di peta" />
    </div>
</section>

<section class="panel form-panel">
    <header class="panel__head"><h2>Deskripsi</h2><span>Catatan tentang medan dan kondisi jalur</span></header>
    <div class="form-grid">
        <x-admin.textarea label="Deskripsi jalur" name="description" :value="$trail->description" maxlength="5000" />
    </div>
</section>
