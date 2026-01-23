<div class="space-y-4 md:space-y-6">
    
    {{-- Options Selection --}}
    @if($optionGroups->isNotEmpty())
        <div class="space-y-3 md:space-y-4">
            @foreach($optionGroups as $groupName => $options)
                <div class="glass-card p-4">
                    <h3 class="text-sm font-medium text-white/60 mb-3 flex items-center gap-2">
                        <span class="w-1 h-4 bg-gradient-to-b from-primary-400 to-accent-purple rounded-full"></span>
                        {{ $groupName }}
                    </h3>
                    
                    {{-- Horizontal scroll on mobile --}}
                    <div class="flex flex-wrap gap-2">
                        @foreach($options as $option)
                            <button 
                                wire:click="selectOption('{{ $groupName }}', {{ $option->id }})"
                                class="option-chip touch-target
                                    {{ isset($selectedOptions[$groupName]) && $selectedOptions[$groupName] === $option->id 
                                        ? 'option-chip-active' 
                                        : '' 
                                    }}">
                                {{ $option->label }}
                            </button>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Custom Input (if allowed) --}}
    @if($style->allow_user_custom_prompt)
        <div class="glass-card p-4">
            <label class="block text-sm font-medium text-white/60 mb-2 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                </svg>
                Mô tả thêm
            </label>
            <textarea 
                wire:model.defer="customInput"
                rows="2"
                placeholder="VD: tóc dài, đeo kính, áo trắng..."
                class="glass-input resize-none"
            ></textarea>
        </div>
    @endif

    {{-- Generate Section --}}
    <div class="glass-card p-4 md:p-5">
        {{-- Cost Display --}}
        <div class="flex items-center justify-between mb-4 pb-4 border-b border-white/5">
            <span class="text-white/50">Chi phí</span>
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-accent-cyan" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.736 6.979C9.208 6.193 9.696 6 10 6c.304 0 .792.193 1.264.979a1 1 0 001.715-1.029C12.279 4.784 11.232 4 10 4s-2.279.784-2.979 1.95c-.285.475-.507 1-.67 1.55H6a1 1 0 000 2h.013a9.358 9.358 0 000 1H6a1 1 0 100 2h.351c.163.55.385 1.075.67 1.55C7.721 15.216 8.768 16 10 16s2.279-.784 2.979-1.95a1 1 0 10-1.715-1.029c-.472.786-.96.979-1.264.979-.304 0-.792-.193-1.264-.979a4.265 4.265 0 01-.264-.521H10a1 1 0 100-2H8.017a7.36 7.36 0 010-1H10a1 1 0 100-2H8.472c.08-.185.167-.36.264-.521z"/>
                </svg>
                <span class="text-xl font-bold text-white">{{ number_format($style->price, 0) }}</span>
            </div>
        </div>

        {{-- Action Button --}}
        @auth
            @if($user && $user->hasEnoughCredits($style->price))
                <button 
                    wire:click="generate"
                    wire:loading.attr="disabled"
                    @if($isGenerating) disabled @endif
                    class="btn-primary w-full text-base py-3.5 touch-target {{ $isGenerating ? 'opacity-60 cursor-wait' : 'animate-glow-pulse' }}">
                    <span wire:loading.remove wire:target="generate" class="flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                        </svg>
                        Tạo ảnh
                    </span>
                    <span wire:loading wire:target="generate" class="flex items-center justify-center gap-2">
                        <div class="spinner"></div>
                        Đang tạo...
                    </span>
                </button>
            @else
                <a href="{{ route('wallet.index') }}" class="btn-secondary w-full text-base py-3.5 touch-target">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Nạp thêm Credits
                </a>
            @endif
        @else
            <a href="{{ route('login') }}" class="btn-primary w-full text-base py-3.5 touch-target">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                </svg>
                Đăng nhập để tạo ảnh
            </a>
        @endauth

        {{-- Balance reminder --}}
        @auth
            @if($user)
                <p class="text-center text-xs text-white/30 mt-3">
                    Số dư hiện tại: <span class="text-accent-cyan font-medium">{{ number_format($user->credits, 0) }}</span> credits
                </p>
            @endif
        @endauth
    </div>

    {{-- Error Message --}}
    @if($errorMessage)
        <div class="glass-card p-4 border-red-500/30 bg-red-500/5 animate-scale-in">
            <div class="flex items-start gap-3 text-red-300">
                <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <p class="text-sm">{{ $errorMessage }}</p>
            </div>
        </div>
    @endif

    {{-- Loading State --}}
    @if($isGenerating)
        <div class="glass-card p-6 md:p-8 text-center animate-scale-in">
            <div class="inline-flex items-center justify-center w-16 h-16 mb-4 rounded-2xl bg-gradient-to-br from-primary-500/20 to-accent-purple/20">
                <div class="w-8 h-8 border-3 border-white/10 border-t-primary-500 rounded-full animate-spin"></div>
            </div>
            <p class="text-white/80 font-medium mb-1">AI đang sáng tạo... ✨</p>
            <p class="text-sm text-white/40">Chờ khoảng 10-30 giây</p>
        </div>
    @endif

    {{-- Result Image --}}
    @if($generatedImageUrl)
        <div class="glass-card overflow-hidden animate-scale-in">
            {{-- Header --}}
            <div class="flex items-center justify-between p-4 border-b border-white/5">
                <div class="flex items-center gap-2 text-accent-green">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span class="font-semibold">Ảnh đã tạo!</span>
                </div>
                <button wire:click="resetForm" class="text-sm text-white/40 hover:text-white transition-colors">
                    Tạo lại
                </button>
            </div>
            
            {{-- Image --}}
            <div class="p-3 md:p-4">
                <img src="{{ $generatedImageUrl }}" 
                     alt="Generated Image"
                     class="w-full rounded-xl">
            </div>
            
            {{-- Actions --}}
            <div class="p-3 md:p-4 pt-0">
                <a href="{{ $generatedImageUrl }}" 
                   target="_blank"
                   download
                   class="btn-primary w-full py-3 touch-target">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Tải xuống
                </a>
            </div>
        </div>
    @endif
</div>
