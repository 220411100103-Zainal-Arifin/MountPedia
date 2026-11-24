@props(['view' => null, 'edit' => null, 'delete' => null, 'name' => null, 'note' => null])

<div class="row-actions">
    @if ($view)
        <a class="row-btn" href="{{ $view }}" target="_blank" rel="noopener" title="Lihat di website" aria-label="Lihat di website">
            <x-admin.icon name="eye" />
        </a>
    @endif

    @if ($edit)
        <a class="row-btn" href="{{ $edit }}" title="Edit" aria-label="Edit {{ $name }}">
            <x-admin.icon name="pencil" />
        </a>
    @else
        <span class="row-btn is-disabled" title="Edit tersedia di tahap berikutnya" aria-hidden="true"><x-admin.icon name="pencil" /></span>
    @endif

    @if ($delete)
        <button class="row-btn row-btn--danger" type="button" title="Hapus" aria-label="Hapus {{ $name }}"
                data-delete data-action="{{ $delete }}" data-name="{{ $name }}" data-note="{{ $note }}">
            <x-admin.icon name="trash-2" />
        </button>
    @else
        <span class="row-btn is-disabled" title="Hapus tersedia di tahap berikutnya" aria-hidden="true"><x-admin.icon name="trash-2" /></span>
    @endif
</div>