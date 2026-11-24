@if ($errors->any())
    <div class="flash flash--error" role="alert">Data belum bisa disimpan. Periksa kembali isian yang ditandai merah.</div>
@endif

<section class="panel form-panel">
    <header class="panel__head"><h2>Identitas Campsite</h2><span>Gunung, jalur, dan nama area kemah</span></header>
    <div class="form-grid">
        <x-admin.select label="Gunung" name="mountain_id" required span
                        hint="Pilih gunung tempat campsite ini berada."
                        x-data x-on:change="$dispatch('mountain-changed', {id: $event.target.value})">
            <option value="">Pilih gunung</option>
            @foreach ($mountains as $m)
                <option value="{{ $m->id }}" @selected((string) old('mountain_id', $campsite->mountain_id) === (string) $m->id)>{{ $m->name }}</option>
            @endforeach
        </x-admin.select>

        <x-admin.select label="Jalur (opsional)" name="trail_id"
                        hint="Kosongkan bila campsite bisa diakses dari semua jalur.">
            <option value="">— Semua jalur / tidak spesifik —</option>
            @foreach ($trails as $t)
                <option value="{{ $t->id }}" @selected((string) old('trail_id', $campsite->trail_id) === (string) $t->id)>{{ $t->name }}</option>
            @endforeach
        </x-admin.select>

        <x-admin.field label="Nama campsite" name="name" :value="$campsite->name"
                       required maxlength="120" placeholder="Contoh: Ranu Kumbolo" />
    </div>
</section>

<section class="panel form-panel">
    <header class="panel__head"><h2>Data Fisik</h2><span>Ketinggian, kapasitas, dan sumber air</span></header>
    <div class="form-grid">
        <x-admin.field label="Ketinggian (mdpl)" name="elevation_mdpl" type="number"
                       :value="$campsite->elevation_mdpl"
                       min="0" max="9000" step="1" inputmode="numeric"
                       placeholder="Contoh: 2400" />

        <x-admin.field label="Kapasitas tenda" name="capacity_tents" type="number"
                       :value="$campsite->capacity_tents"
                       min="1" max="9999" step="1" inputmode="numeric"
                       hint="Perkiraan jumlah tenda yang bisa dipasang." />

        <div class="f-field f-field--wide">
            <span class="f-label">Sumber air</span>
            <label class="f-switch">
                <input type="hidden" name="has_water_source" value="0">
                <input type="checkbox" name="has_water_source" value="1"
                       @checked(old('has_water_source', $campsite->has_water_source ?? false))>
                <span class="f-switch__track" aria-hidden="true"></span>
                <span>Tersedia sumber air di area ini</span>
            </label>
        </div>

        <x-admin.field label="Catatan sumber air" name="water_note"
                       :value="$campsite->water_note" maxlength="255" span
                       placeholder="Contoh: Mata air 200 m ke arah barat, mengalir sepanjang tahun"
                       hint="Diisi bila ada sumber air. Kosongkan bila tidak ada." />
    </div>
</section>

<section class="panel form-panel">
    <header class="panel__head"><h2>Lokasi</h2><span>Koordinat GPS untuk peta (opsional)</span></header>
    <div class="form-grid">
        <x-admin.map-picker
            lat-name="latitude"  :lat-value="$campsite->latitude"
            lng-name="longitude" :lng-value="$campsite->longitude"
            label="Koordinat area kemah di peta" />
    </div>
</section>

<section class="panel form-panel">
    <header class="panel__head"><h2>Catatan</h2><span>Informasi tambahan tentang kondisi campsite</span></header>
    <div class="form-grid">
        <x-admin.textarea label="Catatan" name="notes" :value="$campsite->notes" maxlength="2000"
                          hint="Contoh: Area datar, cocok untuk kelompok besar. Hindari mendirikan tenda di dekat tepi danau." />
    </div>
</section>
