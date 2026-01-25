<x-app-layout>
    <x-slot name="title">{{ $style->name }} - Studio | {{ App\Models\Setting::get('site_name', 'ZDream') }}</x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-6 sm:py-8">
        <!-- Back Button (Mobile) -->
        <a href="{{ route('home') }}" class="lg:hidden inline-flex items-center gap-2 text-sm text-white/50 hover:text-white transition-colors mb-4">
            <i class="fa-solid fa-arrow-left" style="font-size: 14px;"></i>
            <span>Quay lại</span>
        </a>

        <div class="grid lg:grid-cols-5 gap-6 lg:gap-8">
            <!-- Left: Style Preview (2/5) -->
            <div class="lg:col-span-2 space-y-4">
                <div class="relative rounded-2xl overflow-hidden bg-white/[0.03] border border-white/[0.08]">
                    <img src="{{ $style->thumbnail }}" 
                         alt="{{ $style->name }}" 
                         class="w-full aspect-[3/4] md:aspect-[4/5] object-cover"
                         onerror="this.src='/images/placeholder.svg'; this.onerror=null;">
                    <div class="absolute inset-0 bg-gradient-to-t from-[#0a0a0f]/90 via-transparent to-transparent"></div>
                    <div class="absolute bottom-0 left-0 right-0 p-4 md:p-6">
                        <h1 class="text-xl md:text-2xl font-bold text-white mb-1.5">{{ $style->name }}</h1>
                        @if($style->description)
                            <p class="text-sm text-white/50 line-clamp-2">{{ $style->description }}</p>
                        @endif
                    </div>
                </div>
                
                <!-- Model Info -->
                <div class="hidden lg:block bg-white/[0.03] border border-white/[0.08] rounded-xl p-4">
                    <div class="flex items-center gap-3 text-sm text-white/40 mb-3">
                        <i class="fa-solid fa-microchip text-purple-400" style="font-size: 14px;"></i>
                        <span class="font-mono text-xs truncate">{{ $style->openrouter_model_id }}</span>
                    </div>
                    <div class="flex items-center gap-3 text-sm text-white/40">
                        <i class="fa-solid fa-crop text-cyan-400" style="font-size: 14px;"></i>
                        <span>Aspect Ratio: <span class="text-white/60">{{ $style->aspect_ratio }}</span></span>
                    </div>
                </div>

                <!-- Tips (Desktop) -->
                <div class="hidden lg:block bg-gradient-to-br from-purple-500/10 to-pink-500/10 border border-purple-500/20 rounded-xl p-4">
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

                <!-- User's History với Style này (Desktop) -->
                @auth
                    @php
                        $userImages = \App\Models\GeneratedImage::where('user_id', auth()->id())
                            ->where('style_id', $style->id)
                            ->completed()
                            ->latest()
                            ->limit(6)
                            ->get();
                    @endphp
                    @if($userImages->isNotEmpty())
                        <div class="hidden lg:block bg-white/[0.03] border border-white/[0.08] rounded-xl overflow-hidden">
                            <div class="flex items-center justify-between p-4 border-b border-white/[0.05]">
                                <div class="flex items-center gap-2 text-white/60">
                                    <i class="fa-solid fa-clock-rotate-left" style="font-size: 14px;"></i>
                                    <span class="font-medium text-sm">Ảnh đã tạo</span>
                                </div>
                                <a href="{{ route('history.index') }}" class="text-xs text-cyan-400 hover:text-cyan-300 transition-colors">
                                    Xem tất cả
                                </a>
                            </div>
                            <div class="p-3">
                                <div class="grid grid-cols-3 gap-2">
                                    @foreach($userImages as $img)
                                        <a href="{{ $img->image_url }}" target="_blank" class="group relative aspect-square rounded-lg overflow-hidden bg-white/[0.05]">
                                            <img src="{{ $img->image_url }}" alt="Generated" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" onerror="this.src='/images/placeholder.svg'">
                                            <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                                <i class="fa-solid fa-expand text-white"></i>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                @endauth
            </div>

            <!-- Right: Generator (3/5) -->
            <div class="lg:col-span-3">
                @livewire('image-generator', ['style' => $style])
            </div>
        </div>
    </div>
</x-app-layout>
