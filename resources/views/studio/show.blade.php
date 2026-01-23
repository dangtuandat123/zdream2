<x-app-layout>
    <x-slot name="title">{{ $style->name }} - Studio | ZDream</x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-6 sm:py-8">
        <!-- Back Button (Mobile) -->
        <a href="{{ route('home') }}" class="lg:hidden inline-flex items-center gap-2 text-sm text-white/50 hover:text-white transition-colors mb-4">
            <i class="fa-solid fa-arrow-left w-4 h-4"></i> Quay lại
        </a>

        <div class="grid lg:grid-cols-5 gap-6 lg:gap-8">
            <!-- Left: Style Preview (2/5) -->
            <div class="lg:col-span-2 space-y-4">
                <div class="relative rounded-2xl overflow-hidden bg-white/[0.03] border border-white/[0.08]">
                    <img src="{{ $style->thumbnail }}" alt="{{ $style->name }}" class="w-full aspect-[3/4] md:aspect-[4/5] object-cover">
                    <div class="absolute inset-0 bg-gradient-to-t from-[#0a0a0f]/90 via-transparent to-transparent"></div>
                    <div class="absolute bottom-0 left-0 right-0 p-4 md:p-6">
                        <h1 class="text-xl md:text-2xl font-bold text-white mb-1.5">{{ $style->name }}</h1>
                        @if($style->description)
                            <p class="text-sm text-white/50 line-clamp-2">{{ $style->description }}</p>
                        @endif
                    </div>
                </div>
                <div class="hidden lg:block bg-white/[0.03] border border-white/[0.08] rounded-xl p-3">
                    <div class="flex items-center gap-2 text-xs text-white/40">
                        <i class="fa-solid fa-microchip w-4 h-4"></i>
                        <span class="font-mono truncate">{{ $style->openrouter_model_id }}</span>
                    </div>
                </div>
            </div>

            <!-- Right: Generator (3/5) -->
            <div class="lg:col-span-3">
                @livewire('image-generator', ['style' => $style])
            </div>
        </div>
    </div>
</x-app-layout>
