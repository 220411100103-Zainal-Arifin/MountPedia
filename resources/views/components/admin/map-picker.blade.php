@props([
    'latName'  => 'latitude',         // name attribute untuk input latitude
    'lngName'  => 'longitude',         // name attribute untuk input longitude
    'latValue' => null,                // nilai awal latitude
    'lngValue' => null,                // nilai awal longitude
    'label'    => 'Lokasi di peta',    // judul section
    'height'   => '380px',            // tinggi peta
])

@php
    // ID unik per instance (aman bila ada >1 map-picker di halaman yang sama)
    $mapId  = 'map-' . md5($latName . $lngName . uniqid());
    $initLat = old($latName, $latValue);
    $initLng = old($lngName, $lngValue);
    $hasCoords = $initLat !== null && $initLat !== '' && $initLng !== null && $initLng !== '';
@endphp

{{-- Leaflet CSS & JS dimuat sekali saja walaupun komponen dipakai berkali-kali --}}
@once
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" defer></script>
<style>
.map-picker-wrap { display:flex; flex-direction:column; gap:.5rem; }
.map-picker-map  { width:100%; border-radius:.5rem; border:1px solid var(--border,#d1d5db);
                   overflow:hidden; cursor:crosshair; }
.map-picker-coords { display:flex; gap:.75rem; align-items:center; flex-wrap:wrap; }
.map-picker-coords input { flex:1; min-width:120px; }
.map-picker-info { font-size:.8rem; color:var(--text-muted,#6b7280);
                   padding:.35rem .6rem; background:var(--surface-2,#f3f4f6);
                   border-radius:.375rem; border:1px solid var(--border,#e5e7eb); }
.map-picker-clear { padding:.35rem .75rem; font-size:.8rem; cursor:pointer;
                    border:1px solid var(--border,#d1d5db); border-radius:.375rem;
                    background:var(--surface,#fff); color:var(--text,#374151);
                    white-space:nowrap; }
.map-picker-clear:hover { background:var(--surface-2,#f3f4f6); }
</style>
@endonce

<div class="f-field f-field--wide map-picker-wrap" data-map-picker="{{ $mapId }}">
    <span class="f-label">{{ $label }}</span>
    <p class="f-hint" style="margin-top:-.25rem">
        Klik pada peta untuk memilih lokasi. Marker bisa digeser setelah dipasang.
        Klik kanan marker untuk menghapus.
    </p>

    {{-- Peta --}}
    <div id="{{ $mapId }}" class="map-picker-map" style="height:{{ $height }}" role="application"
         aria-label="Peta pemilihan koordinat"></div>

    {{-- Input koordinat (tersembunyi secara visual tapi tetap bisa diisi manual) --}}
    <div class="map-picker-coords">
        <div style="flex:1;min-width:120px">
            <label class="f-label" for="{{ $mapId }}-lat" style="font-size:.8rem">Latitude</label>
            <input id="{{ $mapId }}-lat" type="number" name="{{ $latName }}"
                   value="{{ $initLat }}"
                   step="any" min="-90" max="90" inputmode="decimal"
                   placeholder="klik peta ↑"
                   class="f-input {{ $errors->has($latName) ? 'is-invalid' : '' }}"
                   data-map-lat="{{ $mapId }}">
            @error($latName)
                <p class="f-error" role="alert">{{ $message }}</p>
            @enderror
        </div>
        <div style="flex:1;min-width:120px">
            <label class="f-label" for="{{ $mapId }}-lng" style="font-size:.8rem">Longitude</label>
            <input id="{{ $mapId }}-lng" type="number" name="{{ $lngName }}"
                   value="{{ $initLng }}"
                   step="any" min="-180" max="180" inputmode="decimal"
                   placeholder="klik peta ↑"
                   class="f-input {{ $errors->has($lngName) ? 'is-invalid' : '' }}"
                   data-map-lng="{{ $mapId }}">
            @error($lngName)
                <p class="f-error" role="alert">{{ $message }}</p>
            @enderror
        </div>
        <button type="button" class="map-picker-clear" data-map-clear="{{ $mapId }}"
                title="Hapus koordinat">✕ Hapus titik</button>
    </div>

    <span class="map-picker-info" data-map-info="{{ $mapId }}">
        @if($hasCoords)
            📍 {{ number_format((float)$initLat, 6) }}, {{ number_format((float)$initLng, 6) }}
        @else
            Belum ada koordinat — klik pada peta untuk memilih lokasi.
        @endif
    </span>
</div>

<script>
(function () {
    function init() {
        var mapId   = @json($mapId);
        var latName = @json($latName);
        var lngName = @json($lngName);
        var initLat = {{ $hasCoords ? (float)$initLat : 'null' }};
        var initLng = {{ $hasCoords ? (float)$initLng : 'null' }};

        var el      = document.getElementById(mapId);
        var latInput = document.querySelector('[data-map-lat="' + mapId + '"]');
        var lngInput = document.querySelector('[data-map-lng="' + mapId + '"]');
        var infoEl   = document.querySelector('[data-map-info="' + mapId + '"]');
        var clearBtn = document.querySelector('[data-map-clear="' + mapId + '"]');

        if (!el || !window.L) return;

        // Pusat default = tengah Indonesia; bila ada koordinat awal, pakai itu
        var defaultCenter = [-2.5, 118];
        var defaultZoom   = 5;
        var markerZoom    = 13;

        var map = L.map(mapId, {
            center: (initLat !== null) ? [initLat, initLng] : defaultCenter,
            zoom:   (initLat !== null) ? markerZoom : defaultZoom,
            zoomControl: true,
        });

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
            maxZoom: 19,
        }).addTo(map);

        // Marker ikon default Leaflet
        var markerIcon = L.icon({
            iconUrl:    'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
            iconRetinaUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png',
            shadowUrl:  'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
            iconSize:   [25, 41],
            iconAnchor: [12, 41],
            popupAnchor:[1, -34],
            shadowSize: [41, 41],
        });

        var marker = null;

        function setCoords(lat, lng) {
            var latR = Math.round(lat * 1e6) / 1e6;
            var lngR = Math.round(lng * 1e6) / 1e6;
            latInput.value = latR;
            lngInput.value = lngR;
            infoEl.textContent = '📍 ' + latR + ', ' + lngR;
        }

        function clearCoords() {
            latInput.value = '';
            lngInput.value = '';
            infoEl.textContent = 'Belum ada koordinat — klik pada peta untuk memilih lokasi.';
            if (marker) { map.removeLayer(marker); marker = null; }
        }

        function placeMarker(lat, lng) {
            if (marker) {
                marker.setLatLng([lat, lng]);
            } else {
                marker = L.marker([lat, lng], { icon: markerIcon, draggable: true }).addTo(map);
                marker.on('dragend', function (e) {
                    var ll = e.target.getLatLng();
                    setCoords(ll.lat, ll.lng);
                });
                marker.on('contextmenu', function () { clearCoords(); });
            }
            setCoords(lat, lng);
        }

        if (initLat !== null) placeMarker(initLat, initLng);

        map.on('click', function (e) {
            placeMarker(e.latlng.lat, e.latlng.lng);
        });

        function onInputChange() {
            var lat = parseFloat(latInput.value);
            var lng = parseFloat(lngInput.value);
            if (!isNaN(lat) && !isNaN(lng) &&
                lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180) {
                placeMarker(lat, lng);
                map.setView([lat, lng], map.getZoom() < 10 ? markerZoom : map.getZoom());
            }
        }
        latInput.addEventListener('change', onInputChange);
        lngInput.addEventListener('change', onInputChange);

        clearBtn.addEventListener('click', clearCoords);
        setTimeout(function () { map.invalidateSize(); }, 200);
    }

    // Tunggu Leaflet selesai dimuat (defer), lalu inisialisasi
    function waitForLeaflet() {
        if (window.L) {
            init();
        } else {
            var t = setInterval(function () {
                if (window.L) { clearInterval(t); init(); }
            }, 50);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', waitForLeaflet);
    } else {
        waitForLeaflet();
    }
})();
</script>
