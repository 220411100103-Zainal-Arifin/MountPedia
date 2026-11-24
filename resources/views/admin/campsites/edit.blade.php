<x-layouts.admin title="Edit Campsite" section="Master Data">
    <x-admin.page-head :text="'Ubah data '.$campsite->name.'.'">
        <a class="btn btn--ghost" href="{{ route('admin.mountains.edit', $campsite->mountain_id) }}">
            Kembali ke {{ $campsite->mountain->name ?? 'Gunung' }}
        </a>
    </x-admin.page-head>

    <form method="POST" action="{{ route('admin.campsites.update', $campsite->id) }}" novalidate>
        @csrf
        @method('PUT')
        @include('admin.campsites._form')

        <div class="form-actions">
            <button class="btn btn--danger-ghost" type="button" data-delete
                    data-action="{{ route('admin.campsites.destroy', $campsite->id) }}"
                    data-name="{{ $campsite->name }}"
                    data-note="Area kemah ini akan dihapus permanen. Tindakan ini tidak bisa dibatalkan.">
                <x-admin.icon name="trash-2" /> Hapus campsite
            </button>
            <span class="form-actions__right">
                <a class="btn btn--ghost" href="{{ route('admin.mountains.edit', $campsite->mountain_id) }}">Batal</a>
                <button class="btn btn--primary" type="submit">Simpan perubahan</button>
            </span>
        </div>
    </form>
</x-layouts.admin>
