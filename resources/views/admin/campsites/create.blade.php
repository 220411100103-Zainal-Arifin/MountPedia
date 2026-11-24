<x-layouts.admin title="Tambah Campsite" section="Master Data">
    <x-admin.page-head text="Tambahkan area kemah baru untuk sebuah gunung.">
        <a class="btn btn--ghost" href="{{ $backUrl }}">Kembali</a>
    </x-admin.page-head>

    <form method="POST" action="{{ route('admin.campsites.store') }}" novalidate>
        @csrf
        @include('admin.campsites._form')

        <div class="form-actions">
            <a class="btn btn--ghost" href="{{ $backUrl }}">Batal</a>
            <button class="btn btn--primary" type="submit">Simpan campsite</button>
        </div>
    </form>
</x-layouts.admin>
