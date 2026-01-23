<x-app-layout>
    <x-slot name="title">{{ $style->name }} - Studio | EZShot AI</x-slot>

    <div class="container mx-auto px-4 py-6 md:py-8">
        {{-- Mobile: Stack, Desktop: Grid --}}
        <div class="grid lg:grid-cols-5 gap-6 lg:gap-8">
            
            {{-- Left: Style Preview (2/5 on desktop) --}}
            <div class="lg:col-span-2 space-y-4">
                {{-- Back Button (Mobile) --}}
                <a href="{{ route('home') }}" class="lg:hidden inline-flex items-center gap-2 text-sm text-white/50 hover:text-white transition-colors mb-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Quay lại
                </a>

                {{-- Thumbnail Card --}}
                <div class="relative rounded-2xl overflow-hidden glass-card">
                    <img src="{{ $style->thumbnail }}" 
                         alt="{{ $style->name }}"
                         class="w-full aspect-[3/4] md:aspect-[4/5] object-cover">
                    <div class="absolute inset-0 bg-gradient-to-t from-dark-950/90 via-transparent to-transparent"></div>
                    
                    {{-- Overlay Info --}}
                    <div class="absolute bottom-0 left-0 right-0 p-4 md:p-6">
                        <h1 class="text-xl md:text-2xl font-bold text-white mb-1.5">{{ $style->name }}</h1>
                        @if($style->description)
                            <p class="text-sm text-white/50 line-clamp-2">{{ $style->description }}</p>
                        @endif
                    </div>
                </div>

                {{-- Model Badge (Desktop only) --}}
                <div class="hidden lg:block glass-card p-3">
                    <div class="flex items-center gap-2 text-xs text-white/40">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <span class="font-mono truncate">{{ $style->openrouter_model_id }}</span>
                    </div>
                </div>
            </div>

            {{-- Right: Generator (3/5 on desktop) --}}
            <div class="lg:col-span-3">
                @livewire('image-generator', ['style' => $style])
            </div>
        </div>
    </div>
</x-app-layout>
