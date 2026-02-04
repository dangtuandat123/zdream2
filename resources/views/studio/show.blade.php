<x-app-layout>
    <x-slot name="title">{{ $style->name }} - Studio | {{ App\Models\Setting::get('site_name', 'ZDream') }}</x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-6 sm:py-8">
        <!-- Back Button (Mobile) -->
        <a href="{{ route('home') }}"
            class="lg:hidden inline-flex items-center gap-2 text-sm text-white/50 hover:text-white transition-colors mb-4">
            <i class="fa-solid fa-arrow-left" style="font-size: 14px;"></i>
            <span>Quay lại</span>
        </a>

        <div class="grid lg:grid-cols-5 gap-6 lg:gap-8">
            <!-- Left: Style Preview (2/5) -->
            <div class="lg:col-span-2 space-y-4">
                <div class="relative rounded-xl overflow-hidden bg-white/20 border border-white/20 aspect-[1/1]">
                    <img src="{{ $style->thumbnail }}" alt="{{ $style->name }}" class="w-full h-full object-cover"
                        onerror="this.src='/images/placeholder.svg'; this.onerror=null;">
                    <div class="absolute inset-0 bg-gradient-to-t from-[#1b1c21]/90 via-transparent to-transparent">
                    </div>
                    <div class="absolute bottom-0 left-0 right-0 p-4 md:p-6">
                        <h1 class="text-xl md:text-2xl font-bold text-white mb-1.5">{{ $style->name }}</h1>

                    </div>
                </div>

                <!-- Description Block -->
                @if($style->description)
                    <div class="bg-[#1b1c21] border border-[#2a2b30] rounded-xl p-4">
                        <h3 class="text-white font-bold text-base mb-2">Mô tả Style</h3>
                        <p class="text-sm text-white/80 leading-relaxed">{{ $style->description }}</p>
                    </div>
                @endif

                <!-- Model Info (Hidden as requested) -->
                {{--
                <div class="hidden lg:block bg-[#1b1c21] border border-[#2a2b30] rounded-xl p-4">
                    <div class="flex items-center gap-3 text-sm text-white/40 mb-3">
                        <i class="fa-solid fa-microchip text-purple-400" style="font-size: 14px;"></i>
                        <span class="font-mono text-xs truncate">{{ $style->bfl_model_id ?? $style->openrouter_model_id
                            }}</span>
                    </div>
                    <div class="flex items-center gap-3 text-sm text-white/40">
                        <i class="fa-solid fa-crop text-cyan-400" style="font-size: 14px;"></i>
                        <span>Aspect Ratio: <span class="text-white/60">{{ $style->aspect_ratio }}</span></span>
                    </div>
                </div>
                --}}

                <!-- Tips -->
                <div class="bg-purple-500/10 border border-purple-500/30 rounded-xl p-4">
                    <h3 class="text-white font-medium text-sm mb-2 inline-flex items-center gap-2">
                        <i class="fa-solid fa-lightbulb text-yellow-400" style="font-size: 12px;"></i>
                        Mẹo nhỏ
                    </h3>
                    <ul class="text-white/50 text-xs space-y-1">
                        <li>• Chọn options để customize prompt</li>
                        <li>• Kết quả AI sẽ khác nhau mỗi lần</li>
                        <li>• Ảnh sẽ được lưu vào lịch sử</li>
                    </ul>
                </div>

                <!-- User's History với Style này (Desktop) - Livewire reactive -->
                <div id="desktop-history" class="hidden lg:block">
                    <livewire:user-style-history :style="$style" :key="'desktop-history-' . $style->id" />
                </div>
            </div>

            <!-- Right: Generator (3/5) -->
            <div class="lg:col-span-3">
                @livewire('image-generator', ['style' => $style])
            </div>
        </div>
    </div>
</x-app-layout>