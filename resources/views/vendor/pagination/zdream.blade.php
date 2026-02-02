@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Điều hướng trang" class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        {{-- Mobile --}}
        <div class="flex items-center justify-between sm:hidden gap-2">
            @if ($paginator->onFirstPage())
                <span class="inline-flex items-center justify-center px-4 py-2 rounded-full text-sm text-white/40 bg-white/[0.04] border border-white/[0.08] cursor-not-allowed">
                    Trước
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="inline-flex items-center justify-center px-4 py-2 rounded-full text-sm text-[#d3d6db] bg-white/[0.06] border border-white/[0.12] hover:bg-white/[0.12] transition-all">
                    Trước
                </a>
            @endif

            <span class="px-3 py-2 rounded-full text-xs font-semibold text-white/80 bg-white/[0.06] border border-white/[0.12]">
                {{ $paginator->currentPage() }}/{{ $paginator->lastPage() }}
            </span>

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="inline-flex items-center justify-center px-4 py-2 rounded-full text-sm text-[#d3d6db] bg-gradient-to-r from-purple-500 to-pink-500 shadow-[0_6px_20px_rgba(168,85,247,0.35)] hover:from-purple-400 hover:to-pink-400 transition-all">
                    Sau
                </a>
            @else
                <span class="inline-flex items-center justify-center px-4 py-2 rounded-full text-sm text-white/40 bg-white/[0.04] border border-white/[0.08] cursor-not-allowed">
                    Sau
                </span>
            @endif
        </div>

        {{-- Desktop --}}
        <div class="hidden sm:flex sm:items-center sm:justify-between sm:flex-1">
            <div class="text-xs text-white/50">
                @if ($paginator->firstItem())
                    Hiển thị <span class="font-semibold text-[#d3d6db]">{{ $paginator->firstItem() }}</span>
                    – <span class="font-semibold text-[#d3d6db]">{{ $paginator->lastItem() }}</span>
                    / <span class="font-semibold text-[#d3d6db]">{{ $paginator->total() }}</span>
                @else
                    Hiển thị <span class="font-semibold text-[#d3d6db]">{{ $paginator->count() }}</span>
                @endif
            </div>

            <div class="flex items-center gap-2">
                {{-- Previous --}}
                @if ($paginator->onFirstPage())
                    <span class="w-9 h-9 rounded-full inline-flex items-center justify-center text-white/40 bg-white/[0.04] border border-white/[0.08] cursor-not-allowed" aria-disabled="true" aria-label="Trang trước">
                        ‹
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="w-9 h-9 rounded-full inline-flex items-center justify-center text-white/80 bg-white/[0.06] border border-white/[0.12] hover:bg-white/[0.12] transition-all" aria-label="Trang trước">
                        ‹
                    </a>
                @endif

                {{-- Page Numbers --}}
                <div class="flex items-center gap-2">
                    @foreach ($elements as $element)
                        @if (is_string($element))
                            <span class="w-9 h-9 rounded-full inline-flex items-center justify-center text-white/40 bg-white/[0.04] border border-white/[0.08]">
                                {{ $element }}
                            </span>
                        @endif

                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <span aria-current="page" class="w-9 h-9 rounded-full inline-flex items-center justify-center text-[#d3d6db] font-semibold bg-gradient-to-r from-purple-500 to-pink-500 shadow-[0_6px_20px_rgba(168,85,247,0.35)]">
                                        {{ $page }}
                                    </span>
                                @else
                                    <a href="{{ $url }}" class="w-9 h-9 rounded-full inline-flex items-center justify-center text-white/80 bg-white/[0.06] border border-white/[0.12] hover:bg-white/[0.12] transition-all" aria-label="Đi tới trang {{ $page }}">
                                        {{ $page }}
                                    </a>
                                @endif
                            @endforeach
                        @endif
                    @endforeach
                </div>

                {{-- Next --}}
                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="w-9 h-9 rounded-full inline-flex items-center justify-center text-white/80 bg-white/[0.06] border border-white/[0.12] hover:bg-white/[0.12] transition-all" aria-label="Trang sau">
                        ›
                    </a>
                @else
                    <span class="w-9 h-9 rounded-full inline-flex items-center justify-center text-white/40 bg-white/[0.04] border border-white/[0.08] cursor-not-allowed" aria-disabled="true" aria-label="Trang sau">
                        ›
                    </span>
                @endif
            </div>
        </div>
    </nav>
@endif
