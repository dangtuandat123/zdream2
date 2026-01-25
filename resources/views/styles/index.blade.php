<x-app-layout>
    <x-slot name="title">Khám phá Styles - {{ App\Models\Setting::get('site_name', 'ZDream') }}</x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
        
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-white">Khám phá Styles</h1>
                <p class="text-white/50 text-sm mt-1">{{ $styles->total() }} styles đang sẵn sàng</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white/[0.03] border border-white/[0.08] rounded-xl p-4 mb-6">
            <form method="GET" action="{{ route('styles.index') }}" class="flex flex-col md:flex-row md:items-center gap-4">
                <!-- Search -->
                <div class="flex-1">
                    <div class="relative">
                        <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-white/40"></i>
                        <input 
                            type="text" 
                            name="search" 
                            value="{{ $currentSearch }}"
                            placeholder="Tìm kiếm style..."
                            class="w-full pl-10 pr-4 py-2.5 rounded-lg bg-white/[0.05] border border-white/[0.1] text-white placeholder:text-white/40 focus:outline-none focus:ring-2 focus:ring-purple-500/40">
                    </div>
                </div>
                
                <!-- Price Filter -->
                <div class="w-full md:w-40">
                    <select name="price" class="w-full px-3 py-2.5 rounded-lg bg-white/[0.05] border border-white/[0.1] text-white focus:outline-none focus:ring-2 focus:ring-purple-500/40">
                        <option value="">Tất cả giá</option>
                        @foreach($priceRanges as $key => $label)
                            <option value="{{ $key }}" {{ $currentPrice === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Sort -->
                <div class="w-full md:w-44">
                    <select name="sort" class="w-full px-3 py-2.5 rounded-lg bg-white/[0.05] border border-white/[0.1] text-white focus:outline-none focus:ring-2 focus:ring-purple-500/40">
                        @foreach($sortOptions as $key => $label)
                            <option value="{{ $key }}" {{ $currentSort === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Submit -->
                <button type="submit" class="w-full md:w-auto px-5 py-2.5 rounded-lg bg-purple-500 text-white font-medium hover:bg-purple-600 transition-colors inline-flex items-center justify-center gap-2">
                    <i class="fa-solid fa-filter"></i>
                    <span>Lọc</span>
                </button>
                
                @if($currentSearch || $currentPrice)
                    <a href="{{ route('styles.index') }}" class="w-full md:w-auto px-4 py-2.5 rounded-lg bg-red-500/20 border border-red-500/30 text-red-400 text-sm hover:bg-red-500/30 transition-colors text-center">
                        <i class="fa-solid fa-times mr-1"></i> Xóa lọc
                    </a>
                @endif
            </form>
        </div>

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
                        <div class="style-card relative overflow-hidden h-full bg-gradient-to-b from-white/[0.05] to-white/[0.02] backdrop-blur-[8px] border border-white/[0.08] rounded-2xl sm:rounded-3xl transition-all duration-500 hover:border-purple-500/30 hover:shadow-[0_20px_60px_rgba(168,85,247,0.15)] hover:-translate-y-2 cursor-pointer flex flex-col">
                            <div class="relative aspect-[3/4] overflow-hidden rounded-t-2xl sm:rounded-t-3xl">
                                <img src="{{ $style->thumbnail }}" alt="{{ $style->name }}" class="w-full h-full object-cover transition-all duration-700 group-hover:scale-110" loading="lazy">
                                <div class="absolute inset-0 bg-gradient-to-t from-[#0a0a0f] via-transparent to-transparent opacity-80"></div>
                                <div class="absolute inset-0 bg-gradient-to-br from-purple-500/10 to-pink-500/10 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                                <div class="absolute top-2 sm:top-3 left-2 sm:left-3 right-2 sm:right-3 flex items-start justify-between">
                                    @if($style->generated_images_count > 100)
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
</x-app-layout>
