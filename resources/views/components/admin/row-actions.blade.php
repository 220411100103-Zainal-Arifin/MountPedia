@props(['view' => null])

{{-- Edit dan hapus dikerjakan di tahap berikutnya; sementara hanya tampil nonaktif --}}
<div class="row-actions">
    @if ($view)
        <a class="row-btn" href="{{ $view }}" target="_blank" rel="noopener" title="Lihat di website" aria-label="Lihat di website">
            <x-admin.icon name="eye" />
        </a>
    @endif
    <span class="row-btn is-disabled" title="Edit tersedia di tahap berikutnya" aria-hidden="true"><x-admin.icon name="pencil" /></span>
    <span class="row-btn is-disabled" title="Hapus tersedia di tahap berikutnya" aria-hidden="true"><x-admin.icon name="trash-2" /></span>
</div>
