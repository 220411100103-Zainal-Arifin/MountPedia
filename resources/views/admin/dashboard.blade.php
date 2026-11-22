<x-layouts.admin title="Dashboard">
    
    <section class="welcome">
        <div>
            <h2 class="welcome__title">Selamat Datang, Administrator!</h2>
            <p class="welcome__text">
                Kelola dan pantau seluruh informasi pendakian, perlengkapan outdoor, dan tempat penyewaan
                MountPedia dari satu tempat.
            </p>
        </div>
        <div class="welcome__actions">
            <a class="btn btn--ghost" href="{{ url('/') }}" target="_blank" rel="noopener">
                <x-admin.icon name="external-link" /> View Website
            </a>
            <a class="btn btn--primary" href="#">
                <x-admin.icon name="plus" /> Tambah Data
            </a>
        </div>
    </section>

    
    <section class="stats" aria-label="Ringkasan data">
        @foreach ($stats as $s)
            <article class="stat">
                <div class="stat__top">
                    <span class="stat__icon"><x-admin.icon :name="$s['icon']" /></span>
                    @if (! is_null($s['trend']))
                        <span class="trend @if ($s['trend'] > 0) is-up @endif">
                            @if ($s['trend'] > 0)<x-admin.icon name="trending-up" />@endif
                            {{ $s['trend'] > 0 ? '+' : '' }}{{ number_format($s['trend'], 0, ',', '.') }} bulan ini
                        </span>
                    @endif
                </div>
                <p class="stat__label">{{ $s['label'] }}</p>
                <p class="stat__value">{{ is_null($s['value']) ? '—' : number_format($s['value'], 0, ',', '.') }}</p>
                @if ($s['meta'])
                    <p class="stat__meta">{{ $s['meta'] }}</p>
                @endif
            </article>
        @endforeach
    </section>

    
    <section class="panel" aria-label="Gunung terbaru">
        <header class="panel__head">
            <h2>Gunung terbaru</h2>
            <span>5 data terakhir yang ditambahkan</span>
        </header>

        @if ($recentMountains->isEmpty())
            <p class="panel__empty">Belum ada data gunung.</p>
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Gunung</th>
                            <th>Wilayah</th>
                            <th class="num">Ketinggian</th>
                            <th>Ditambahkan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recentMountains as $m)
                            <tr>
                                <td class="strong">{{ $m->name }}</td>
                                <td>
                                    @if ($m->region)
                                        {{ Str::replaceFirst('Kabupaten ', '', $m->region->regency) }}, {{ $m->region->province }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="num">{{ number_format($m->elevation_mdpl, 0, ',', '.') }} mdpl</td>
                                <td class="muted">{{ $m->created_at?->diffForHumans() ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
</x-layouts.admin>
