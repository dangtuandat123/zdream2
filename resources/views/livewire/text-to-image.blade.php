<div class="min-h-screen flex flex-col" x-data="{
    showModelPicker: false,
    showSettings: false
}">
    {{-- Main Content Area (Gallery/Results) --}}
    <div class="flex-1 overflow-y-auto pb-40 sm:pb-48">
        <div class="max-w-6xl mx-auto px-4 py-6">
            {{-- Header --}}
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold text-white">
                        Tạo ảnh AI
                    </h1>
                    <p class="text-white/50 text-sm mt-0.5">Biến ý tưởng thành hình ảnh</p>
                </div>

                {{-- Filters (like reference) --}}
                <div class="hidden sm:flex items-center gap-2">
                    <button
                        class="px-3 py-1.5 text-sm text-white/60 hover:text-white hover:bg-white/5 rounded-lg transition-colors flex items-center gap-1.5">
                        Theo ngày
                        <i class="fa-solid fa-chevron-down text-xs"></i>
                    </button>
                </div>
            </div>

            {{-- Generated Result (show when has result) --}}
            @if($generatedImageUrl)
                <div class="mb-6">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="text-white/50 text-sm">Kết quả mới nhất</span>
                        <div class="flex-1 h-px bg-white/10"></div>
                    </div>

                    <div class="relative group inline-block">
                        <div class="relative rounded-xl overflow-hidden border border-white/10 bg-black/20"
                            style="max-width: 400px;">
                            {{-- Loading overlay if still generating --}}
                            @if($isGenerating)
                                <div
                                    class="absolute inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-10">
                                    <div class="text-center">
                                        <div
                                            class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-purple-500/20 border border-purple-500/30 text-purple-300 text-sm">
                                            <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                    stroke-width="4" fill="none"></circle>
                                                <path class="opacity-75" fill="currentColor"
                                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                            </svg>
                                            Đang tạo...
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <img src="{{ $generatedImageUrl }}" alt="Generated" class="w-full h-auto object-contain"
                                style="max-height: 60vh;">
                        </div>

                        {{-- Actions overlay --}}
                        <div class="absolute top-2 right-2 flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                            <a href="{{ $generatedImageUrl }}" download
                                class="w-8 h-8 rounded-lg bg-black/60 backdrop-blur-sm border border-white/10 flex items-center justify-center text-white/70 hover:text-white transition-colors">
                                <i class="fa-solid fa-download text-sm"></i>
                            </a>
                        </div>

                        {{-- Image info --}}
                        <div class="mt-2 flex items-center gap-2 text-xs text-white/40">
                            <span>{{ $modelId }}</span>
                            <span>•</span>
                            <span>{{ $aspectRatio }}</span>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Empty State or Generating State --}}
            @if(!$generatedImageUrl)
                <div class="flex flex-col items-center justify-center py-20 sm:py-32">
                    @if($isGenerating)
                        <div class="text-center">
                            <div class="relative w-24 h-24 mx-auto mb-6">
                                <div
                                    class="absolute inset-0 rounded-2xl bg-gradient-to-r from-purple-500 to-pink-500 animate-pulse opacity-30">
                                </div>
                                <div class="absolute inset-2 rounded-xl bg-[#0a0a0f] flex items-center justify-center">
                                    <svg class="animate-spin h-10 w-10 text-purple-400" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"
                                            fill="none"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                    </svg>
                                </div>
                            </div>
                            <p class="text-white/70 text-lg">Đang tạo ảnh...</p>
                            <p class="text-white/40 text-sm mt-1">Thường mất 10-30 giây</p>
                        </div>
                    @else
                        <div class="text-center">
                            <div
                                class="w-20 h-20 mx-auto mb-4 rounded-2xl bg-gradient-to-br from-purple-500/20 to-pink-500/20 border border-white/10 flex items-center justify-center">
                                <i class="fa-solid fa-wand-magic-sparkles text-3xl text-purple-400"></i>
                            </div>
                            <p class="text-white/60 text-base">Nhập mô tả để bắt đầu tạo ảnh</p>
                            <p class="text-white/30 text-sm mt-1">Ví dụ: "A cute cat wearing sunglasses"</p>
                        </div>
                    @endif
                </div>
            @endif

            {{-- User History (later) --}}
            @auth
                <div class="mt-8" wire:ignore>
                    {{-- Future: Add user generation history here --}}
                </div>
            @endauth
        </div>
    </div>

    {{-- Error Toast --}}
    @if($errorMessage)
        <div class="fixed top-4 left-1/2 -translate-x-1/2 z-50 max-w-md w-full mx-4" x-data="{ show: true }" x-show="show"
            x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 -translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0" x-init="setTimeout(() => show = false, 5000)">
            <div
                class="flex items-center gap-3 px-4 py-3 rounded-xl bg-red-500/20 border border-red-500/30 backdrop-blur-sm">
                <i class="fa-solid fa-triangle-exclamation text-red-400"></i>
                <p class="text-sm text-red-300 flex-1">{{ $errorMessage }}</p>
                <button @click="show = false" class="text-red-400 hover:text-red-300">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        </div>
    @endif

    {{-- Bottom Fixed Prompt Bar --}}
    <div class="fixed bottom-0 left-0 right-0 z-40">
        {{-- Gradient fade --}}
        <div
            class="absolute inset-x-0 bottom-full h-20 bg-gradient-to-t from-[#0a0a0f] to-transparent pointer-events-none">
        </div>

        <div class="bg-[#0a0a0f]/95 backdrop-blur-xl border-t border-white/5">
            <div class="max-w-4xl mx-auto px-4 py-4 sm:py-5">
                {{-- Main Input Container --}}
                <div class="relative">
                    {{-- Glow effect --}}
                    <div
                        class="absolute -inset-0.5 bg-gradient-to-r from-purple-600/20 via-pink-500/20 to-purple-600/20 rounded-2xl blur-lg opacity-50">
                    </div>

                    <div class="relative bg-[#15161a] rounded-2xl border border-white/10 overflow-hidden">
                        {{-- Textarea Row --}}
                        <div class="p-3 sm:p-4">
                            <textarea wire:model="prompt" rows="2" placeholder="Mô tả hình ảnh bạn muốn tạo..."
                                class="w-full bg-transparent border-none outline-none ring-0 focus:ring-0 text-white placeholder-white/40 text-sm sm:text-base resize-none"
                                {{ $isGenerating ? 'disabled' : '' }} @keydown.meta.enter="$wire.generate()"
                                @keydown.ctrl.enter="$wire.generate()"></textarea>
                        </div>

                        {{-- Bottom Options Bar --}}
                        <div
                            class="flex items-center justify-between gap-2 px-3 sm:px-4 py-2.5 border-t border-white/5 bg-black/20">
                            {{-- Left: Options --}}
                            <div class="flex items-center gap-1.5 sm:gap-2 overflow-x-auto scrollbar-none">
                                {{-- Model Selector --}}
                                <div class="relative" x-data="{ open: false }" @click.away="open = false">
                                    <button type="button" @click="open = !open"
                                        class="flex items-center gap-1.5 h-8 px-2.5 sm:px-3 rounded-lg bg-white/5 hover:bg-white/10 border border-white/10 text-white/70 hover:text-white text-xs sm:text-sm transition-all whitespace-nowrap">
                                        <i class="fa-solid fa-microchip text-purple-400" style="font-size: 11px;"></i>
                                        <span
                                            class="hidden sm:inline">{{ collect($availableModels)->firstWhere('id', $modelId)['name'] ?? 'FLUX' }}</span>
                                        <span class="sm:hidden">FLUX</span>
                                        <i class="fa-solid fa-chevron-down text-[10px] text-white/40"></i>
                                    </button>

                                    {{-- Dropdown --}}
                                    <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-150"
                                        x-transition:enter-start="opacity-0 translate-y-1"
                                        x-transition:enter-end="opacity-100 translate-y-0"
                                        class="absolute bottom-full left-0 mb-2 w-48 py-1 rounded-xl bg-[#1a1b1f] border border-white/10 shadow-2xl">
                                        @foreach($availableModels as $model)
                                            <button type="button" wire:click="$set('modelId', '{{ $model['id'] }}')"
                                                @click="open = false"
                                                class="w-full px-3 py-2 text-left text-sm hover:bg-white/5 transition-colors flex items-center gap-2 {{ $modelId === $model['id'] ? 'text-purple-400' : 'text-white/70' }}">
                                                @if($modelId === $model['id'])
                                                    <i class="fa-solid fa-check text-xs"></i>
                                                @else
                                                    <span class="w-3"></span>
                                                @endif
                                                {{ $model['name'] ?? $model['id'] }}
                                            </button>
                                        @endforeach
                                    </div>
                                </div>

                                {{-- Aspect Ratio Selector --}}
                                <div class="relative" x-data="{ open: false }" @click.away="open = false">
                                    <button type="button" @click="open = !open"
                                        class="flex items-center gap-1.5 h-8 px-2.5 sm:px-3 rounded-lg bg-white/5 hover:bg-white/10 border border-white/10 text-white/70 hover:text-white text-xs sm:text-sm transition-all whitespace-nowrap">
                                        <i class="fa-solid fa-crop text-cyan-400" style="font-size: 11px;"></i>
                                        <span>{{ $aspectRatio }}</span>
                                        <i class="fa-solid fa-chevron-down text-[10px] text-white/40"></i>
                                    </button>

                                    {{-- Dropdown --}}
                                    <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-150"
                                        x-transition:enter-start="opacity-0 translate-y-1"
                                        x-transition:enter-end="opacity-100 translate-y-0"
                                        class="absolute bottom-full left-0 mb-2 w-36 py-1 rounded-xl bg-[#1a1b1f] border border-white/10 shadow-2xl max-h-60 overflow-y-auto">
                                        @foreach($aspectRatios as $ratio => $label)
                                            <button type="button" wire:click="$set('aspectRatio', '{{ $ratio }}')"
                                                @click="open = false"
                                                class="w-full px-3 py-2 text-left text-sm hover:bg-white/5 transition-colors flex items-center gap-2 {{ $aspectRatio === $ratio ? 'text-cyan-400' : 'text-white/70' }}">
                                                @if($aspectRatio === $ratio)
                                                    <i class="fa-solid fa-check text-xs"></i>
                                                @else
                                                    <span class="w-3"></span>
                                                @endif
                                                {{ $label }}
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            {{-- Right: Credits + Submit --}}
                            <div class="flex items-center gap-2 sm:gap-3">
                                {{-- Credits --}}
                                <div class="flex items-center gap-1 text-xs text-white/50">
                                    <i class="fa-solid fa-coins text-yellow-400" style="font-size: 11px;"></i>
                                    <span class="hidden sm:inline">{{ number_format($creditCost, 0) }}</span>
                                </div>

                                {{-- Submit Button --}}
                                <button type="button" wire:click="generate" {{ $isGenerating ? 'disabled' : '' }}
                                    class="w-10 h-10 sm:w-auto sm:h-auto sm:px-5 sm:py-2.5 rounded-xl bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-500 hover:to-pink-500 text-white font-medium shadow-lg shadow-purple-500/30 disabled:opacity-50 disabled:cursor-not-allowed transition-all flex items-center justify-center gap-2">
                                    @if($isGenerating)
                                        <svg class="animate-spin h-5 w-5" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                stroke-width="4" fill="none"></circle>
                                            <path class="opacity-75" fill="currentColor"
                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                        </svg>
                                    @else
                                        <i class="fa-solid fa-arrow-up"></i>
                                    @endif
                                    <span
                                        class="hidden sm:inline">{{ $isGenerating ? 'Đang tạo...' : 'Tạo ảnh' }}</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Polling for async mode --}}
    @if($isGenerating && $lastImageId)
    <div wire:poll.2000ms="pollImageStatus"></div>
    @endif
</div>