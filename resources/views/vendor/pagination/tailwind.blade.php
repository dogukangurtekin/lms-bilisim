{{--
    Ozel pagination gorunumu: Laravel'in varsayilan tailwind.blade.php'si
    mobilde (sm:hidden) sayfa numaralarini gizleyip sadece "Onceki"/"Sonraki"
    metnini gosteriyordu (ayrica bu metin, projede lang_path() cakismasi
    yuzunden hic cevrilmeyip "pagination.previous" olarak ciplak goruluyordu -
    o sorun ayrica lang/resources-lang cakismasi duzeltilerek cozuldu).
    Burada mobil/masaustu ayrimi kaldirildi - sayfa numaralari her ekran
    boyutunda ayni sekilde gosteriliyor, dar ekranlarda yatay kaydirma ile.
--}}
@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Sayfalama" style="margin-top:10px;">
        <div style="display:flex;align-items:center;justify-content:center;gap:4px;overflow-x:auto;padding:4px 2px;-webkit-overflow-scrolling:touch;">
            @if ($paginator->onFirstPage())
                <span style="display:inline-flex;align-items:center;padding:8px 12px;font-size:13px;font-weight:600;color:#9ca3af;background:#f9fafb;border:1px solid #e5e7eb;border-radius:8px;cursor:not-allowed;white-space:nowrap;">‹ Önceki</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" style="display:inline-flex;align-items:center;padding:8px 12px;font-size:13px;font-weight:600;color:#374151;background:#fff;border:1px solid #d1d5db;border-radius:8px;text-decoration:none;white-space:nowrap;">‹ Önceki</a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span style="display:inline-flex;align-items:center;padding:8px 6px;font-size:13px;color:#9ca3af;">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page" style="display:inline-flex;align-items:center;justify-content:center;min-width:34px;padding:8px 10px;font-size:13px;font-weight:700;color:#fff;background:#5B3DF5;border:1px solid #5B3DF5;border-radius:8px;">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" style="display:inline-flex;align-items:center;justify-content:center;min-width:34px;padding:8px 10px;font-size:13px;font-weight:600;color:#374151;background:#fff;border:1px solid #d1d5db;border-radius:8px;text-decoration:none;">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" style="display:inline-flex;align-items:center;padding:8px 12px;font-size:13px;font-weight:600;color:#374151;background:#fff;border:1px solid #d1d5db;border-radius:8px;text-decoration:none;white-space:nowrap;">Sonraki ›</a>
            @else
                <span style="display:inline-flex;align-items:center;padding:8px 12px;font-size:13px;font-weight:600;color:#9ca3af;background:#f9fafb;border:1px solid #e5e7eb;border-radius:8px;cursor:not-allowed;white-space:nowrap;">Sonraki ›</span>
            @endif
        </div>
        <p style="text-align:center;margin:8px 0 0;font-size:12.5px;color:#64748b;">
            Toplam <strong>{{ $paginator->total() }}</strong> kayıttan
            <strong>{{ $paginator->firstItem() }}</strong>-<strong>{{ $paginator->lastItem() }}</strong> arası gösteriliyor
        </p>
    </nav>
@endif
