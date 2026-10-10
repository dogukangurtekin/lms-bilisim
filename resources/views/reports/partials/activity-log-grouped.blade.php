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
    $lessonKinds = ['Ders', 'Ders Ödevi'];
    if ($only === 'lessons') {
        $sortedKinds = $sortedKinds->filter(fn ($k) => in_array($k, $lessonKinds, true))->values();
    } elseif ($only === 'others') {
        $sortedKinds = $sortedKinds->reject(fn ($k) => in_array($k, $lessonKinds, true))->values();
    }
    $widths = $only ? [37, 17, 19, 18, 9] : [58, 14, 11, 11, 6];
@endphp
@forelse($sortedKinds as $kind)
    @php $rows = $groups->get($kind); @endphp
    <h4 style="margin:14px 0 6px">{{ $kind }} <small style="opacity:.65;font-weight:400">({{ $rows->count() }} kayıt · {{ (int) $rows->sum('xp') }} XP)</small></h4>
    <table class="{{ $tableClass }}{{ $tableClass !== '' ? ' activity-log-table' : '' }}">
        @if($tableClass !== '')
        <colgroup>
            @foreach($widths as $w)<col style="width:{{ $w }}%">@endforeach
        </colgroup>
        @endif
        <thead>
            <tr>
                <th>İçerik</th>
                <th>Tarih</th>
                <th>Durum</th>
                <th>Sonuç</th>
                <th>XP</th>
            </tr>
        </thead>
        <tbody>
        @foreach($rows as $item)
            <tr>
                <td class="activity-title" title="{{ $item['title'] }}"><div class="clamp">{{ $item['title'] }}</div></td>
                <td>{{ $item['sort_date'] ? \Carbon\Carbon::parse($item['sort_date'])->format('d.m.Y H:i') : '-' }}</td>
                <td>{{ $item['status'] }}</td>
                <td>{{ $item['result'] }}</td>
                <td>{{ (int) $item['xp'] }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@empty
    <p>Kayıt yok.</p>
@endforelse
