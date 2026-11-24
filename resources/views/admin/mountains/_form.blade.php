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
    <header class="panel__head"><h2>Lokasi</h2><span>Opsional, dipakai untuk peta</span></header>
    <div class="form-grid">
        <x-admin.field label="Lintang (latitude)" name="latitude" type="number" :value="$mountain->latitude"
                       step="any" min="-90" max="90" placeholder="-8.108000" inputmode="decimal" />
        <x-admin.field label="Bujur (longitude)" name="longitude" type="number" :value="$mountain->longitude"
                       step="any" min="-180" max="180" placeholder="112.922000" inputmode="decimal" />
    </div>
</section>

<section class="panel form-panel">
    <header class="panel__head"><h2>Deskripsi &amp; gambar</h2><span>Teks pengantar dan gambar sampul</span></header>
    <div class="form-grid">
        <x-admin.field label="URL gambar" name="image_url" type="url" :value="$mountain->image_url" span maxlength="255"
                       placeholder="https://..." hint="Kosongkan untuk memakai ilustrasi bawaan." />
        <x-admin.textarea label="Deskripsi" name="description" :value="$mountain->description" maxlength="5000" />
    </div>
</section>
