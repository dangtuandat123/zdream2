<section class="px-2 sm:px-4 py-8 sm:py-12">
    <div class="text-center mb-8 sm:mb-10">
        <h2 class="text-xl sm:text-2xl lg:text-3xl font-bold text-[#d3d6db] mb-2">Inspiration Gallery</h2>
        <p class="text-white/50 text-sm sm:text-base">Khám phá ý tưởng sáng tạo từ cộng đồng</p>
    </div>

    <!-- Masonry Grid -->
    <div class="columns-2 sm:columns-3 lg:columns-4 gap-1 sm:gap-1.5">
        @foreach($inspirations as $inspiration)
        <div class="group relative mb-1 sm:mb-1.5 break-inside-avoid" wire:key="inspiration-{{ $inspiration['id'] }}">
            <div class="inspiration-card relative overflow-hidden rounded-lg bg-[#1b1c21] border border-[#2a2b30] transition-all duration-300 hover:border-purple-500/40 hover:shadow-lg hover:shadow-purple-500/10">
                <!-- Skeleton Loading -->
                <div class="skeleton-loader aspect-square bg-gradient-to-r from-[#1b1c21] via-[#2a2b30] to-[#1b1c21] bg-[length:200%_100%] animate-pulse">
                    <div class="absolute inset-0 flex items-center justify-center">
                        <i class="fa-solid fa-image text-white/10 text-2xl"></i>
                    </div>
                </div>
                
                <!-- Image (hidden until loaded) -->
                <img 
                    src="{{ $inspiration['image_url'] }}" 
                    alt="Inspiration" 
                    class="inspiration-img absolute inset-0 w-full h-full object-cover transition-all duration-500 opacity-0 group-hover:scale-105"
                    loading="lazy"
                    decoding="async"
                    onload="this.classList.remove('opacity-0'); this.classList.add('opacity-100'); this.previousElementSibling.style.display='none'; this.classList.remove('absolute', 'inset-0'); this.classList.add('relative');"
                >
                
                <!-- Hover Overlay with Prompt -->
                <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex flex-col justify-end p-3 sm:p-4 pointer-events-none group-hover:pointer-events-auto">
                    <p class="text-white/90 text-xs sm:text-sm line-clamp-4 leading-relaxed">
                        {{ Str::limit($inspiration['prompt'], 150) }}
                    </p>
                    @if(isset($inspiration['ref_images']) && count($inspiration['ref_images']) > 0)
                    <div class="mt-2 flex items-center gap-1.5">
                        <i class="fa-solid fa-images text-purple-400 text-xs"></i>
                        <span class="text-purple-300 text-xs">{{ count($inspiration['ref_images']) }} ảnh tham chiếu</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Infinite Scroll Trigger -->
    @if($hasMore)
        <div 
            x-data 
            x-intersect="$wire.loadMore()" 
            class="flex justify-center p-4 mt-4"
        >
            <div wire:loading class="flex items-center gap-2 text-white/50">
                <i class="fa-solid fa-circle-notch fa-spin"></i>
                <span>Đang tải thêm...</span>
            </div>
        </div>
    @endif
</section>
