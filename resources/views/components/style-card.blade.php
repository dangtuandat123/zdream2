{{-- Style Card Component - Mobile First --}}
@props(['style'])

<a href="{{ route('studio.show', $style->slug) }}" 
   class="group relative overflow-hidden rounded-2xl glass-card-hover">
    
    {{-- Image Container --}}
    <div class="aspect-[3/4] overflow-hidden">
        <img src="{{ $style->thumbnail }}" 
             alt="{{ $style->name }}"
             class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
             loading="lazy">
        
        {{-- Gradient Overlays --}}
        <div class="absolute inset-0 bg-gradient-to-t from-dark-950 via-dark-950/40 to-transparent opacity-90"></div>
        
        {{-- Glow on hover --}}
        <div class="absolute inset-0 bg-gradient-to-t from-primary-500/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
    </div>

    {{-- Content --}}
    <div class="absolute bottom-0 left-0 right-0 p-3 md:p-4">
        {{-- Title --}}
        <h3 class="text-base md:text-lg font-semibold text-white mb-1 group-hover:text-primary-300 transition-colors line-clamp-2">
            {{ $style->name }}
        </h3>
        
        {{-- Description (Desktop only) --}}
        @if($style->description)
            <p class="hidden md:block text-sm text-white/40 line-clamp-2 mb-3">{{ $style->description }}</p>
        @endif

        {{-- Footer --}}
        <div class="flex items-center justify-between mt-2">
            {{-- Price Badge --}}
            <div class="flex items-center gap-1 text-accent-cyan">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.736 6.979C9.208 6.193 9.696 6 10 6c.304 0 .792.193 1.264.979a1 1 0 001.715-1.029C12.279 4.784 11.232 4 10 4s-2.279.784-2.979 1.95c-.285.475-.507 1-.67 1.55H6a1 1 0 000 2h.013a9.358 9.358 0 000 1H6a1 1 0 100 2h.351c.163.55.385 1.075.67 1.55C7.721 15.216 8.768 16 10 16s2.279-.784 2.979-1.95a1 1 0 10-1.715-1.029c-.472.786-.96.979-1.264.979-.304 0-.792-.193-1.264-.979a4.265 4.265 0 01-.264-.521H10a1 1 0 100-2H8.017a7.36 7.36 0 010-1H10a1 1 0 100-2H8.472c.08-.185.167-.36.264-.521z"/>
                </svg>
                <span class="text-sm font-bold">{{ number_format($style->price, 0) }}</span>
            </div>

            {{-- Arrow Button --}}
            <div class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center group-hover:bg-primary-500 group-hover:scale-110 transition-all duration-300">
                <svg class="w-4 h-4 text-white/60 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </div>
        </div>
    </div>
</a>
