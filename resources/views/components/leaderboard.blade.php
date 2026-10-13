@props(['scores' => collect()])

@once
    <link rel="stylesheet" href="{{ asset('css/flamestone.css') }}?v={{ @filemtime(public_path('css/flamestone.css')) }}">
@endonce

<section class="fs fs-card">
    <h2 style="margin:0 0 12px;font-size:21px;font-weight:900;">🏆 Skor Tablosu</h2>
    <div class="fs-table-wrap">
        <table class="fs-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Oyuncu</th>
                    <th>Level</th>
                    <th>Adım</th>
                    <th>Süre</th>
                    <th>Tarih</th>
                </tr>
            </thead>
            <tbody>
                @forelse($scores as $i => $score)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $score->user?->name ?? 'Bilinmiyor' }}</td>
                        <td>{{ $score->level?->name ?? ('Level #' . $score->level_id) }}</td>
                        <td>{{ $score->moves }}</td>
                        <td>{{ (int) ($score->duration_seconds ?? 0) }} sn</td>
                        <td>{{ optional($score->completed_at)->format('d.m.Y H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="fs-empty">Henüz skor yok.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
