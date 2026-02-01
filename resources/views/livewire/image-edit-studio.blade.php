<div class="text-white/95 font-sans selection:bg-blue-500/30"
     @if($isProcessing) wire:poll.2s="pollStatus" @endif>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
        {{-- ========================================== --}}
        {{-- HEADER: Title + Actions --}}
        {{-- ========================================== --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
            <div class="flex items-center gap-4">
                <a href="/" class="group flex items-center justify-center w-10 h-10 rounded-xl bg-white/[0.05] border border-white/[0.1] hover:bg-white/[0.1] hover:border-white/[0.2] transition-all">
                    <svg class="w-5 h-5 text-white/70 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-white to-white/70 tracking-tight">
                        Studio Chỉnh Sửa AI
                    </h1>
                    <p class="text-white/50 text-sm mt-0.5">Biến ý tưởng thành hiện thực</p>
                </div>
            </div>
            

        </div>

        {{-- ========================================== --}}
        {{-- MESSAGES --}}
        {{-- ========================================== --}}
        @if($errorMessage)
            <div class="mb-4 p-4 bg-red-500/10 backdrop-blur-md border border-red-500/20 rounded-xl text-red-200 flex items-center gap-3 animate-in fade-in slide-in-from-top-2">
                <div class="w-8 h-8 rounded-full bg-red-500/20 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                </div>
                <div>
                    <p class="text-sm font-medium">{{ $errorMessage }}</p>
                </div>
                <button wire:click="$set('errorMessage', '')" class="ml-auto text-red-300 hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
        @endif

        {{-- ========================================== --}}
        {{-- MAIN CONTENT GRID --}}
        {{-- ========================================== --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            
            {{-- ============================== --}}
            {{-- LEFT: Canvas Area (2 cols) --}}
            {{-- ============================== --}}
            <div class="lg:col-span-2 space-y-4">
                
                {{-- Upload Zone (No image yet) --}}
                @if(empty($sourceImage))
                    <div class="bg-white/[0.03] backdrop-blur-xl border border-white/[0.08] rounded-2xl p-6 shadow-2xl">
                        <div class="relative group"
                             x-data="{ isDragging: false }"
                             @dragover.prevent="isDragging = true"
                             @dragleave.prevent="isDragging = false"
                             @drop.prevent="isDragging = false; @this.upload('uploadedImage', $event.dataTransfer.files[0])">
                            
                            <label for="image-upload" 
                                   class="flex flex-col items-center justify-center w-full h-80 rounded-2xl border-2 border-dashed transition-all duration-300 cursor-pointer"
                                   :class="isDragging ? 'border-blue-500 bg-blue-500/10' : 'border-white/[0.15] bg-white/[0.02] hover:bg-white/[0.05] hover:border-white/[0.25]'">
                                
                                <div class="flex flex-col items-center p-6 text-center">
                                    <div class="w-16 h-16 mb-4 rounded-2xl bg-gradient-to-br from-blue-500/20 to-purple-500/20 flex items-center justify-center border border-white/10 group-hover:scale-110 transition-transform">
                                        <svg class="w-8 h-8 text-white/70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                    <h3 class="text-lg font-semibold text-white/90 mb-1">Tải ảnh lên để bắt đầu</h3>
                                    <p class="text-white/50 text-sm mb-4">Kéo thả hoặc nhấp để chọn file</p>
                                    <span class="px-4 py-2 bg-blue-600/20 text-blue-400 rounded-lg text-sm font-medium border border-blue-500/30 group-hover:bg-blue-600 group-hover:text-white transition-all">Chọn ảnh</span>
                                </div>
                                
                                <input type="file" id="image-upload" wire:model="uploadedImage" accept="image/*" class="hidden">
                            </label>
                        </div>

                        {{-- Upload loading --}}
                        <div wire:loading wire:target="uploadedImage" class="mt-4 flex items-center justify-center gap-2 text-blue-400">
                            <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span class="text-sm">Đang tải ảnh...</span>
                        </div>
                    </div>
                @else
                    {{-- Canvas Container --}}
                    <div class="bg-white/[0.03] backdrop-blur-xl border border-white/[0.08] rounded-2xl overflow-hidden shadow-2xl"
                         wire:key="editor-canvas-container"
                         x-data="canvasEditor()" 
                         x-init="init(); $nextTick(() => { if($wire.sourceImage) loadImage({ src: $wire.sourceImage }) })"
                         @image-loaded.window="loadImage($event.detail)"
                         @clear-canvas-mask.window="clearMask()"
                         @reset-canvas.window="resetCanvas()">
                        
                        {{-- Canvas Wrapper - Image scales to fit, no scroll --}}
                        <div class="relative bg-black/40 flex items-center justify-center bg-[url('https://zdream.vn/images/transparent-bg.png')] bg-repeat p-4 cursor-crosshair"
                             @mousedown="startDraw($event)">
                            {{-- Change Image Button (top-right corner) --}}
                            <label for="image-upload-canvas" 
                                   class="absolute top-3 right-3 z-20 flex items-center gap-2 px-3 py-2 rounded-lg bg-black/60 backdrop-blur-sm border border-white/20 hover:bg-black/80 hover:border-white/30 cursor-pointer transition-all group">
                                <svg class="w-4 h-4 text-white/70 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <span class="text-sm font-medium text-white/80 group-hover:text-white">Đổi ảnh</span>
                            </label>
                            <input type="file" id="image-upload-canvas" wire:model="uploadedImage" accept="image/*" class="hidden">
                            
                            <div wire:ignore class="relative inline-block" style="line-height: 0; max-width: 100%; max-height: 70vh;">
                                <canvas x-ref="imageLayer" class="max-w-full max-h-[70vh] w-auto h-auto" style="display: block;"></canvas>
                                <canvas x-ref="drawLayer" 
                                        class="absolute inset-0 w-full h-full cursor-crosshair touch-none"
                                        style="z-index: 10; opacity: 0.6;"
                                        @mousedown="startDraw($event)"
                                        @mousemove.window="draw($event)" 
                                        @mouseup.window="stopDraw($event)"
                                        @touchstart="startDraw($event.touches[0])"
                                        @touchmove="draw($event.touches[0]); $event.preventDefault()"
                                        @touchend="stopDraw()">
                                </canvas>
                            </div>

                            {{-- Processing Overlay (Queue-based) --}}
                            @if($isProcessing)
                            <div class="absolute inset-0 bg-[#0a0a0f]/90 flex items-center justify-center z-50 backdrop-blur-md">
                                <div class="text-center p-6">
                                    <div class="relative w-14 h-14 mx-auto mb-3">
                                        <div class="absolute inset-0 rounded-full border-4 border-blue-500/30"></div>
                                        <div class="absolute inset-0 rounded-full border-4 border-t-blue-500 animate-spin"></div>
                                    </div>
                                    <p class="text-blue-400 font-medium animate-pulse">AI đang xử lý...</p>
                                    <p class="text-white/40 text-xs mt-2">Đang làm việc trong nền, vui lòng đợi</p>
                                </div>
                            </div>
                            @endif
                        </div>

                        {{-- Toolbar (Only show when mask is needed) --}}
                        @if(in_array($editMode, ['replace', 'background']))
                            <div class="p-3 bg-black/40 border-t border-white/[0.08]">
                                <div class="flex flex-wrap items-center gap-2">
                                    {{-- Tools --}}
                                    <div class="flex items-center gap-1 bg-white/[0.05] rounded-lg p-1">
                                        <button type="button" @click="setTool('brush')" 
                                                :class="tool === 'brush' ? 'bg-blue-500 text-white shadow-lg' : 'text-white/60 hover:bg-white/[0.1] hover:text-white'" 
                                                class="p-2 rounded-lg transition-all" title="Brush">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                        </button>
                                        <button type="button" @click="setTool('rect')" 
                                                :class="tool === 'rect' ? 'bg-blue-500 text-white shadow-lg' : 'text-white/60 hover:bg-white/[0.1] hover:text-white'" 
                                                class="p-2 rounded-lg transition-all" title="Rectangle">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4h16v16H4z"></path></svg>
                                        </button>
                                    </div>

                                    {{-- Brush Size Slider --}}
                                    <div x-show="tool === 'brush'" class="flex items-center gap-2 bg-white/[0.05] px-3 py-1.5 rounded-lg">
                                        <span class="text-xs text-white/50">Size</span>
                                        <input type="range" x-model.number="brushSize" min="5" max="100" class="w-20 h-1 bg-white/20 rounded-lg appearance-none cursor-pointer accent-blue-500">
                                        <span class="text-xs font-mono text-white/70 w-6" x-text="brushSize"></span>
                                    </div>

                                    {{-- Spacer --}}
                                    <div class="flex-1"></div>

                                    {{-- Actions --}}
                                    <div class="flex items-center gap-1">
                                        <button type="button" @click="undo()" :disabled="historyStep <= 0" class="p-2 rounded-lg text-white/50 hover:bg-white/[0.1] hover:text-white disabled:opacity-30 disabled:cursor-not-allowed transition-all" title="Undo">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" /></svg>
                                        </button>
                                        <button type="button" @click="redo()" :disabled="historyStep >= history.length - 1" class="p-2 rounded-lg text-white/50 hover:bg-white/[0.1] hover:text-white disabled:opacity-30 disabled:cursor-not-allowed transition-all" title="Redo">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 10h-10a8 8 0 00-8 8v2M21 10l-6 6m6-6l-6-6" /></svg>
                                        </button>
                                        <button type="button" @click="clearMask()" class="p-2 rounded-lg text-red-400 hover:bg-red-500/20 transition-all" title="Xóa mask">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Instructions based on mode --}}
                    <div class="p-3 bg-blue-500/5 border border-blue-500/10 rounded-xl flex items-start gap-3">
                        <svg class="w-5 h-5 text-blue-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <p class="text-sm text-white/70">
                            @switch($editMode)
                                @case('replace')
                                    <span class="text-cyan-300 font-medium">Thay thế:</span> Tô vùng muốn xóa, sau đó mô tả vật thể mới.
                                    @break
                                @case('text')
                                    <span class="text-blue-300 font-medium">Sửa text:</span> Không cần vẽ. Mô tả: Change "OLD" to "NEW".
                                    @break
                                @case('background')
                                    <span class="text-cyan-300 font-medium">Background:</span> Tô lên chủ thể chính để giữ lại, AI thay nền xung quanh.
                                    @break
                                @case('expand')
                                    <span class="text-blue-300 font-medium">Expand:</span> Điều chỉnh slider bên phải để mở rộng ảnh.
                                    @break
                            @endswitch
                        </p>
                    </div>
                @endif
            </div>

            {{-- ============================== --}}
            {{-- RIGHT: Controls Panel --}}
            {{-- ============================== --}}
            <div class="lg:col-span-1">
                <div class="bg-white/[0.03] backdrop-blur-xl rounded-2xl border border-white/[0.08] shadow-2xl overflow-hidden lg:sticky lg:top-4">
                    
                    {{-- Mode Tabs --}}
                    <div class="p-4 border-b border-white/[0.08]">
                        <label class="block text-xs font-medium text-white/40 uppercase tracking-wider mb-3">Chế độ chỉnh sửa</label>
                        <div class="grid grid-cols-4 gap-1 bg-white/[0.05] rounded-xl p-1">
                            @php
                                $modes = [
                                    'replace' => ['icon' => 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14', 'label' => 'Thay'],
                                    'text' => ['icon' => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z', 'label' => 'Text'],
                                    'background' => ['icon' => 'M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z', 'label' => 'Nền'],
                                    'expand' => ['icon' => 'M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4', 'label' => 'Mở'],
                                ];
                            @endphp
                            @foreach($modes as $mode => $config)
                                <button wire:click="setEditMode('{{ $mode }}')"
                                        class="flex flex-col items-center gap-1 py-2.5 rounded-lg transition-all {{ $editMode === $mode ? 'bg-blue-600 text-white shadow-lg' : 'text-white/60 hover:bg-white/[0.1] hover:text-white' }}">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $config['icon'] }}" />
                                    </svg>
                                    <span class="text-[10px] font-medium">{{ $config['label'] }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Mode-specific Options --}}
                    <div class="p-4 border-b border-white/[0.08]">
                        @if($editMode === 'expand')
                            {{-- Expand Sliders --}}
                            <div class="space-y-4">
                                <label class="block text-xs font-medium text-white/40 uppercase tracking-wider">Mở rộng (pixels)</label>
                                
                                {{-- Visual Preview with actual image --}}
                                <div class="relative w-full aspect-video bg-black/30 rounded-lg border border-white/10 flex items-center justify-center overflow-hidden">
                                    <div class="relative">
                                        {{-- Center box (show actual image with correct aspect ratio) --}}
                                        @if($sourceImage)
                                            <img src="{{ $sourceImage }}" alt="Preview" class="object-contain border-2 border-white/40 rounded" style="max-width: 150px; max-height: 105px;">
                                        @else
                                            <div class="w-20 h-14 bg-white/20 border-2 border-white/40 rounded flex items-center justify-center">
                                                <svg class="w-6 h-6 text-white/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                            </div>
                                        @endif
                                        
                                        {{-- Expansion indicators --}}
                                        {{-- Top bar (full width including corners) --}}
                                        @if($expandDirections['top'] > 0)
                                            <div class="absolute bottom-full bg-blue-500/30 border border-blue-500/50" 
                                                 style="height: {{ min($expandDirections['top'] / 20, 20) }}px; left: {{ $expandDirections['left'] > 0 ? '-' . min($expandDirections['left'] / 20, 20) . 'px' : '0' }}; right: {{ $expandDirections['right'] > 0 ? '-' . min($expandDirections['right'] / 20, 20) . 'px' : '0' }};"></div>
                                        @endif
                                        {{-- Bottom bar (full width including corners) --}}
                                        @if($expandDirections['bottom'] > 0)
                                            <div class="absolute top-full bg-blue-500/30 border border-blue-500/50" 
                                                 style="height: {{ min($expandDirections['bottom'] / 20, 20) }}px; left: {{ $expandDirections['left'] > 0 ? '-' . min($expandDirections['left'] / 20, 20) . 'px' : '0' }}; right: {{ $expandDirections['right'] > 0 ? '-' . min($expandDirections['right'] / 20, 20) . 'px' : '0' }};"></div>
                                        @endif
                                        {{-- Left bar --}}
                                        @if($expandDirections['left'] > 0)
                                            <div class="absolute top-0 bottom-0 right-full bg-blue-500/30 border border-blue-500/50" 
                                                 style="width: {{ min($expandDirections['left'] / 20, 20) }}px"></div>
                                        @endif
                                        {{-- Right bar --}}
                                        @if($expandDirections['right'] > 0)
                                            <div class="absolute top-0 bottom-0 left-full bg-blue-500/30 border border-blue-500/50" 
                                                 style="width: {{ min($expandDirections['right'] / 20, 20) }}px"></div>
                                        @endif
                                    </div>
                                </div>

                                {{-- Sliders with manual input --}}
                                <div class="space-y-3 mt-3">
                                    @foreach(['top' => 'Trên', 'bottom' => 'Dưới', 'left' => 'Trái', 'right' => 'Phải'] as $dir => $label)
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs text-white/50 w-10">{{ $label }}</span>
                                            <input type="range" wire:model.live="expandDirections.{{ $dir }}" 
                                                   min="0" max="512" step="16"
                                                   class="flex-1 h-1.5 bg-white/10 rounded-lg appearance-none cursor-pointer accent-blue-500">
                                            <input type="number" wire:model.live="expandDirections.{{ $dir }}"
                                                   min="0" max="2048" step="16"
                                                   class="w-16 px-2 py-1 text-xs font-mono text-white/90 bg-white/[0.05] border border-white/10 rounded-lg text-center focus:outline-none focus:border-blue-500/50 focus:ring-1 focus:ring-blue-500/30 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                                        </div>
                                    @endforeach
                                </div>
                                
                                {{-- Quick presets --}}
                                <div class="flex flex-wrap gap-1 mt-3">
                                    <button type="button" wire:click="setExpandPreset('vertical')" 
                                            class="px-2 py-1 text-[10px] bg-white/[0.05] hover:bg-white/[0.1] border border-white/10 rounded text-white/60 hover:text-white transition">
                                        Dọc 256
                                    </button>
                                    <button type="button" wire:click="setExpandPreset('horizontal')" 
                                            class="px-2 py-1 text-[10px] bg-white/[0.05] hover:bg-white/[0.1] border border-white/10 rounded text-white/60 hover:text-white transition">
                                        Ngang 256
                                    </button>
                                    <button type="button" wire:click="setExpandPreset('all')" 
                                            class="px-2 py-1 text-[10px] bg-white/[0.05] hover:bg-white/[0.1] border border-white/10 rounded text-white/60 hover:text-white transition">
                                        Đều 128
                                    </button>
                                    <button type="button" wire:click="setExpandPreset('reset')" 
                                            class="px-2 py-1 text-[10px] bg-white/[0.05] hover:bg-white/[0.1] border border-white/10 rounded text-white/60 hover:text-white transition">
                                        Reset
                                    </button>
                                </div>
                            </div>
                        @elseif($editMode === 'text')
                            {{-- Text Replacement UI --}}
                            <div class="space-y-3">
                                <label class="block text-xs font-medium text-white/40 uppercase tracking-wider">Thay đổi text</label>
                                
                                {{-- Instruction --}}
                                <p class="text-xs text-white/50">AI sẽ tự động tìm và thay đổi text trong ảnh.</p>
                                
                                {{-- Text replacement pairs --}}
                                <div class="space-y-2">
                                    @foreach($textReplacements as $index => $pair)
                                        <div class="flex items-center gap-2 p-2 bg-white/[0.03] rounded-lg border border-white/[0.06]">
                                            <div class="flex-1 space-y-1">
                                                <input type="text" 
                                                       wire:model="textReplacements.{{ $index }}.from"
                                                       placeholder="Text gốc"
                                                       class="w-full px-2.5 py-1.5 text-xs bg-white/[0.05] border border-white/[0.1] rounded-lg text-white placeholder-white/30 focus:ring-1 focus:ring-blue-500/40 focus:border-blue-500/50 outline-none">
                                                <input type="text" 
                                                       wire:model="textReplacements.{{ $index }}.to"
                                                       placeholder="Text mới"
                                                       class="w-full px-2.5 py-1.5 text-xs bg-white/[0.05] border border-white/[0.1] rounded-lg text-white placeholder-white/30 focus:ring-1 focus:ring-green-500/40 focus:border-green-500/50 outline-none">
                                            </div>
                                            @if(count($textReplacements) > 1)
                                                <button type="button" wire:click="removeTextReplacement({{ $index }})"
                                                        class="p-1.5 text-white/30 hover:text-red-400 hover:bg-red-500/10 rounded-lg transition">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                                
                                {{-- Add more button --}}
                                <button type="button" wire:click="addTextReplacement"
                                        class="w-full py-2 text-xs text-white/50 hover:text-white bg-white/[0.03] hover:bg-white/[0.06] border border-dashed border-white/[0.1] hover:border-white/[0.2] rounded-lg transition flex items-center justify-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    Thêm cặp text
                                </button>
                            </div>
                        @else
                            {{-- Mode Description --}}
                            <div class="text-sm text-white/60 leading-relaxed">
                                @switch($editMode)
                                    @case('replace')
                                        Dùng công cụ <span class="text-blue-300 bg-blue-500/20 px-1.5 py-0.5 rounded text-xs font-medium border border-blue-500/40">Brush</span> hoặc <span class="text-blue-300 bg-blue-500/20 px-1.5 py-0.5 rounded text-xs font-medium border border-blue-500/40">Rect</span> để tô vùng muốn thay thế.
                                        @break
                                    @case('background')
                                        Tô đỏ lên chủ thể chính (người/vật) mà bạn muốn giữ lại. AI sẽ thay đổi nền xung quanh.
                                        @break
                                @endswitch
                            </div>
                        @endif
                    </div>

                    {{-- Prompt Input --}}
                    <div class="p-4 border-b border-white/[0.08]">
                        <label class="block text-xs font-medium text-white/40 uppercase tracking-wider mb-2">Mô tả thay đổi</label>
                        <textarea wire:model="editPrompt"
                                  rows="3"
                                  placeholder="{{ $this->placeholderText }}"
                                  class="w-full px-3 py-2.5 bg-white/[0.05] border border-white/[0.1] rounded-xl text-white text-sm placeholder-white/30 focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500/50 outline-none transition-all resize-none"></textarea>
                        @error('editPrompt')
                            <p class="mt-1.5 text-xs text-red-400 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Pricing & Submit Button --}}
                    <div class="p-4">
                        {{-- Credits Info --}}
                        <div class="flex items-center justify-between mb-3 text-sm">
                            <div class="flex items-center gap-2 text-white/60">
                                <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.736 6.979C9.208 6.193 9.696 6 10 6c.304 0 .792.193 1.264.979a1 1 0 001.715-1.029C12.279 4.784 11.232 4 10 4s-2.279.784-2.979 1.95c-.285.475-.507 1-.67 1.55H6a1 1 0 000 2h.013a9.358 9.358 0 000 1H6a1 1 0 100 2h.351c.163.55.385 1.075.67 1.55C7.721 15.216 8.768 16 10 16s2.279-.784 2.979-1.95a1 1 0 10-1.715-1.029c-.472.786-.96.979-1.264.979-.304 0-.792-.193-1.264-.979a4.265 4.265 0 01-.264-.521H10a1 1 0 100-2H8.017a7.36 7.36 0 010-1H10a1 1 0 100-2H8.472c.08-.185.167-.36.264-.521z"/>
                                </svg>
                                <span>Số dư: <span class="font-semibold text-yellow-300">{{ number_format($userCredits, 2) }} Xu</span></span>
                            </div>
                            <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-white/[0.05] border border-white/[0.1]">
                                <span class="text-white/50">Giá:</span>
                                <span class="font-bold text-cyan-400">{{ number_format($this->currentPrice, 2) }} Xu</span>
                            </div>
                        </div>

                        {{-- Insufficient Credits Warning --}}
                        @if(!$this->hasEnoughCredits)
                            <div class="mb-3 p-2.5 bg-red-500/10 border border-red-500/30 rounded-lg flex items-center gap-2 text-red-300 text-sm">
                                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                <span>Bạn không đủ Xu. <a href="{{ route('wallet.index') }}" class="underline hover:text-white">Nạp thêm</a></span>
                            </div>
                        @endif

                        <button wire:click="processEdit"
                                @if(empty($sourceImage) || !$this->hasEnoughCredits || $isProcessing) disabled @endif
                                class="w-full py-3 bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 hover:from-blue-500 hover:via-indigo-500 hover:to-purple-500 disabled:opacity-40 disabled:cursor-not-allowed rounded-xl text-white font-bold transition-all shadow-lg shadow-blue-500/25 hover:shadow-blue-500/40 text-center group">
                            
                            @if($isProcessing)
                            <span class="inline-flex items-center justify-center gap-2">
                                <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span>Đang xử lý...</span>
                            </span>
                            @else
                            <span class="inline-flex items-center justify-center gap-2">
                                <svg class="w-5 h-5 group-hover:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                                <span>Tạo tác phẩm ({{ number_format($this->currentPrice, 2) }} Xu)</span>
                            </span>
                            @endif
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================== --}}
    {{-- RESULT MODAL --}}
    {{-- ========================================== --}}
    @if($resultImage)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm animate-in fade-in"
             x-data="{ open: true }"
             x-show="open"
             @keydown.escape.window="open = false; @this.set('resultImage', '')">
            
            <div class="relative w-full max-w-2xl bg-gradient-to-b from-[#14141f] to-[#0d0d15] rounded-2xl border border-white/10 shadow-2xl overflow-hidden animate-in zoom-in-95">
                
                {{-- Modal Header --}}
                <div class="flex items-center justify-between px-5 py-4 border-b border-white/[0.08]">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-green-400 to-emerald-500 flex items-center justify-center shadow-lg shadow-green-500/25">
                            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-white">Kết quả AI</h3>
                            <p class="text-xs text-white/50">Chỉnh sửa hoàn tất</p>
                        </div>
                    </div>
                    <button @click="open = false; @this.set('resultImage', '')" 
                            class="p-2.5 rounded-xl text-white/50 hover:bg-white/10 hover:text-white transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                {{-- Modal Body - Image Preview --}}
                <div class="p-5">
                    <div class="relative rounded-xl overflow-hidden bg-black/40 border border-white/[0.05]">
                        <img src="{{ $resultImage }}" alt="Edited result" class="w-full h-auto max-h-[50vh] object-contain">
                    </div>
                </div>

                {{-- Modal Footer - Action Buttons --}}
                <div class="grid grid-cols-3 gap-3 px-5 mb-5 pb-5">
                    {{-- Download --}}
                    <button type="button"
                            @click.stop.prevent="
                                fetch('{{ $resultImage }}')
                                    .then(res => res.blob())
                                    .then(blob => {
                                        const url = URL.createObjectURL(blob);
                                        const link = document.createElement('a');
                                        link.href = url;
                                        link.download = 'edited-image-' + Date.now() + '.png';
                                        document.body.appendChild(link);
                                        link.click();
                                        document.body.removeChild(link);
                                        URL.revokeObjectURL(url);
                                    })
                                    .catch(err => {
                                        // Fallback: open in new tab
                                        window.open('{{ $resultImage }}', '_blank');
                                    });
                            "
                            class="flex flex-col items-center gap-1.5 py-3 px-4 rounded-xl bg-white/[0.05] hover:bg-white/[0.1] border border-white/[0.08] hover:border-white/[0.15] text-white/80 hover:text-white transition-all group">
                        <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        <span class="text-xs font-medium">Tải xuống</span>
                    </button>
                    
                    {{-- New Edit --}}
                    <button wire:click="resetEditor"
                            class="flex flex-col items-center gap-1.5 py-3 px-4 rounded-xl bg-gradient-to-br from-blue-500/20 to-purple-500/20 hover:from-blue-500/30 hover:to-purple-500/30 border border-blue-500/30 hover:border-blue-500/50 text-white transition-all group">
                        <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        <span class="text-xs font-medium">Tạo mới</span>
                    </button>

                    {{-- Continue Editing --}}
                    <button wire:click="continueEditing"
                            class="flex flex-col items-center gap-1.5 py-3 px-4 rounded-xl bg-white/[0.05] hover:bg-white/[0.1] border border-white/[0.08] hover:border-white/[0.15] text-white/80 hover:text-white transition-all group">
                        <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                        <span class="text-xs font-medium">Tiếp tục</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ========================================== --}}
    {{-- ALPINE.JS CANVAS EDITOR --}}
    {{-- ========================================== --}}
    <script>
        function canvasEditor() {
            return {
                imageCanvas: null,
                drawCanvas: null,
                imageCtx: null,
                drawCtx: null,
                maskCanvas: null,
                maskCtx: null,

                tool: 'brush',
                brushSize: 40,
                isDrawing: false,
                image: null,
                
                startX: 0,
                startY: 0,
                lastX: null,
                lastY: null,
                snapshot: null,

                history: [],
                historyStep: -1,

                init() {
                    this.imageCanvas = this.$refs.imageLayer;
                    this.drawCanvas = this.$refs.drawLayer;
                    if (this.imageCanvas && this.drawCanvas) {
                        this.imageCtx = this.imageCanvas.getContext('2d');
                        this.drawCtx = this.drawCanvas.getContext('2d');
                    }
                    this.maskCanvas = document.createElement('canvas');
                    this.maskCtx = this.maskCanvas.getContext('2d');
                },

                loadImage(detail) {
                    if (!detail || !detail.src) return;
                    
                    const img = new Image();
                    img.onload = () => {
                        this.image = img;
                        const maxDim = 2048; 
                        let width = img.width;
                        let height = img.height;

                        if (width > maxDim || height > maxDim) {
                            const ratio = Math.min(maxDim / width, maxDim / height);
                            width = Math.round(width * ratio);
                            height = Math.round(height * ratio);
                        }
                        
                        this.imageCanvas.width = width;
                        this.imageCanvas.height = height;
                        this.drawCanvas.width = width;
                        this.drawCanvas.height = height;
                        this.maskCanvas.width = width;
                        this.maskCanvas.height = height;
                        
                        this.imageCtx.drawImage(img, 0, 0, width, height);
                        this.clearDrawLayer();
                        
                        this.history = [];
                        this.historyStep = -1;
                        this.saveState();
                    };
                    img.src = detail.src;
                },

                saveState() {
                    if (!this.drawCanvas || !this.maskCanvas) return;
                    const drawData = this.drawCtx.getImageData(0, 0, this.drawCanvas.width, this.drawCanvas.height);
                    const maskData = this.maskCtx.getImageData(0, 0, this.maskCanvas.width, this.maskCanvas.height);
                    
                    if (this.historyStep < this.history.length - 1) {
                        this.history = this.history.slice(0, this.historyStep + 1);
                    }
                    
                    this.history.push({ draw: drawData, mask: maskData });
                    this.historyStep++;
                },

                undo() {
                    if (this.historyStep > 0) {
                        this.historyStep--;
                        this.restoreState();
                    }
                },

                redo() {
                    if (this.historyStep < this.history.length - 1) {
                        this.historyStep++;
                        this.restoreState();
                    }
                },

                restoreState() {
                    const state = this.history[this.historyStep];
                    this.drawCtx.putImageData(state.draw, 0, 0);
                    this.maskCtx.putImageData(state.mask, 0, 0);
                    this.syncMaskToLivewire();
                },

                setTool(newTool) {
                    this.tool = newTool;
                },

                startDraw(e) {
                    this.isDrawing = true;
                    if (this.tool === 'rect') {
                         this.snapshot = this.drawCtx.getImageData(0, 0, this.drawCanvas.width, this.drawCanvas.height);
                    }
                    
                    const coords = this.getCanvasCoordinates(e);
                    this.startX = coords.x;
                    this.startY = coords.y;
                    this.lastX = coords.x;
                    this.lastY = coords.y;
                    
                    if (this.tool === 'brush') {
                        this.drawBrushStroke(this.startX, this.startY, this.startX, this.startY);
                    }
                },

                draw(e) {
                    if (!this.isDrawing) return;
                    
                    const coords = this.getCanvasCoordinates(e);
                    const x = coords.x;
                    const y = coords.y;
                    
                    if (this.tool === 'brush') {
                        this.drawBrushStroke(this.lastX, this.lastY, x, y);
                        this.lastX = x;
                        this.lastY = y;
                    } else if (this.tool === 'rect') {
                        this.redrawPreviewRect(x, y);
                    }
                },

                stopDraw(e) {
                    if (!this.isDrawing) return;
                    
                    if (this.tool === 'rect') {
                        const coords = e ? this.getCanvasCoordinates(e) : { x: this.startX, y: this.startY };
                        this.drawRectFinal(this.startX, this.startY, coords.x, coords.y);
                    }
                    
                    this.isDrawing = false;
                    this.snapshot = null;
                    this.lastX = null;
                    this.lastY = null;
                    this.saveState();
                    this.syncMaskToLivewire();
                },

                getCanvasCoordinates(e) {
                    const rect = this.drawCanvas.getBoundingClientRect();
                    const scaleX = this.drawCanvas.width / rect.width;
                    const scaleY = this.drawCanvas.height / rect.height;
                    
                    return {
                        x: (e.clientX - rect.left) * scaleX,
                        y: (e.clientY - rect.top) * scaleY
                    };
                },

                drawBrushStroke(fromX, fromY, toX, toY) {
                    // Draw line between points for smooth continuous strokes
                    this.drawCtx.strokeStyle = 'rgb(0, 212, 255)';
                    this.drawCtx.lineWidth = this.brushSize * 2;
                    this.drawCtx.lineCap = 'round';
                    this.drawCtx.lineJoin = 'round';
                    this.drawCtx.beginPath();
                    this.drawCtx.moveTo(fromX, fromY);
                    this.drawCtx.lineTo(toX, toY);
                    this.drawCtx.stroke();
                    
                    // Hidden mask - same line
                    this.maskCtx.strokeStyle = 'white';
                    this.maskCtx.lineWidth = this.brushSize * 2;
                    this.maskCtx.lineCap = 'round';
                    this.maskCtx.lineJoin = 'round';
                    this.maskCtx.beginPath();
                    this.maskCtx.moveTo(fromX, fromY);
                    this.maskCtx.lineTo(toX, toY);
                    this.maskCtx.stroke();
                },

                redrawPreviewRect(currentX, currentY) {
                    if (this.snapshot) {
                         this.drawCtx.putImageData(this.snapshot, 0, 0);
                    }
                    
                    // Bold cyan dashed border for clear preview
                    this.drawCtx.strokeStyle = 'rgb(0, 212, 255)';  // Solid Cyan
                    this.drawCtx.lineWidth = 8;
                    this.drawCtx.setLineDash([10, 6]);
                    this.drawCtx.strokeRect(this.startX, this.startY, currentX - this.startX, currentY - this.startY);
                    this.drawCtx.setLineDash([]);
                },

                drawRectFinal(x1, y1, x2, y2) {
                     if (this.snapshot) {
                        this.drawCtx.putImageData(this.snapshot, 0, 0);
                        this.snapshot = null;
                     }

                     const x = Math.min(x1, x2);
                     const y = Math.min(y1, y2);
                     const w = Math.abs(x2 - x1);
                     const h = Math.abs(y2 - y1);

                     // Solid cyan fill only (no border)
                     this.drawCtx.fillStyle = 'rgb(0, 212, 255)';
                     this.drawCtx.fillRect(x, y, w, h);

                     // Hidden mask
                     this.maskCtx.fillStyle = 'white';
                     this.maskCtx.fillRect(x, y, w, h);
                },

                clearDrawLayer() {
                    if (this.drawCtx) {
                        this.drawCtx.clearRect(0, 0, this.drawCanvas.width, this.drawCanvas.height);
                    }
                    if (this.maskCtx) {
                        this.maskCtx.fillStyle = 'black';
                        this.maskCtx.fillRect(0, 0, this.maskCanvas.width, this.maskCanvas.height);
                    }
                },

                clearMask() {
                    this.clearDrawLayer();
                    this.saveState();
                    this.syncMaskToLivewire();
                },

                resetCanvas() {
                    this.image = null;
                    if (this.imageCtx) this.imageCtx.clearRect(0, 0, this.imageCanvas.width, this.imageCanvas.height);
                    if (this.drawCtx) this.clearDrawLayer();
                    this.history = [];
                    this.historyStep = -1;
                },

                syncMaskToLivewire() {
                    if (!this.maskCanvas) return;
                    const dataUrl = this.maskCanvas.toDataURL('image/png');
                    @this.call('setMaskData', dataUrl);
                }
            };
        }

        window.addEventListener('download-image', (e) => {
            const link = document.createElement('a');
            link.href = e.detail.src;
            link.download = e.detail.filename;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });

        // Listen for Livewire image-loaded event (from continueEditing)
        // and dispatch as window event for Alpine to pick up
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('image-loaded', (data) => {
                // Handle both array and object format
                const src = Array.isArray(data) ? data[0]?.src : data?.src;
                if (!src) return;
                
                // Dispatch as window CustomEvent for Alpine (@image-loaded.window)
                window.dispatchEvent(new CustomEvent('image-loaded', {
                    detail: { src: src }
                }));
            });
        });
    </script>
</div>
