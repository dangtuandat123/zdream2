<x-app-layout>
    <x-slot name="title">Sửa Option - {{ $option->label }} | Admin</x-slot>

    <div class="max-w-2xl mx-auto px-4 sm:px-6 py-6 sm:py-8">
        <div class="flex items-center gap-4 mb-6">
            <a href="{{ route('admin.styles.options.index', $style) }}" class="w-10 h-10 rounded-xl bg-white/[0.05] border border-white/[0.1] inline-flex items-center justify-center text-white/60 hover:text-white hover:bg-white/[0.1] transition-all">
                <i class="fa-solid fa-arrow-left" style="font-size: 14px;"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-white">Sửa Option</h1>
                <p class="text-white/50 text-sm">{{ $option->label }} - {{ $style->name }}</p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.styles.options.update', [$style, $option]) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="bg-white/[0.03] border border-white/[0.08] rounded-2xl p-6">
                <h2 class="text-lg font-semibold text-white mb-4 inline-flex items-center gap-2">
                    <i class="fa-solid fa-sliders text-purple-400" style="font-size: 16px;"></i>
                    Thông tin Option
                </h2>

                <div class="space-y-4">
                    <div>
                        <label for="label" class="block text-sm font-medium text-white/70 mb-2">Tên hiển thị *</label>
                        <input id="label" type="text" name="label" value="{{ old('label', $option->label) }}" 
                               class="w-full px-4 py-3 rounded-xl bg-white/[0.03] border border-white/[0.08] text-white/90 focus:outline-none focus:ring-2 focus:ring-purple-500/40 focus:border-purple-500/40 transition-all"
                               required>
                        @error('label')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="group_name" class="block text-sm font-medium text-white/70 mb-2">Tên nhóm *</label>
                        <input id="group_name" type="text" name="group_name" value="{{ old('group_name', $option->group_name) }}" 
                               list="existing_groups"
                               class="w-full px-4 py-3 rounded-xl bg-white/[0.03] border border-white/[0.08] text-white/90 focus:outline-none focus:ring-2 focus:ring-purple-500/40 focus:border-purple-500/40 transition-all"
                               required>
                        <datalist id="existing_groups">
                            @foreach($existingGroups as $group)
                                <option value="{{ $group }}">
                            @endforeach
                        </datalist>
                        @error('group_name')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="prompt_fragment" class="block text-sm font-medium text-white/70 mb-2">Prompt Fragment *</label>
                        <textarea id="prompt_fragment" name="prompt_fragment" rows="3"
                                  class="w-full px-4 py-3 rounded-xl bg-white/[0.03] border border-white/[0.08] text-white/90 font-mono text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/40 focus:border-purple-500/40 transition-all resize-none"
                                  required>{{ old('prompt_fragment', $option->prompt_fragment) }}</textarea>
                        @error('prompt_fragment')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="icon" class="block text-sm font-medium text-white/70 mb-2">Icon</label>
                            <input id="icon" type="text" name="icon" value="{{ old('icon', $option->icon) }}" 
                                   class="w-full px-4 py-3 rounded-xl bg-white/[0.03] border border-white/[0.08] text-white/90 focus:outline-none focus:ring-2 focus:ring-purple-500/40 focus:border-purple-500/40 transition-all"
                                   placeholder="fa-solid fa-star">
                        </div>
                        <div>
                            <label for="sort_order" class="block text-sm font-medium text-white/70 mb-2">Thứ tự</label>
                            <input id="sort_order" type="number" name="sort_order" value="{{ old('sort_order', $option->sort_order) }}" min="0"
                                   class="w-full px-4 py-3 rounded-xl bg-white/[0.03] border border-white/[0.08] text-white/90 focus:outline-none focus:ring-2 focus:ring-purple-500/40 focus:border-purple-500/40 transition-all">
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <input type="checkbox" id="is_default" name="is_default" value="1" 
                               class="w-5 h-5 rounded bg-white/[0.03] border-white/[0.15] text-purple-500 focus:ring-purple-500/50"
                               {{ old('is_default', $option->is_default) ? 'checked' : '' }}>
                        <label for="is_default" class="text-sm text-white/70">Đặt làm option mặc định</label>
                    </div>
                </div>
            </div>

            <!-- Submit -->
            <div class="flex items-center gap-4">
                <button type="submit" class="px-8 py-3 rounded-xl bg-gradient-to-r from-purple-500 to-pink-500 text-white font-semibold inline-flex items-center justify-center gap-2 hover:shadow-[0_8px_30px_rgba(168,85,247,0.5)] transition-all">
                    <i class="fa-solid fa-save" style="font-size: 14px;"></i>
                    <span>Lưu thay đổi</span>
                </button>
                <a href="{{ route('admin.styles.options.index', $style) }}" class="px-6 py-3 rounded-xl bg-white/[0.05] border border-white/[0.1] text-white/80 font-medium hover:bg-white/[0.1] transition-all">
                    Hủy
                </a>
            </div>
        </form>
    </div>
</x-app-layout>
