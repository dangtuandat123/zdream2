<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
    <!-- Hero -->
    <section class="styles-hero mb-6">
        <div class="styles-hero-grid" aria-hidden="true"></div>
        <div class="styles-hero-motion" aria-hidden="true"></div>
        <div class="styles-hero-sheen" aria-hidden="true"></div>
        <div class="styles-hero-orb styles-hero-orb-1" aria-hidden="true"></div>
        <div class="styles-hero-orb styles-hero-orb-2" aria-hidden="true"></div>

        <div class="styles-hero-inner px-4 sm:px-8 py-6 sm:py-10">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                <div class="max-w-2xl">
                    <div class="inline-flex items-center gap-2 rounded-full border border-white/[0.1] bg-white/[0.03] px-3 py-1 text-[11px] text-white/70 anim-pulse-soft">
                        <span class="inline-flex h-2 w-2 rounded-full bg-emerald-400"></span>
                        Có sẵn {{ $styles->total() }} phong cách
                    </div>
                    <h1 class="mt-3 text-2xl sm:text-4xl lg:text-5xl font-extrabold text-white tracking-tight">
                        Chọn phong cách, tạo ảnh nhanh và đẹp
                    </h1>
                    <p class="mt-2 text-white/60 text-sm sm:text-base">
                        Chỉ cần chọn style và nhập vài từ gợi ý. ZDream sẽ tự hoàn thiện ảnh.
                    </p>
                    <div class="mt-4 flex flex-wrap items-center gap-3">
                        <a href="#styles-title" class="h-10 sm:h-11 px-4 sm:px-5 rounded-xl bg-white text-gray-900 text-sm font-semibold inline-flex items-center gap-2 hover:bg-gray-100 transition-colors btn-glow btn-pop">
                            Xem bộ sưu tập
                            <i class="fa-solid fa-arrow-down text-[12px]"></i>
                        </a>
                    </div>
                </div>

                <div class="hidden sm:block"></div>
            </div>
        </div>
    </section>

    <!-- Header -->
    <div id="styles-title" class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6 scroll-mt-24">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-white">Khám phá Styles</h1>
            <p class="text-white/50 text-sm mt-1">{{ $styles->total() }} styles đang sẵn sàng</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="mb-5 styles-filter">
        <div class="p-0 sm:p-4 lg:p-0 sm:rounded-2xl sm:bg-white/[0.03] sm:border sm:border-white/[0.06] lg:bg-transparent lg:border-0">
            <div class="grid gap-3 lg:grid-cols-[1fr_auto] lg:items-center">
            <!-- Search -->
            <div class="min-w-0">
                <div class="relative">
                    <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-white/40"></i>
                    <input
                        type="text"
                        wire:model.live.debounce.400ms="search"
                        wire:key="styles-search-input"
                        placeholder="Tìm kiếm style..."
                        class="w-full h-11 pl-10 pr-10 rounded-xl bg-white/[0.04] border border-white/[0.08] text-white text-sm sm:text-base placeholder:text-white/40 focus:outline-none focus:ring-2 focus:ring-purple-500/40"
                    >
                    @if(trim($search) !== '')
                        <button
                            type="button"
                            wire:click="$set('search','')"
                            class="absolute right-3 top-1/2 -translate-y-1/2 w-7 h-7 rounded-full bg-white/10 text-white/60 hover:text-white hover:bg-white/20 transition-colors inline-flex items-center justify-center"
                            aria-label="Xóa tìm kiếm"
                        >
                            <i class="fa-solid fa-xmark text-[12px]"></i>
                        </button>
                    @endif
                </div>
            </div>

            <!-- Filters -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:flex lg:items-center gap-3">
                <div class="relative min-w-0 sm:min-w-[160px]" wire:ignore x-data="select2Livewire({ model: @entangle('price').live, minResults: 9999 })">
                    <select x-ref="select" data-no-select2="true" class="w-full h-11 px-3 rounded-xl bg-white/[0.04] border border-white/[0.08] text-white text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-purple-500/40">
                        <option value="">Tất cả giá</option>
                        @foreach($priceRanges as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="relative min-w-0 sm:min-w-[180px]" wire:ignore x-data="select2Livewire({ model: @entangle('tag').live, minResults: 9999 })">
                    <select x-ref="select" data-no-select2="true" class="w-full h-11 px-3 rounded-xl bg-white/[0.04] border border-white/[0.08] text-white text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-purple-500/40">
                        <option value="">Tất cả chủ đề</option>
                        @foreach($tags as $tagItem)
                            <option value="{{ $tagItem->id }}">{{ $tagItem->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="relative min-w-0 sm:min-w-[180px]" wire:ignore x-data="select2Livewire({ model: @entangle('sort').live, minResults: 9999 })">
                    <select x-ref="select" data-no-select2="true" class="w-full h-11 px-3 rounded-xl bg-white/[0.04] border border-white/[0.08] text-white text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-purple-500/40">
                        @foreach($sortOptions as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                @if(trim($search) !== '' || $price !== '' || $tag !== '' || $sort !== 'popular')
                    <button type="button" wire:click="resetFilters" class="w-full sm:w-auto h-11 px-4 rounded-xl bg-red-500/15 border border-red-500/30 text-red-300 text-sm hover:bg-red-500/25 transition-colors inline-flex items-center justify-center gap-2 whitespace-nowrap">
                        <i class="fa-solid fa-xmark text-[12px]"></i>
                        Xóa lọc
                    </button>
                @endif
            </div>
            </div>
        </div>
    </div>

    <div id="styles-grid" wire:loading.class="opacity-60" class="transition-opacity scroll-mt-24">
        <!-- Styles Grid -->
        @if($styles->isEmpty())
            <div class="bg-white/[0.03] border border-white/[0.08] rounded-2xl text-center py-16">
                <i class="fa-solid fa-palette text-4xl text-white/20 mb-4"></i>
                <p class="text-white/50 text-lg mb-2">Không tìm thấy Style nào</p>
                <p class="text-white/30 text-sm">Hãy thử thay đổi bộ lọc</p>
            </div>
        @else
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4 lg:gap-5">
                @foreach($styles as $style)
                    <a href="{{ route('studio.show', $style->slug) }}" class="group block h-full">
                        <div class="style-card card-anim relative overflow-hidden h-full bg-gradient-to-b from-white/[0.05] to-white/[0.02] backdrop-blur-[8px] border border-white/[0.08] rounded-2xl sm:rounded-3xl transition-all duration-500 hover:border-purple-500/30 hover:shadow-[0_20px_60px_rgba(168,85,247,0.15)] hover:-translate-y-2 cursor-pointer flex flex-col">
                            <div class="relative aspect-[3/4] overflow-hidden rounded-t-2xl sm:rounded-t-3xl">
                                <img src="{{ $style->thumbnail }}" alt="{{ $style->name }}" class="w-full h-full object-cover transition-all duration-700 group-hover:scale-110" loading="lazy" decoding="async" fetchpriority="low">
                                <div class="absolute inset-0 bg-gradient-to-t from-[#0a0a0f] via-transparent to-transparent opacity-80"></div>
                                <div class="absolute inset-0 bg-gradient-to-br from-purple-500/10 to-pink-500/10 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                                <div class="absolute top-2 sm:top-3 left-2 sm:left-3 right-2 sm:right-3 flex items-start justify-between">
                                    @if($style->tag)
                                        <span class="inline-flex items-center gap-1 px-1.5 sm:px-2 py-0.5 sm:py-1 rounded-full bg-gradient-to-r from-{{ $style->tag->color_from }} to-{{ $style->tag->color_to }} text-white text-[9px] sm:text-xs font-bold shadow-lg">
                                            <i class="fa-solid {{ $style->tag->icon }} w-2 h-2 sm:w-2.5 sm:h-2.5"></i> {{ $style->tag->name }}
                                        </span>
                                    @elseif($style->generated_images_count > 100)
                                        <span class="inline-flex items-center gap-1 px-1.5 sm:px-2 py-0.5 sm:py-1 rounded-full bg-gradient-to-r from-orange-500 to-red-500 text-white text-[9px] sm:text-xs font-bold shadow-lg">
                                            <i class="fa-solid fa-fire w-2 h-2 sm:w-2.5 sm:h-2.5"></i> HOT
                                        </span>
                                    @else
                                        <div></div>
                                    @endif
                                    <div class="px-2 sm:px-3 py-0.5 sm:py-1.5 rounded-full bg-black/60 backdrop-blur-md border border-white/[0.15] shadow-lg">
                                        <span class="text-white font-bold text-[9px] sm:text-xs flex items-center gap-0.5 sm:gap-1">
                                            @if($style->price == 0)
                                                <i class="fa-solid fa-gift w-2 h-2 sm:w-3 sm:h-3 text-green-400"></i> Free
                                            @else
                                                <i class="fa-solid fa-star w-2 h-2 sm:w-3 sm:h-3 text-yellow-400"></i> {{ number_format($style->price, 0) }} Xu
                                            @endif
                                        </span>
                                    </div>
                                </div>
                                <div class="hidden sm:flex absolute inset-0 items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-300">
                                    <div class="transform translate-y-4 group-hover:translate-y-0 transition-transform duration-300">
                                        <div class="px-6 py-3 rounded-full bg-gradient-to-r from-purple-500 to-pink-500 text-white font-semibold text-sm shadow-xl shadow-purple-500/30 flex items-center gap-2">
                                            Thử ngay <i class="fa-solid fa-arrow-right w-3.5 h-3.5"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="flex flex-col flex-1 p-2.5 sm:p-4">
                                <h3 class="font-bold text-white text-xs sm:text-base lg:text-lg line-clamp-1 group-hover:text-purple-300 transition-colors duration-300">{{ $style->name }}</h3>
                                @if($style->description)
                                    <p class="hidden sm:block text-white/40 text-[10px] sm:text-sm mt-1 sm:mt-1.5 line-clamp-2 flex-1">{{ $style->description }}</p>
                                @endif
                                <div class="flex items-center justify-between mt-2 sm:mt-3 pt-2 sm:pt-3 border-t border-white/[0.05]">
                                    <div class="flex items-center gap-1 sm:gap-1.5 text-white/50 text-[10px] sm:text-xs">
                                        <i class="fa-solid fa-image w-3 h-3"></i>
                                        <span>{{ number_format($style->generated_images_count) }} ảnh</span>
                                    </div>
                                    <div class="flex items-center gap-1 text-purple-400 text-[10px] sm:text-xs font-medium">
                                        <i class="fa-solid fa-arrow-right w-2.5 h-2.5 sm:w-3 sm:h-3"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-8">
                {{ $styles->links() }}
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('select2Livewire', ({ model, minResults = 5 }) => ({
            model,
            minResults,
            init() {
                const $select = $(this.$refs.select);
                const $dropdownParent = $select.parent();
                $select.select2({
                    minimumResultsForSearch: this.minResults,
                    dropdownAutoWidth: false,
                    width: '100%',
                    dropdownParent: $dropdownParent
                });

                $select.val(this.model).trigger('change.select2');

                $select.on('change', (event) => {
                    this.model = event.target.value;
                });

                this.$watch('model', (value) => {
                    if ($select.val() !== value) {
                        $select.val(value).trigger('change.select2');
                    }
                });
            }
        }));
    });
</script>
@endpush
