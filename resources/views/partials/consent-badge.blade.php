@php
    $cbStatus = \App\Support\ConsentPolicy::status($u ?? null);
    $cbAt = ($u->consent_accepted_at ?? null) ? \Illuminate\Support\Carbon::parse($u->consent_accepted_at)->format('d.m.Y H:i') : null;
@endphp
@if($cbStatus === 'ok')
    <span class="consent-ico" title="Onayladı{{ $cbAt ? ': ' . $cbAt : '' }}" aria-label="Onayladı" style="font-size:18px;line-height:1">✅</span>
@elseif($cbStatus === 'old')
    <span class="consent-ico" title="Eski sürümü onayladı{{ $cbAt ? ' (' . $cbAt . ')' : '' }}; yeni sürümü henüz onaylamadı" aria-label="Yeni sürümü onaylamadı" style="font-size:18px;line-height:1">⏳</span>
@else
    <span class="consent-ico" title="Henüz onay vermedi" aria-label="Onaylamadı" style="font-size:18px;line-height:1">⏳</span>
@endif
