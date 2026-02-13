{{-- ============================================================ --}}
{{-- LOADING SKELETON (appears at bottom while generating) --}}
{{-- ============================================================ --}}
@if($isGenerating && !$generatedImageUrl)
    <div x-data="{ elapsed: 0, timer: null }" x-init="
                    startLoading();
                    timer = setInterval(() => elapsed++, 1000);
                    $cleanup(() => { clearInterval(timer); stopLoading(); });
                    $nextTick(() => setTimeout(() => document.documentElement.scrollTo({ top: document.documentElement.scrollHeight, behavior: 'smooth' }), 100));
                " x-effect="if (!@js($isGenerating)) { stopLoading(); clearInterval(timer); timer = null; }">
        <div
            class="bg-white/[0.03] backdrop-blur-[12px] border border-white/[0.08] rounded-xl overflow-hidden shadow-[inset_0_1px_1px_rgba(255,255,255,0.05)]">
            {{-- Progress bar --}}
            <div class="h-0.5 bg-white/[0.03] overflow-hidden">
                <div class="h-full bg-gradient-to-r from-purple-500 via-fuchsia-500 to-purple-500 animate-pulse"
                    style="width: 100%; animation: progress-slide 2s ease-in-out infinite;"></div>
            </div>
            <div class="p-4">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-8 h-8 rounded-lg bg-purple-500/20 flex items-center justify-center">
                        <div class="w-5 h-5 border-2 border-purple-400 border-t-transparent rounded-full animate-spin">
                        </div>
                    </div>
                    <div class="flex-1">
                        <p class="text-white/90 text-sm font-medium" x-text="loadingMessages[currentLoadingMessage]">Đang
                            tạo ảnh...</p>
                        <p class="text-white/40 text-xs mt-0.5">
                            <span x-text="Math.floor(elapsed / 60) > 0 ? Math.floor(elapsed / 60) + ' phút ' : ''"></span>
                            <span x-text="(elapsed % 60) + ' giây'"></span>
                        </p>
                    </div>
                    <button wire:click="cancelGeneration"
                        class="h-8 px-3 rounded-lg bg-white/[0.05] hover:bg-red-500/20 border border-white/[0.08] text-xs text-white/50 hover:text-red-400 transition-all active:scale-[0.95]">
                        <i class="fa-solid fa-xmark mr-1"></i>Hủy
                    </button>
                </div>
                {{-- Loading placeholder per batch --}}
                <div class="grid grid-cols-2 gap-1 rounded-lg overflow-hidden">
                    @for ($i = 0; $i < $batchSize; $i++)
                        <div class="bg-white/[0.03] flex items-center justify-center {{ $batchSize == 1 ? 'col-span-2 max-w-sm' : '' }}"
                            style="aspect-ratio: {{ $aspectRatio !== 'auto' && strpos($aspectRatio, ':') !== false ? str_replace(':', ' / ', $aspectRatio) : '1 / 1' }};">
                            <div class="w-6 h-6 border-2 border-purple-500/40 border-t-transparent rounded-full animate-spin">
                            </div>
                        </div>
                    @endfor
                </div>
            </div>
        </div>
    </div>
@endif