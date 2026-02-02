<x-app-layout>
    <x-slot name="title">Tạo Tag - Admin | ZDream</x-slot>

    <div class="max-w-2xl mx-auto px-4 sm:px-6 py-6 sm:py-8">
        <div class="flex items-center gap-4 mb-6">
            <a href="{{ route('admin.tags.index') }}" class="w-10 h-10 rounded-xl bg-white/[0.05] border border-white/[0.1] flex items-center justify-center text-white/60 hover:text-[#d3d6db] hover:bg-white/[0.1] transition-all">
                <i class="fa-solid fa-arrow-left w-4 h-4"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-[#d3d6db]">Tạo Tag mới</h1>
                <p class="text-white/50 text-sm">Tag sẽ được gắn lên các styles</p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.tags.store') }}" class="space-y-6">
            @csrf

            <div class="bg-white/[0.03] border border-white/[0.08] rounded-2xl p-6 space-y-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-white/70 mb-2">Tên Tag *</label>
                    <input id="name" type="text" name="name" value="{{ old('name') }}" 
                           class="w-full px-4 py-3 rounded-xl bg-white/[0.03] border border-white/[0.08] text-white/90 focus:outline-none focus:ring-2 focus:ring-purple-500/40"
                           placeholder="VD: HOT, MỚI, SALE" required>
                    @error('name') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="color_from" class="block text-sm font-medium text-white/70 mb-2">Màu Gradient (từ) *</label>
                        <select id="color_from" name="color_from" class="w-full px-4 py-3 rounded-xl bg-white/[0.03] border border-white/[0.08] text-white/90 focus:outline-none focus:ring-2 focus:ring-purple-500/40" required>
                            <option value="orange-500" {{ old('color_from', 'orange-500') == 'orange-500' ? 'selected' : '' }}>🟠 Orange</option>
                            <option value="red-500" {{ old('color_from') == 'red-500' ? 'selected' : '' }}>🔴 Red</option>
                            <option value="pink-500" {{ old('color_from') == 'pink-500' ? 'selected' : '' }}>🩷 Pink</option>
                            <option value="purple-500" {{ old('color_from') == 'purple-500' ? 'selected' : '' }}>🟣 Purple</option>
                            <option value="cyan-500" {{ old('color_from') == 'cyan-500' ? 'selected' : '' }}>🔵 Cyan</option>
                            <option value="blue-500" {{ old('color_from') == 'blue-500' ? 'selected' : '' }}>🔷 Blue</option>
                            <option value="green-500" {{ old('color_from') == 'green-500' ? 'selected' : '' }}>🟢 Green</option>
                            <option value="yellow-500" {{ old('color_from') == 'yellow-500' ? 'selected' : '' }}>🟡 Yellow</option>
                        </select>
                    </div>
                    <div>
                        <label for="color_to" class="block text-sm font-medium text-white/70 mb-2">Màu Gradient (đến) *</label>
                        <select id="color_to" name="color_to" class="w-full px-4 py-3 rounded-xl bg-white/[0.03] border border-white/[0.08] text-white/90 focus:outline-none focus:ring-2 focus:ring-purple-500/40" required>
                            <option value="red-500" {{ old('color_to', 'red-500') == 'red-500' ? 'selected' : '' }}>🔴 Red</option>
                            <option value="orange-500" {{ old('color_to') == 'orange-500' ? 'selected' : '' }}>🟠 Orange</option>
                            <option value="pink-500" {{ old('color_to') == 'pink-500' ? 'selected' : '' }}>🩷 Pink</option>
                            <option value="purple-500" {{ old('color_to') == 'purple-500' ? 'selected' : '' }}>🟣 Purple</option>
                            <option value="cyan-500" {{ old('color_to') == 'cyan-500' ? 'selected' : '' }}>🔵 Cyan</option>
                            <option value="blue-500" {{ old('color_to') == 'blue-500' ? 'selected' : '' }}>🔷 Blue</option>
                            <option value="green-500" {{ old('color_to') == 'green-500' ? 'selected' : '' }}>🟢 Green</option>
                            <option value="yellow-500" {{ old('color_to') == 'yellow-500' ? 'selected' : '' }}>🟡 Yellow</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label for="icon" class="block text-sm font-medium text-white/70 mb-2">Icon (FontAwesome) *</label>
                    <select id="icon" name="icon" class="w-full px-4 py-3 rounded-xl bg-white/[0.03] border border-white/[0.08] text-white/90 focus:outline-none focus:ring-2 focus:ring-purple-500/40" required>
                        <option value="fa-fire" {{ old('icon', 'fa-fire') == 'fa-fire' ? 'selected' : '' }}>🔥 fa-fire</option>
                        <option value="fa-bolt" {{ old('icon') == 'fa-bolt' ? 'selected' : '' }}>⚡ fa-bolt</option>
                        <option value="fa-star" {{ old('icon') == 'fa-star' ? 'selected' : '' }}>⭐ fa-star</option>
                        <option value="fa-crown" {{ old('icon') == 'fa-crown' ? 'selected' : '' }}>👑 fa-crown</option>
                        <option value="fa-gem" {{ old('icon') == 'fa-gem' ? 'selected' : '' }}>💎 fa-gem</option>
                        <option value="fa-gift" {{ old('icon') == 'fa-gift' ? 'selected' : '' }}>🎁 fa-gift</option>
                        <option value="fa-percent" {{ old('icon') == 'fa-percent' ? 'selected' : '' }}>% fa-percent</option>
                        <option value="fa-tag" {{ old('icon') == 'fa-tag' ? 'selected' : '' }}>🏷️ fa-tag</option>
                        <option value="fa-heart" {{ old('icon') == 'fa-heart' ? 'selected' : '' }}>❤️ fa-heart</option>
                        <option value="fa-rocket" {{ old('icon') == 'fa-rocket' ? 'selected' : '' }}>🚀 fa-rocket</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="sort_order" class="block text-sm font-medium text-white/70 mb-2">Thứ tự</label>
                        <input id="sort_order" type="number" name="sort_order" value="{{ old('sort_order', 0) }}" min="0"
                               class="w-full px-4 py-3 rounded-xl bg-white/[0.03] border border-white/[0.08] text-white/90 focus:outline-none focus:ring-2 focus:ring-purple-500/40">
                    </div>
                    <div class="flex items-end">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                                   class="w-5 h-5 rounded bg-white/[0.03] border-white/[0.15] text-purple-500 focus:ring-purple-500/50">
                            <span class="text-sm text-white/70">Kích hoạt</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <button type="submit" class="px-8 py-3 rounded-xl bg-gradient-to-r from-purple-500 to-pink-500 text-[#d3d6db] font-semibold flex items-center gap-2 hover:shadow-[0_8px_30px_rgba(168,85,247,0.5)] transition-all">
                    <i class="fa-solid fa-save w-4 h-4"></i> Tạo Tag
                </button>
                <a href="{{ route('admin.tags.index') }}" class="px-6 py-3 rounded-xl bg-white/[0.05] border border-white/[0.1] text-white/80 font-medium hover:bg-white/[0.1] transition-all">
                    Hủy
                </a>
            </div>
        </form>
    </div>
</x-app-layout>
