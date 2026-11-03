@php
    $cbStatus = \App\Support\ConsentPolicy::status($u ?? null);
    $cbAt = ($u->consent_accepted_at ?? null) ? \Illuminate\Support\Carbon::parse($u->consent_accepted_at)->format('d.m.Y H:i') : null;
@endphp
@if($cbStatus === 'ok')
    <span title="KVKK / kullanım şartları onaylandı{{ $cbAt ? ': ' . $cbAt : '' }}" style="display:inline-block;margin-left:6px;padding:2px 8px;border-radius:999px;background:#dcfce7;color:#166534;font-size:11px;font-weight:800;white-space:nowrap">✅ Onayladı</span>
@elseif($cbStatus === 'old')
    <span title="Eski sürümü onayladı{{ $cbAt ? ': ' . $cbAt : '' }}; yeni sürümü henüz onaylamadı" style="display:inline-block;margin-left:6px;padding:2px 8px;border-radius:999px;background:#fef3c7;color:#92400e;font-size:11px;font-weight:800;white-space:nowrap">⏳ Yeni sürümü onaylamadı</span>
@else
    <span title="Henüz onay vermedi" style="display:inline-block;margin-left:6px;padding:2px 8px;border-radius:999px;background:#fef3c7;color:#92400e;font-size:11px;font-weight:800;white-space:nowrap">⏳ Onaylamadı</span>
@endif
