<x-app-layout>
    <x-slot name="title">Thêm Option - {{ $style->name }} | Admin</x-slot>

    <div class="max-w-2xl mx-auto px-4 sm:px-6 py-6 sm:py-8">
        <div class="flex items-center gap-4 mb-6">
            <a href="{{ route('admin.styles.options.index', $style) }}" class="w-10 h-10 rounded-xl bg-white/[0.05] border border-white/[0.1] inline-flex items-center justify-center text-white/60 hover:text-white hover:bg-white/[0.1] transition-all">
                <i class="fa-solid fa-arrow-left" style="font-size: 14px;"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-white">Thêm Option</h1>
                <p class="text-white/50 text-sm">Style: {{ $style->name }}</p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.styles.options.store', $style) }}" class="space-y-6" enctype="multipart/form-data">
            @csrf

            <div class="bg-white/[0.03] border border-white/[0.08] rounded-2xl p-6">
                <h2 class="text-lg font-semibold text-white mb-4 inline-flex items-center gap-2">
                    <i class="fa-solid fa-sliders text-purple-400" style="font-size: 16px;"></i>
                    Thông tin Option
                </h2>

                <div class="space-y-4">
                    <div>
                        <label for="label" class="block text-sm font-medium text-white/70 mb-2">Tên hiển thị *</label>
                        <input id="label" type="text" name="label" value="{{ old('label') }}" 
                               class="w-full px-4 py-3 rounded-xl bg-white/[0.03] border border-white/[0.08] text-white/90 focus:outline-none focus:ring-2 focus:ring-purple-500/40 focus:border-purple-500/40 transition-all"
                               placeholder="VD: Làm mịn da" required>
                        @error('label')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="group_name" class="block text-sm font-medium text-white/70 mb-2">Tên nhóm *</label>
                        <input id="group_name" type="text" name="group_name" value="{{ old('group_name') }}" 
                               list="existing_groups"
                               class="w-full px-4 py-3 rounded-xl bg-white/[0.03] border border-white/[0.08] text-white/90 focus:outline-none focus:ring-2 focus:ring-purple-500/40 focus:border-purple-500/40 transition-all"
                               placeholder="VD: Làn da, Ánh sáng, Background" required>
                        <datalist id="existing_groups">
                            @foreach($existingGroups as $group)
                                <option value="{{ $group }}">
                            @endforeach
                        </datalist>
                        @error('group_name')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-white/40">Các options cùng nhóm sẽ hiển thị cạnh nhau</p>
                    </div>

                    <div>
                        <label for="prompt_fragment" class="block text-sm font-medium text-white/70 mb-2">Prompt Fragment *</label>
                        <textarea id="prompt_fragment" name="prompt_fragment" rows="3"
                                  class="w-full px-4 py-3 rounded-xl bg-white/[0.03] border border-white/[0.08] text-white/90 font-mono text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/40 focus:border-purple-500/40 transition-all resize-none"
                                  placeholder=", smooth soft skin texture, highly detailed pores"
                                  required>{{ old('prompt_fragment') }}</textarea>
                        @error('prompt_fragment')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-white/40">Đoạn text sẽ nối vào cuối base prompt. Nên bắt đầu bằng dấu phẩy.</p>
                    </div>

                    <!-- Thumbnail Upload -->
                    <div>
                        <label for="thumbnail" class="block text-sm font-medium text-white/70 mb-2">Thumbnail (ảnh vuông nhỏ)</label>
                        <div class="flex items-center gap-4">
                            <div class="w-16 h-16 rounded-xl bg-white/[0.05] border border-white/[0.1] flex items-center justify-center overflow-hidden" id="thumbnail-preview">
                                <i class="fa-solid fa-image text-white/30" style="font-size: 20px;"></i>
                            </div>
                            <div class="flex-1">
                                <input id="thumbnail" type="file" name="thumbnail" accept="image/jpeg,image/png,image/webp"
                                       class="w-full text-sm text-white/70 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-purple-500/20 file:text-purple-300 file:cursor-pointer hover:file:bg-purple-500/30 transition-all"
                                       onchange="previewThumbnail(this)">
                                <p class="mt-1 text-xs text-white/40">PNG, JPG, WebP - max 1MB. Kích thước đề xuất: 100x100px</p>
                            </div>
                        </div>
                        @error('thumbnail')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="icon" class="block text-sm font-medium text-white/70 mb-2">Icon (tùy chọn)</label>
                            <input id="icon" type="text" name="icon" value="{{ old('icon') }}" 
                                   class="w-full px-4 py-3 rounded-xl bg-white/[0.03] border border-white/[0.08] text-white/90 focus:outline-none focus:ring-2 focus:ring-purple-500/40 focus:border-purple-500/40 transition-all"
                                   placeholder="fa-solid fa-star">
                        </div>
                        <div>
                            <label for="sort_order" class="block text-sm font-medium text-white/70 mb-2">Thứ tự</label>
                            <input id="sort_order" type="number" name="sort_order" value="{{ old('sort_order', 0) }}" min="0"
                                   class="w-full px-4 py-3 rounded-xl bg-white/[0.03] border border-white/[0.08] text-white/90 focus:outline-none focus:ring-2 focus:ring-purple-500/40 focus:border-purple-500/40 transition-all">
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <input type="checkbox" id="is_default" name="is_default" value="1" 
                               class="w-5 h-5 rounded bg-white/[0.03] border-white/[0.15] text-purple-500 focus:ring-purple-500/50"
                               {{ old('is_default') ? 'checked' : '' }}>
                        <label for="is_default" class="text-sm text-white/70">Đặt làm option mặc định (tự động chọn khi user mở trang)</label>
                    </div>
                </div>
            </div>

            <script>
                function previewThumbnail(input) {
                    const preview = document.getElementById('thumbnail-preview');
                    if (input.files && input.files[0]) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            preview.innerHTML = '<img src="' + e.target.result + '" class="w-full h-full object-cover">';
                        };
                        reader.readAsDataURL(input.files[0]);
                    }
                }
            </script>

            <!-- Submit -->
            <div class="flex items-center gap-4">
                <button type="submit" class="px-8 py-3 rounded-xl bg-gradient-to-r from-purple-500 to-pink-500 text-white font-semibold inline-flex items-center justify-center gap-2 hover:shadow-[0_8px_30px_rgba(168,85,247,0.5)] transition-all">
                    <i class="fa-solid fa-plus" style="font-size: 14px;"></i>
                    <span>Thêm Option</span>
                </button>
                <a href="{{ route('admin.styles.options.index', $style) }}" class="px-6 py-3 rounded-xl bg-white/[0.05] border border-white/[0.1] text-white/80 font-medium hover:bg-white/[0.1] transition-all">
                    Hủy
                </a>
            </div>
        </form>
    </div>
</x-app-layout>
