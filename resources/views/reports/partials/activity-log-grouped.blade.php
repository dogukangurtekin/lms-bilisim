@php
    $order = ['Ders', 'Ders Ödevi', 'Ödev', 'Oyun Ödevi', 'Uygulama Ödevi', 'Oyun / Uygulama', 'Etkinlik', 'Günlük Egzersiz', 'Canlı Quiz', 'Canlı Yarışma', 'Klavye Yarışı'];
    $groups = collect($log)->groupBy('kind');
    $sortedKinds = $groups->keys()->sortBy(function ($kind) use ($order) {
        $i = array_search($kind, $order, true);
        return $i === false ? 999 : $i;
    })->values();
    $tableClass = $tableClass ?? '';
    // $only: 'lessons' => yalnızca ders türleri, 'others' => ders dışındaki her şey, null => hepsi.
    $only = $only ?? null;
    $compact = $only !== null;
    $lessonKinds = ['Ders', 'Ders Ödevi'];
    if ($only === 'lessons') {
        $sortedKinds = $sortedKinds->filter(fn ($k) => in_array($k, $lessonKinds, true))->values();
    } elseif ($only === 'others') {
        $sortedKinds = $sortedKinds->reject(fn ($k) => in_array($k, $lessonKinds, true))->values();
    }
    // Kompakt (iki sütunlu) düzen: İçerik | XP | Tarih | D/Y | Durum
    $widths = $compact ? [43, 8, 28, 10, 11] : [58, 14, 11, 11, 6];
    $doneStatuses = ['Tamamlandı', 'Katıldı'];
@endphp
@forelse($sortedKinds as $kind)
    @php $rows = $groups->get($kind); @endphp
    <h4 style="margin:14px 0 6px">{{ $kind }} <small style="opacity:.65;font-weight:400">({{ $rows->count() }} kayıt · {{ (int) $rows->sum('xp') }} XP)</small></h4>
    <table class="{{ $tableClass }}{{ $tableClass !== '' ? ' activity-log-table' : '' }}{{ $compact ? ' activity-log-table--compact' : '' }}">
        @if($tableClass !== '')
        <colgroup>
            @foreach($widths as $w)<col style="width:{{ $w }}%">@endforeach
        </colgroup>
        @endif
        <thead>
            <tr>
                @if($compact)
                    <th>İçerik</th>
                    <th class="col-xp">XP</th>
                    <th>Tarih</th>
                    <th class="col-dy" title="Doğru / Yanlış">D/Y</th>
                    <th class="col-status">Durum</th>
                @else
                    <th>İçerik</th>
                    <th>Tarih</th>
                    <th>Durum</th>
                    <th>Sonuç</th>
                    <th>XP</th>
                @endif
            </tr>
        </thead>
        <tbody>
        @foreach($rows as $item)
            @php
                $dateText = $item['sort_date'] ? \Carbon\Carbon::parse($item['sort_date'])->format('d.m.Y H:i') : '-';
                // "5/0 (5 soru)" -> "5/0": toplam soru sayısı yazısı kompakt tabloda gösterilmez.
                $resultText = $compact ? trim((string) preg_replace('/\s*\(\d+\s*soru\)/u', '', (string) $item['result'])) : $item['result'];
            @endphp
            <tr>
                <td class="activity-title" title="{{ $item['title'] }}"><div class="clamp">{{ $item['title'] }}</div></td>
                @if($compact)
                    <td class="col-xp">{{ (int) $item['xp'] }}</td>
                    <td class="col-date">{{ $dateText }}</td>
                    <td class="col-dy">{{ $resultText }}</td>
                    <td class="col-status">
                        @if(in_array($item['status'], $doneStatuses, true))
                            <svg class="status-icon" viewBox="0 0 20 20" width="17" height="17" role="img" aria-label="{{ $item['status'] }}"><title>{{ $item['status'] }}</title><circle cx="10" cy="10" r="9" fill="#dcfce7" stroke="#4ade80" stroke-width="1.2"/><path d="M5.8 10.4l2.8 2.8 5.6-6" fill="none" stroke="#15803d" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        @elseif($item['status'] === 'Devam Ediyor')
                            <svg class="status-icon" viewBox="0 0 20 20" width="17" height="17" role="img" aria-label="{{ $item['status'] }}"><title>{{ $item['status'] }}</title><circle cx="10" cy="10" r="9" fill="#fef3c7" stroke="#fbbf24" stroke-width="1.2"/><path d="M10 5.6V10l3 1.8" fill="none" stroke="#b45309" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        @else
                            <svg class="status-icon" viewBox="0 0 20 20" width="17" height="17" role="img" aria-label="{{ $item['status'] }}"><title>{{ $item['status'] }}</title><circle cx="10" cy="10" r="9" fill="#f1f5f9" stroke="#cbd5e1" stroke-width="1.2"/><circle cx="10" cy="10" r="2.2" fill="#94a3b8"/></svg>
                        @endif
                    </td>
                @else
                    <td>{{ $dateText }}</td>
                    <td>{{ $item['status'] }}</td>
                    <td>{{ $item['result'] }}</td>
                    <td>{{ (int) $item['xp'] }}</td>
                @endif
            </tr>
        @endforeach
        </tbody>
    </table>
@empty
    <p>Kayıt yok.</p>
@endforelse
