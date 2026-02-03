<div class="space-y-4 md:space-y-6">
    @php $step = 1; @endphp
    
    <!-- Image Upload Slots (Dynamic từ Admin config) -->
    @php
        $imageSlots = $style->image_slots ?? [];
    @endphp
    
    @if(!empty($imageSlots))
        <div class="space-y-3">
            <!-- Step Header -->
            <div class="flex items-center gap-3 px-1">
                <div class="flex items-center justify-center w-7 h-7 rounded-full bg-gradient-to-r from-purple-500 to-pink-500 text-[#d3d6db] text-xs font-bold shadow-lg shadow-purple-500/30">
                    {{ $step++ }}
                </div>
                <h3 class="text-[#d3d6db] font-bold text-sm uppercase tracking-wide">Tải ảnh lên</h3>
            </div>
            <!-- Upload Limits Warning -->
            <div class="bg-blue-500/10 border border-blue-500/20 rounded-lg px-3 py-2 flex items-start gap-2">
                <i class="fa-solid fa-info-circle text-blue-400 mt-0.5" style="font-size: 12px;"></i>
                <p class="text-xs text-blue-300/80">
                    Định dạng: JPEG, PNG, GIF, WebP. Tối đa <strong>10MB</strong>/ảnh, <strong>25MB</strong> tổng cộng.
                </p>
            </div>
            @if($supportsImageInput && $maxInputImages > 0)
                <div class="bg-purple-500/10 border border-purple-500/20 rounded-lg px-3 py-2 flex items-start gap-2">
                    <i class="fa-solid fa-layer-group text-purple-400 mt-0.5" style="font-size: 12px;"></i>
                    <p class="text-xs text-purple-300/80">
                        Model hiện tại hỗ trợ tối đa <strong>{{ $maxInputImages }}</strong> ảnh tham chiếu (bao gồm ảnh hệ thống).
                    </p>
                </div>
            @endif

            @foreach($imageSlots as $slot)
                @php
                    $slotKey = $slot['key'] ?? 'slot_' . $loop->index;
                    $slotLabel = $slot['label'] ?? 'Ảnh ' . ($loop->index + 1);
                    $slotDescription = $slot['description'] ?? null;
                    $isRequired = $slot['required'] ?? false;
                @endphp

                <div class="bg-[#1b1c21] border border-[#2a2b30] rounded-xl p-4">
                    <label class="block text-sm font-medium text-white/60 mb-2 inline-flex items-center gap-2">
                        <i class="fa-solid fa-image" style="font-size: 14px;"></i>
                        <span>{{ $slotLabel }}</span>
                        @if($isRequired)
                            <span class="text-red-400">*</span>
                        @else
                            <span class="text-white/30 text-xs font-normal">(Tùy chọn)</span>
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
                                class="absolute top-2 right-2 w-8 h-8 rounded-lg bg-red-500/80 hover:bg-red-500 text-[#d3d6db] inline-flex items-center justify-center transition-colors">
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
        <!-- Step Header -->
        <div class="flex items-center gap-3 px-1 mb-2">
            <div class="flex items-center justify-center w-7 h-7 rounded-full bg-gradient-to-r from-purple-500 to-pink-500 text-[#d3d6db] text-xs font-bold shadow-lg shadow-purple-500/30">
                {{ $step++ }}
            </div>
            <h3 class="text-[#d3d6db] font-bold text-sm uppercase tracking-wide">
                Tùy chỉnh chi tiết
                <span class="text-white/40 text-xs font-normal normal-case ml-1">(Tùy chọn)</span>
            </h3>
        </div>
        <div class="bg-[#1b1c21] border border-[#2a2b30] rounded-xl overflow-hidden" x-data="{ open: {{ $optionGroups->isNotEmpty() ? 'true' : 'false' }} }">
            <button 
                @click="open = !open" 
                class="w-full flex items-center justify-between p-4 text-left hover:bg-[#1b1c21] transition-colors">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-purple-500/20 flex items-center justify-center">
                        <i class="fa-solid fa-sliders text-purple-400" style="font-size: 14px;"></i>
                    </div>
                    <div>
                        <span class="text-[#d3d6db] font-medium">Option thêm</span>
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
                 class="border-t border-[#2a2b30]">
                <div class="p-4 space-y-4">
                    <!-- Options Selection với Thumbnails -->
                    @if($optionGroups->isNotEmpty())
                        @foreach($optionGroups as $groupName => $options)
                                                                                                                                                                <div wire:key="group-{{ $loop->index }}">
                                                                                                                                                                    <h3 class="text-sm font-medium text-white/60 mb-3 flex items-center gap-2">
                                                                                                                                                                        <span class="w-1 h-4 bg-gradient-to-b from-purple-400 to-pink-500 rounded-full"></span>
                                                                                                                                                                        {{ $groupName }}
                                                                                                                                                                    </h3>
                                                                                                                                                                    <div class="grid grid-cols-3 sm:grid-cols-4 lg:grid-cols-6 gap-3">
                                                                                                                                                                        {{-- Default option --}}
                                                                                                                                                                        @php 
                                                                                                                                                                            $isDefaultSelected = !isset($selectedOptions[$groupName]) || $selectedOptions[$groupName] === null;
                                                                                                                                                                        @endphp
                                                                                                                                                                        <button 
                                                                                                                                                                            type="button"
                                                                                                                                                                            wire:click="selectOption(@js($groupName), null)"
                                                                                                                                                                            wire:key="option-{{ Str::slug($groupName) }}-default"
                                                                                                                                                                            class="relative w-full min-w-0 flex flex-col items-center gap-1.5 p-1.5 rounded-xl transition-all duration-300 
                                                                                                                                                                                {{ $isDefaultSelected
                            ? 'bg-gradient-to-br from-cyan-500/20 to-cyan-500/20 shadow-[0_0_20px_rgba(6,182,212,0.3)]'
                            : 'hover:bg-white/[0.05]' }}">
                                                                                                                                                                            {{-- Selected indicator --}}
                                                                                                                                                                            @if($isDefaultSelected)
                                                                                                                                                                                <div class="absolute -top-1 -right-1 w-5 h-5 rounded-full bg-gradient-to-r from-cyan-400 to-cyan-500 flex items-center justify-center shadow-lg z-20">
                                                                                                                                                                                    <i class="fa-solid fa-check text-[#d3d6db]" style="font-size: 10px;"></i>
                                                                                                                                                                                </div>
                                                                                                                                                                            @endif
                                                                                                                                                                            {{-- Icon container - hình vuông --}}
                                                                                                                                                                            <div class="w-full aspect-square max-w-[120px] sm:max-w-[140px] mx-auto rounded-md transition-all duration-300
                                                                                                                                                                                {{ $isDefaultSelected
                            ? 'bg-gradient-to-br from-cyan-500/30 to-cyan-500/30 border-2 border-cyan-400/50'
                            : 'bg-[#1a1a2e] border border-white/10 hover:border-white/20' }}
                                                                                                                                                                                flex items-center justify-center">
                                                                                                                                                                                <i class="fa-solid fa-ban {{ $isDefaultSelected ? 'text-cyan-400' : 'text-white/30' }}" style="font-size: 20px;"></i>
                                                                                                                                                                            </div>
                                                                                                                                                                            <span class="w-full text-center text-[11px] sm:text-xs font-medium leading-snug whitespace-normal break-words min-h-8 {{ $isDefaultSelected ? 'text-cyan-400' : 'text-white/50' }}">Mặc định</span>
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
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                class="relative w-full min-w-0 flex flex-col items-center gap-1.5 p-1.5 rounded-xl transition-all duration-300
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    {{ $isSelected
                                                                                                                                                                            ? 'bg-gradient-to-br from-cyan-500/20 to-cyan-500/20 shadow-[0_0_20px_rgba(6,182,212,0.3)]'
                                                                                                                                                                            : 'hover:bg-white/[0.05]' }}">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                {{-- Selected indicator --}}
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                @if($isSelected)
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <div class="absolute -top-1 -right-1 w-5 h-5 rounded-full bg-gradient-to-r from-cyan-400 to-cyan-500 flex items-center justify-center shadow-lg z-20">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <i class="fa-solid fa-check text-[#d3d6db]" style="font-size: 10px;"></i>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                @endif
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                {{-- Thumbnail container - hình vuông --}}
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <div class="w-full aspect-square max-w-[120px] sm:max-w-[140px] mx-auto rounded-md overflow-hidden transition-all duration-300
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
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <span class="w-full text-center text-[11px] sm:text-xs font-medium leading-snug whitespace-normal break-words min-h-8 {{ $isSelected ? 'text-cyan-400' : 'text-white/50' }}">{{ $option->label }}</span>
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
                                <span class="text-white/30 text-xs font-normal">(Tùy chọn)</span>
                            </label>
                            <textarea 
                                wire:model.live.debounce.300ms="customInput"
                                x-on:input="charCount = $event.target.value.length"
                                maxlength="500"
                                rows="2"
                                placeholder="VD: tóc dài, đeo kính, áo trắng..."
                                class="w-full px-4 py-3 rounded-xl bg-[#1b1c21] border border-[#2a2b30] text-white/90 placeholder:text-white/40 focus:outline-none focus:ring-2 focus:ring-purple-500/40 focus:border-purple-500/40 transition-all duration-200 resize-none"
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
    <!-- Step Header -->
    <div class="flex items-center gap-3 px-1 mb-2">
        <div class="flex items-center justify-center w-7 h-7 rounded-full bg-gradient-to-r from-purple-500 to-pink-500 text-[#d3d6db] text-xs font-bold shadow-lg shadow-purple-500/30">
            {{ $step++ }}
        </div>
    <h3 class="text-[#d3d6db] font-bold text-sm uppercase tracking-wide">
        Tuỳ chỉnh nâng cao
        <span class="text-white/40 text-xs font-normal normal-case ml-1">(Không bắt buộc)</span>
    </h3>
    </div>
    <div class="bg-[#1b1c21] border border-[#2a2b30] rounded-xl overflow-hidden" x-data="{ open: false }">
        <button 
            @click="open = !open" 
            class="w-full flex items-center justify-between p-4 text-left hover:bg-[#1b1c21] transition-colors">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-cyan-500/20 flex items-center justify-center">
                    <i class="fa-solid fa-gear text-cyan-400" style="font-size: 14px;"></i>
                </div>
                <div>
                    <span class="text-[#d3d6db] font-medium">Tuỳ chỉnh nâng cao</span>
                    <p class="text-xs text-white/40">Tỉ lệ và kích thước</p>
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
             class="border-t border-[#2a2b30]">
            <div class="p-4 space-y-4">
                @if($supportsWidthHeight)
                    <div class="grid grid-cols-2 sm:inline-flex rounded-xl bg-black/20 border border-cyan-500/30 p-1 w-full sm:w-auto">
                        <button type="button"
                                wire:click="$set('sizeMode','ratio')"
                                class="w-full sm:w-auto px-3 py-1.5 rounded-lg text-xs font-semibold transition-all
                                    {{ $sizeMode === 'ratio' ? 'bg-cyan-500/30 text-cyan-200 shadow-[0_0_12px_rgba(6,182,212,0.35)]' : 'text-white/60 hover:text-white/80' }}">
                            Theo tỉ lệ
                        </button>
                        <button type="button"
                                wire:click="$set('sizeMode','custom')"
                                class="w-full sm:w-auto px-3 py-1.5 rounded-lg text-xs font-semibold transition-all
                                    {{ $sizeMode === 'custom' ? 'bg-cyan-500/30 text-cyan-200 shadow-[0_0_12px_rgba(6,182,212,0.35)]' : 'text-white/60 hover:text-white/80' }}">
                            Nhập kích thước
                        </button>
                    </div>
                @endif

                <!-- Aspect Ratio Selector -->
                @if(!$supportsWidthHeight || $sizeMode === 'ratio')
                    <div>
                        <label class="block text-sm font-semibold text-white/80 mb-3 inline-flex items-center gap-2">
                            <i class="fa-solid fa-crop" style="font-size: 14px;"></i>
                            <span>Tỉ lệ</span>
                        </label>
                        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
                            @foreach($aspectRatios as $ratio => $label)
                                                                                                                                                                                                                                                        @php
                                                                                                                                                                                                                                                            $dim = $ratioDimensions[$ratio] ?? null;
                                                                                                                                                                                                                                                            $dimText = $dim ? ($dim['width'] . '×' . $dim['height'] . ' px') : null;
                                                                                                                                                                                                                                                        @endphp
                                                                                                                                                                                                                                                        <button 
                                                                                                                                                                                                                                                            type="button"
                                                                                                                                                                                                                                                            wire:click="$set('selectedAspectRatio', '{{ $ratio }}')"
                                                                                                                                                                                                                                                            wire:key="ratio-{{ Str::slug($ratio) }}"
                                                                                                                                                                                                                                                            class="py-2.5 px-2 text-[11px] sm:text-xs rounded-xl border transition-all duration-200 text-center font-semibold
                                                                                                                                                                                                                                                                {{ $selectedAspectRatio === $ratio
                                ? 'bg-cyan-500/10 border-cyan-500/50 text-cyan-400 shadow-[0_0_15px_rgba(6,182,212,0.15)] ring-1 ring-cyan-500/30'
                                : 'bg-[#1b1c21] border-[#2a2b30] text-white/60 hover:bg-white/[0.06] hover:border-white/[0.15]' 
                                                                                                                                                                                                                                                                }}">
                                                                                                                                                                                                                                                            <span class="block">{{ $label }}</span>
                                                                                                                                                                                                                                                            @if($dimText)
                                                                                                                                                                                                                                                                <span class="block text-[10px] text-white/40 mt-0.5">{{ $dimText }}</span>
                                                                                                                                                                                                                                                            @endif
                                                                                                                                                                                                                                                        </button>
                            @endforeach
                        </div>



                    </div>
                @endif

                @if($supportsWidthHeight && $sizeMode === 'custom')
                    <div>
                        <label class="block text-sm font-semibold text-white/80 mb-2 inline-flex items-center gap-2">
                            <i class="fa-solid fa-ruler-combined" style="font-size: 12px;"></i>
                            <span>Kích thước (rộng × cao)</span>
                        </label>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <input type="number" min="{{ $dimensionMin }}" max="{{ $dimensionMax }}" step="{{ $dimensionMultiple }}"
                                       wire:model.live="customWidth"
                                       placeholder="Rộng (px)"
                                       class="w-full px-3 py-2 rounded-lg bg-[#1b1c21] border border-[#2a2b30] text-white/90 text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500/40">
                            </div>
                            <div>
                                <input type="number" min="{{ $dimensionMin }}" max="{{ $dimensionMax }}" step="{{ $dimensionMultiple }}"
                                       wire:model.live="customHeight"
                                       placeholder="Cao (px)"
                                       class="w-full px-3 py-2 rounded-lg bg-[#1b1c21] border border-[#2a2b30] text-white/90 text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500/40">
                            </div>
                        </div>
                        <p class="text-xs text-white/40 mt-2">
                            Để trống = tự động. Giới hạn: {{ $dimensionMin }}–{{ $dimensionMax }}px, bội {{ $dimensionMultiple }}
                        </p>
                    </div>
                @endif

                <!-- Image Size Selector (chỉ cho Gemini models) -->
                @if($supportsImageConfig)
                    <div class="bg-[#1b1c21] border border-[#2a2b30] rounded-xl p-4 md:p-5">
                        <label class="block text-sm font-semibold text-white/80 mb-3 inline-flex items-center gap-2">
                            <i class="fa-solid fa-expand" style="font-size: 14px;"></i>
                            <span>Chất lượng</span>
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
                                : 'bg-[#1b1c21] border-[#2a2b30] text-white/50 hover:bg-white/[0.06] hover:border-white/[0.15]' 
                                                                                                                                                                                                                                                                    }}">
                                                                                                                                                                                                                                                                {{ $label }}
                                                                                                                                                                                                                                                            </button>
                            @endforeach
                        </div>
                        <p class="text-xs text-white/30 mt-2">4K = chất lượng cao nhưng lâu hơn</p>
                    </div>
                @endif


                @if($supportsSteps || $supportsGuidance || $supportsSafetyTolerance || $supportsImagePromptStrength)
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                        @if($supportsSteps)
                            @php
                                $stepsMin = $stepsRange['min'] ?? 1;
                                $stepsMax = $stepsRange['max'] ?? 50;
                            @endphp
                            <div class="bg-[#1b1c21] border border-[#2a2b30] rounded-xl p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <label class="text-sm font-semibold text-white/80 inline-flex items-center gap-2">
                                        <i class="fa-solid fa-stairs" style="font-size: 12px;"></i>
                                        <span>Độ chi tiết</span>
                                        <i class="fa-solid fa-circle-question text-white/30" style="font-size: 12px;" title="Càng cao thì ảnh càng chi tiết nhưng xử lý lâu hơn."></i>
                                    </label>
                                    <span class="text-xs text-white/50">{{ $steps ?? ($stepsRange['default'] ?? $stepsMin) }}</span>
                                </div>
                                <input type="range"
                                       min="{{ $stepsMin }}"
                                       max="{{ $stepsMax }}"
                                       step="1"
                                       wire:model.live="steps"
                                       class="w-full accent-cyan-500">
                                <div class="flex justify-between text-[10px] text-white/30 mt-1">
                                    <span>{{ $stepsMin }}</span>
                                    <span>{{ $stepsMax }}</span>
                                </div>
                                <p class="text-xs text-white/40 mt-2">
                                    Tăng lên thì ảnh chi tiết hơn nhưng lâu hơn (chỉ vài mẫu có tuỳ chọn này).
                                </p>
                            </div>
                        @endif

                        @if($supportsGuidance)
                            @php
                                $guidanceMin = $guidanceRange['min'] ?? 1.5;
                                $guidanceMax = $guidanceRange['max'] ?? 10;
                            @endphp
                            <div class="bg-[#1b1c21] border border-[#2a2b30] rounded-xl p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <label class="text-sm font-semibold text-white/80 inline-flex items-center gap-2">
                                        <i class="fa-solid fa-sliders" style="font-size: 12px;"></i>
                                        <span>Độ bám theo mô tả</span>
                                        <i class="fa-solid fa-circle-question text-white/30" style="font-size: 12px;" title="Cao hơn thì ảnh bám theo mô tả hơn."></i>
                                    </label>
                                    <span class="text-xs text-white/50">{{ $guidance ?? ($guidanceRange['default'] ?? $guidanceMin) }}</span>
                                </div>
                                <input type="range"
                                       min="{{ $guidanceMin }}"
                                       max="{{ $guidanceMax }}"
                                       step="0.1"
                                       wire:model.live="guidance"
                                       class="w-full accent-cyan-500">
                                <div class="flex justify-between text-[10px] text-white/30 mt-1">
                                    <span>{{ $guidanceMin }}</span>
                                    <span>{{ $guidanceMax }}</span>
                                </div>
                                <p class="text-xs text-white/40 mt-2">
                                    Cao hơn → ảnh bám theo mô tả hơn; thấp hơn → tự do sáng tạo hơn.
                                </p>
                            </div>
                        @endif


                        @if($supportsImagePromptStrength)
                            @php
                                $ipsMin = $imagePromptStrengthRange['min'] ?? 0;
                                $ipsMax = $imagePromptStrengthRange['max'] ?? 1;
                            @endphp
                            <div class="bg-[#1b1c21] border border-[#2a2b30] rounded-xl p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <label class="text-sm font-semibold text-white/80 inline-flex items-center gap-2">
                                        <i class="fa-solid fa-blender" style="font-size: 12px;"></i>
                                        <span>Ảnh tham chiếu ảnh hưởng</span>
                                        <i class="fa-solid fa-circle-question text-white/30" style="font-size: 12px;" title="0 = ảnh tham chiếu ảnh hưởng ít, 1 = ảnh ảnh hưởng mạnh."></i>
                                    </label>
                                    <span class="text-xs text-white/50">{{ $imagePromptStrength ?? ($imagePromptStrengthRange['default'] ?? $ipsMin) }}</span>
                                </div>
                                <input type="range"
                                       min="{{ $ipsMin }}"
                                       max="{{ $ipsMax }}"
                                       step="0.05"
                                       wire:model.live="imagePromptStrength"
                                       class="w-full accent-cyan-500">
                                <div class="flex justify-between text-[10px] text-white/30 mt-1">
                                    <span>{{ $ipsMin }}</span>
                                    <span>{{ $ipsMax }}</span>
                                </div>
                                <p class="text-xs text-white/40 mt-2">
                                    Thấp → ảnh tham chiếu ảnh hưởng ít; cao → ảnh tham chiếu ảnh hưởng mạnh.
                                </p>
                            </div>
                        @endif
                    </div>
                @endif

                @if($supportsPromptUpsampling || $supportsRaw)
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                        @if($supportsPromptUpsampling)
                            <div class="bg-[#1b1c21] border border-[#2a2b30] rounded-xl p-4">
                                <label class="flex items-center gap-2 text-sm text-white/70">
                                    <input type="checkbox" wire:model.live="promptUpsampling"
                                           class="w-4 h-4 rounded bg-[#1b1c21] border-white/[0.15] text-cyan-500 focus:ring-cyan-500/40">
                                    <span>Tự làm rõ mô tả</span>
                                    <i class="fa-solid fa-circle-question text-white/30" style="font-size: 12px;" title="Hệ thống tự thêm chi tiết khi bạn mô tả ngắn."></i>
                                </label>
                                <p class="text-xs text-white/40 mt-2">
                                    Hệ thống tự thêm chi tiết khi bạn mô tả ngắn, giúp ảnh đẹp hơn.
                                </p>
                            </div>
                        @endif
                        @if($supportsRaw)
                            <div class="bg-[#1b1c21] border border-[#2a2b30] rounded-xl p-4">
                                <label class="flex items-center gap-2 text-sm text-white/70">
                                    <input type="checkbox" wire:model.live="raw"
                                           class="w-4 h-4 rounded bg-[#1b1c21] border-white/[0.15] text-cyan-500 focus:ring-cyan-500/40">
                                    <span>Phong cách tự nhiên</span>
                                    <i class="fa-solid fa-circle-question text-white/30" style="font-size: 12px;" title="Ảnh trông tự nhiên, ít hiệu ứng 'vẽ'."></i>
                                </label>
                                <p class="text-xs text-white/40 mt-2">
                                    Ảnh trông tự nhiên, ít “vẽ”. Chỉ một số mẫu hỗ trợ.
                                </p>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Generate Section -->
    <!-- Step Header -->
    <div class="flex items-center gap-3 px-1 mb-2">
        <div class="flex items-center justify-center w-7 h-7 rounded-full bg-gradient-to-r from-purple-500 to-pink-500 text-[#d3d6db] text-xs font-bold shadow-lg shadow-purple-500/30">
            {{ $step++ }}
        </div>
        <h3 class="text-[#d3d6db] font-bold text-sm uppercase tracking-wide">Hoàn tất & Tạo ảnh</h3>
    </div>
    <div class="bg-[#1b1c21] border border-[#2a2b30] rounded-xl p-4 md:p-5">
        <div class="flex items-center justify-between mb-4 pb-4 border-b border-[#2a2b30]">
            <span class="text-white/50">Chi phí</span>
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-gem w-5 h-5 text-cyan-400"></i>
                {{-- [FIX UX-04] Hiển thị decimal nếu không phải số nguyên --}}
                @php $price = $style->price ?? 0; @endphp
                <span class="text-xl font-bold text-[#d3d6db]">{{ $price == floor($price) ? number_format($price, 0) : number_format($price, 2) }}</span>
                <span class="text-white/50">Xu</span>
            </div>
        </div>

        @php
            $requiredSlotKeys = collect($imageSlots ?? [])
                ->filter(fn($slot) => !empty($slot['required']))
                ->pluck('key')
                ->filter()
                ->values()
                ->all();
            $hasAllRequiredImages = true;
            if (!empty($requiredSlotKeys)) {
                foreach ($requiredSlotKeys as $requiredKey) {
                    if (empty($uploadedImages[$requiredKey])) {
                        $hasAllRequiredImages = false;
                        break;
                    }
                }
            }
        @endphp

        @auth
            @if($user && $user->hasEnoughCredits($style->price ?? 0))
                @if(!$isGenerating)
                    <button 
                        wire:click="generate"
                        wire:loading.attr="disabled"
                        @disabled(!$hasAllRequiredImages)
                        class="w-full py-3.5 rounded-xl bg-gradient-to-r from-purple-500 to-pink-500 text-[#d3d6db] font-semibold text-base transition-all duration-300 inline-flex items-center justify-center gap-2 hover:shadow-[0_8px_30px_rgba(168,85,247,0.5)] hover:-translate-y-0.5 disabled:opacity-40 disabled:cursor-not-allowed">
                        <span wire:loading.remove wire:target="generate" class="inline-flex items-center gap-2">
                            <i class="fa-solid fa-wand-magic-sparkles" style="font-size: 18px;"></i>
                            <span>Tạo ảnh</span>
                        </span>
                        <span wire:loading wire:target="generate" class="inline-flex items-center gap-2">
                            <i class="fa-solid fa-spinner animate-spin" style="font-size: 18px;"></i>
                            <span>ZDream đang tạo ảnh...</span>
                        </span>
                    </button>
                    @if(!$hasAllRequiredImages)
                        <p class="text-xs text-orange-300/80 mt-2 text-center">
                            Vui lòng tải đủ ảnh bắt buộc để tiếp tục.
                        </p>
                    @endif
                @endif
            @else
                <a href="{{ route('wallet.index') }}" class="w-full py-3.5 rounded-xl bg-white/[0.05] border border-white/[0.1] text-[#d3d6db] font-medium text-base inline-flex items-center justify-center gap-2 hover:bg-white/[0.1] transition-all">
                    <i class="fa-solid fa-coins" style="font-size: 18px;"></i>
                    <span>Nạp thêm Xu</span>
                </a>
            @endif
        @else
            <a href="{{ route('login') }}" class="w-full py-3.5 rounded-xl bg-gradient-to-r from-purple-500 to-pink-500 text-[#d3d6db] font-semibold text-base inline-flex items-center justify-center gap-2 hover:shadow-[0_8px_30px_rgba(168,85,247,0.5)] transition-all">
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
    
    {{-- 1. IMMEDIATE LOADING STATE (Client-side, shows immediately on click) --}}
    @teleport('body')
        <div wire:loading.flex wire:target="generate"
             x-data
             x-init="
                 new MutationObserver(() => {
                     if ($el.style.display !== 'none') {
                         document.body.classList.add('overflow-hidden');
                     } else {
                         // Chỉ bỏ lock nếu main modal không xuất hiện (tránh flicker)
                         if (!document.getElementById('main-image-modal')) {
                             document.body.classList.remove('overflow-hidden');
                         }
                     }
                 }).observe($el, { attributes: true, attributeFilter: ['style'] });
             "
             class="fixed inset-0 z-[99999] flex items-center justify-center animate-fade-in"
             style="display: none;">
            <!-- Backdrop -->
            <div class="absolute inset-0 bg-[#000000]/80 backdrop-blur-xl transition-opacity"></div>
            
            <!-- Content -->
            <div class="relative z-10 w-full max-w-2xl text-center space-y-8 md:space-y-12 p-4">
                <!-- Spinner -->
                <div class="relative flex items-center justify-center">
                    <div class="absolute w-48 h-48 rounded-full bg-purple-500/20 blur-3xl animate-pulse"></div>
                    <div class="relative w-24 h-24 md:w-32 md:h-32">
                        <svg class="animate-spin w-full h-full text-transparent" viewBox="0 0 100 100">
                            <defs>
                                <linearGradient id="spinner-gradient-loading" x1="0%" y1="0%" x2="100%" y2="0%">
                                    <stop offset="0%" stop-color="#a855f7" />
                                    <stop offset="100%" stop-color="#ec4899" />
                                </linearGradient>
                            </defs>
                            <circle cx="50" cy="50" r="45" stroke="url(#spinner-gradient-loading)" stroke-width="4" fill="none" stroke-linecap="round" stroke-dasharray="200" stroke-dashoffset="100" />
                        </svg>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <i class="fa-solid fa-wand-magic-sparkles text-2xl md:text-3xl text-[#d3d6db] animate-bounce-slight"></i>
                        </div>
                    </div>
                </div>
                <!-- Text -->
                <div class="space-y-4 px-4">
                    <h3 class="text-xl md:text-3xl font-bold text-[#d3d6db] tracking-tight">ZDream đang tạo ảnh...</h3>
                    <p class="text-white/50 text-base md:text-lg">Vui lòng chờ trong giây lát...</p>
                </div>
            </div>
        </div>
    @endteleport

    {{-- 2. REAL MODAL (Server-side state) --}}
    @if($isGenerating || $generatedImageUrl)
        @teleport('body')
            <div 
                id="main-image-modal"
                x-data="{ 
                    init() { 
                        document.body.classList.add('overflow-hidden'); 
                    },
                    close() {
                        document.body.classList.remove('overflow-hidden');
                        $wire.closeModal();
                    },
                    reset() {
                        document.body.classList.remove('overflow-hidden');
                        $wire.resetForm();
                    }
                }"
                x-init="init()"
                x-destroy="document.body.classList.remove('overflow-hidden')"
                class="fixed inset-0 z-[99999] flex items-center justify-center animate-fade-in"
            >
                <!-- Backdrop (Enhanced Glassmorphism) -->
                <div class="absolute inset-0 bg-[#000000]/80 backdrop-blur-xl transition-opacity"></div>

                <!-- Close Button (ẩn khi đang tạo ảnh) -->
                @if(!$isGenerating)
                    <button 
                        @click="close()"
                        class="absolute top-4 right-4 md:top-6 md:right-6 z-[100000] w-10 h-10 md:w-12 md:h-12 rounded-full bg-white/10 hover:bg-white/20 text-white/60 hover:text-[#d3d6db] flex items-center justify-center transition-all duration-300 hover:rotate-90 backdrop-blur-md border border-white/10 group shadow-lg">
                        <i class="fa-solid fa-xmark text-lg md:text-xl group-hover:scale-110 transition-transform"></i>
                    </button>
                @endif

                <!-- MODAL CONTENT -->
                <div class="relative w-full h-full flex flex-col items-center justify-center z-10 p-4 md:p-6">

                    <!-- LOADING STATE -->
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
                             class="w-full max-w-2xl text-center space-y-8 md:space-y-12">

                            <!-- Spinner -->
                            <div class="relative flex items-center justify-center">
                                <div class="absolute w-48 h-48 rounded-full bg-purple-500/20 blur-3xl animate-pulse"></div>
                                <div class="relative w-24 h-24 md:w-32 md:h-32">
                                    <svg class="animate-spin w-full h-full text-transparent" viewBox="0 0 100 100">
                                        <defs>
                                            <linearGradient id="spinner-gradient-real" x1="0%" y1="0%" x2="100%" y2="0%">
                                                <stop offset="0%" stop-color="#a855f7" />
                                                <stop offset="100%" stop-color="#ec4899" />
                                            </linearGradient>
                                        </defs>
                                        <circle cx="50" cy="50" r="45" stroke="url(#spinner-gradient-real)" stroke-width="4" fill="none" stroke-linecap="round" stroke-dasharray="200" stroke-dashoffset="100" />
                                    </svg>
                                    <div class="absolute inset-0 flex items-center justify-center">
                                        <i class="fa-solid fa-wand-magic-sparkles text-2xl md:text-3xl text-[#d3d6db] animate-bounce-slight"></i>
                                    </div>
                                </div>
                            </div>

                            <!-- Text -->
                            <div class="space-y-4 px-4">
                                <h3 class="text-xl md:text-3xl font-bold text-[#d3d6db] tracking-tight h-8 md:h-10"
                                    x-text="tips[tipIndex]"
                                    x-transition:enter="transition ease-out duration-500"
                                    x-transition:enter-start="opacity-0 transform translate-y-2"
                                    x-transition:enter-end="opacity-100 transform translate-y-0">
                                </h3>
                                <p class="text-white/50 text-base md:text-lg" x-text="messages[currentMessage]"></p>
                            </div>

                            <!-- Progress Bar -->
                            <div class="w-full max-w-xs md:max-w-md mx-auto px-4">
                                <div class="h-1.5 bg-white/10 rounded-full overflow-hidden backdrop-blur-sm">
                                    <div class="h-full rounded-full bg-gradient-to-r from-purple-500 via-pink-500 to-purple-500 bg-[length:200%_100%] animate-gradient-x"></div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- RESULT STATE -->
                    @if($generatedImageUrl && !$isGenerating)
                        <div class="w-full h-full flex flex-col md:flex-row gap-6 md:gap-8 items-center justify-center animate-zoom-in max-w-7xl mx-auto">

                            <!-- Image Container -->
                            <div class="relative flex-1 w-full h-full max-h-[70vh] md:max-h-[85vh] flex items-center justify-center bg-[#000000]/30 rounded-2xl border border-white/10 p-2 md:p-4 backdrop-blur-md shadow-2xl overflow-hidden">
                                <img src="{{ $generatedImageUrl }}" 
                                     alt="Generated Image" 
                                     class="w-full h-full object-contain rounded-lg shadow-lg select-none"
                                     oncontextmenu="return false;">
                            </div>

                            <!-- Actions Sidebar -->
                            <div class="w-full md:w-80 flex-shrink-0 space-y-4 bg-white/[0.05] backdrop-blur-xl border border-white/10 p-5 md:p-6 rounded-2xl md:self-center shadow-2xl">
                                <div class="text-center mb-4 md:mb-6">
                                    <div class="w-12 h-12 rounded-full bg-green-500/20 text-green-400 flex items-center justify-center mx-auto mb-3 animate-bounce-slight">
                                        <i class="fa-solid fa-check text-xl"></i>
                                    </div>
                                    <h3 class="text-xl font-bold text-[#d3d6db]">Hoàn tất!</h3>
                                    <p class="text-sm text-white/50">Ảnh của bạn đã sẵn sàng</p>
                                </div>

                                <!-- Download Button -->
                                @if($lastImageId)
                                    <a href="{{ route('history.download', $lastImageId) }}" 
                                       class="block w-full py-3.5 md:py-4 rounded-xl bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-500 hover:to-pink-500 text-[#d3d6db] font-bold text-center shadow-lg hover:shadow-purple-500/30 transition-all transform hover:-translate-y-0.5">
                                        <i class="fa-solid fa-download mr-2"></i> Tải xuống (HD)
                                    </a>
                                @else
                                    <a href="{{ $generatedImageUrl }}" download
                                       class="block w-full py-3.5 md:py-4 rounded-xl bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-500 hover:to-pink-500 text-[#d3d6db] font-bold text-center shadow-lg hover:shadow-purple-500/30 transition-all transform hover:-translate-y-0.5">
                                        <i class="fa-solid fa-download mr-2"></i> Tải xuống
                                    </a>
                                @endif

                                <!-- Action Buttons Grid -->
                                <div class="grid grid-cols-2 gap-3">
                                    <button @click="close()" class="py-3 rounded-xl bg-white/5 hover:bg-white/10 border border-white/10 text-[#d3d6db] font-medium transition-all hover:border-white/20">
                                        <i class="fa-solid fa-pen-to-square mr-2"></i> Chỉnh sửa
                                    </button>
                                    <button @click="reset()" class="py-3 rounded-xl bg-white/5 hover:bg-white/10 border border-white/10 text-[#d3d6db] font-medium transition-all hover:border-white/20">
                                        <i class="fa-solid fa-rotate-right mr-2"></i> Tạo mới
                                    </button>
                                </div>

                                <!-- Share Section -->
                                <div class="pt-4 border-t border-white/10">
                                    <p class="text-xs text-white/40 mb-3 text-center">Chia sẻ tác phẩm này</p>
                                    <div class="flex justify-center gap-3">
                                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($generatedImageUrl) }}" target="_blank" class="w-10 h-10 rounded-full bg-[#1877F2]/20 text-[#1877F2] hover:bg-[#1877F2] hover:text-[#d3d6db] flex items-center justify-center transition-all">
                                            <i class="fa-brands fa-facebook-f"></i>
                                        </a>
                                        <a href="https://twitter.com/intent/tweet?url={{ urlencode($generatedImageUrl) }}" target="_blank" class="w-10 h-10 rounded-full bg-white/10 text-[#d3d6db] hover:bg-white hover:text-black flex items-center justify-center transition-all">
                                            <i class="fa-brands fa-x-twitter"></i>
                                        </a>
                                        <button onclick="navigator.clipboard.writeText('{{ $generatedImageUrl }}'); alert('Đã sao chép link!')" class="w-10 h-10 rounded-full bg-green-500/20 text-green-400 hover:bg-green-500 hover:text-[#d3d6db] flex items-center justify-center transition-all">
                                            <i class="fa-solid fa-link"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endteleport
    @endif

    <style>
        .animate-fade-in { animation: fadeIn 0.3s ease-out; }
        .animate-zoom-in { animation: zoomIn 0.4s cubic-bezier(0.16, 1, 0.3, 1); }
        .animate-bounce-slight { animation: bounceSlight 2s infinite; }
        .animate-gradient-x { background-size: 200% 100%; animation: gradientX 2s linear infinite; }
        
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes zoomIn { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
        @keyframes bounceSlight { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-5px); } }
        @keyframes gradientX { 0% { background-position: 0% 50%; } 100% { background-position: 100% 50%; } }
    </style>

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
        <div class="lg:hidden bg-[#1b1c21] border border-[#2a2b30] rounded-xl overflow-hidden">
            <div class="flex items-center justify-between p-4 border-b border-[#2a2b30]">
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
