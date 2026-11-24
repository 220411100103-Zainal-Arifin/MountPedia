<x-layouts.admin title="Edit Jalur" section="Master Data">
    <x-admin.page-head :text="'Ubah data '.$trail->name.'.'">
        <a class="btn btn--ghost" href="{{ route('admin.mountains.index', ['tab' => 'jalur']) }}">Kembali ke daftar</a>
    </x-admin.page-head>

    <form method="POST" action="{{ route('admin.trails.update', $trail->id) }}" novalidate>
        @csrf
        @method('PUT')
        @include('admin.trails._form')

        <div class="form-actions">
            <button class="btn btn--danger-ghost" type="button" data-delete
                    data-action="{{ route('admin.trails.destroy', $trail->id) }}" data-name="{{ $trail->name }}"
                    data-note="Status pendakian jalur ini ikut terhapus. Camp dan biaya simaksi yang terkait tetap ada, hanya tidak lagi terhubung ke jalur ini. Tindakan ini tidak bisa dibatalkan.">
                <x-admin.icon name="trash-2" /> Hapus jalur
            </button>
            <span class="form-actions__right">
                <a class="btn btn--ghost" href="{{ route('admin.mountains.index', ['tab' => 'jalur']) }}">Batal</a>
                <button class="btn btn--primary" type="submit">Simpan perubahan</button>
            </span>
        </div>
    </form>
</x-layouts.admin>
