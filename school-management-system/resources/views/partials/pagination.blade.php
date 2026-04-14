@if ($paginator->hasPages())
    <nav class="app-pagination" role="navigation" aria-label="Sayfalama">
        @if ($paginator->onFirstPage())
            <span class="page-btn disabled">Önceki</span>
        @else
            <a class="page-btn" href="{{ $paginator->previousPageUrl() }}" rel="prev">Önceki</a>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="page-dot">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="page-btn active">{{ $page }}</span>
                    @else
                        <a class="page-btn" href="{{ $url }}">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a class="page-btn" href="{{ $paginator->nextPageUrl() }}" rel="next">Sonraki</a>
        @else
            <span class="page-btn disabled">Sonraki</span>
        @endif
    </nav>
@endif
