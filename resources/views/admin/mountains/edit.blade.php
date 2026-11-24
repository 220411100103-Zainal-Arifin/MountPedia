<x-layouts.admin title="Edit Gunung" section="Master Data">
    <x-admin.page-head :text="'Ubah data '.$mountain->name.'.'">
        <a class="btn btn--ghost" href="{{ route('mountains.show', $mountain->slug) }}" target="_blank" rel="noopener">
            <x-admin.icon name="external-link" /> Lihat di website
        </a>
    </x-admin.page-head>

    <form method="POST" action="{{ route('admin.mountains.update', $mountain->id) }}" novalidate>
        @csrf
        @method('PUT')
        @include('admin.mountains._form')

        <div class="form-actions">
            <button class="btn btn--danger-ghost" type="button" data-delete
                    data-action="{{ route('admin.mountains.destroy', $mountain->id) }}" data-name="{{ $mountain->name }}"
                    data-note="Semua jalur, camp, foto, status pendakian, biaya simaksi, dan rekomendasi gear milik gunung ini ikut terhapus. Tindakan ini tidak bisa dibatalkan.">
                <x-admin.icon name="trash-2" /> Hapus gunung
            </button>
            <span class="form-actions__right">
                <a class="btn btn--ghost" href="{{ route('admin.mountains.index') }}">Batal</a>
                <button class="btn btn--primary" type="submit">Simpan perubahan</button>
            </span>
        </div>
    </form>
</x-layouts.admin>
