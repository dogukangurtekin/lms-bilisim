@php
    $order = ['Ders', 'Ders Ödevi', 'Ödev', 'Oyun Ödevi', 'Uygulama Ödevi', 'Oyun / Uygulama', 'Etkinlik', 'Günlük Egzersiz', 'Canlı Quiz', 'Canlı Yarışma', 'Klavye Yarışı'];
    $groups = collect($log)->groupBy('kind');
    $sortedKinds = $groups->keys()->sortBy(function ($kind) use ($order) {
        $i = array_search($kind, $order, true);
        return $i === false ? 999 : $i;
    })->values();
    $tableClass = $tableClass ?? '';
@endphp
@forelse($sortedKinds as $kind)
    @php $rows = $groups->get($kind); @endphp
    <h4 style="margin:14px 0 6px">{{ $kind }} <small style="opacity:.65;font-weight:400">({{ $rows->count() }} kayıt · {{ (int) $rows->sum('xp') }} XP)</small></h4>
    <table class="{{ $tableClass }}">
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
                <td>{{ $item['title'] }}</td>
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
