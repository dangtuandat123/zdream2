<div class="space-y-4 md:space-y-6">
    
    <!-- Image Upload Slots (Dynamic từ Admin config) -->
    @php
        $imageSlots = $style->image_slots ?? [];
    @endphp
    
    @if(!empty($imageSlots))
        <div class="space-y-3">
            <!-- Upload Limits Warning -->
            <div class="bg-blue-500/10 border border-blue-500/20 rounded-lg px-3 py-2 flex items-start gap-2">
                <i class="fa-solid fa-info-circle text-blue-400 mt-0.5" style="font-size: 12px;"></i>
                <p class="text-xs text-blue-300/80">
                    Định dạng: JPEG, PNG, GIF, WebP. Tối đa <strong>10MB</strong>/ảnh, <strong>25MB</strong> tổng cộng.
                </p>
            </div>
            
            @foreach($imageSlots as $slot)
                @php
                    $slotKey = $slot['key'] ?? 'slot_' . $loop->index;
                    $slotLabel = $slot['label'] ?? 'Ảnh ' . ($loop->index + 1);
                    $slotDescription = $slot['description'] ?? null;
                    $isRequired = $slot['required'] ?? false;
                @endphp
                
                <div class="bg-white/[0.03] border border-white/[0.08] rounded-xl p-4">
                    <label class="block text-sm font-medium text-white/60 mb-2 inline-flex items-center gap-2">
                        <i class="fa-solid fa-image" style="font-size: 14px;"></i>
                        <span>{{ $slotLabel }}</span>
                        @if($isRequired)
                            <span class="text-red-400">*</span>
                        @endif
                    </label>
                    
                    <!-- Slot Description -->
                    @if($slotDescription)
                        <p class="text-xs text-white/40 mb-3 pl-6">{{ $slotDescription }}</p>
                    @endif
                    
                    @if(isset($uploadedImagePreviews[$slotKey]))
                        <!-- Preview uploaded image -->
                        <div class="relative">
                            <img src="{{ $uploadedImagePreviews[$slotKey] }}" alt="{{ $slotLabel }}" class="w-full max-h-40 object-contain rounded-xl bg-black/20">
                            <button 
                                wire:click="removeUploadedImage('{{ $slotKey }}')" 
                                class="absolute top-2 right-2 w-8 h-8 rounded-lg bg-red-500/80 hover:bg-red-500 text-white inline-flex items-center justify-center transition-colors">
                                <i class="fa-solid fa-times" style="font-size: 14px;"></i>
                            </button>
                        </div>
                    @else
                        <!-- Upload area -->
                        <div class="relative">
                            <input 
                                type="file" 
                                wire:model="uploadedImages.{{ $slotKey }}" 
                                accept="image/*"
                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                            <div class="border-2 border-dashed border-white/[0.1] hover:border-purple-500/50 rounded-xl p-4 text-center transition-colors">
                                <div wire:loading.remove wire:target="uploadedImages.{{ $slotKey }}">
                                    <i class="fa-solid fa-cloud-arrow-up text-xl text-white/30 mb-1"></i>
                                    <p class="text-white/50 text-xs">Kéo thả hoặc click để chọn</p>
                                </div>
                                <div wire:loading wire:target="uploadedImages.{{ $slotKey }}" class="inline-flex items-center gap-2 text-purple-400 text-sm">
                                    <i class="fa-solid fa-spinner animate-spin" style="font-size: 14px;"></i>
                                    <span>Đang tải...</span>
                                </div>
                            </div>
                        </div>
                    @endif
                    
                    @error("uploadedImages.{$slotKey}")
                        <p class="text-red-400 text-xs mt-2">{{ $message }}</p>
                    @enderror
                </div>
            @endforeach
        </div>
    @endif

    <!-- ========== DROPDOWN: Option thêm (mặc định mở nếu có options) ========== -->
    @if($optionGroups->isNotEmpty() || $style->allow_user_custom_prompt)
        <div class="bg-white/[0.03] border border-white/[0.08] rounded-xl overflow-hidden" x-data="{ open: {{ $optionGroups->isNotEmpty() ? 'true' : 'false' }} }">
            <button 
                @click="open = !open" 
                class="w-full flex items-center justify-between p-4 text-left hover:bg-white/[0.02] transition-colors">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-purple-500/20 flex items-center justify-center">
                        <i class="fa-solid fa-sliders text-purple-400" style="font-size: 14px;"></i>
                    </div>
                    <div>
                        <span class="text-white font-medium">Option thêm</span>
                        <p class="text-xs text-white/40">Style & mô tả tùy chỉnh</p>
                    </div>
                </div>
                <i class="fa-solid fa-chevron-down text-white/40 transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
            </button>
            
            <div x-show="open" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 transform -translate-y-2"
                 x-transition:enter-end="opacity-100 transform translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 transform translate-y-0"
                 x-transition:leave-end="opacity-0 transform -translate-y-2"
                 class="border-t border-white/[0.05]">
                <div class="p-4 space-y-4">
                    <!-- Options Selection với Thumbnails -->
                    @if($optionGroups->isNotEmpty())
                        @foreach($optionGroups as $groupName => $options)
                            <div wire:key="group-{{ $loop->index }}">
                                <h3 class="text-sm font-medium text-white/60 mb-3 flex items-center gap-2">
                                    <span class="w-1 h-4 bg-gradient-to-b from-purple-400 to-pink-500 rounded-full"></span>
                                    {{ $groupName }}
                                </h3>
                                <div class="flex flex-wrap gap-3">
                                    {{-- Default option --}}
                                    @php 
                                        $isDefaultSelected = !isset($selectedOptions[$groupName]) || $selectedOptions[$groupName] === null;
                                    @endphp
                                    <button 
                                        type="button"
                                        wire:click="selectOption(@js($groupName), null)"
                                        wire:key="option-{{ Str::slug($groupName) }}-default"
                                        class="relative flex flex-col items-center gap-1.5 p-1 rounded-xl transition-all duration-300 
                                            {{ $isDefaultSelected 
                                                ? 'bg-gradient-to-br from-cyan-500/20 to-cyan-500/20 shadow-[0_0_20px_rgba(6,182,212,0.3)]' 
                                                : 'hover:bg-white/[0.05]' }}">
                                        {{-- Selected indicator --}}
                                        @if($isDefaultSelected)
                                            <div class="absolute -top-1 -right-1 w-5 h-5 rounded-full bg-gradient-to-r from-cyan-400 to-cyan-500 flex items-center justify-center shadow-lg z-20">
                                                <i class="fa-solid fa-check text-white" style="font-size: 10px;"></i>
                                            </div>
                                        @endif
                                        {{-- Icon container - hình vuông --}}
                                        <div class="w-14 h-14 sm:w-16 sm:h-16 aspect-square rounded-md transition-all duration-300
                                            {{ $isDefaultSelected 
                                                ? 'bg-gradient-to-br from-cyan-500/30 to-cyan-500/30 border-2 border-cyan-400/50' 
                                                : 'bg-[#1a1a2e] border border-white/10 hover:border-white/20' }}
                                            flex items-center justify-center">
                                            <i class="fa-solid fa-ban {{ $isDefaultSelected ? 'text-cyan-400' : 'text-white/30' }}" style="font-size: 20px;"></i>
                                        </div>
                                        <span class="text-xs font-medium {{ $isDefaultSelected ? 'text-cyan-400' : 'text-white/50' }}">Mặc định</span>
                                    </button>

                                    {{-- Style options --}}
                                    @foreach($options as $option)
                                        @php 
                                            $isSelected = isset($selectedOptions[$groupName]) && $selectedOptions[$groupName] === $option->id;
                                        @endphp
                                        <button 
                                            type="button"
                                            wire:click="selectOption(@js($groupName), {{ $option->id }})"
                                            wire:key="option-{{ $option->id }}"
                                            class="relative flex flex-col items-center gap-1.5 p-1 rounded-xl transition-all duration-300
                                                {{ $isSelected 
                                                    ? 'bg-gradient-to-br from-cyan-500/20 to-cyan-500/20 shadow-[0_0_20px_rgba(6,182,212,0.3)]' 
                                                    : 'hover:bg-white/[0.05]' }}">
                                            {{-- Selected indicator --}}
                                            @if($isSelected)
                                                <div class="absolute -top-1 -right-1 w-5 h-5 rounded-full bg-gradient-to-r from-cyan-400 to-cyan-500 flex items-center justify-center shadow-lg z-20">
                                                    <i class="fa-solid fa-check text-white" style="font-size: 10px;"></i>
                                                </div>
                                            @endif
                                            {{-- Thumbnail container - hình vuông --}}
                                            <div class="w-14 h-14 sm:w-16 sm:h-16 aspect-square rounded-md overflow-hidden transition-all duration-300
                                                {{ $isSelected 
                                                    ? 'border-2 border-cyan-400/50 shadow-[0_0_15px_rgba(6,182,212,0.4)]' 
                                                    : 'border border-white/10 hover:border-white/20' }}
                                                bg-[#1a1a2e] flex items-center justify-center">
                                                @if($option->thumbnail_url)
                                                    <img src="{{ $option->thumbnail_url }}" alt="{{ $option->label }}" class="w-full h-full object-cover">
                                                @elseif($option->icon)
                                                    <i class="{{ $option->icon }} {{ $isSelected ? 'text-cyan-400' : 'text-white/40' }}" style="font-size: 20px;"></i>
                                                @else
                                                    <i class="fa-solid fa-wand-magic-sparkles {{ $isSelected ? 'text-cyan-400' : 'text-white/30' }}" style="font-size: 18px;"></i>
                                                @endif
                                            </div>
                                            <span class="text-xs font-medium max-w-[60px] truncate {{ $isSelected ? 'text-cyan-400' : 'text-white/50' }}">{{ $option->label }}</span>
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    @endif

                    <!-- Custom Input -->
                    @if($style->allow_user_custom_prompt)
                        <div x-data="{ charCount: @js(strlen($customInput ?? '')) }" x-init="charCount = $refs.customTextarea?.value?.length || 0">
                            <label class="block text-sm font-medium text-white/60 mb-2 inline-flex items-center gap-2">
                                <i class="fa-solid fa-pencil" style="font-size: 14px;"></i>
                                <span>Mô tả thêm</span>
                                <span class="text-white/30 text-xs font-normal">(tùy chọn)</span>
                            </label>
                            <textarea 
                                wire:model.live.debounce.300ms="customInput"
                                x-on:input="charCount = $event.target.value.length"
                                maxlength="500"
                                rows="2"
                                placeholder="VD: tóc dài, đeo kính, áo trắng..."
                                class="w-full px-4 py-3 rounded-xl bg-white/[0.03] border border-white/[0.08] text-white/90 placeholder:text-white/40 focus:outline-none focus:ring-2 focus:ring-purple-500/40 focus:border-purple-500/40 transition-all duration-200 resize-none"
                            ></textarea>
                            <div class="flex items-center justify-between mt-2">
                                <span class="text-xs text-white/30">Mô tả chi tiết giúp AI hiểu ý bạn hơn</span>
                                <span class="text-xs" :class="charCount > 450 ? 'text-orange-400' : 'text-white/30'">
                                    <span x-text="charCount">0</span>/500
                                </span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <!-- ========== DROPDOWN: Tùy chọn nâng cao ========== -->
    <div class="bg-white/[0.03] border border-white/[0.08] rounded-xl overflow-hidden" x-data="{ open: false }">
        <button 
            @click="open = !open" 
            class="w-full flex items-center justify-between p-4 text-left hover:bg-white/[0.02] transition-colors">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-cyan-500/20 flex items-center justify-center">
                    <i class="fa-solid fa-gear text-cyan-400" style="font-size: 14px;"></i>
                </div>
                <div>
                    <span class="text-white font-medium">Tùy chọn nâng cao</span>
                    <p class="text-xs text-white/40">Kích thước & chất lượng</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-xs text-white/40 hidden sm:block">{{ $selectedAspectRatio }}</span>
                <i class="fa-solid fa-chevron-down text-white/40 transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
            </div>
        </button>
        
        <div x-show="open" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 transform -translate-y-2"
             x-transition:enter-end="opacity-100 transform translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 transform translate-y-0"
             x-transition:leave-end="opacity-0 transform -translate-y-2"
             class="border-t border-white/[0.05]">
            <div class="p-4 space-y-4">
                <!-- Aspect Ratio Selector -->
                <div>
                    <label class="block text-sm font-medium text-white/60 mb-3 inline-flex items-center gap-2">
                        <i class="fa-solid fa-crop" style="font-size: 14px;"></i>
                        <span>Tỉ lệ khung hình</span>
                    </label>
                    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
                        @foreach($aspectRatios as $ratio => $label)
                            <button 
                                type="button"
                                wire:click="$set('selectedAspectRatio', '{{ $ratio }}')"
                                wire:key="ratio-{{ Str::slug($ratio) }}"
                                class="py-2.5 px-2 text-[10px] sm:text-xs rounded-xl border transition-all duration-200 text-center font-medium
                                    {{ $selectedAspectRatio === $ratio 
                                        ? 'bg-cyan-500/10 border-cyan-500/50 text-cyan-400 shadow-[0_0_15px_rgba(6,182,212,0.15)] ring-1 ring-cyan-500/30' 
                                        : 'bg-white/[0.03] border-white/[0.08] text-white/50 hover:bg-white/[0.06] hover:border-white/[0.15]' 
                                    }}">
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>
                    
                    {{-- B2 FIX: Warning cho non-Gemini models --}}
                    @if(!$supportsImageConfig)
                        <p class="text-yellow-400/70 text-xs mt-2 flex items-center gap-1">
                            <i class="fa-solid fa-info-circle"></i>
                            <span>Tỷ lệ khung hình chỉ được hỗ trợ tốt nhất với Gemini models</span>
                        </p>
                    @endif
                </div>

                <!-- Image Size Selector (chỉ cho Gemini models) -->
                @if($supportsImageConfig)
                    <div>
                        <label class="block text-sm font-medium text-white/60 mb-3 inline-flex items-center gap-2">
                            <i class="fa-solid fa-expand" style="font-size: 14px;"></i>
                            <span>Chất lượng ảnh</span>
                        </label>
                        <div class="grid grid-cols-3 gap-3">
                            @foreach($imageSizes as $size => $label)
                                <button 
                                    type="button"
                                    wire:click="$set('selectedImageSize', '{{ $size }}')"
                                    wire:key="size-{{ Str::slug($size) }}"
                                    class="py-2.5 px-3 text-xs rounded-xl border transition-all duration-200 text-center font-medium
                                        {{ $selectedImageSize === $size 
                                            ? 'bg-cyan-500/10 border-cyan-500/50 text-cyan-400 shadow-[0_0_15px_rgba(6,182,212,0.15)] ring-1 ring-cyan-500/30' 
                                            : 'bg-white/[0.03] border-white/[0.08] text-white/50 hover:bg-white/[0.06] hover:border-white/[0.15]' 
                                        }}">
                                    {{ $label }}
                                </button>
                            @endforeach
                        </div>
                        <p class="text-xs text-white/30 mt-2">Ảnh 4K sẽ tốn thêm thời gian xử lý</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Generate Section -->
    <div class="bg-white/[0.03] border border-white/[0.08] rounded-xl p-4 md:p-5">
        <div class="flex items-center justify-between mb-4 pb-4 border-b border-white/[0.05]">
            <span class="text-white/50">Chi phí</span>
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-gem w-5 h-5 text-cyan-400"></i>
                {{-- [FIX UX-04] Hiển thị decimal nếu không phải số nguyên --}}
                @php $price = $style->price ?? 0; @endphp
                <span class="text-xl font-bold text-white">{{ $price == floor($price) ? number_format($price, 0) : number_format($price, 2) }}</span>
                <span class="text-white/50">Xu</span>
            </div>
        </div>

        @auth
            @if($user && $user->hasEnoughCredits($style->price ?? 0))
                @if(!$isGenerating)
                    <button 
                        wire:click="generate"
                        wire:loading.attr="disabled"
                        class="w-full py-3.5 rounded-xl bg-gradient-to-r from-purple-500 to-pink-500 text-white font-semibold text-base transition-all duration-300 inline-flex items-center justify-center gap-2 hover:shadow-[0_8px_30px_rgba(168,85,247,0.5)] hover:-translate-y-0.5 disabled:opacity-60 disabled:cursor-wait">
                        <span wire:loading.remove wire:target="generate" class="inline-flex items-center gap-2">
                            <i class="fa-solid fa-wand-magic-sparkles" style="font-size: 18px;"></i>
                            <span>Tạo ảnh</span>
                        </span>
                        <span wire:loading wire:target="generate" class="inline-flex items-center gap-2">
                            <i class="fa-solid fa-spinner animate-spin" style="font-size: 18px;"></i>
                            <span>Đang khởi tạo...</span>
                        </span>
                    </button>
                @endif
            @else
                <a href="{{ route('wallet.index') }}" class="w-full py-3.5 rounded-xl bg-white/[0.05] border border-white/[0.1] text-white font-medium text-base inline-flex items-center justify-center gap-2 hover:bg-white/[0.1] transition-all">
                    <i class="fa-solid fa-coins" style="font-size: 18px;"></i>
                    <span>Nạp thêm Xu</span>
                </a>
            @endif
        @else
            <a href="{{ route('login') }}" class="w-full py-3.5 rounded-xl bg-gradient-to-r from-purple-500 to-pink-500 text-white font-semibold text-base inline-flex items-center justify-center gap-2 hover:shadow-[0_8px_30px_rgba(168,85,247,0.5)] transition-all">
                <i class="fa-solid fa-right-to-bracket" style="font-size: 18px;"></i>
                <span>Đăng nhập để tạo ảnh</span>
            </a>
        @endauth

        @auth
            @if($user)
                <p class="text-center text-xs text-white/30 mt-3">
                    Số dư: <span class="text-cyan-400 font-medium">{{ number_format($user->credits, 0) }}</span> Xu
                </p>
            @endif
        @endauth
    </div>

    <!-- Error Message -->
    @if($errorMessage)
        <div class="bg-red-500/10 border border-red-500/30 rounded-xl p-4">
            <div class="flex items-start gap-3 text-red-300">
                <i class="fa-solid fa-circle-exclamation w-5 h-5 flex-shrink-0 mt-0.5"></i>
                <p class="text-sm">{{ $errorMessage }}</p>
            </div>
        </div>
    @endif

    <!-- ==================================================================================== -->
    <!-- FULL SCREEN MODAL: LOADING & RESULT -->
    <!-- ==================================================================================== -->
    @if($isGenerating || $generatedImageUrl)
        <div class="fixed inset-0 z-[9999] flex items-center justify-center p-4 sm:p-6 animate-fade-in">
            <!-- Backdrop (Glassmorphism) -->
            <div class="absolute inset-0 bg-[#0a0a0f]/90 backdrop-blur-xl transition-opacity"></div>

            <!-- Close Button (Top Right) -->
            <button 
                wire:click="resetForm" 
                class="absolute top-6 right-6 z-[10000] w-12 h-12 rounded-full bg-white/10 hover:bg-white/20 text-white/60 hover:text-white flex items-center justify-center transition-all duration-300 hover:rotate-90 backdrop-blur-md border border-white/10 group">
                <i class="fa-solid fa-xmark text-xl group-hover:scale-110 transition-transform"></i>
            </button>

            <!-- MODAL CONTENT CONTAINER -->
            <div class="relative w-full max-w-7xl h-full max-h-[90vh] flex flex-col items-center justify-center z-10">
                
                <!-- STATE 1: LOADING -->
                @if($isGenerating)
                    <div wire:poll.2s="pollImageStatus" 
                         x-data="{ 
                             tipIndex: 0,
                             tips: [
                                 '🎨 AI đang phân tích phong cách...',
                                 '✨ Đang tạo bố cục sáng tạo...',
                                 '🔮 Rendering chi tiết hình ảnh...',
                                 '🌈 Tối ưu màu sắc và ánh sáng...',
                                 '💫 Hoàn thiện nét cuối...'
                             ],
                             messages: [
                                 'Mỗi tác phẩm đều độc nhất vô nhị!',
                                 'AI đang vẽ ước mơ của bạn...',
                                 'Sáng tạo cần thời gian ⏳',
                                 'Đợi chút nhé, sẽ rất xứng đáng!'
                             ],
                             currentMessage: 0
                         }" 
                         x-init="
                             setInterval(() => { tipIndex = (tipIndex + 1) % tips.length }, 3000);
                             setInterval(() => { currentMessage = (currentMessage + 1) % messages.length }, 5000);
                         "
                         class="w-full max-w-2xl text-center space-y-8 p-8">
                        
                        <!-- Animated Avatar/Icon -->
                        <div class="relative inline-flex items-center justify-center">
                            <div class="absolute w-32 h-32 rounded-full bg-purple-500/20 animate-ping-slow"></div>
                            <div class="absolute w-24 h-24 rounded-full bg-pink-500/20 animate-ping-slower"></div>
                            <div class="relative w-24 h-24 rounded-2xl bg-gradient-to-br from-purple-600 to-pink-600 shadow-[0_0_50px_rgba(168,85,247,0.5)] flex items-center justify-center animate-pulse-glow">
                                <i class="fa-solid fa-wand-magic-sparkles text-white text-4xl animate-bounce-slight"></i>
                            </div>
                        </div>

                        <!-- Text Content -->
                        <div class="space-y-4">
                            <h3 class="text-2xl md:text-3xl font-bold text-white tracking-tight"
                                x-text="tips[tipIndex]"
                                x-transition:enter="transition ease-out duration-500"
                                x-transition:enter-start="opacity-0 transform translate-y-2"
                                x-transition:enter-end="opacity-100 transform translate-y-0">
                            </h3>
                            <p class="text-white/50 text-lg" x-text="messages[currentMessage]"></p>
                        </div>

                        <!-- Progress Bar -->
                        <div class="w-full max-w-md mx-auto">
                            <div class="h-1.5 bg-white/10 rounded-full overflow-hidden backdrop-blur-sm">
                                <div class="h-full rounded-full bg-gradient-to-r from-purple-500 via-pink-500 to-purple-500 bg-[length:200%_100%] animate-gradient-x"></div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- STATE 2: RESULT IMAGE -->
                @if($generatedImageUrl && !$isGenerating)
                    <div class="w-full h-full flex flex-col md:flex-row gap-6 md:gap-8 items-center justify-center animate-zoom-in">
                        
                        <!-- Image Container (Center/Left) -->
                        <div class="relative flex-1 w-full h-full max-h-[80vh] flex items-center justify-center bg-[#0a0a0f]/50 rounded-2xl border border-white/10 p-2 md:p-4 backdrop-blur-sm shadow-2xl">
                            <img src="{{ $generatedImageUrl }}" 
                                 alt="Generated Image" 
                                 class="max-w-full max-h-full object-contain rounded-lg shadow-lg select-none"
                                 oncontextmenu="return false;">
                                 
                            <!-- Zoom Button (Optional) -->
                            <a href="{{ $generatedImageUrl }}" target="_blank" class="absolute bottom-6 right-6 w-10 h-10 rounded-full bg-black/50 hover:bg-black/70 text-white flex items-center justify-center backdrop-blur-md border border-white/10 transition-all">
                                <i class="fa-solid fa-expand"></i>
                            </a>
                        </div>

                        <!-- Actions Sidebar (Right/Bottom) -->
                        <div class="w-full md:w-80 flex-shrink-0 space-y-4 bg-white/[0.03] backdrop-blur-md border border-white/10 p-6 rounded-2xl">
                            <div class="text-center mb-6">
                                <div class="w-12 h-12 rounded-full bg-green-500/20 text-green-400 flex items-center justify-center mx-auto mb-3">
                                    <i class="fa-solid fa-check text-xl"></i>
                                </div>
                                <h3 class="text-xl font-bold text-white">Hoàn tất!</h3>
                                <p class="text-sm text-white/50">Ảnh của bạn đã sẵn sàng</p>
                            </div>

                            <!-- Download Button -->
                            @if($lastImageId)
                                <a href="{{ route('history.download', $lastImageId) }}" 
                                   class="block w-full py-4 rounded-xl bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-500 hover:to-pink-500 text-white font-bold text-center shadow-lg hover:shadow-purple-500/30 transition-all transform hover:-translate-y-0.5">
                                    <i class="fa-solid fa-download mr-2"></i> Tải xuống (HD)
                                </a>
                            @else
                                <a href="{{ $generatedImageUrl }}" download
                                   class="block w-full py-4 rounded-xl bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-500 hover:to-pink-500 text-white font-bold text-center shadow-lg hover:shadow-purple-500/30 transition-all transform hover:-translate-y-0.5">
                                    <i class="fa-solid fa-download mr-2"></i> Tải xuống
                                </a>
                            @endif

                            <!-- Action Buttons Grid -->
                            <div class="grid grid-cols-2 gap-3">
                                <button wire:click="resetForm" class="py-3 rounded-xl bg-white/5 hover:bg-white/10 border border-white/10 text-white font-medium transition-all">
                                    <i class="fa-solid fa-rotate-right mr-2"></i> Tạo lại
                                </button>
                                <a href="{{ route('history.index') }}" class="py-3 rounded-xl bg-white/5 hover:bg-white/10 border border-white/10 text-white font-medium text-center transition-all">
                                    <i class="fa-solid fa-clock-rotate-left mr-2"></i> Lịch sử
                                </a>
                            </div>

                            <!-- Share Section -->
                            <div class="pt-4 border-t border-white/10">
                                <p class="text-xs text-white/40 mb-3 text-center">Chia sẻ tác phẩm này</p>
                                <div class="flex justify-center gap-3">
                                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($generatedImageUrl) }}" target="_blank" class="w-10 h-10 rounded-full bg-[#1877F2]/20 text-[#1877F2] hover:bg-[#1877F2] hover:text-white flex items-center justify-center transition-all">
                                        <i class="fa-brands fa-facebook-f"></i>
                                    </a>
                                    <a href="https://twitter.com/intent/tweet?url={{ urlencode($generatedImageUrl) }}" target="_blank" class="w-10 h-10 rounded-full bg-white/10 text-white hover:bg-white hover:text-black flex items-center justify-center transition-all">
                                        <i class="fa-brands fa-x-twitter"></i>
                                    </a>
                                    <button onclick="navigator.clipboard.writeText('{{ $generatedImageUrl }}'); alert('Đã sao chép link!')" class="w-10 h-10 rounded-full bg-green-500/20 text-green-400 hover:bg-green-500 hover:text-white flex items-center justify-center transition-all">
                                        <i class="fa-solid fa-link"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <style>
            .animate-fade-in { animation: fadeIn 0.3s ease-out; }
            .animate-zoom-in { animation: zoomIn 0.4s cubic-bezier(0.16, 1, 0.3, 1); }
            .animate-bounce-slight { animation: bounceSlight 2s infinite; }
            
            @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
            @keyframes zoomIn { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
            @keyframes bounceSlight { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-5px); } }
        </style>
    @endif

    <!-- User's History với Style này (Mobile only) -->
    @if($userStyleImages->isNotEmpty())
        @php
            $mobileImageData = $userStyleImages->map(fn($img) => [
                'url' => $img->image_url,
                'id' => $img->id,
                'download' => route('history.download', $img),
                'delete' => route('history.destroy', $img),
            ])->toArray();
        @endphp
        <div class="lg:hidden bg-white/[0.03] border border-white/[0.08] rounded-xl overflow-hidden">
            <div class="flex items-center justify-between p-4 border-b border-white/[0.05]">
                <div class="flex items-center gap-2 text-white/60">
                    <i class="fa-solid fa-clock-rotate-left" style="font-size: 14px;"></i>
                    <span class="font-medium text-sm">Ảnh đã tạo với style này</span>
                </div>
                <a href="{{ route('history.index') }}" class="text-xs text-cyan-400 hover:text-cyan-300 transition-colors">
                    Xem tất cả
                </a>
            </div>
            <div class="p-3">
                <div class="grid grid-cols-3 gap-2">
                    @foreach($userStyleImages as $index => $img)
                        <button 
                            onclick="openLightboxWithActions({{ $index }}, {{ json_encode($mobileImageData) }})"
                            class="group relative aspect-square rounded-lg overflow-hidden bg-white/[0.05] focus:outline-none"
                        >
                            <img src="{{ $img->image_url }}" alt="Generated" class="w-full h-full object-cover" onerror="this.src='/images/placeholder.svg'">
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>
