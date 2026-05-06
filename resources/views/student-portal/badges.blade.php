@extends('layout.app')
@section('title','Rozetlerim')
@section('content')
<div class="top"><h1>Rozetlerim</h1></div>

<div class="v2-metrics">
    <article class="card"><span>Kazanilan Rozet</span><strong>{{ $earnedCount }}</strong></article>
    <article class="card"><span>Toplam Rozet</span><strong>{{ count($items) }}</strong></article>
</div>

<div class="card">
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:12px">
        @foreach($items as $i => $item)
            @php
                $emojiMap = [
                    'Ilk Adim' => '🚀',
                    'Odev Ustasi' => '📘',
                    'Oyun Avcisi' => '🎮',
                    'Ders Kesifi' => '📚',
                    'XP 100' => '⭐',
                    'XP 300' => '💎',
                    'Maratoncu' => '⏱️',
                    'Sinif Birincisi' => '🥇',
                    'Okul Birincisi' => '🏆',
                    'Efsane Tamamlayici' => '👑',
                    'Gorev Serisi 10' => '🔥',
                    'Gorev Serisi 25' => '🏅',
                    'Ders Ustasi' => '🧠',
                    'Ders Efsanesi' => '🎓',
                    'Oyun Uzmani' => '🕹️',
                    'Oyun Sampiyonu' => '🎯',
                    'XP 500' => '🌟',
                    'XP 1000' => '🚀',
                    'Disiplinli Calisma' => '🗂️',
                    'Panel Ustasi' => '📈',
                    'Istikrar Madalyasi' => '🥈',
                    'Tamamlama Zirvesi' => '🏔️',
                ];
                $displayEmoji = $emojiMap[$item['name']] ?? '🏅';
            @endphp
            <div style="border:1px solid #dbe5f2;border-radius:14px;padding:12px;background:{{ $item['earned'] ? '#ecfdf5' : '#ffffff' }}">
                <div style="display:flex;align-items:center;justify-content:space-between">
                    <div style="font-size:28px;line-height:1">
                        {{ $displayEmoji }}
                    </div>
                    <span class="badge">{{ $item['current'] }}/{{ $item['target'] }}</span>
                </div>
                <h3 style="margin:8px 0 6px;font-size:18px">{{ $item['name'] }}</h3>
                <p style="margin:0;color:#475569;font-size:13px">{{ $item['description'] }}</p>
                <div style="margin-top:10px;height:8px;background:#e2e8f0;border-radius:999px;overflow:hidden">
                    @php
                        $pct = $item['target'] > 0 ? (int) floor(($item['current'] / $item['target']) * 100) : 0;
                        $pct = max(0, min(100, $pct));
                    @endphp
                    <div style="height:100%;width:{{ $pct }}%;background:{{ $item['earned'] ? '#16a34a' : '#3b82f6' }}"></div>
                </div>
                <div style="margin-top:8px;font-size:12px;color:{{ $item['earned'] ? '#166534' : '#64748b' }}">
                    {{ $item['earned'] ? 'Kazanildi' : 'Devam ediyor' }}
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
