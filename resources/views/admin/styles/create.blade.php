<x-app-layout>
    <x-slot name="title">Tạo Style mới - Admin | ZDream</x-slot>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 py-6 sm:py-8">
        <div class="flex items-center gap-4 mb-6">
            <a href="{{ route('admin.styles.index') }}" class="w-10 h-10 rounded-xl bg-white/[0.05] border border-white/[0.1] flex items-center justify-center text-white/60 hover:text-white hover:bg-white/[0.1] transition-all">
                <i class="fa-solid fa-arrow-left w-4 h-4"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-white">Tạo Style mới</h1>
                <p class="text-white/50 text-sm">Thêm style AI mới vào hệ thống</p>
            </div>
        </div>

        @if(session('error'))
            <div class="mb-6 p-4 rounded-xl bg-red-500/10 border border-red-500/30 text-red-400 flex items-center gap-2">
                <i class="fa-solid fa-exclamation-circle" style="font-size: 16px;"></i>
                {{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.styles.store') }}" class="space-y-6" enctype="multipart/form-data">
            @csrf

            <!-- Basic Info -->
            <div class="bg-white/[0.03] border border-white/[0.08] rounded-2xl p-6">
                <h2 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-info-circle w-5 h-5 text-purple-400"></i>
                    Thông tin cơ bản
                </h2>

                <div class="space-y-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-white/70 mb-2">Tên Style *</label>
                        <input id="name" type="text" name="name" value="{{ old('name') }}" 
                               class="w-full px-4 py-3 rounded-xl bg-white/[0.03] border border-white/[0.08] text-white/90 focus:outline-none focus:ring-2 focus:ring-purple-500/40 focus:border-purple-500/40 transition-all"
                               placeholder="VD: Tết 2026 🧧" required>
                        @error('name')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="slug" class="block text-sm font-medium text-white/70 mb-2">Slug (tự động nếu để trống)</label>
                        <input id="slug" type="text" name="slug" value="{{ old('slug') }}" 
                               class="w-full px-4 py-3 rounded-xl bg-white/[0.03] border border-white/[0.08] text-white/90 focus:outline-none focus:ring-2 focus:ring-purple-500/40 focus:border-purple-500/40 transition-all"
                               placeholder="tet-2026">
                        @error('slug')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-white/70 mb-2">Mô tả</label>
                        <textarea id="description" name="description" rows="2"
                                  class="w-full px-4 py-3 rounded-xl bg-white/[0.03] border border-white/[0.08] text-white/90 focus:outline-none focus:ring-2 focus:ring-purple-500/40 focus:border-purple-500/40 transition-all resize-none"
                                  placeholder="Mô tả ngắn về style...">{{ old('description') }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="price" class="block text-sm font-medium text-white/70 mb-2">Giá (Xu) *</label>
                            <input id="price" type="number" name="price" value="{{ old('price', 2) }}" min="0"
                                   class="w-full px-4 py-3 rounded-xl bg-white/[0.03] border border-white/[0.08] text-white/90 focus:outline-none focus:ring-2 focus:ring-purple-500/40 focus:border-purple-500/40 transition-all"
                                   required>
                            @error('price')
                                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="sort_order" class="block text-sm font-medium text-white/70 mb-2">Thứ tự</label>
                            <input id="sort_order" type="number" name="sort_order" value="{{ old('sort_order', 0) }}"
                                   class="w-full px-4 py-3 rounded-xl bg-white/[0.03] border border-white/[0.08] text-white/90 focus:outline-none focus:ring-2 focus:ring-purple-500/40 focus:border-purple-500/40 transition-all">
                        </div>
                    </div>

                    <div>
                        <label for="thumbnail_url" class="block text-sm font-medium text-white/70 mb-2">URL Ảnh thumbnail *</label>
                        <input id="thumbnail_url" type="url" name="thumbnail_url" value="{{ old('thumbnail_url') }}" 
                               class="w-full px-4 py-3 rounded-xl bg-white/[0.03] border border-white/[0.08] text-white/90 focus:outline-none focus:ring-2 focus:ring-purple-500/40 focus:border-purple-500/40 transition-all"
                               placeholder="https://..." required>
                        @error('thumbnail_url')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- [FIX loi.md #7] Tag select dropdown --}}
                    <div>
                        <label for="tag_id" class="block text-sm font-medium text-white/70 mb-2">Danh mục (Tag)</label>
                        <select id="tag_id" name="tag_id"
                                class="w-full px-4 py-3 rounded-xl bg-white/[0.03] border border-white/[0.08] text-white/90 focus:outline-none focus:ring-2 focus:ring-purple-500/40 focus:border-purple-500/40 transition-all">
                            <option value="">Không có danh mục</option>
                            @foreach($tags as $tag)
                                <option value="{{ $tag->id }}" {{ old('tag_id') == $tag->id ? 'selected' : '' }}>{{ $tag->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- AI Config -->
            <div class="bg-white/[0.03] border border-white/[0.08] rounded-2xl p-6">
                <h2 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-microchip w-5 h-5 text-cyan-400"></i>
                    Cấu hình AI
                </h2>

                <div class="space-y-4">
                    <div x-data="{
                        search: '',
                        selectedModelId: '{{ old('bfl_model_id') }}',
                        groupedModels: @js($groupedModels),
                        allModels: @js($models),
                        priceFilter: 'all',
                        
                        get filteredGroups() {
                            let filtered = {};
                            
                            for (let provider in this.groupedModels) {
                                let models = this.groupedModels[provider].filter(model => {
                                    // Search filter
                                    let matchSearch = this.search === '' || 
                                        model.name.toLowerCase().includes(this.search.toLowerCase()) ||
                                        model.id.toLowerCase().includes(this.search.toLowerCase());
                                    
                                    // Price filter
                                    let cost = model.estimated_cost_per_image;
                                    let matchPrice = true;
                                    if (this.priceFilter !== 'all') {
                                        if (cost === null || cost === undefined) {
                                            matchPrice = false;
                                        } else if (this.priceFilter === 'free') {
                                            matchPrice = cost === 0;
                                        } else if (this.priceFilter === 'low') {
                                            matchPrice = cost > 0 && cost < 0.001;
                                        } else if (this.priceFilter === 'mid') {
                                            matchPrice = cost >= 0.001 && cost < 0.01;
                                        } else if (this.priceFilter === 'high') {
                                            matchPrice = cost >= 0.01;
                                        }
                                    }
                                    
                                    return matchSearch && matchPrice;
                                });
                                
                                if (models.length > 0) {
                                    filtered[provider] = models;
                                }
                            }
                            
                            return filtered;
                        },
                        
                        formatCost(cost) {
                            if (cost === null || cost === undefined) return 'N/A';
                            if (cost <= 0) return 'Free';
                            if (cost < 0.0001) return '< $0.0001';
                            return '$' + cost.toFixed(4);
                        }
                    }">
                        <label for="bfl_model_id" class="block text-sm font-medium text-white/70 mb-2">
                            BFL Model *
                            <span class="text-white/40 font-normal" x-text="'(' + allModels.length + ' models)'"></span>
                        </label>
                        
                        <!-- Hidden input for form submission -->
                        <input type="hidden" name="bfl_model_id" x-model="selectedModelId" required>
                        
                        <!-- Search Box -->
                        <input 
                            type="text" 
                            x-model="search"
                            placeholder="🔍 Search models..."
                            class="w-full px-4 py-3 rounded-xl bg-white/[0.03] border border-white/[0.08] text-white/90 mb-3 focus:outline-none focus:ring-2 focus:ring-purple-500/40 focus:border-purple-500/40 transition-all">
                        
                        <!-- Price Filter -->
                        <div class="flex gap-2 mb-3 flex-wrap">
                            <button type="button" @click="priceFilter = 'all'" :class="priceFilter === 'all' ? 'bg-purple-500/20 text-purple-300 border-purple-500/50' : 'bg-white/[0.03] text-white/50 border-white/[0.08]'" class="px-3 py-1.5 rounded-lg text-xs border transition-colors">
                                All
                            </button>
                            <button type="button" @click="priceFilter = 'free'" :class="priceFilter === 'free' ? 'bg-green-500/20 text-green-300 border-green-500/50' : 'bg-white/[0.03] text-white/50 border-white/[0.08]'" class="px-3 py-1.5 rounded-lg text-xs border transition-colors">
                                🆓 Free
                            </button>
                            <button type="button" @click="priceFilter = 'low'" :class="priceFilter === 'low' ? 'bg-cyan-500/20 text-cyan-300 border-cyan-500/50' : 'bg-white/[0.03] text-white/50 border-white/[0.08]'" class="px-3 py-1.5 rounded-lg text-xs border transition-colors">
                                💵 Low (< $0.001)
                            </button>
                            <button type="button" @click="priceFilter = 'mid'" :class="priceFilter === 'mid' ? 'bg-yellow-500/20 text-yellow-300 border-yellow-500/50' : 'bg-white/[0.03] text-white/50 border-white/[0.08]'" class="px-3 py-1.5 rounded-lg text-xs border transition-colors">
                                💰 Mid ($0.001-$0.01)
                            </button>
                            <button type="button" @click="priceFilter = 'high'" :class="priceFilter === 'high' ? 'bg-red-500/20 text-red-300 border-red-500/50' : 'bg-white/[0.03] text-white/50 border-white/[0.08]'" class="px-3 py-1.5 rounded-lg text-xs border transition-colors">
                                💎 High (> $0.01)
                            </button>
                        </div>
                        
                        <!-- Model List (Grouped) -->
                        <div class="max-h-96 overflow-y-auto space-y-4 p-4 rounded-xl bg-white/[0.02] border border-white/[0.05]">
                            
                            <!-- Custom Input Option -->
                            <div x-show="search.length > 0" class="mb-4 pb-4 border-b border-white/[0.05]">
                                <button 
                                    type="button"
                                    @click="selectedModelId = search"
                                    class="w-full p-3 rounded-lg border border-dashed border-purple-500/50 bg-purple-500/10 hover:bg-purple-500/20 text-left transition-all group">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <span class="text-purple-300 font-medium text-sm block mb-1">Sử dụng Model ID tùy chỉnh</span>
                                            <span class="text-white/60 text-xs font-mono" x-text="search"></span>
                                        </div>
                                        <i class="fa-solid fa-plus text-purple-400 group-hover:scale-110 transition-transform"></i>
                                    </div>
                                </button>
                            </div>

                            <!-- Selected Custom Model Display (if not in list) -->
                            <div x-show="selectedModelId && !allModels.find(m => m.id === selectedModelId)" class="mb-4 p-3 rounded-lg bg-green-500/10 border border-green-500/30">
                                <p class="text-xs text-green-400 mb-1">Đang chọn (Custom):</p>
                                <p class="text-sm text-white font-mono break-all" x-text="selectedModelId"></p>
                            </div>

                            <template x-for="(models, provider) in filteredGroups" :key="provider">
                                <div>
                                    <!-- Provider Header -->
                                    <h4 class="text-sm font-semibold text-white/60 mb-2 flex items-center gap-2">
                                        <span class="w-1 h-4 bg-gradient-to-b from-purple-400 to-pink-500 rounded-full"></span>
                                        <span x-text="provider"></span>
                                        <span class="text-xs text-white/40" x-text="'(' + models.length + ')'"></span>
                                    </h4>
                                    
                                    <!-- Models in this provider -->
                                    <div class="space-y-2 mb-3">
                                        <template x-for="model in models" :key="model.id">
                                            <button 
                                                type="button"
                                                @click="selectedModelId = model.id"
                                                :class="selectedModelId === model.id ? 'bg-gradient-to-r from-purple-500/20 to-pink-500/20 border-purple-500/50' : 'bg-white/[0.03] border-white/[0.08] hover:border-white/[0.15]'"
                                                class="w-full p-3 rounded-lg border transition-all text-left">
                                                <!-- Model Name -->
                                                <div class="flex items-start justify-between gap-2 mb-2">
                                                    <span class="font-medium text-sm" :class="selectedModelId === model.id ? 'text-white' : 'text-white/80'" x-text="model.name"></span>
                                                    <div class="flex items-center gap-1">
                                                        <!-- Selected Checkmark -->
                                                        <span x-show="selectedModelId === model.id" class="w-5 h-5 rounded-full bg-gradient-to-r from-cyan-400 to-cyan-500 flex items-center justify-center">
                                                            <i class="fa-solid fa-check text-white" style="font-size: 10px;"></i>
                                                        </span>
                                                    </div>
                                                </div>
                                                
                                                <!-- Model ID -->
                                                <p class="text-xs text-white/40 font-mono mb-2" x-text="model.id"></p>
                                                
                                                <!-- Badges -->
                                                <div class="flex items-center gap-2 flex-wrap">
                                                    <!-- Price Badge -->
                                                    <span class="px-2 py-0.5 rounded text-xs font-medium"
                                                          :class="model.estimated_cost_per_image === 0
                                                              ? 'bg-green-500/20 text-green-300'
                                                              : (model.estimated_cost_per_image === null || model.estimated_cost_per_image === undefined)
                                                                  ? 'bg-white/[0.05] text-white/50'
                                                                  : 'bg-cyan-500/20 text-cyan-300'"
                                                          x-text="formatCost(model.estimated_cost_per_image)"></span>
                                                    
                                                    <!-- Capabilities -->
                                                    <template x-if="model.supports_aspect_ratio">
                                                        <span class="px-2 py-0.5 rounded bg-purple-500/20 text-purple-300 text-xs">🔧 Aspect</span>
                                                    </template>
                                                    <template x-if="model.supports_text_input">
                                                        <span class="px-2 py-0.5 rounded bg-blue-500/20 text-blue-300 text-xs">📝 Text</span>
                                                    </template>
                                                    <template x-if="model.supports_image_input">
                                                        <span class="px-2 py-0.5 rounded bg-pink-500/20 text-pink-300 text-xs">🖼️ Image</span>
                                                    </template>
                                                    <template x-if="model.uses_image_prompt">
                                                        <span class="px-2 py-0.5 rounded bg-cyan-500/20 text-cyan-300 text-xs">🧪 Prompt</span>
                                                    </template>
                                                </div>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </template>
                            
                            <!-- No Results -->
                            <div x-show="Object.keys(filteredGroups).length === 0 && search.length === 0" class="text-center py-8 text-white/40">
                                <i class="fa-solid fa-search mb-2" style="font-size: 24px;"></i>
                                <p class="text-sm">No models found</p>
                            </div>
                        </div>
                        
                            <p class="mt-2 text-xs text-white/40">
                            🖼️ Image input | 🔧 Aspect ratio | 🧪 Image prompt
                        </p>
                        
                        @error('bfl_model_id')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="base_prompt" class="block text-sm font-medium text-white/70 mb-2">Base Prompt *</label>
                        <textarea id="base_prompt" name="base_prompt" rows="4"
                                  class="w-full px-4 py-3 rounded-xl bg-white/[0.03] border border-white/[0.08] text-white/90 font-mono text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/40 focus:border-purple-500/40 transition-all resize-none"
                                  placeholder="A professional portrait photo..." required>{{ old('base_prompt') }}</textarea>
                        @error('base_prompt')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- [FIX loi.md #8] Dynamic aspect ratios from config --}}
                    <div>
                        <label for="aspect_ratio" class="block text-sm font-medium text-white/70 mb-2">Aspect Ratio mặc định</label>
                        <select id="aspect_ratio" name="aspect_ratio"
                                class="w-full px-4 py-3 rounded-xl bg-white/[0.03] border border-white/[0.08] text-white/90 focus:outline-none focus:ring-2 focus:ring-purple-500/40 focus:border-purple-500/40 transition-all">
                            <option value="">Mặc định (1:1)</option>
                            @foreach($aspectRatios as $ratio => $label)
                                <option value="{{ $ratio }}" {{ old('aspect_ratio') == $ratio ? 'selected' : '' }}>{{ $ratio }} - {{ $label }}</option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-white/40">Aspect ratio sẽ được map sang kích thước phù hợp nếu model không hỗ trợ trực tiếp</p>
                    </div>

                    <div class="flex items-center gap-3">
                        <input type="checkbox" id="allow_user_custom_prompt" name="allow_user_custom_prompt" value="1" 
                               class="w-5 h-5 rounded bg-white/[0.03] border-white/[0.15] text-purple-500 focus:ring-purple-500/50"
                               {{ old('allow_user_custom_prompt') ? 'checked' : '' }}>
                        <label for="allow_user_custom_prompt" class="text-sm text-white/70">Cho phép người dùng nhập thêm mô tả</label>
                    </div>

                    <div class="flex items-center gap-3">
                        <input type="checkbox" id="is_active" name="is_active" value="1" checked
                               class="w-5 h-5 rounded bg-white/[0.03] border-white/[0.15] text-purple-500 focus:ring-purple-500/50"
                               {{ old('is_active', true) ? 'checked' : '' }}>
                        <label for="is_active" class="text-sm text-white/70">Kích hoạt style</label>
                    </div>
                </div>
            </div>

            <!-- Image Slots Config -->
            <div class="bg-white/[0.03] border border-white/[0.08] rounded-2xl p-6" x-data="{
                slots: [],
                addSlot() {
                    this.slots.push({ key: 'slot_' + Date.now(), label: '', description: '', required: false });
                },
                removeSlot(index) {
                    this.slots.splice(index, 1);
                }
            }">
                <h2 class="text-lg font-semibold text-white mb-4 inline-flex items-center gap-2">
                    <i class="fa-solid fa-images text-pink-400" style="font-size: 18px;"></i>
                    <span>Cấu hình ô upload ảnh</span>
                </h2>
                <p class="text-white/40 text-sm mb-4">Mỗi ô có Label (hiển thị cho user) và Mô tả cho AI (để AI hiểu ảnh này dùng làm gì)</p>

                <!-- Slots List -->
                <div class="space-y-3 mb-4">
                    <template x-for="(slot, index) in slots" :key="slot.key">
                        <div class="p-4 bg-white/[0.02] border border-white/[0.05] rounded-xl space-y-3">
                            <div class="flex items-center gap-3">
                                <div class="flex-1">
                                    <input 
                                        type="text" 
                                        x-model="slot.label"
                                        :name="'image_slots[' + index + '][label]'"
                                        placeholder="Label hiển thị (VD: Ảnh người 1)"
                                        class="w-full px-3 py-2 rounded-lg bg-white/[0.03] border border-white/[0.08] text-white/90 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/40">
                                    <input type="hidden" :name="'image_slots[' + index + '][key]'" :value="slot.key">
                                </div>
                                <label class="flex items-center gap-2 text-xs text-white/60 cursor-pointer whitespace-nowrap">
                                    <input type="checkbox" 
                                           x-model="slot.required"
                                           :name="'image_slots[' + index + '][required]'"
                                           value="1"
                                           class="w-4 h-4 rounded bg-white/[0.03] border-white/[0.15] text-purple-500">
                                    <span>Bắt buộc</span>
                                </label>
                                <button type="button" @click="removeSlot(index)" class="w-8 h-8 rounded-lg bg-red-500/10 border border-red-500/30 text-red-400 hover:bg-red-500/20 inline-flex items-center justify-center transition-colors">
                                    <i class="fa-solid fa-times" style="font-size: 12px;"></i>
                                </button>
                            </div>
                            <div>
                                <input 
                                    type="text" 
                                    x-model="slot.description"
                                    :name="'image_slots[' + index + '][description]'"
                                    placeholder="Mô tả cho AI (VD: This is the main person to be transformed into the style)"
                                    class="w-full px-3 py-2 rounded-lg bg-white/[0.03] border border-white/[0.08] text-white/70 text-xs font-mono focus:outline-none focus:ring-2 focus:ring-cyan-500/40">
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Empty State -->
                <div x-show="slots.length === 0" class="text-center py-4 text-white/30 text-sm">
                    Chưa có ô upload nào. Bấm nút bên dưới để thêm.
                </div>

                <!-- Add Button -->
                <button type="button" @click="addSlot()" class="w-full py-2.5 rounded-xl border-2 border-dashed border-white/[0.1] hover:border-purple-500/50 text-white/50 hover:text-purple-400 text-sm inline-flex items-center justify-center gap-2 transition-colors">
                    <i class="fa-solid fa-plus" style="font-size: 12px;"></i>
                    <span>Thêm ô upload ảnh</span>
                </button>
            </div>

            <!-- System Images Config -->
            <div class="bg-white/[0.03] border border-white/[0.08] rounded-2xl p-6" x-data="{
                systemImages: [],
                addSystemImage() {
                    this.systemImages.push({ file: null, preview: null, label: '', description: '' });
                },
                removeSystemImage(index) {
                    this.systemImages.splice(index, 1);
                },
                handleFileSelect(event, index) {
                    const file = event.target.files[0];
                    if (file) {
                        this.systemImages[index].file = file;
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            this.systemImages[index].preview = e.target.result;
                        };
                        reader.readAsDataURL(file);
                    }
                }
            }">
                <h2 class="text-lg font-semibold text-white mb-4 inline-flex items-center gap-2">
                    <i class="fa-solid fa-layer-group text-cyan-400" style="font-size: 18px;"></i>
                    <span>Ảnh hệ thống (Background/Overlay)</span>
                </h2>
                <p class="text-white/40 text-sm mb-4">Ảnh nền, khung, overlay sẽ được gửi kèm với ảnh user lên AI</p>

                <!-- System Images List -->
                <div class="space-y-3 mb-4">
                    <template x-for="(img, index) in systemImages" :key="index">
                        <div class="p-4 bg-white/[0.02] border border-white/[0.05] rounded-xl space-y-3">
                            <div class="flex items-start gap-3">
                                <!-- Preview -->
                                <div class="w-24 h-24 rounded-lg overflow-hidden bg-black/20 flex-shrink-0">
                                    <template x-if="img.preview">
                                        <img :src="img.preview" alt="Preview" class="w-full h-full object-cover">
                                    </template>
                                    <template x-if="!img.preview">
                                        <div class="w-full h-full flex items-center justify-center text-white/30">
                                            <i class="fa-solid fa-image" style="font-size: 24px;"></i>
                                        </div>
                                    </template>
                                </div>
                                
                                <div class="flex-1 space-y-2">
                                    <!-- File Input -->
                                    <input 
                                        type="file" 
                                        :name="'system_images_files[]'"
                                        accept="image/*"
                                        @change="handleFileSelect($event, index)"
                                        class="text-sm text-white/70 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-purple-500/20 file:text-purple-300 file:cursor-pointer hover:file:bg-purple-500/30">
                                    
                                    <!-- Label -->
                                    <input 
                                        type="text" 
                                        x-model="img.label"
                                        :name="'system_images_labels[]'"
                                        placeholder="Label (VD: Nền tuyết)"
                                        class="w-full px-3 py-2 rounded-lg bg-white/[0.03] border border-white/[0.08] text-white/90 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/40">
                                    
                                    <!-- Description for AI -->
                                    <input 
                                        type="text" 
                                        x-model="img.description"
                                        :name="'system_images_descriptions[]'"
                                        placeholder="Mô tả cho AI (VD: This is the winter background scene)"
                                        class="w-full px-3 py-2 rounded-lg bg-white/[0.03] border border-white/[0.08] text-white/70 text-xs font-mono focus:outline-none focus:ring-2 focus:ring-cyan-500/40">
                                </div>
                                
                                <!-- Remove Button -->
                                <button type="button" @click="removeSystemImage(index)" class="w-8 h-8 rounded-lg bg-red-500/10 border border-red-500/30 text-red-400 hover:bg-red-500/20 inline-flex items-center justify-center transition-colors">
                                    <i class="fa-solid fa-times" style="font-size: 12px;"></i>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Empty State -->
                <div x-show="systemImages.length === 0" class="text-center py-4 text-white/30 text-sm">
                    Chưa có ảnh hệ thống nào. Bấm nút bên dưới để thêm.
                </div>

                <!-- Add Button -->
                <button type="button" @click="addSystemImage()" class="w-full py-2.5 rounded-xl border-2 border-dashed border-white/[0.1] hover:border-cyan-500/50 text-white/50 hover:text-cyan-400 text-sm inline-flex items-center justify-center gap-2 transition-colors">
                    <i class="fa-solid fa-plus" style="font-size: 12px;"></i>
                    <span>Thêm ảnh hệ thống</span>
                </button>
            </div>

            <!-- Submit -->
            <div class="flex items-center gap-4">
                <button type="submit" class="px-8 py-3 rounded-xl bg-gradient-to-r from-purple-500 to-pink-500 text-white font-semibold flex items-center gap-2 hover:shadow-[0_8px_30px_rgba(168,85,247,0.5)] transition-all">
                    <i class="fa-solid fa-plus w-4 h-4"></i> Tạo Style
                </button>
                <a href="{{ route('admin.styles.index') }}" class="px-6 py-3 rounded-xl bg-white/[0.05] border border-white/[0.1] text-white/80 font-medium hover:bg-white/[0.1] transition-all">
                    Hủy
                </a>
            </div>
        </form>
    </div>
</x-app-layout>
