<x-app-layout>
    <x-slot name="title">Sửa Style - Admin | ZDream</x-slot>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 py-6 sm:py-8">
        <div class="flex items-center gap-4 mb-6">
            <a href="{{ route('admin.styles.index') }}" class="w-10 h-10 rounded-xl bg-white/[0.05] border border-white/[0.1] flex items-center justify-center text-white/60 hover:text-white hover:bg-white/[0.1] transition-all">
                <i class="fa-solid fa-arrow-left w-4 h-4"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-white">Sửa Style</h1>
                <p class="text-white/50 text-sm">{{ $style->name }}</p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.styles.update', $style) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Basic Info -->
            <div class="bg-white/[0.03] border border-white/[0.08] rounded-2xl p-6">
                <h2 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-info-circle w-5 h-5 text-purple-400"></i>
                    Thông tin cơ bản
                </h2>

                <div class="space-y-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-white/70 mb-2">Tên Style *</label>
                        <input id="name" type="text" name="name" value="{{ old('name', $style->name) }}" 
                               class="w-full px-4 py-3 rounded-xl bg-white/[0.03] border border-white/[0.08] text-white/90 focus:outline-none focus:ring-2 focus:ring-purple-500/40 focus:border-purple-500/40 transition-all"
                               required>
                        @error('name')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="slug" class="block text-sm font-medium text-white/70 mb-2">Slug</label>
                        <input id="slug" type="text" name="slug" value="{{ old('slug', $style->slug) }}" 
                               class="w-full px-4 py-3 rounded-xl bg-white/[0.03] border border-white/[0.08] text-white/90 focus:outline-none focus:ring-2 focus:ring-purple-500/40 focus:border-purple-500/40 transition-all">
                        @error('slug')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-white/70 mb-2">Mô tả</label>
                        <textarea id="description" name="description" rows="2"
                                  class="w-full px-4 py-3 rounded-xl bg-white/[0.03] border border-white/[0.08] text-white/90 focus:outline-none focus:ring-2 focus:ring-purple-500/40 focus:border-purple-500/40 transition-all resize-none">{{ old('description', $style->description) }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="price" class="block text-sm font-medium text-white/70 mb-2">Giá (Xu) *</label>
                            <input id="price" type="number" name="price" value="{{ old('price', $style->price) }}" min="0"
                                   class="w-full px-4 py-3 rounded-xl bg-white/[0.03] border border-white/[0.08] text-white/90 focus:outline-none focus:ring-2 focus:ring-purple-500/40 focus:border-purple-500/40 transition-all"
                                   required>
                            @error('price')
                                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="sort_order" class="block text-sm font-medium text-white/70 mb-2">Thứ tự</label>
                            <input id="sort_order" type="number" name="sort_order" value="{{ old('sort_order', $style->sort_order) }}"
                                   class="w-full px-4 py-3 rounded-xl bg-white/[0.03] border border-white/[0.08] text-white/90 focus:outline-none focus:ring-2 focus:ring-purple-500/40 focus:border-purple-500/40 transition-all">
                        </div>
                    </div>

                    <div>
                        <label for="thumbnail_url" class="block text-sm font-medium text-white/70 mb-2">URL Ảnh thumbnail *</label>
                        <input id="thumbnail_url" type="url" name="thumbnail_url" value="{{ old('thumbnail_url', $style->thumbnail_url) }}" 
                               class="w-full px-4 py-3 rounded-xl bg-white/[0.03] border border-white/[0.08] text-white/90 focus:outline-none focus:ring-2 focus:ring-purple-500/40 focus:border-purple-500/40 transition-all"
                               required>
                        @error('thumbnail_url')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    @if($style->thumbnail_url)
                        <div class="p-3 rounded-xl bg-white/[0.02]">
                            <p class="text-xs text-white/40 mb-2">Preview:</p>
                            <img src="{{ $style->thumbnail_url }}" alt="Preview" class="w-32 h-40 object-cover rounded-lg">
                        </div>
                    @endif
                </div>
            </div>

            <!-- AI Config -->
            <div class="bg-white/[0.03] border border-white/[0.08] rounded-2xl p-6">
                <h2 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-microchip w-5 h-5 text-cyan-400"></i>
                    Cấu hình AI
                </h2>

                <div class="space-y-4">
                    <div>
                        <label for="openrouter_model_id" class="block text-sm font-medium text-white/70 mb-2">
                            OpenRouter Model *
                            <span class="text-white/40 font-normal">({{ count($models) }} models có khả năng tạo ảnh)</span>
                        </label>
                        <select id="openrouter_model_id" name="openrouter_model_id" required
                                class="w-full px-4 py-3 rounded-xl bg-white/[0.03] border border-white/[0.08] text-white/90 focus:outline-none focus:ring-2 focus:ring-purple-500/40 focus:border-purple-500/40 transition-all">
                            <option value="">Chọn model...</option>
                            @foreach($models as $model)
                                <option value="{{ $model['id'] }}" {{ old('openrouter_model_id', $style->openrouter_model_id) == $model['id'] ? 'selected' : '' }}>
                                    {{ $model['name'] }}
                                </option>
                            @endforeach
                        </select>
                        @error('openrouter_model_id')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="base_prompt" class="block text-sm font-medium text-white/70 mb-2">Base Prompt *</label>
                        <textarea id="base_prompt" name="base_prompt" rows="4"
                                  class="w-full px-4 py-3 rounded-xl bg-white/[0.03] border border-white/[0.08] text-white/90 font-mono text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/40 focus:border-purple-500/40 transition-all resize-none"
                                  required>{{ old('base_prompt', $style->base_prompt) }}</textarea>
                        @error('base_prompt')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center gap-3">
                        <input type="checkbox" id="allow_user_custom_prompt" name="allow_user_custom_prompt" value="1" 
                               class="w-5 h-5 rounded bg-white/[0.03] border-white/[0.15] text-purple-500 focus:ring-purple-500/50"
                               {{ old('allow_user_custom_prompt', $style->allow_user_custom_prompt) ? 'checked' : '' }}>
                        <label for="allow_user_custom_prompt" class="text-sm text-white/70">Cho phép người dùng nhập thêm mô tả</label>
                    </div>

                    <div class="flex items-center gap-3">
                        <input type="checkbox" id="is_active" name="is_active" value="1"
                               class="w-5 h-5 rounded bg-white/[0.03] border-white/[0.15] text-purple-500 focus:ring-purple-500/50"
                               {{ old('is_active', $style->is_active) ? 'checked' : '' }}>
                        <label for="is_active" class="text-sm text-white/70">Kích hoạt style</label>
                    </div>
                </div>
            </div>

            <!-- Image Slots Config -->
            <div class="bg-white/[0.03] border border-white/[0.08] rounded-2xl p-6" x-data="{
                slots: @js($style->image_slots ?? []),
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
                    <template x-for="(slot, index) in slots" :key="slot.key || index">
                        <div class="p-4 bg-white/[0.02] border border-white/[0.05] rounded-xl space-y-3">
                            <div class="flex items-center gap-3">
                                <div class="flex-1">
                                    <input 
                                        type="text" 
                                        x-model="slot.label"
                                        :name="'image_slots[' + index + '][label]'"
                                        placeholder="Label hiển thị (VD: Ảnh người 1)"
                                        class="w-full px-3 py-2 rounded-lg bg-white/[0.03] border border-white/[0.08] text-white/90 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/40">
                                    <input type="hidden" :name="'image_slots[' + index + '][key]'" :value="slot.key || 'slot_' + index">
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

            <!-- Submit -->
            <div class="flex items-center gap-4">
                <button type="submit" class="px-8 py-3 rounded-xl bg-gradient-to-r from-purple-500 to-pink-500 text-white font-semibold flex items-center gap-2 hover:shadow-[0_8px_30px_rgba(168,85,247,0.5)] transition-all">
                    <i class="fa-solid fa-save w-4 h-4"></i> Lưu thay đổi
                </button>
                <a href="{{ route('admin.styles.index') }}" class="px-6 py-3 rounded-xl bg-white/[0.05] border border-white/[0.1] text-white/80 font-medium hover:bg-white/[0.1] transition-all">
                    Hủy
                </a>
            </div>
        </form>
    </div>
</x-app-layout>
