<div class="min-h-screen bg-gray-900 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Header --}}
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-white">Image Edit Studio</h1>
            <p class="mt-2 text-gray-400">Upload ảnh, vẽ vùng muốn chỉnh sửa, và mô tả thay đổi</p>
        </div>

        {{-- Messages --}}
        @if($errorMessage)
            <div class="mb-4 p-4 bg-red-900/50 border border-red-500 rounded-lg text-red-200">
                {{ $errorMessage }}
            </div>
        @endif

        @if($successMessage)
            <div class="mb-4 p-4 bg-green-900/50 border border-green-500 rounded-lg text-green-200">
                {{ $successMessage }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Left Column: Canvas Area --}}
            <div class="lg:col-span-2 bg-gray-800 rounded-xl p-6">
                {{-- Upload Zone (shown when no image) --}}
                @if(empty($sourceImage))
                    <div class="border-2 border-dashed border-gray-600 rounded-lg p-12 text-center hover:border-blue-500 transition-colors">
                        <input type="file" 
                               wire:model="uploadedImage" 
                               accept="image/*"
                               class="hidden"
                               id="image-upload">
                        <label for="image-upload" class="cursor-pointer">
                            <svg class="mx-auto h-16 w-16 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <p class="mt-4 text-lg text-gray-300">Click để upload ảnh</p>
                            <p class="mt-1 text-sm text-gray-500">PNG, JPG, WEBP (max 10MB)</p>
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
                        <div class="mb-4 flex items-center justify-between"
                             x-show="!['text', 'expand'].includes($wire.editMode)"
                             x-transition>
                            <div class="flex items-center gap-2 px-4 py-2 bg-gray-800 rounded-xl border border-gray-700 shadow-sm">
                                <span class="text-xs font-medium text-gray-400 uppercase tracking-wider mr-2">Công cụ</span>
                                
                                {{-- Change Image --}}
                                <label for="image-upload" 
                                       class="p-2 text-gray-400 hover:text-white hover:bg-gray-700 rounded-lg transition-all cursor-pointer"
                                       title="Đổi ảnh khác">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                </label>

                                <div class="w-px h-6 bg-gray-700 mx-2"></div>
                                
                                {{-- Brush Tool --}}
                                <button type="button" 
                                        @click="setTool('brush')"
                                        :class="tool === 'brush' ? 'bg-blue-600 text-white shadow-lg shadow-blue-900/50' : 'text-gray-400 hover:text-white hover:bg-gray-700'"
                                        class="p-2 rounded-lg transition-all"
                                        title="Cọ vẽ (Brush)">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                </button>

                                {{-- Rectangle Tool --}}
                                <button type="button" 
                                        @click="setTool('rect')"
                                        :class="tool === 'rect' ? 'bg-blue-600 text-white shadow-lg shadow-blue-900/50' : 'text-gray-400 hover:text-white hover:bg-gray-700'"
                                        class="p-2 rounded-lg transition-all"
                                        title="Vùng chọn (Rectangle)">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4h16v16H4z"></path></svg>
                                </button>

                                <div class="w-px h-6 bg-gray-700 mx-2"></div>

                                {{-- Brush Size --}}
                                <div x-show="tool === 'brush'" class="flex items-center gap-3">
                                    <span class="text-xs text-gray-400">Size</span>
                                    <input type="range" x-model.number="brushSize" min="5" max="100" class="w-24 h-1.5 bg-gray-700 rounded-lg appearance-none cursor-pointer accent-blue-500">
                                    <span class="text-xs text-gray-400 w-6" x-text="brushSize"></span>
                                </div>
                            </div>

                            {{-- Clear Button --}}
                            <button type="button" 
                                    @click="clearMask()"
                                    class="px-4 py-2 text-sm text-red-400 hover:text-white hover:bg-red-500/20 bg-gray-800 border border-gray-700 rounded-xl transition-all flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                Xóa Mask
                            </button>
                        </div>

                        {{-- Canvas Wrapper --}}
                        <div class="relative bg-gray-900 rounded-lg overflow-hidden shadow-2xl ring-1 ring-white/10 flex items-center justify-center bg-[url('https://zdream.vn/images/transparent-bg.png')] bg-repeat">
                            
                            {{-- Inner Wrapper for Alignment (Ignored by Livewire) --}}
                            <div wire:ignore class="relative inline-block max-w-full" style="line-height: 0;">
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
                                        @mouseleave="stopDraw($event)">
                                </canvas>
                            </div>
 
                            {{-- Processing Overlay (Outside wire:ignore) --}}
                            <div wire:loading.flex wire:target="processEdit" 
                                 class="absolute inset-0 bg-gray-900/80 items-center justify-center z-50">
                                <div class="text-center">
                                    <svg class="animate-spin h-12 w-12 text-blue-500 mx-auto" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <p class="mt-4 text-white">Đang xử lý...</p>
                                </div>
                            </div>
                        </div>

                        {{-- Instructions --}}
                        <p class="mt-3 text-sm text-gray-400">
                            @if($editMode === 'expand')
                                Chế độ Expand: Điều chỉnh số pixel mở rộng ở panel bên phải
                            @else
                                Dùng brush hoặc rectangle để vẽ vùng muốn chỉnh sửa (hiển thị màu đỏ mờ)
                            @endif
                        </p>
                    </div>
                @endif

                {{-- Result Preview --}}
                @if($resultImage)
                    <div class="mt-6 pt-6 border-t border-gray-700">
                        <h3 class="text-lg font-semibold text-white mb-4">Kết quả</h3>
                        <div class="relative">
                            <img src="{{ $resultImage }}" 
                                 alt="Edited result" 
                                 class="max-w-full h-auto rounded-lg mx-auto shadow-lg ring-1 ring-white/10">
                        </div>
                        <div class="mt-4 flex gap-3 justify-center">
                            <button wire:click="downloadResult"
                                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 rounded-lg text-white transition-colors">
                                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                Tải xuống
                            </button>
                            <button wire:click="resetEditor"
                                    class="px-4 py-2 bg-gray-600 hover:bg-gray-500 rounded-lg text-white transition-colors">
                                Tạo mới
                            </button>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Right Column: Controls --}}
            <div class="bg-gray-800 rounded-xl p-6 h-fit">
                <h2 class="text-xl font-semibold text-white mb-6">Chỉnh sửa</h2>

                {{-- Edit Mode Selector --}}
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-300 mb-3">Chế độ chỉnh sửa</label>
                    <div class="grid grid-cols-2 gap-2">
                        <button wire:click="setEditMode('replace')"
                                class="p-3 rounded-lg border-2 transition-all {{ $editMode === 'replace' ? 'border-blue-500 bg-blue-500/20 text-blue-400' : 'border-gray-600 text-gray-300 hover:border-gray-500' }}">
                            <svg class="w-6 h-6 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14" />
                            </svg>
                            <span class="text-sm">Thay thế</span>
                        </button>

                        <button wire:click="setEditMode('text')"
                                class="p-3 rounded-lg border-2 transition-all {{ $editMode === 'text' ? 'border-blue-500 bg-blue-500/20 text-blue-400' : 'border-gray-600 text-gray-300 hover:border-gray-500' }}">
                            <svg class="w-6 h-6 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            <span class="text-sm">Sửa text</span>
                        </button>

                        <button wire:click="setEditMode('background')"
                                class="p-3 rounded-lg border-2 transition-all {{ $editMode === 'background' ? 'border-blue-500 bg-blue-500/20 text-blue-400' : 'border-gray-600 text-gray-300 hover:border-gray-500' }}">
                            <svg class="w-6 h-6 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                            </svg>
                            <span class="text-sm">Background</span>
                        </button>

                        <button wire:click="setEditMode('expand')"
                                class="p-3 rounded-lg border-2 transition-all {{ $editMode === 'expand' ? 'border-blue-500 bg-blue-500/20 text-blue-400' : 'border-gray-600 text-gray-300 hover:border-gray-500' }}">
                            <svg class="w-6 h-6 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                            </svg>
                            <span class="text-sm">Expand</span>
                        </button>
                    </div>
                </div>

                {{-- Expand Directions (shown only in expand mode) --}}
                @if($editMode === 'expand')
                    <div class="mb-6 p-4 bg-gray-700/50 rounded-lg">
                        <label class="block text-sm font-medium text-gray-300 mb-3">Mở rộng (pixels)</label>
                        <div class="grid grid-cols-3 gap-2 text-center">
                            {{-- Top --}}
                            <div></div>
                            <div>
                                <label class="text-xs text-gray-400">Top</label>
                                <input type="number" wire:model="expandDirections.top" 
                                       min="0" max="1024" step="16"
                                       class="w-full mt-1 px-2 py-1 bg-gray-600 border border-gray-500 rounded text-white text-center text-sm">
                            </div>
                            <div></div>

                            {{-- Left & Right --}}
                            <div>
                                <label class="text-xs text-gray-400">Left</label>
                                <input type="number" wire:model="expandDirections.left" 
                                       min="0" max="1024" step="16"
                                       class="w-full mt-1 px-2 py-1 bg-gray-600 border border-gray-500 rounded text-white text-center text-sm">
                            </div>
                            <div class="flex items-center justify-center">
                                <div class="w-12 h-12 border-2 border-dashed border-gray-500 rounded"></div>
                            </div>
                            <div>
                                <label class="text-xs text-gray-400">Right</label>
                                <input type="number" wire:model="expandDirections.right" 
                                       min="0" max="1024" step="16"
                                       class="w-full mt-1 px-2 py-1 bg-gray-600 border border-gray-500 rounded text-white text-center text-sm">
                            </div>

                            {{-- Bottom --}}
                            <div></div>
                            <div>
                                <label class="text-xs text-gray-400">Bottom</label>
                                <input type="number" wire:model="expandDirections.bottom" 
                                       min="0" max="1024" step="16"
                                       class="w-full mt-1 px-2 py-1 bg-gray-600 border border-gray-500 rounded text-white text-center text-sm">
                            </div>
                            <div></div>
                        </div>
                    </div>
                @endif

                {{-- Edit Prompt --}}
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-300 mb-2">
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
                              class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"></textarea>
                    @error('editPrompt')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Mode Description --}}
                <div class="mb-6 p-3 bg-gray-700/50 rounded-lg text-sm text-gray-400">
                    @switch($editMode)
                        @case('replace')
                            <strong class="text-gray-300">Thay thế:</strong> Vẽ vùng muốn thay đổi, mô tả nội dung mới
                            @break
                        @case('text')
                            <strong class="text-gray-300">Sửa text:</strong> Mô tả text cần đổi, không cần vẽ mask
                            @break
                        @case('background')
                            <strong class="text-gray-300">Background:</strong> Vẽ vùng giữ lại (subject), background sẽ thay đổi
                            @break
                        @case('expand')
                            <strong class="text-gray-300">Expand:</strong> Mở rộng ảnh theo 4 hướng
                            @break
                    @endswitch
                </div>

                {{-- Submit Button --}}
                <button wire:click="processEdit"
                        wire:loading.attr="disabled"
                        wire:target="processEdit"
                        @if(empty($sourceImage)) disabled @endif
                        class="w-full py-3 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 disabled:opacity-50 disabled:cursor-not-allowed rounded-lg text-white font-semibold transition-all shadow-lg shadow-blue-900/40">
                    <span wire:loading.remove wire:target="processEdit">
                        Áp dụng chỉnh sửa
                    </span>
                    <span wire:loading wire:target="processEdit" class="inline-flex items-center">
                        <svg class="animate-spin -ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Đang xử lý...
                    </span>
                </button>
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
                snapshot: null, // For rect tool preview

                init() {
                    // Get refs
                    this.imageCanvas = this.$refs.imageLayer;
                    this.drawCanvas = this.$refs.drawLayer;
                    
                    // Contexts
                    this.imageCtx = this.imageCanvas.getContext('2d');
                    this.drawCtx = this.drawCanvas.getContext('2d');
                    
                    // Create hidden mask canvas
                    this.maskCanvas = document.createElement('canvas');
                    this.maskCtx = this.maskCanvas.getContext('2d');
                },

                loadImage(detail) {
                    if (!detail || !detail.src) return;
                    
                    const img = new Image();
                    img.onload = () => {
                        this.image = img;
                        
                        // Limit display size but keep resolution high for drawing
                        // If logic width > 1200, scale down only if needed for performance, 
                        // but generally we want full res for BFL.
                        // However, displaying 4K on screen is hard.
                        // Solution: Canvas internal resolution = Source Image Resolution.
                        // CSS displays it fitted.
                        
                        // Fix for "Image too small": Don't shrink to 800. Use original unless HUGE.
                        // Use max safe canvas limit (e.g. 2048 or 4096).
                        const maxDim = 2048; 
                        let width = img.width;
                        let height = img.height;

                        if (width > maxDim || height > maxDim) {
                            const ratio = Math.min(maxDim / width, maxDim / height);
                            width = Math.round(width * ratio);
                            height = Math.round(height * ratio);
                        }
                        
                        // Set Internal Resolution
                        this.imageCanvas.width = width;
                        this.imageCanvas.height = height;
                        this.drawCanvas.width = width;
                        this.drawCanvas.height = height;
                        this.maskCanvas.width = width;
                        this.maskCanvas.height = height;
                        
                        // Draw Image Base
                        this.imageCtx.drawImage(img, 0, 0, width, height);

                        // Clear Overlay
                        this.clearDrawLayer();
                    };
                    img.onerror = () => {
                        // console.error('Failed to load image');
                    };
                    img.src = detail.src;
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
                    this.syncMaskToLivewire();
                },

                resetCanvas() {
                    this.image = null;
                    if (this.imageCtx) this.imageCtx.clearRect(0, 0, this.imageCanvas.width, this.imageCanvas.height);
                    if (this.drawCtx) this.clearDrawLayer();
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
