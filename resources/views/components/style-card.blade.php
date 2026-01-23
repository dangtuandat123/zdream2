{{-- Style Card Component - ZDream Design --}}
@props(['style', 'loop' => null])

@php
    $isHot = $loop && $loop->index < 3;
    $isNew = $loop && $loop->index >= 3 && $loop->index < 5;
@endphp

<a href="{{ route('studio.show', $style->slug) }}" class="group block h-full">
    <div class="style-card shine-effect h-full">
        {{-- Image Container --}}
        <div class="relative aspect-[3/4] overflow-hidden rounded-t-2xl sm:rounded-t-3xl">
            <img src="{{ $style->thumbnail }}" 
                 alt="{{ $style->name }}" 
                 class="style-card-image"
                 loading="lazy">
            
            {{-- Gradient Overlays --}}
            <div class="absolute inset-0 bg-gradient-to-t from-[#0a0a0f] via-transparent to-transparent opacity-80"></div>
            <div class="absolute inset-0 bg-gradient-to-br from-purple-500/10 to-pink-500/10 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
            
            {{-- Top Badges --}}
            <div class="absolute top-2 sm:top-3 left-2 sm:left-3 right-2 sm:right-3 flex items-start justify-between">
                @if($isHot)
                    <span class="badge-hot">
                        <i class="fa-solid fa-fire w-2 h-2 sm:w-2.5 sm:h-2.5"></i> HOT
                    </span>
                @elseif($isNew)
                    <span class="badge-new">
                        <i class="fa-solid fa-bolt w-2 h-2 sm:w-2.5 sm:h-2.5"></i> MỚI
                    </span>
                @else
                    <div></div>
                @endif
                
                {{-- Price Badge --}}
                <div class="badge-price">
                    <span class="text-white font-bold text-[9px] sm:text-xs flex items-center gap-0.5 sm:gap-1">
                        <i class="fa-solid fa-star w-2 h-2 sm:w-3 sm:h-3 text-yellow-400"></i> {{ number_format($style->price, 0) }} Xu
                    </span>
                </div>
            </div>
            
            {{-- Hover CTA (Desktop) --}}
            <div class="style-card-cta absolute inset-0 items-center justify-center">
                <div class="transform translate-y-4 group-hover:translate-y-0 transition-transform duration-300">
                    <div class="px-6 py-3 rounded-full bg-gradient-to-r from-purple-500 to-pink-500 text-white font-semibold text-sm shadow-xl shadow-purple-500/30 flex items-center gap-2">
                        Thử ngay <i class="fa-solid fa-arrow-right w-3.5 h-3.5"></i>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Content --}}
        <div class="flex flex-col flex-1 p-2.5 sm:p-4">
            <h3 class="style-card-title text-xs sm:text-base lg:text-lg line-clamp-1">{{ $style->name }}</h3>
            @if($style->description)
                <p class="hidden sm:block text-white/40 text-[10px] sm:text-sm mt-1 sm:mt-1.5 line-clamp-2 flex-1">{{ $style->description }}</p>
            @endif
            
            {{-- Footer --}}
            <div class="flex items-center justify-between mt-2 sm:mt-3 pt-2 sm:pt-3 border-t border-white/[0.05]">
                <div class="flex items-center gap-1 sm:gap-1.5 text-white/50 text-[10px] sm:text-xs">
                    <span class="w-1 h-1 sm:w-1.5 sm:h-1.5 rounded-full bg-green-500 animate-pulse"></span> Sẵn sàng
                </div>
                <div class="flex items-center gap-1 text-purple-400 text-[10px] sm:text-xs font-medium">
                    <i class="fa-solid fa-arrow-right w-2.5 h-2.5 sm:w-3 sm:h-3"></i>
                </div>
            </div>
        </div>
    </div>
</a>
