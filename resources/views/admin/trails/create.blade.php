<x-layouts.admin title="Tambah Jalur" section="Master Data">
    <x-admin.page-head text="Isi data jalur pendakian baru untuk sebuah gunung.">
        <a class="btn btn--ghost" href="{{ route('admin.mountains.index', ['tab' => 'jalur']) }}">Kembali ke daftar</a>
    </x-admin.page-head>

    <form method="POST" action="{{ route('admin.trails.store') }}" novalidate>
        @csrf
        @include('admin.trails._form')

        <div class="form-actions">
            <a class="btn btn--ghost" href="{{ route('admin.mountains.index', ['tab' => 'jalur']) }}">Batal</a>
            <button class="btn btn--primary" type="submit">Simpan jalur</button>
        </div>
    </form>
</x-layouts.admin>
