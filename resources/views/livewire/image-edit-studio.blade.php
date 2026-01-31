<div class="min-h-screen bg-[#0a0a0f] text-white/95 font-sans selection:bg-blue-500/30">
    
    {{-- Ambient Background Glows --}}
    <div class="fixed top-0 left-0 w-full h-full overflow-hidden -z-10 pointer-events-none">
        <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] bg-blue-600/20 rounded-full blur-[120px] mix-blend-screen animate-pulse-slow"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-purple-600/20 rounded-full blur-[120px] mix-blend-screen animate-pulse-slow" style="animation-delay: 2s"></div>
    </div>

    <div class="container mx-auto px-4 py-8 max-w-7xl">
        {{-- Header --}}
        <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
            <div>
                <h1 class="text-3xl md:text-4xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-white to-white/60 tracking-tight">
                    Studio Chỉnh Sửa AI
                </h1>
                <p class="text-white/60 mt-1 font-light">Biến ý tưởng thành hiện thực với công nghệ BFL</p>
            </div>
            
            <a href="/" class="group flex items-center gap-2 px-5 py-2.5 rounded-full bg-white/[0.03] border border-white/[0.08] hover:bg-white/[0.08] hover:border-white/[0.15] backdrop-blur-sm transition-all duration-300">
                <svg class="w-4 h-4 text-white/60 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span class="text-sm font-medium text-white/80 group-hover:text-white">Quay lại</span>
            </a>
        </div>

        {{-- Messages --}}
        @if($errorMessage)
            <div class="mb-6 p-4 bg-red-500/10 backdrop-blur-md border border-red-500/20 rounded-xl text-red-200 flex items-center gap-3 animate-in fade-in slide-in-from-top-2">
                <div class="w-8 h-8 rounded-full bg-red-500/20 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                </div>
                <div>
                    <h4 class="font-medium text-red-100">Đã có lỗi xảy ra</h4>
                    <p class="text-sm opacity-90">{{ $errorMessage }}</p>
                </div>
            </div>
        @endif

        @if($successMessage)
            <div class="mb-6 p-4 bg-green-500/10 backdrop-blur-md border border-green-500/20 rounded-xl text-green-200 flex items-center gap-3 animate-in fade-in slide-in-from-top-2">
                <div class="w-8 h-8 rounded-full bg-green-500/20 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                </div>
                <div>
                    <h4 class="font-medium text-green-100">Thành công!</h4>
                    <p class="text-sm opacity-90">{{ $successMessage }}</p>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Left Column: Canvas Area --}}
            <div class="lg:col-span-2 bg-white/[0.03] backdrop-blur-[12px] border border-white/[0.08] rounded-2xl p-6 shadow-2xl relative overflow-hidden">
                {{-- Decorative glow for the card --}}
                <div class="absolute -top-20 -right-20 w-64 h-64 bg-blue-500/10 rounded-full blur-[80px] pointer-events-none"></div>

                {{-- Upload Zone (shown when no image) --}}
                @if(empty($sourceImage))
                    <div class="relative group"
                         x-data="{ isDragging: false }"
                         @dragover.prevent="isDragging = true"
                         @dragleave.prevent="isDragging = false"
                         @drop.prevent="isDragging = false; @this.upload('uploadedImage', $event.dataTransfer.files[0])">
                        
                        <label for="image-upload" 
                               class="flex flex-col items-center justify-center w-full h-96 rounded-2xl border-2 border-dashed transition-all duration-300 cursor-pointer overflow-hidden relative"
                               :class="isDragging ? 'border-blue-500 bg-blue-500/10 scale-[0.99]' : 'border-white/[0.1] bg-white/[0.02] hover:bg-white/[0.04] hover:border-white/[0.2]'">
                            
                            {{-- Glow effect on hover --}}
                            <div class="absolute inset-0 bg-gradient-to-br from-blue-500/5 to-purple-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"></div>

                            <div class="relative z-10 flex flex-col items-center p-6 text-center">
                                <div class="w-20 h-20 mb-6 rounded-2xl bg-white/[0.05] flex items-center justify-center shadow-lg shadow-black/20 group-hover:scale-110 transition-transform duration-300 ring-1 ring-white/10">
                                    <svg class="w-10 h-10 text-white/70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                <h3 class="text-xl font-semibold text-white/90 mb-2">Tải ảnh lên</h3>
                                <p class="text-white/50 text-sm mb-6">Kéo thả hình ảnh vào đây hoặc nhấp để chọn file</p>
                                <span class="px-4 py-2 bg-blue-600/20 text-blue-400 rounded-lg text-sm font-medium border border-blue-500/30 group-hover:bg-blue-600 group-hover:text-white group-hover:border-transparent transition-all">Chọn ảnh từ máy</span>
                            </div>
                            
                            <input type="file" 
                                   id="image-upload"
                                   wire:model="uploadedImage" 
                                   accept="image/*"
                                   class="hidden">
                        </label>
                    </div>

                    {{-- Upload loading --}}
                    <div wire:loading wire:target="uploadedImage" class="mt-4 text-center">
                        <div class="inline-flex items-center text-blue-400">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Đang tải ảnh...
                        </div>
                    </div>
                @else
                    {{-- Canvas Container --}}
                    <div wire:key="editor-canvas-container"
                         x-data="canvasEditor()" 
                         x-init="init(); $nextTick(() => { if($wire.sourceImage) loadImage({ src: $wire.sourceImage }) })"
                         @image-loaded.window="loadImage($event.detail)"
                         @clear-canvas-mask.window="clearMask()"
                         @reset-canvas.window="resetCanvas()"
                         class="relative">
                        
                        {{-- Toolbar --}}

                        {{-- Hidden Input (Kept for functionality) --}}
                        <input type="file" 
                               wire:model="uploadedImage" 
                               accept="image/*"
                               class="hidden"
                               id="image-upload">

                        {{-- Toolbar (Static Position) --}}
                        {{-- Toolbar (Glassmorphism & Sticky) --}}
                        <div class="mb-6 bg-white/[0.05] backdrop-blur-xl rounded-2xl border border-white/[0.1] shadow-2xl p-2 sticky top-4 z-40 transition-all duration-300"
                             x-show="!['text', 'expand'].includes($wire.editMode)"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 -translate-y-2"
                             x-transition:enter-end="opacity-100 translate-y-0">
                             
                            <div class="flex flex-wrap items-center justify-between gap-3 p-1">
                                {{-- Group 1: Primary Action --}}
                                <div class="flex-shrink-0">
                                    <label for="image-upload" 
                                           class="px-4 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-green-600 to-emerald-500 hover:from-green-500 hover:to-emerald-400 rounded-xl transition-all cursor-pointer flex items-center gap-2 shadow-lg shadow-green-500/20 hover:shadow-green-500/40 hover:-translate-y-0.5 border border-white/10"
                                           title="Upload ảnh khác">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        <span class="hidden sm:inline">Đổi ảnh</span>
                                    </label>
                                </div>

                                {{-- Divider --}}
                                <div class="hidden sm:block w-px h-8 bg-white/[0.1]"></div>

                                {{-- Group 2: Drawing Tools --}}
                                <div class="flex items-center gap-1.5 overflow-x-auto max-w-full pb-1 sm:pb-0 scrollbar-hide">
                                    {{-- Brush --}}
                                    <button type="button" 
                                            @click="setTool('brush')"
                                            :class="tool === 'brush' ? 'bg-blue-500 text-white shadow-lg shadow-blue-500/25 ring-1 ring-blue-400/50' : 'text-white/70 hover:bg-white/[0.1] hover:text-white'"
                                            class="p-2.5 rounded-xl transition-all flex-shrink-0"
                                            title="Cọ vẽ (Brush)">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                    </button>

                                    {{-- Rectangle --}}
                                    <button type="button" 
                                            @click="setTool('rect')"
                                            :class="tool === 'rect' ? 'bg-blue-500 text-white shadow-lg shadow-blue-500/25 ring-1 ring-blue-400/50' : 'text-white/70 hover:bg-white/[0.1] hover:text-white'"
                                            class="p-2.5 rounded-xl transition-all flex-shrink-0"
                                            title="Vùng chọn (Rectangle)">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4h16v16H4z"></path></svg>
                                    </button>

                                    {{-- Brush Size --}}
                                    <div x-show="tool === 'brush'" 
                                         class="flex items-center gap-3 bg-black/20 px-4 py-2 rounded-xl border border-white/[0.05] ml-2 flex-shrink-0 backdrop-blur-sm">
                                        <span class="text-xs font-medium text-white/60">Size</span>
                                        <input type="range" x-model.number="brushSize" min="5" max="100" class="w-20 md:w-28 h-1.5 bg-white/10 rounded-lg appearance-none cursor-pointer accent-blue-500 hover:accent-blue-400">
                                        <span class="text-xs font-mono text-white/80 w-6 text-right" x-text="brushSize"></span>
                                    </div>
                                </div>

                                {{-- Divider --}}
                                <div class="hidden sm:block w-px h-8 bg-white/[0.1]"></div>

                                {{-- Group 3: History & Actions --}}
                                <div class="flex items-center gap-1.5 ml-auto sm:ml-0">
                                    <button type="button" @click="undo()" :disabled="historyStep <= 0" class="p-2.5 rounded-xl transition-all text-white/60 hover:bg-white/[0.1] hover:text-white disabled:opacity-30 disabled:cursor-not-allowed" title="Hoàn tác">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" /></svg>
                                    </button>
                                    <button type="button" @click="redo()" :disabled="historyStep >= history.length - 1" class="p-2.5 rounded-xl transition-all text-white/60 hover:bg-white/[0.1] hover:text-white disabled:opacity-30 disabled:cursor-not-allowed" title="Làm lại">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 10h-10a8 8 0 00-8 8v2M21 10l-6 6m6-6l-6-6" /></svg>
                                    </button>
                                    
                                    <div class="w-px h-8 bg-white/[0.1] mx-1"></div>

                                    <button type="button" @click="clearMask()" class="px-4 py-2.5 text-sm font-medium text-red-400 bg-red-500/10 hover:bg-red-500/20 border border-red-500/20 rounded-xl transition-all flex items-center gap-2" title="Xóa toàn bộ mask">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        <span class="hidden sm:inline">Xóa Mask</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Canvas Wrapper (Mobile Constrained Height) --}}
                        <div class="relative bg-black/40 backdrop-blur-sm rounded-xl overflow-hidden shadow-2xl ring-1 ring-white/10 flex items-center justify-center bg-[url('https://zdream.vn/images/transparent-bg.png')] bg-repeat max-h-[60vh] lg:max-h-none overflow-y-auto lg:overflow-visible custom-scrollbar group">
                            
                            {{-- Inner Wrapper for Alignment (Ignored by Livewire) --}}
                            <div wire:ignore class="relative inline-block max-w-full shadow-2xl" style="line-height: 0;">
                                {{-- Layer 1: Base Image (Controls Layout Size) --}}
                                <canvas x-ref="imageLayer" 
                                        class="max-w-full h-auto"
                                        style="display: block;">
                                </canvas>

                                {{-- Layer 2: Drawing Overlay (Matches Layer 1) --}}
                                <canvas x-ref="drawLayer" 
                                        class="absolute inset-0 w-full h-full cursor-crosshair touch-none"
                                        style="z-index: 10;"
                                        @mousedown="startDraw($event)"
                                        @mousemove.window="draw($event)" 
                                        @mouseup.window="stopDraw($event)"
                                        @mouseleave="stopDraw($event)"
                                        @touchstart="startDraw($event.touches[0])"
                                        @touchmove="draw($event.touches[0]); $event.preventDefault()"
                                        @touchend="stopDraw()">
                                </canvas>
                            </div>
 
                            {{-- Processing Overlay (Outside wire:ignore) --}}
                            <div wire:loading.flex wire:target="processEdit" 
                                 class="absolute inset-0 bg-[#0a0a0f]/80 items-center justify-center z-50 backdrop-blur-md transition-all duration-300">
                                <div class="text-center p-6 rounded-2xl bg-white/[0.05] border border-white/[0.1] shadow-xl">
                                    <div class="relative w-16 h-16 mx-auto mb-4">
                                        <div class="absolute inset-0 rounded-full border-4 border-blue-500/30"></div>
                                        <div class="absolute inset-0 rounded-full border-4 border-t-blue-500 animate-spin"></div>
                                    </div>
                                    <p class="text-blue-400 font-medium animate-pulse tracking-wide">Đang xử lý AI...</p>
                                    <p class="text-white/40 text-xs mt-2">Vui lòng đợi trong giây lát</p>
                                </div>
                            </div>
                        </div>

                        {{-- Instructions --}}
                        <div class="mt-6 flex items-start gap-4 p-4 bg-blue-500/5 rounded-xl border border-blue-500/10 backdrop-blur-sm">
                             <div class="w-8 h-8 rounded-full bg-blue-500/10 flex items-center justify-center flex-shrink-0 text-blue-400 border border-blue-500/20">
                                 <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                             </div>
                             <div class="text-sm text-white/70 leading-relaxed mt-1">
                                @if($editMode === 'expand')
                                    <strong class="text-blue-200">Chế độ Expand:</strong> Điều chỉnh hướng mở rộng ở cột bên phải.
                                @else
                                    Dùng <strong class="text-white bg-white/10 px-1.5 py-0.5 rounded text-xs border border-white/10">Brush</strong> hoặc <strong class="text-white bg-white/10 px-1.5 py-0.5 rounded text-xs border border-white/10">Vùng chọn</strong> để tô màu đỏ lên khu vực muốn chỉnh sửa.
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Result Preview --}}
                @if($resultImage)
                    <div class="mt-8 pt-8 border-t border-white/[0.1]">
                        <h3 class="text-2xl font-bold text-white mb-6 flex items-center gap-3">
                            <span class="w-1.5 h-8 bg-gradient-to-b from-blue-500 to-purple-500 rounded-full"></span>
                            <span class="bg-clip-text text-transparent bg-gradient-to-r from-white to-white/70">Kết quả AI</span>
                        </h3>
                        
                        <div class="relative group rounded-2xl overflow-hidden shadow-2xl ring-1 ring-white/10 bg-black/40">
                            {{-- Glass overlay effect --}}
                            <div class="absolute inset-0 bg-gradient-to-b from-transparent to-black/50 opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none z-10"></div>
                            
                            <img src="{{ $resultImage }}" 
                                 alt="Edited result" 
                                 class="max-w-full h-auto mx-auto transition-transform duration-700 group-hover:scale-[1.02]">
                             
                             {{-- Actions overlay on hover --}}
                             <div class="absolute bottom-0 left-0 right-0 p-6 translate-y-full group-hover:translate-y-0 transition-transform duration-300 z-20 flex justify-center gap-3">
                                 {{-- Actions moved here for better UX on desktop, keeping normal buttons below for mobile --}}
                             </div>
                        </div>

                        <div class="mt-8 flex flex-wrap gap-4 justify-center">
                            <button wire:click="downloadResult"
                                    class="px-6 py-3 bg-white/[0.1] hover:bg-white/[0.2] backdrop-blur-md rounded-xl text-white font-medium transition-all flex items-center gap-2 border border-white/10 shadow-lg hover:shadow-white/5 hover:-translate-y-0.5 group">
                                <svg class="w-5 h-5 group-hover:text-blue-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                <span>Tải xuống</span>
                            </button>
                            
                            <button wire:click="resetEditor"
                                    class="px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-500 hover:to-purple-500 rounded-xl text-white font-medium transition-all shadow-lg shadow-blue-500/25 hover:shadow-blue-500/40 hover:-translate-y-0.5 flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                Tạo tác phẩm mới
                            </button>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Right Column: Controls (Sticky on Desktop) --}}
            <div class="lg:col-span-1 pb-24 lg:pb-0">
                <div class="bg-white/[0.03] backdrop-blur-xl rounded-2xl p-6 shadow-2xl border border-white/[0.08] lg:sticky lg:top-8 self-start relative overflow-hidden">
                {{-- Decorative gradient --}}
                <div class="absolute top-0 right-0 w-full h-1 bg-gradient-to-r from-blue-500 via-purple-500 to-pink-500 opacity-50"></div>
                
                <h2 class="text-xl font-semibold text-white mb-6 flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
                    Công cụ chỉnh sửa
                </h2>

                {{-- Edit Mode Selector --}}
                <div class="mb-6">
                    <label class="block text-xs font-medium text-white/50 uppercase tracking-wider mb-3">Chế độ</label>
                    <div class="grid grid-cols-2 gap-3">
                        <button wire:click="setEditMode('replace')"
                                class="p-3 rounded-xl border transition-all flex flex-col items-center justify-center gap-2 group {{ $editMode === 'replace' ? 'bg-blue-600/20 border-blue-500/50 text-blue-400 shadow-lg shadow-blue-500/10' : 'bg-white/[0.03] border-white/[0.08] text-white/70 hover:bg-white/[0.08] hover:border-white/[0.15] hover:text-white' }}">
                            <svg class="w-6 h-6 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14" />
                            </svg>
                            <span class="text-sm font-medium">Thay thế</span>
                        </button>

                        <button wire:click="setEditMode('text')"
                                class="p-3 rounded-xl border transition-all flex flex-col items-center justify-center gap-2 group {{ $editMode === 'text' ? 'bg-blue-600/20 border-blue-500/50 text-blue-400 shadow-lg shadow-blue-500/10' : 'bg-white/[0.03] border-white/[0.08] text-white/70 hover:bg-white/[0.08] hover:border-white/[0.15] hover:text-white' }}">
                            <svg class="w-6 h-6 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            <span class="text-sm font-medium">Sửa text</span>
                        </button>

                        <button wire:click="setEditMode('background')"
                                class="p-3 rounded-xl border transition-all flex flex-col items-center justify-center gap-2 group {{ $editMode === 'background' ? 'bg-blue-600/20 border-blue-500/50 text-blue-400 shadow-lg shadow-blue-500/10' : 'bg-white/[0.03] border-white/[0.08] text-white/70 hover:bg-white/[0.08] hover:border-white/[0.15] hover:text-white' }}">
                            <svg class="w-6 h-6 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                            </svg>
                            <span class="text-sm font-medium">Background</span>
                        </button>

                        <button wire:click="setEditMode('expand')"
                                class="p-3 rounded-xl border transition-all flex flex-col items-center justify-center gap-2 group {{ $editMode === 'expand' ? 'bg-blue-600/20 border-blue-500/50 text-blue-400 shadow-lg shadow-blue-500/10' : 'bg-white/[0.03] border-white/[0.08] text-white/70 hover:bg-white/[0.08] hover:border-white/[0.15] hover:text-white' }}">
                            <svg class="w-6 h-6 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                            </svg>
                            <span class="text-sm font-medium">Expand</span>
                        </button>
                    </div>
                </div>

                {{-- Expand Directions (shown only in expand mode) --}}
                @if($editMode === 'expand')
                    <div class="mb-6 p-4 bg-white/[0.03] rounded-xl border border-white/[0.05]">
                        <label class="block text-xs font-medium text-white/50 uppercase tracking-wider mb-3">Mở rộng (pixels)</label>
                        <div class="grid grid-cols-3 gap-2 text-center">
                            {{-- Top --}}
                            <div></div>
                            <div>
                                <label class="text-[10px] text-white/40 uppercase mb-1 block">Top</label>
                                <input type="number" wire:model="expandDirections.top" 
                                       min="0" max="1024" step="16"
                                       class="w-full px-2 py-1.5 bg-black/20 border border-white/10 rounded-lg text-white text-center text-sm focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500/50 outline-none transition-all">
                            </div>
                            <div></div>

                            {{-- Left & Right --}}
                            <div>
                                <label class="text-[10px] text-white/40 uppercase mb-1 block">Left</label>
                                <input type="number" wire:model="expandDirections.left" 
                                       min="0" max="1024" step="16"
                                       class="w-full px-2 py-1.5 bg-black/20 border border-white/10 rounded-lg text-white text-center text-sm focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500/50 outline-none transition-all">
                            </div>
                            <div class="flex items-center justify-center">
                                <div class="w-10 h-10 border-2 border-dashed border-white/20 rounded-lg"></div>
                            </div>
                            <div>
                                <label class="text-[10px] text-white/40 uppercase mb-1 block">Right</label>
                                <input type="number" wire:model="expandDirections.right" 
                                       min="0" max="1024" step="16"
                                       class="w-full px-2 py-1.5 bg-black/20 border border-white/10 rounded-lg text-white text-center text-sm focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500/50 outline-none transition-all">
                            </div>

                            {{-- Bottom --}}
                            <div></div>
                            <div>
                                <label class="text-[10px] text-white/40 uppercase mb-1 block">Bottom</label>
                                <input type="number" wire:model="expandDirections.bottom" 
                                       min="0" max="1024" step="16"
                                       class="w-full px-2 py-1.5 bg-black/20 border border-white/10 rounded-lg text-white text-center text-sm focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500/50 outline-none transition-all">
                            </div>
                            <div></div>
                        </div>
                    </div>
                @endif

                {{-- Edit Prompt --}}
                <div class="mb-6">
                    <label class="block text-xs font-medium text-white/50 uppercase tracking-wider mb-2">
                        @if($editMode === 'text')
                            Mô tả thay đổi text
                        @elseif($editMode === 'expand')
                            Mô tả nội dung mới (optional)
                        @else
                            Mô tả thay đổi
                        @endif
                    </label>
                    <textarea wire:model="editPrompt"
                              rows="4"
                              placeholder="{{ $this->placeholderText }}"
                              class="w-full px-4 py-3 bg-white/[0.03] backdrop-blur-sm border border-white/[0.1] rounded-xl text-white placeholder-white/30 focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500/50 focus:bg-white/[0.05] outline-none transition-all resize-none"></textarea>
                    @error('editPrompt')
                        <p class="mt-2 text-sm text-red-400 flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Mode Description --}}
                <div class="mb-6 p-4 bg-blue-500/5 border border-blue-500/10 rounded-xl">
                    <div class="flex gap-3">
                        <svg class="w-5 h-5 text-blue-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <p class="text-sm text-white/70 leading-relaxed">
                        @switch($editMode)
                            @case('replace')
                                <span class="text-blue-200 font-medium">Thay thế:</span> Vẽ vùng muốn xóa trên ảnh, sau đó mô tả vật thể mới bạn muốn thêm vào.
                                @break
                            @case('text')
                                <span class="text-blue-200 font-medium">Sửa text:</span> Mô tả đoạn văn bản cũ và mới. AI sẽ tự động tìm và thay thế.
                                @break
                            @case('background')
                                <span class="text-blue-200 font-medium">Background:</span> Vẽ đè lên chủ thể chính (người/vật) để giữ lại. AI sẽ thay thế nền xung quanh.
                                @break
                            @case('expand')
                                <span class="text-blue-200 font-medium">Expand:</span> Nhập số pixel muốn mở rộng mỗi chiều. AI sẽ vẽ thêm cảnh vật phù hợp.
                                @break
                        @endswitch
                        </p>
                    </div>
                </div>

                {{-- Fixed Bottom Action Bar (Mobile Only) --}}
                <div class="fixed bottom-0 left-0 right-0 p-4 bg-[#0a0a0f]/90 backdrop-blur-xl border-t border-white/[0.1] lg:static lg:bg-transparent lg:border-0 lg:p-0 z-50">
                    <button wire:click="processEdit"
                            wire:loading.attr="disabled"
                            wire:target="processEdit"
                            @if(empty($sourceImage)) disabled @endif
                            class="w-full py-3.5 bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 hover:from-blue-500 hover:via-indigo-500 hover:to-purple-500 disabled:opacity-50 disabled:cursor-not-allowed rounded-xl text-white font-bold tracking-wide transition-all shadow-lg shadow-blue-500/25 hover:shadow-blue-500/40 hover:-translate-y-0.5 relative overflow-hidden group">
                        
                        {{-- Shine effect --}}
                        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent translate-x-[-100%] group-hover:translate-x-[100%] transition-transform duration-700"></div>

                        <span wire:loading.remove wire:target="processEdit" class="flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                            Tạo tác phẩm
                        </span>
                        <span wire:loading wire:target="processEdit" class="inline-flex items-center">
                            <svg class="animate-spin -ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Đang khởi tạo phép màu...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Alpine.js Canvas Editor (Updated for 2 Layers) --}}
    <script>
        function canvasEditor() {
            return {
                imageCanvas: null, // Layer 1: Static Image
                drawCanvas: null,  // Layer 2: Interactive Drawing
                imageCtx: null,
                drawCtx: null,
                
                // Helper hidden canvas for Mask generation
                maskCanvas: null,
                maskCtx: null,

                tool: 'brush',
                brushSize: 40, // Default larger brush
                isDrawing: false,
                image: null,
                
                startX: 0,
                startY: 0,
                snapshot: null,

                // History for Undo/Redo
                history: [],
                historyStep: -1,

                init() {
                    this.imageCanvas = this.$refs.imageLayer;
                    this.drawCanvas = this.$refs.drawLayer;
                    this.imageCtx = this.imageCanvas.getContext('2d');
                    this.drawCtx = this.drawCanvas.getContext('2d');
                    this.maskCanvas = document.createElement('canvas');
                    this.maskCtx = this.maskCanvas.getContext('2d');

                    // Save initial (blank) state
                    // We need to wait for dimensions to be set first, but if init is called before image load, dimensions are 0.
                    // So saveState will be called in loadImage initially.
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
                        
                        // Reset history
                        this.history = [];
                        this.historyStep = -1;
                        this.saveState();
                    };
                    img.onerror = () => {
                        // console.error('Failed to load image');
                    };
                    img.src = detail.src;
                },

                saveState() {
                    // Capture current state of both Draw (Visual) and Mask (Data) layers
                    const drawData = this.drawCtx.getImageData(0, 0, this.drawCanvas.width, this.drawCanvas.height);
                    const maskData = this.maskCtx.getImageData(0, 0, this.maskCanvas.width, this.maskCanvas.height);
                    
                    // Remove redo history if we are in the middle of the stack
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
                    // Snapshot for Rect tool undo
                    if (this.tool === 'rect') {
                         this.snapshot = this.drawCtx.getImageData(0, 0, this.drawCanvas.width, this.drawCanvas.height);
                    }
                    
                    // Coordinate mapping: Mouse (CSS) -> Canvas (Internal)
                    const coords = this.getCanvasCoordinates(e);
                    this.startX = coords.x;
                    this.startY = coords.y;
                    
                    if (this.tool === 'brush') {
                        this.drawBrushStroke(this.startX, this.startY);
                    }
                },

                draw(e) {
                    if (!this.isDrawing) return;
                    
                    const coords = this.getCanvasCoordinates(e);
                    const x = coords.x;
                    const y = coords.y;
                    
                    if (this.tool === 'brush') {
                        this.drawBrushStroke(x, y);
                    } else if (this.tool === 'rect') {
                        this.redrawPreviewRect(x, y);
                    }
                },

                stopDraw(e) {
                    if (!this.isDrawing) return;
                    
                    if (this.tool === 'rect') {
                        // Finalize rect
                        const coords = e ? this.getCanvasCoordinates(e) : { x: this.startX, y: this.startY };
                        this.drawRectFinal(this.startX, this.startY, coords.x, coords.y);
                    }
                    
                    this.isDrawing = false;
                    this.snapshot = null; // Clear snapshot after drawing
                    this.saveState(); // Save to history
                    this.syncMaskToLivewire();
                },

                // Helper: Map CSS coordinates to Canvas coordinates
                getCanvasCoordinates(e) {
                    const rect = this.drawCanvas.getBoundingClientRect();
                    const scaleX = this.drawCanvas.width / rect.width;
                    const scaleY = this.drawCanvas.height / rect.height;
                    
                    return {
                        x: (e.clientX - rect.left) * scaleX,
                        y: (e.clientY - rect.top) * scaleY
                    };
                },

                drawBrushStroke(x, y) {
                    // 1. Draw Red on Visible Overlay
                    this.drawCtx.fillStyle = 'rgba(255, 50, 50, 0.5)';
                    this.drawCtx.beginPath();
                    this.drawCtx.arc(x, y, this.brushSize, 0, Math.PI * 2);
                    this.drawCtx.fill();
                    
                    // 2. Draw White on Hidden Mask
                    this.maskCtx.fillStyle = 'white';
                    this.maskCtx.beginPath();
                    this.maskCtx.arc(x, y, this.brushSize, 0, Math.PI * 2);
                    this.maskCtx.fill();
                },

                redrawPreviewRect(currentX, currentY) {
                    // Restore snapshot to clear guidance lines
                    if (this.snapshot) {
                         this.drawCtx.putImageData(this.snapshot, 0, 0);
                    }
                    
                    this.drawCtx.strokeStyle = 'rgba(255, 0, 0, 0.8)';
                    this.drawCtx.lineWidth = 4;
                    this.drawCtx.setLineDash([10, 10]);
                    this.drawCtx.strokeRect(this.startX, this.startY, currentX - this.startX, currentY - this.startY);
                    this.drawCtx.setLineDash([]);
                },

                drawRectFinal(x1, y1, x2, y2) {
                     // Restore snapshot to clear guidance lines
                     if (this.snapshot) {
                        this.drawCtx.putImageData(this.snapshot, 0, 0);
                        this.snapshot = null;
                     }

                     const x = Math.min(x1, x2);
                     const y = Math.min(y1, y2);
                     const w = Math.abs(x2 - x1);
                     const h = Math.abs(y2 - y1);

                     // 1. Visible Red
                     this.drawCtx.fillStyle = 'rgba(255, 50, 50, 0.5)';
                     this.drawCtx.fillRect(x, y, w, h);

                     // 2. Hidden White
                     this.maskCtx.fillStyle = 'white';
                     this.maskCtx.fillRect(x, y, w, h);
                },

                // Clear Draw Layer
                clearDrawLayer() {
                    this.drawCtx.clearRect(0, 0, this.drawCanvas.width, this.drawCanvas.height);
                    
                    // Clear Mask
                    this.maskCtx.fillStyle = 'black';
                    this.maskCtx.fillRect(0, 0, this.maskCanvas.width, this.maskCanvas.height);
                },

                clearMask() {
                    this.clearDrawLayer();
                    this.saveState(); // Save cleared state
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

        // Handle download
        window.addEventListener('download-image', (e) => {
            const link = document.createElement('a');
            link.href = e.detail.src;
            link.download = e.detail.filename;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });
    </script>
</div>
