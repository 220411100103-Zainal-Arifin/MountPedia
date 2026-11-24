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

        <div class="f-field">
            <span class="f-label">Status jalur</span>
            <label class="f-switch">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $trail->is_active ?? true))>
                <span class="f-switch__track" aria-hidden="true"></span>
                <span>Aktif (tampil di website)</span>
            </label>
        </div>
    </div>
</section>

<section class="panel form-panel">
    <header class="panel__head"><h2>Rute &amp; waktu tempuh</h2><span>Opsional, ditampilkan sebagai ringkasan jalur</span></header>
    <div class="form-grid">
        <x-admin.field label="Nama basecamp" name="basecamp_name" :value="$trail->basecamp_name" maxlength="150" span />
        <x-admin.field label="Lintang basecamp" name="basecamp_latitude" type="number" :value="$trail->basecamp_latitude"
                       step="any" min="-90" max="90" inputmode="decimal" />
        <x-admin.field label="Bujur basecamp" name="basecamp_longitude" type="number" :value="$trail->basecamp_longitude"
                       step="any" min="-180" max="180" inputmode="decimal" />
        <x-admin.field label="Ketinggian awal (mdpl)" name="start_elevation_mdpl" type="number" :value="$trail->start_elevation_mdpl"
                       min="0" max="9000" step="1" inputmode="numeric" />
        <x-admin.field label="Jarak (km)" name="distance_km" type="number" :value="$trail->distance_km"
                       min="0" max="999.99" step="0.01" inputmode="decimal" />
        <x-admin.field label="Durasi naik (jam)" name="duration_up_hours" type="number" :value="$trail->duration_up_hours"
                       min="0" max="99.9" step="0.1" inputmode="decimal" />
        <x-admin.field label="Durasi turun (jam)" name="duration_down_hours" type="number" :value="$trail->duration_down_hours"
                       min="0" max="99.9" step="0.1" inputmode="decimal" />
    </div>
</section>

<section class="panel form-panel">
    <header class="panel__head"><h2>Deskripsi</h2><span>Catatan tentang medan dan kondisi jalur</span></header>
    <div class="form-grid">
        <x-admin.textarea label="Deskripsi jalur" name="description" :value="$trail->description" maxlength="5000" />
    </div>
</section>
