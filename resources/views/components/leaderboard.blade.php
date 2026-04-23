@props(['scores' => collect()])

<section class="card">
    <h2 style="margin:0 0 10px;color:#0f172a;">Leaderboard</h2>
    <div style="overflow:auto;">
        <table class="table" style="width:100%;border-collapse:collapse;">
            <thead>
                <tr>
                    <th style="text-align:left;padding:8px;border-bottom:1px solid #e2e8f0;">#</th>
                    <th style="text-align:left;padding:8px;border-bottom:1px solid #e2e8f0;">Oyuncu</th>
                    <th style="text-align:left;padding:8px;border-bottom:1px solid #e2e8f0;">Level</th>
                    <th style="text-align:left;padding:8px;border-bottom:1px solid #e2e8f0;">Adim</th>
                    <th style="text-align:left;padding:8px;border-bottom:1px solid #e2e8f0;">Sure</th>
                    <th style="text-align:left;padding:8px;border-bottom:1px solid #e2e8f0;">Tarih</th>
                </tr>
            </thead>
            <tbody>
                @forelse($scores as $i => $score)
                    <tr>
                        <td style="padding:8px;border-bottom:1px solid #f1f5f9;">{{ $i + 1 }}</td>
                        <td style="padding:8px;border-bottom:1px solid #f1f5f9;">{{ $score->user?->name ?? 'Bilinmiyor' }}</td>
                        <td style="padding:8px;border-bottom:1px solid #f1f5f9;">{{ $score->level?->name ?? ('Level #' . $score->level_id) }}</td>
                        <td style="padding:8px;border-bottom:1px solid #f1f5f9;font-weight:700;">{{ $score->moves }}</td>
                        <td style="padding:8px;border-bottom:1px solid #f1f5f9;">{{ (int) ($score->duration_seconds ?? 0) }} sn</td>
                        <td style="padding:8px;border-bottom:1px solid #f1f5f9;">{{ optional($score->completed_at)->format('d.m.Y H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" style="padding:10px;color:#64748b;">Henuz skor yok.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
