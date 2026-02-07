<div class="min-h-[calc(100vh-80px)] flex items-center justify-center px-4 py-8 sm:py-12"
    wire:poll.2000ms="pollImageStatus" x-data="{
        showAdvanced: false,
        copied: false,
        copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                this.copied = true;
                setTimeout(() => this.copied = false, 2000);
            });
        }
     }">

    <div class="w-full max-w-4xl">
        <!-- Header -->
        <div class="text-center mb-8 sm:mb-10">
            <h1 class="text-2xl sm:text-3xl font-bold text-white mb-2">
                <i class="fa-solid fa-wand-magic-sparkles text-purple-400 mr-2" style="font-size: inherit;"></i>
                Tạo ảnh AI
            </h1>
            <p class="text-white/60 text-sm sm:text-base">Nhập mô tả và để AI biến ý tưởng thành hình ảnh</p>
        </div>

        <!-- Main Container -->
        <div class="bg-white/[0.03] backdrop-blur-[12px] border border-white/[0.08] rounded-2xl p-4 sm:p-6 shadow-lg">

            <!-- Prompt Input -->
            <div class="mb-6">
                <label for="prompt" class="block text-sm font-medium text-white/80 mb-2">
                    Mô tả hình ảnh
                </label>
                <div class="relative">
                    <textarea id="prompt" wire:model="prompt" rows="4"
                        placeholder="Ví dụ: A majestic lion standing on a cliff at sunset, cinematic lighting, ultra detailed..."
                        class="w-full px-4 py-3 rounded-xl bg-white/[0.05] border border-white/[0.1] text-white placeholder-white/40 focus:outline-none focus:ring-2 focus:ring-purple-500/50 focus:border-purple-500/50 resize-none transition-all duration-200"
                        {{ $isGenerating ? 'disabled' : '' }}></textarea>
                    <div class="absolute bottom-3 right-3 text-xs text-white/30">
                        {{ mb_strlen($prompt) }}/2000
                    </div>
                </div>
            </div>

            <!-- Options Row -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                <!-- Aspect Ratio -->
                <div>
                    <label class="block text-sm font-medium text-white/80 mb-2">
                        <i class="fa-solid fa-crop text-cyan-400 mr-1.5" style="font-size: 12px;"></i>
                        Tỷ lệ khung hình
                    </label>
                    <select wire:model="aspectRatio"
                        class="w-full px-4 py-2.5 rounded-lg bg-white/[0.05] border border-white/[0.1] text-white focus:outline-none focus:ring-2 focus:ring-purple-500/50 appearance-none cursor-pointer"
                        {{ $isGenerating ? 'disabled' : '' }}>
                        @foreach($aspectRatios as $ratio => $label)
                            <option value="{{ $ratio }}" class="bg-[#1b1c21] text-white">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Model Selector (Optional - can be hidden) -->
                <div>
                    <label class="block text-sm font-medium text-white/80 mb-2">
                        <i class="fa-solid fa-microchip text-purple-400 mr-1.5" style="font-size: 12px;"></i>
                        Model AI
                    </label>
                    <select wire:model="modelId"
                        class="w-full px-4 py-2.5 rounded-lg bg-white/[0.05] border border-white/[0.1] text-white focus:outline-none focus:ring-2 focus:ring-purple-500/50 appearance-none cursor-pointer"
                        {{ $isGenerating ? 'disabled' : '' }}>
                        @foreach($availableModels as $model)
                            <option value="{{ $model['id'] }}" class="bg-[#1b1c21] text-white">
                                {{ $model['name'] ?? $model['id'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Credit Cost Info -->
            <div
                class="flex items-center justify-between mb-6 p-3 bg-purple-500/10 border border-purple-500/20 rounded-lg">
                <div class="flex items-center gap-2 text-sm text-white/70">
                    <i class="fa-solid fa-coins text-yellow-400" style="font-size: 14px;"></i>
                    <span>Chi phí: <strong class="text-white">{{ number_format($creditCost, 0) }}
                            credits</strong></span>
                </div>
                @auth
                    <div class="text-sm text-white/50">
                        Số dư: <span class="text-white">{{ number_format(auth()->user()->credits ?? 0, 0) }}</span>
                    </div>
                @endauth
            </div>

            <!-- Error Message -->
            @if($errorMessage)
                <div class="mb-4 p-4 bg-red-500/10 border border-red-500/30 rounded-lg flex items-start gap-3">
                    <i class="fa-solid fa-triangle-exclamation text-red-400 mt-0.5" style="font-size: 16px;"></i>
                    <p class="text-sm text-red-300">{{ $errorMessage }}</p>
                </div>
            @endif

            <!-- Generate Button -->
            <button wire:click="generate" wire:loading.attr="disabled" {{ $isGenerating ? 'disabled' : '' }} class="w-full py-4 px-6 rounded-xl font-semibold text-white text-base
                       bg-gradient-to-r from-purple-600 to-pink-600 
                       hover:from-purple-500 hover:to-pink-500
                       disabled:opacity-50 disabled:cursor-not-allowed
                       shadow-lg shadow-purple-500/30 hover:shadow-purple-500/50
                       transform transition-all duration-200 
                       hover:scale-[1.02] active:scale-[0.98]
                       flex items-center justify-center gap-2">
                @if($isGenerating)
                    <svg class="animate-spin h-5 w-5" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"
                            fill="none"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <span>Đang tạo ảnh...</span>
                @else
                    <i class="fa-solid fa-wand-magic-sparkles" style="font-size: 18px;"></i>
                    <span>Tạo ảnh</span>
                @endif
            </button>
        </div>

        <!-- Result Display -->
        @if($generatedImageUrl)
            <div class="mt-8 bg-white/[0.03] backdrop-blur-[12px] border border-white/[0.08] rounded-2xl p-4 sm:p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                        <i class="fa-solid fa-image text-green-400" style="font-size: 18px;"></i>
                        Kết quả
                    </h3>
                    <div class="flex items-center gap-2">
                        <!-- Download Button -->
                        <a href="{{ $generatedImageUrl }}" download
                            class="px-3 py-1.5 text-sm rounded-lg bg-white/[0.05] border border-white/[0.1] text-white/70 hover:text-white hover:bg-white/[0.1] transition-colors flex items-center gap-1.5">
                            <i class="fa-solid fa-download" style="font-size: 12px;"></i>
                            <span>Tải về</span>
                        </a>

                        <!-- New Generation Button -->
                        <button wire:click="resetForm"
                            class="px-3 py-1.5 text-sm rounded-lg bg-purple-500/20 border border-purple-500/30 text-purple-300 hover:bg-purple-500/30 transition-colors flex items-center gap-1.5">
                            <i class="fa-solid fa-plus" style="font-size: 12px;"></i>
                            <span>Tạo mới</span>
                        </button>
                    </div>
                </div>

                <!-- Image Preview -->
                <div class="relative rounded-xl overflow-hidden bg-black/20 flex items-center justify-center"
                    style="max-height: 600px;">
                    <img src="{{ $generatedImageUrl }}" alt="Generated Image"
                        class="max-w-full max-h-[600px] object-contain" loading="lazy">
                </div>

                <!-- Prompt Used -->
                @if($prompt)
                    <div class="mt-4 p-3 bg-white/[0.02] border border-white/[0.05] rounded-lg">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs text-white/50 uppercase tracking-wide">Prompt đã dùng</span>
                            <button @click="copyToClipboard('{{ addslashes($prompt) }}')"
                                class="text-xs text-white/50 hover:text-white transition-colors flex items-center gap-1">
                                <template x-if="!copied">
                                    <span class="flex items-center gap-1">
                                        <i class="fa-regular fa-copy" style="font-size: 10px;"></i> Copy
                                    </span>
                                </template>
                                <template x-if="copied">
                                    <span class="flex items-center gap-1 text-green-400">
                                        <i class="fa-solid fa-check" style="font-size: 10px;"></i> Đã copy
                                    </span>
                                </template>
                            </button>
                        </div>
                        <p class="text-sm text-white/70 leading-relaxed">{{ $prompt }}</p>
                    </div>
                @endif
            </div>
        @endif

        <!-- Tips Section -->
        <div class="mt-6 grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="p-4 bg-white/[0.02] border border-white/[0.05] rounded-xl">
                <div class="flex items-center gap-2 mb-2">
                    <i class="fa-solid fa-lightbulb text-yellow-400" style="font-size: 14px;"></i>
                    <span class="text-sm font-medium text-white/80">Mẹo 1</span>
                </div>
                <p class="text-xs text-white/50">Mô tả chi tiết giúp AI tạo ảnh chính xác hơn</p>
            </div>
            <div class="p-4 bg-white/[0.02] border border-white/[0.05] rounded-xl">
                <div class="flex items-center gap-2 mb-2">
                    <i class="fa-solid fa-language text-blue-400" style="font-size: 14px;"></i>
                    <span class="text-sm font-medium text-white/80">Mẹo 2</span>
                </div>
                <p class="text-xs text-white/50">Có thể viết tiếng Việt, AI sẽ tự dịch</p>
            </div>
            <div class="p-4 bg-white/[0.02] border border-white/[0.05] rounded-xl">
                <div class="flex items-center gap-2 mb-2">
                    <i class="fa-solid fa-palette text-pink-400" style="font-size: 14px;"></i>
                    <span class="text-sm font-medium text-white/80">Mẹo 3</span>
                </div>
                <p class="text-xs text-white/50">Thêm style như "cinematic", "anime", "oil painting"</p>
            </div>
        </div>
    </div>
</div>