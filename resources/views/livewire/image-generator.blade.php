<div class="space-y-4 md:space-y-6">
    
    <!-- Image Upload Slots (Dynamic từ Admin config) -->
    @php
        $imageSlots = $style->image_slots ?? [['key' => 'default', 'label' => 'Ảnh mẫu (tùy chọn)', 'required' => false]];
    @endphp
    
    @if(!empty($imageSlots))
        <div class="space-y-3">
            @foreach($imageSlots as $slot)
                @php
                    $slotKey = $slot['key'] ?? 'slot_' . $loop->index;
                    $slotLabel = $slot['label'] ?? 'Ảnh ' . ($loop->index + 1);
                    $isRequired = $slot['required'] ?? false;
                @endphp
                
                <div class="bg-white/[0.03] border border-white/[0.08] rounded-xl p-4">
                    <label class="block text-sm font-medium text-white/60 mb-3 inline-flex items-center gap-2">
                        <i class="fa-solid fa-image" style="font-size: 14px;"></i>
                        <span>{{ $slotLabel }}</span>
                        @if($isRequired)
                            <span class="text-red-400">*</span>
                        @endif
                    </label>
                    
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

    <!-- Options Selection -->
    @if($optionGroups->isNotEmpty())
        <div class="space-y-3 md:space-y-4">
            @foreach($optionGroups as $groupName => $options)
                <div class="bg-white/[0.03] border border-white/[0.08] rounded-xl p-4">
                    <h3 class="text-sm font-medium text-white/60 mb-3 flex items-center gap-2">
                        <span class="w-1 h-4 bg-gradient-to-b from-purple-400 to-pink-500 rounded-full"></span>
                        {{ $groupName }}
                    </h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach($options as $option)
                            <button 
                                wire:click="selectOption('{{ $groupName }}', {{ $option->id }})"
                                class="px-4 py-2.5 text-sm rounded-xl border cursor-pointer select-none transition-all duration-200
                                    {{ isset($selectedOptions[$groupName]) && $selectedOptions[$groupName] === $option->id 
                                        ? 'bg-purple-500/20 border-purple-500/50 text-purple-300 shadow-[0_0_20px_rgba(168,85,247,0.2)]' 
                                        : 'bg-white/[0.03] border-white/[0.08] text-white/70 hover:bg-white/[0.06] hover:border-white/[0.15]' 
                                    }}">
                                {{ $option->label }}
                            </button>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Custom Input -->
    @if($style->allow_user_custom_prompt)
        <div class="bg-white/[0.03] border border-white/[0.08] rounded-xl p-4">
            <label class="block text-sm font-medium text-white/60 mb-2 inline-flex items-center gap-2">
                <i class="fa-solid fa-pencil" style="font-size: 14px;"></i>
                <span>Mô tả thêm</span>
            </label>
            <textarea 
                wire:model.defer="customInput"
                rows="2"
                placeholder="VD: tóc dài, đeo kính, áo trắng..."
                class="w-full px-4 py-3 rounded-xl bg-white/[0.03] border border-white/[0.08] text-white/90 placeholder:text-white/40 focus:outline-none focus:ring-2 focus:ring-purple-500/40 focus:border-purple-500/40 transition-all duration-200 resize-none"
            ></textarea>
        </div>
    @endif

    <!-- Aspect Ratio Selector -->
    <div class="bg-white/[0.03] border border-white/[0.08] rounded-xl p-4">
        <label class="block text-sm font-medium text-white/60 mb-3 inline-flex items-center gap-2">
            <i class="fa-solid fa-crop" style="font-size: 14px;"></i>
            <span>Tỉ lệ khung hình</span>
        </label>
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-2">
            @foreach($aspectRatios as $ratio => $label)
                <button 
                    wire:click="$set('selectedAspectRatio', '{{ $ratio }}')"
                    class="py-2 px-2 text-xs rounded-xl border cursor-pointer select-none transition-all duration-200 text-center
                        {{ $selectedAspectRatio === $ratio 
                            ? 'bg-purple-500/20 border-purple-500/50 text-purple-300 shadow-[0_0_20px_rgba(168,85,247,0.2)]' 
                            : 'bg-white/[0.03] border-white/[0.08] text-white/70 hover:bg-white/[0.06] hover:border-white/[0.15]' 
                        }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>
    </div>

    <!-- Image Size Selector (chỉ cho Gemini models) -->
    @if(str_contains($style->openrouter_model_id, 'gemini'))
        <div class="bg-white/[0.03] border border-white/[0.08] rounded-xl p-4">
            <label class="block text-sm font-medium text-white/60 mb-3 inline-flex items-center gap-2">
                <i class="fa-solid fa-expand" style="font-size: 14px;"></i>
                <span>Chất lượng ảnh</span>
            </label>
            <div class="grid grid-cols-3 gap-2">
                @foreach($imageSizes as $size => $label)
                    <button 
                        wire:click="$set('selectedImageSize', '{{ $size }}')"
                        class="py-2.5 px-3 text-sm rounded-xl border cursor-pointer select-none transition-all duration-200 text-center
                            {{ $selectedImageSize === $size 
                                ? 'bg-cyan-500/20 border-cyan-500/50 text-cyan-300 shadow-[0_0_20px_rgba(6,182,212,0.2)]' 
                                : 'bg-white/[0.03] border-white/[0.08] text-white/70 hover:bg-white/[0.06] hover:border-white/[0.15]' 
                            }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>
            <p class="text-xs text-white/30 mt-2">Ảnh 4K sẽ tốn thêm thời gian xử lý</p>
        </div>
    @endif

    <!-- Generate Section -->
    <div class="bg-white/[0.03] border border-white/[0.08] rounded-xl p-4 md:p-5">
        <div class="flex items-center justify-between mb-4 pb-4 border-b border-white/[0.05]">
            <span class="text-white/50">Chi phí</span>
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-gem w-5 h-5 text-cyan-400"></i>
                <span class="text-xl font-bold text-white">{{ number_format($style->price, 0) }}</span>
                <span class="text-white/50">Xu</span>
            </div>
        </div>

        @auth
            @if($user && $user->hasEnoughCredits($style->price))
                <button 
                    wire:click="generate"
                    wire:loading.attr="disabled"
                    @if($isGenerating) disabled @endif
                    class="w-full py-3.5 rounded-xl bg-gradient-to-r from-purple-500 to-pink-500 text-white font-semibold text-base transition-all duration-300 inline-flex items-center justify-center gap-2 {{ $isGenerating ? 'opacity-60 cursor-wait' : 'hover:shadow-[0_8px_30px_rgba(168,85,247,0.5)] hover:-translate-y-0.5' }}">
                    <span wire:loading.remove wire:target="generate" class="inline-flex items-center gap-2">
                        <i class="fa-solid fa-wand-magic-sparkles" style="font-size: 18px;"></i>
                        <span>Tạo ảnh</span>
                    </span>
                    <span wire:loading wire:target="generate" class="inline-flex items-center gap-2">
                        <i class="fa-solid fa-spinner animate-spin" style="font-size: 18px;"></i>
                        <span>Đang tạo...</span>
                    </span>
                </button>
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

    <!-- Loading State -->
    @if($isGenerating)
        <div class="bg-white/[0.03] border border-white/[0.08] rounded-xl p-6 md:p-8 text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 mb-4 rounded-2xl bg-gradient-to-br from-purple-500/20 to-pink-500/20">
                <i class="fa-solid fa-spinner w-8 h-8 text-purple-400 animate-spin"></i>
            </div>
            <p class="text-white/80 font-medium mb-1">AI đang sáng tạo... ✨</p>
            <p class="text-sm text-white/40">Chờ khoảng 10-30 giây</p>
        </div>
    @endif

    <!-- Result Image -->
    @if($generatedImageUrl)
        <div class="bg-white/[0.03] border border-white/[0.08] rounded-xl overflow-hidden">
            <div class="flex items-center justify-between p-4 border-b border-white/[0.05]">
                <div class="flex items-center gap-2 text-green-400">
                    <i class="fa-solid fa-circle-check w-5 h-5"></i>
                    <span class="font-semibold">Ảnh đã tạo!</span>
                </div>
                <button wire:click="resetForm" class="text-sm text-white/40 hover:text-white transition-colors">
                    Tạo lại
                </button>
            </div>
            <div class="p-3 md:p-4">
                <img src="{{ $generatedImageUrl }}" alt="Generated Image" class="w-full rounded-xl">
            </div>
            <div class="p-3 md:p-4 pt-0">
                <a href="{{ $generatedImageUrl }}" target="_blank" download class="w-full py-3 rounded-xl bg-gradient-to-r from-purple-500 to-pink-500 text-white font-semibold inline-flex items-center justify-center gap-2 hover:shadow-[0_8px_30px_rgba(168,85,247,0.5)] transition-all">
                    <i class="fa-solid fa-download" style="font-size: 18px;"></i>
                    <span>Tải xuống</span>
                </a>
            </div>
        </div>
    @endif
</div>
