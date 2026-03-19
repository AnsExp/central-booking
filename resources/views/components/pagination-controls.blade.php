@props([
    'paginator',
    'window' => 1,
])

@if ($paginator->total() > 0)
    @php
        $startPage = max(1, $paginator->currentPage() - $window);
        $endPage = min($paginator->lastPage(), $paginator->currentPage() + $window);
    @endphp

    <div class="border-t border-slate-200 px-4 py-3 sm:px-6 sm:py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <p class="text-xs sm:text-sm text-slate-500">
            Mostrando {{ $paginator->firstItem() }} - {{ $paginator->lastItem() }} de {{ $paginator->total() }} elementos
        </p>

        <div class="flex items-center gap-2">
            @if ($paginator->onFirstPage())
                <span class="inline-flex items-center rounded-lg border border-slate-200 px-3 py-1.5 text-xs sm:text-sm text-slate-400 cursor-not-allowed">
                    Anterior
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}"
                    class="inline-flex items-center rounded-lg border border-slate-300 px-3 py-1.5 text-xs sm:text-sm text-slate-700 hover:bg-slate-50 transition-colors">
                    Anterior
                </a>
            @endif

            @for ($page = $startPage; $page <= $endPage; $page++)
                @if ($page === $paginator->currentPage())
                    <span class="inline-flex items-center rounded-lg bg-slate-900 text-white px-3 py-1.5 text-xs sm:text-sm font-semibold">
                        {{ $page }}
                    </span>
                @else
                    <a href="{{ $paginator->url($page) }}"
                        class="inline-flex items-center rounded-lg border border-slate-300 px-3 py-1.5 text-xs sm:text-sm text-slate-700 hover:bg-slate-50 transition-colors">
                        {{ $page }}
                    </a>
                @endif
            @endfor

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}"
                    class="inline-flex items-center rounded-lg border border-slate-300 px-3 py-1.5 text-xs sm:text-sm text-slate-700 hover:bg-slate-50 transition-colors">
                    Siguiente
                </a>
            @else
                <span class="inline-flex items-center rounded-lg border border-slate-200 px-3 py-1.5 text-xs sm:text-sm text-slate-400 cursor-not-allowed">
                    Siguiente
                </span>
            @endif
        </div>
    </div>
@endif
