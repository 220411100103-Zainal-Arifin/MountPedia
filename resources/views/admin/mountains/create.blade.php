<x-layouts.admin title="Tambah Gunung" section="Master Data">
    <x-admin.page-head text="Isi data gunung baru. Jalur pendakiannya bisa ditambahkan setelah gunung tersimpan.">
        <a class="btn btn--ghost" href="{{ route('admin.mountains.index') }}">Kembali ke daftar</a>
    </x-admin.page-head>

    <form method="POST" action="{{ route('admin.mountains.store') }}" novalidate>
        @csrf
        @include('admin.mountains._form')

        <div class="form-actions">
            <a class="btn btn--ghost" href="{{ route('admin.mountains.index') }}">Batal</a>
            <button class="btn btn--primary" type="submit">Simpan gunung</button>
        </div>
    </form>
</x-layouts.admin>
