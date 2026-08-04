@if ($paginator->hasPages())
<nav class="cp-pag" role="navigation" aria-label="Pagination">
    {{-- Previous --}}
    @if ($paginator->onFirstPage())
        <span class="cp-pag-btn disabled" aria-disabled="true">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M15 5l-7 7 7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Previous
        </span>
    @else
        <a href="{{ $paginator->previousPageUrl() }}" class="cp-pag-btn" rel="prev">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M15 5l-7 7 7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Previous
        </a>
    @endif

    {{-- Page numbers --}}
    <span class="cp-pag-nums">
        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="cp-pag-gap">{{ $element }}</span>
            @endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="cp-pag-num on" aria-current="page">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="cp-pag-num">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach
    </span>

    {{-- Next --}}
    @if ($paginator->hasMorePages())
        <a href="{{ $paginator->nextPageUrl() }}" class="cp-pag-btn" rel="next">
            Next
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M9 5l7 7-7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </a>
    @else
        <span class="cp-pag-btn disabled" aria-disabled="true">
            Next
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M9 5l7 7-7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </span>
    @endif
</nav>
@endif
