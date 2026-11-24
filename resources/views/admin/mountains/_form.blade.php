@if ($errors->any())
    <div class="flash flash--error" role="alert">Data belum bisa disimpan. Periksa kembali isian yang ditandai merah.</div>
@endif

<section class="panel form-panel">
    <header class="panel__head"><h2>Informasi dasar</h2><span>Identitas gunung yang tampil di website</span></header>
    <div class="form-grid">
        <x-admin.field label="Nama gunung" name="name" :value="$mountain->name" required maxlength="120"
                       placeholder="Contoh: Gunung Semeru" data-slug-source />
        <x-admin.field label="Slug (alamat halaman)" name="slug" :value="$mountain->slug" maxlength="140"
                       placeholder="otomatis dari nama" data-slug-target
                       hint="Kosongkan agar dibuat otomatis. Mengubah slug akan mengubah alamat halaman publiknya." />

        <x-admin.select label="Wilayah (kabupaten/kota)" name="region_id" required span
                        hint="Wilayah dikelola di data referensi; hubungi pengembang bila kabupaten yang dibutuhkan belum ada.">
            <option value="">Pilih wilayah</option>
            @foreach ($regions as $province => $list)
                <optgroup label="{{ $province }}">
                    @foreach ($list as $r)
                        <option value="{{ $r->id }}" @selected((string) old('region_id', $mountain->region_id) === (string) $r->id)>{{ $r->regency }}</option>
                    @endforeach
                </optgroup>
            @endforeach
        </x-admin.select>

        <x-admin.field label="Ketinggian (mdpl)" name="elevation_mdpl" type="number" :value="$mountain->elevation_mdpl"
                       required min="0" max="9000" step="1" inputmode="numeric" />
        <x-admin.field label="Pengelola" name="managing_authority" :value="$mountain->managing_authority" maxlength="150"
                       placeholder="Contoh: Balai Besar TN Bromo Tengger Semeru" />
    </div>
</section>

<section class="panel form-panel">
    <header class="panel__head"><h2>Lokasi</h2><span>Koordinat puncak gunung, dipakai untuk peta</span></header>
    <div class="form-grid">
        <x-admin.map-picker
            lat-name="latitude"  :lat-value="$mountain->latitude"
            lng-name="longitude" :lng-value="$mountain->longitude"
            label="Koordinat puncak gunung" />
    </div>
</section>

@php
    use Illuminate\Support\Facades\Storage;
    $existingImg = $mountain->image_url ?? '';
    $isPlaceholder = str_contains($existingImg, 'placehold.co');
    $hasRealImg = $existingImg !== '' && !$isPlaceholder;
    $isExternalImg = $hasRealImg && str_starts_with($existingImg, 'http');
    $imgSrc = $hasRealImg ? ($isExternalImg ? $existingImg : Storage::url($existingImg)) : '';
@endphp

<section class="panel form-panel">
    <header class="panel__head"><h2>Deskripsi &amp; gambar</h2><span>Teks pengantar dan gambar sampul</span></header>
    <div class="form-grid">

        {{-- Pratinjau gambar saat ini (mode edit) --}}
        @if ($hasRealImg)
            <div class="f-field f-field--wide">
                <span class="f-label">Gambar saat ini</span>
                <img src="{{ $imgSrc }}" alt="Gambar {{ $mountain->name }}"
                     style="max-height:180px;border-radius:.5rem;border:1px solid var(--border,#d1d5db);object-fit:cover;display:block;">
            </div>
        @endif

        {{-- Upload file --}}
        <div class="f-field f-field--wide">
            <label class="f-label" for="f-image">Upload gambar baru</label>
            <input id="f-image" type="file" name="image" accept="image/jpeg,image/png,image/webp"
                   class="f-input {{ $errors->has('image') ? 'is-invalid' : '' }}">
            <p class="f-hint">Format: JPG, PNG, WebP. Maks 2 MB. Jika upload gambar, URL di bawah diabaikan.</p>
            @error('image') <p class="f-error" role="alert">{{ $message }}</p> @enderror
        </div>

        {{-- URL eksternal (fallback) --}}
        <x-admin.field label="Atau gunakan URL gambar eksternal" name="image_url" type="url"
                       :value="$isExternalImg ? $existingImg : ''"
                       span maxlength="255" placeholder="https://..."
                       hint="Hanya diisi bila tidak meng-upload file. Kosongkan keduanya untuk memakai ilustrasi bawaan." />

        <x-admin.textarea label="Deskripsi" name="description" :value="$mountain->description" maxlength="5000" />
    </div>
</section>

<section class="panel form-panel">
    <header class="panel__head"><h2>Status Pendakian</h2><span>Menentukan apakah gunung ini terbuka untuk umum</span></header>
    <div class="form-grid">
        <x-admin.select label="Status pendakian" name="status" required>
            <option value="buka" @selected(old('status', $mountain->status ?? 'buka') === 'buka')>🟢 Dibuka</option>
            <option value="tutup" @selected(old('status', $mountain->status ?? 'buka') === 'tutup')>🔴 Ditutup</option>
        </x-admin.select>

        <x-admin.field label="Alasan penutupan / catatan status" name="status_reason"
                       :value="$mountain->status_reason" maxlength="255"
                       placeholder="Contoh: Ditutup sementara karena peningkatan aktivitas vulkanik"
                       hint="Diisi bila gunung ditutup. Muncul di halaman publik." />

        <x-admin.field label="URL sumber status" name="status_source_url" type="url"
                       :value="$mountain->status_source_url" maxlength="255" span
                       placeholder="https://..." hint="Tautan ke pengumuman resmi (TNBTS, BKSDA, dsb.)" />
    </div>
</section>
