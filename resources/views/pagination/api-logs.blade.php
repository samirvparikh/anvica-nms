@if ($paginator->total() > 0)
    <div class="api-logs-pagination">
        <p class="api-logs-pagination__summary">
            Showing
            <strong>{{ $paginator->firstItem() }}</strong>
            to
            <strong>{{ $paginator->lastItem() }}</strong>
            of
            <strong>{{ $paginator->total() }}</strong>
            results
        </p>

        @if ($paginator->hasPages())
            <ul class="api-logs-pagination__nav" role="navigation" aria-label="Pagination">
                <li>
                    @if ($paginator->onFirstPage())
                        <span class="api-logs-pagination__btn is-disabled" aria-disabled="true">&laquo; Prev</span>
                    @else
                        <a class="api-logs-pagination__btn" href="{{ $paginator->previousPageUrl() }}" rel="prev">&laquo; Prev</a>
                    @endif
                </li>

                @foreach ($elements as $element)
                    @if (is_string($element))
                        <li>
                            <span class="api-logs-pagination__btn is-disabled" aria-disabled="true">{{ $element }}</span>
                        </li>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            <li>
                                @if ($page == $paginator->currentPage())
                                    <span class="api-logs-pagination__btn is-active" aria-current="page">{{ $page }}</span>
                                @else
                                    <a class="api-logs-pagination__btn" href="{{ $url }}" aria-label="Go to page {{ $page }}">{{ $page }}</a>
                                @endif
                            </li>
                        @endforeach
                    @endif
                @endforeach

                <li>
                    @if ($paginator->hasMorePages())
                        <a class="api-logs-pagination__btn" href="{{ $paginator->nextPageUrl() }}" rel="next">Next &raquo;</a>
                    @else
                        <span class="api-logs-pagination__btn is-disabled" aria-disabled="true">Next &raquo;</span>
                    @endif
                </li>
            </ul>
        @endif
    </div>
@endif
