@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Điều hướng trang" class="flex items-center justify-between gap-2">
        @if ($paginator->onFirstPage())
            <span class="inline-flex items-center justify-center px-4 py-2 rounded-full text-sm text-white/40 bg-white/[0.04] border border-white/[0.08] cursor-not-allowed">
                Trước
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="inline-flex items-center justify-center px-4 py-2 rounded-full text-sm text-white bg-white/[0.06] border border-white/[0.12] hover:bg-white/[0.12] transition-all">
                Trước
            </a>
        @endif

        <span class="px-3 py-2 rounded-full text-xs font-semibold text-white/80 bg-white/[0.06] border border-white/[0.12]">
            {{ $paginator->currentPage() }}/{{ $paginator->lastPage() }}
        </span>

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="inline-flex items-center justify-center px-4 py-2 rounded-full text-sm text-white bg-gradient-to-r from-purple-500 to-pink-500 shadow-[0_6px_20px_rgba(168,85,247,0.35)] hover:from-purple-400 hover:to-pink-400 transition-all">
                Sau
            </a>
        @else
            <span class="inline-flex items-center justify-center px-4 py-2 rounded-full text-sm text-white/40 bg-white/[0.04] border border-white/[0.08] cursor-not-allowed">
                Sau
            </span>
        @endif
    </nav>
@endif
