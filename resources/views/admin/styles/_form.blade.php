{{-- Admin Style Form (Create/Edit) --}}
@php
    $isEdit = isset($style) && $style;
@endphp

<form action="{{ $isEdit ? route('admin.styles.update', $style) : route('admin.styles.store') }}" 
      method="POST"
      x-data="{
          options: {{ json_encode($isEdit ? $style->options->map(fn($o) => ['id' => $o->id, 'label' => $o->label, 'group_name' => $o->group_name, 'prompt_fragment' => $o->prompt_fragment])->values() : []) }},
          addOption() {
              this.options.push({ id: null, label: '', group_name: '', prompt_fragment: '' });
          },
          removeOption(index) {
              this.options.splice(index, 1);
          }
      }">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="space-y-6">
        {{-- Basic Info --}}
        <div class="p-6 rounded-2xl bg-white/[0.03] border border-white/[0.08]">
            <h3 class="text-lg font-semibold text-white/90 mb-4">Thông tin cơ bản</h3>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-white/70 mb-2">Tên Style *</label>
                    <input type="text" name="name" 
                           value="{{ old('name', $isEdit ? $style->name : '') }}"
                           required
                           class="w-full px-4 py-3 rounded-lg bg-white/[0.03] border border-white/[0.08] text-white/90 focus:outline-none focus:ring-2 focus:ring-primary-500/50"
                           placeholder="VD: Ảnh Tết 2026">
                    @error('name') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-white/70 mb-2">Mô tả</label>
                    <textarea name="description" rows="2"
                              class="w-full px-4 py-3 rounded-lg bg-white/[0.03] border border-white/[0.08] text-white/90 focus:outline-none focus:ring-2 focus:ring-primary-500/50 resize-none"
                              placeholder="Mô tả ngắn về style này...">{{ old('description', $isEdit ? $style->description : '') }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-white/70 mb-2">Thumbnail URL</label>
                    <input type="url" name="thumbnail_url" 
                           value="{{ old('thumbnail_url', $isEdit ? $style->thumbnail_url : '') }}"
                           class="w-full px-4 py-3 rounded-lg bg-white/[0.03] border border-white/[0.08] text-white/90 focus:outline-none focus:ring-2 focus:ring-primary-500/50"
                           placeholder="https://example.com/image.jpg">
                </div>

                <div>
                    <label class="block text-sm font-medium text-white/70 mb-2">Giá (Credits) *</label>
                    <input type="number" name="price" step="0.01" min="0"
                           value="{{ old('price', $isEdit ? $style->price : 1) }}"
                           required
                           class="w-full px-4 py-3 rounded-lg bg-white/[0.03] border border-white/[0.08] text-white/90 focus:outline-none focus:ring-2 focus:ring-primary-500/50">
                </div>
            </div>
        </div>

        {{-- BFL Config --}}
        <div class="p-6 rounded-2xl bg-white/[0.03] border border-white/[0.08]">
            <h3 class="text-lg font-semibold text-white/90 mb-4">Cấu hình AI</h3>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-white/70 mb-2">Model ID (BFL) *</label>
                    <select name="bfl_model_id" required
                            class="w-full px-4 py-3 rounded-lg bg-white/[0.03] border border-white/[0.08] text-white/90 focus:outline-none focus:ring-2 focus:ring-primary-500/50">
                        @foreach($models as $model)
                            @php
                                $modelId = is_array($model) ? ($model['id'] ?? '') : $model;
                            @endphp
                            @if($modelId !== '')
                                <option value="{{ $modelId }}" 
                                        {{ old('bfl_model_id', $isEdit ? ($style->bfl_model_id ?? $style->openrouter_model_id) : '') == $modelId ? 'selected' : '' }}>
                                    {{ $modelId }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-white/70 mb-2">Base Prompt *</label>
                    <textarea name="base_prompt" rows="4" required
                              class="w-full px-4 py-3 rounded-lg bg-white/[0.03] border border-white/[0.08] text-white/90 focus:outline-none focus:ring-2 focus:ring-primary-500/50 font-mono text-sm resize-none"
                              placeholder="A cinematic portrait photography of a person...">{{ old('base_prompt', $isEdit ? $style->base_prompt : '') }}</textarea>
                    @error('base_prompt') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-white/70 mb-2">Aspect Ratio</label>
                    <select name="aspect_ratio"
                            class="w-full px-4 py-3 rounded-lg bg-white/[0.03] border border-white/[0.08] text-white/90 focus:outline-none focus:ring-2 focus:ring-primary-500/50">
                        <option value="">-- Mặc định --</option>
                        @foreach($aspectRatios as $key => $ratio)
                            <option value="{{ $ratio }}" 
                                    {{ old('aspect_ratio', $isEdit ? ($style->config_payload['aspect_ratio'] ?? '') : '') == $ratio ? 'selected' : '' }}>
                                {{ $ratio }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex flex-wrap items-center gap-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="allow_user_custom_prompt" value="1"
                               {{ old('allow_user_custom_prompt', $isEdit ? $style->allow_user_custom_prompt : false) ? 'checked' : '' }}
                               class="w-5 h-5 rounded bg-white/[0.03] border-white/[0.08] text-primary-500 focus:ring-primary-500/50">
                        <span class="text-sm text-white/70">Cho phép user gõ thêm mô tả</span>
                    </label>

                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1"
                               {{ old('is_active', $isEdit ? $style->is_active : true) ? 'checked' : '' }}
                               class="w-5 h-5 rounded bg-white/[0.03] border-white/[0.08] text-primary-500 focus:ring-primary-500/50">
                        <span class="text-sm text-white/70">Active (hiển thị cho user)</span>
                    </label>

                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_featured" value="1"
                               {{ old('is_featured', $isEdit ? $style->is_featured : false) ? 'checked' : '' }}
                               class="w-5 h-5 rounded bg-white/[0.03] border-white/[0.08] text-orange-500 focus:ring-orange-500/50">
                        <span class="text-sm text-orange-400">🔥 Đánh dấu HOT</span>
                    </label>

                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_new" value="1"
                               {{ old('is_new', $isEdit ? $style->is_new : false) ? 'checked' : '' }}
                               class="w-5 h-5 rounded bg-white/[0.03] border-white/[0.08] text-cyan-500 focus:ring-cyan-500/50">
                        <span class="text-sm text-cyan-400">⚡ Đánh dấu MỚI</span>
                    </label>
                </div>
            </div>
        </div>

        {{-- Options (Dynamic) --}}
        <div class="p-6 rounded-2xl bg-white/[0.03] border border-white/[0.08]">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-white/90">Tùy chọn (Options)</h3>
                <button type="button" @click="addOption()"
                        class="px-3 py-1.5 text-sm rounded-lg bg-primary-500/20 text-primary-300 hover:bg-primary-500/30 transition-colors">
                    + Thêm Option
                </button>
            </div>

            <div class="space-y-4">
                <template x-for="(option, index) in options" :key="index">
                    <div class="p-4 rounded-xl bg-white/[0.02] border border-white/[0.05] space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-white/50">Option #<span x-text="index + 1"></span></span>
                            <button type="button" @click="removeOption(index)"
                                    class="text-red-400 hover:text-red-300 transition-colors text-sm">
                                Xóa
                            </button>
                        </div>
                        
                        <input type="hidden" :name="'options[' + index + '][id]'" x-model="option.id">
                        
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <input type="text" :name="'options[' + index + '][label]'" x-model="option.label"
                                       class="w-full px-3 py-2 text-sm rounded-lg bg-white/[0.03] border border-white/[0.08] text-white/90 focus:outline-none focus:ring-2 focus:ring-primary-500/50"
                                       placeholder="Label (VD: Làm mịn da)">
                            </div>
                            <div>
                                <input type="text" :name="'options[' + index + '][group_name]'" x-model="option.group_name"
                                       class="w-full px-3 py-2 text-sm rounded-lg bg-white/[0.03] border border-white/[0.08] text-white/90 focus:outline-none focus:ring-2 focus:ring-primary-500/50"
                                       placeholder="Group (VD: Skin)">
                            </div>
                        </div>
                        
                        <div>
                            <input type="text" :name="'options[' + index + '][prompt_fragment]'" x-model="option.prompt_fragment"
                                   class="w-full px-3 py-2 text-sm rounded-lg bg-white/[0.03] border border-white/[0.08] text-white/90 focus:outline-none focus:ring-2 focus:ring-primary-500/50"
                                   placeholder="Prompt Fragment (VD: , soft skin texture, highly detailed)">
                        </div>
                    </div>
                </template>

                <div x-show="options.length === 0" class="text-center py-4 text-white/40 text-sm">
                    Chưa có option nào. Bấm "+ Thêm Option" để thêm.
                </div>
            </div>
        </div>

        {{-- Submit --}}
        <div class="flex items-center gap-4">
            <button type="submit"
                    class="px-6 py-3 font-semibold rounded-xl bg-gradient-to-r from-primary-500 to-primary-600 text-white hover:from-primary-400 hover:to-primary-500 shadow-lg shadow-primary-500/25 transition-all">
                {{ $isEdit ? 'Cập nhật Style' : 'Tạo Style' }}
            </button>
            <a href="{{ route('admin.styles.index') }}" 
               class="px-6 py-3 font-medium rounded-xl bg-white/5 text-white/70 hover:bg-white/10 transition-colors">
                Hủy
            </a>
        </div>
    </div>
</form>
