<x-app-layout>
    <x-slot name="title">Edit Studio Settings - Admin | ZDream</x-slot>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 py-6 sm:py-8">
        <div class="mb-6">
            <div class="flex items-center gap-3 mb-2">
                <a href="{{ route('admin.settings.index') }}" class="text-white/40 hover:text-white transition-colors">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <h1 class="text-2xl font-bold text-white">Edit Studio Settings</h1>
            </div>
            <p class="text-white/50 text-sm">Cấu hình models và prompts cho chức năng chỉnh sửa ảnh AI</p>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 rounded-xl bg-green-500/10 border border-green-500/30 text-green-400">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 p-4 rounded-xl bg-red-500/10 border border-red-500/30 text-red-400">
                {{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.edit-studio.update') }}" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Replace Mode -->
            <div class="bg-white/[0.03] border border-white/[0.08] rounded-2xl p-6">
                <h2 class="text-lg font-semibold text-white mb-4 inline-flex items-center gap-3">
                    <span class="w-8 h-8 rounded-xl bg-red-500/20 flex items-center justify-center">
                        <i class="fa-solid fa-eraser text-red-400"></i>
                    </span>
                    <span>Replace Mode</span>
                </h2>
                <p class="text-white/40 text-sm mb-4">Xóa và thay thế vật thể trong ảnh bằng nội dung mới.</p>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="model_replace" class="block text-sm font-medium text-white/70 mb-2">Model</label>
                        <select name="model_replace" id="model_replace"
                            class="w-full px-4 py-3 rounded-xl bg-white/[0.03] border border-white/[0.08] text-white/90 text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500/40">
                            @foreach($models['fill'] as $modelId => $modelName)
                                <option value="{{ $modelId }}" {{ $settings['model_replace'] === $modelId ? 'selected' : '' }}>
                                    {{ $modelName }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="credit_cost_replace" class="block text-sm font-medium text-white/70 mb-2">
                            <i class="fa-solid fa-coins text-yellow-400 mr-1"></i>Giá (Xu)
                        </label>
                        <input type="number" step="0.01" min="0" name="credit_cost_replace" id="credit_cost_replace"
                            value="{{ $settings['credit_cost_replace'] }}"
                            class="w-full px-4 py-3 rounded-xl bg-white/[0.03] border border-white/[0.08] text-white/90 text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500/40">
                    </div>
                    <div>
                        <label for="prompt_prefix_replace" class="block text-sm font-medium text-white/70 mb-2">Prompt
                            Prefix</label>
                        <input type="text" name="prompt_prefix_replace" id="prompt_prefix_replace"
                            value="{{ $settings['prompt_prefix_replace'] }}" placeholder="(Để trống nếu không cần)"
                            class="w-full px-4 py-3 rounded-xl bg-white/[0.03] border border-white/[0.08] text-white/90 text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500/40">
                    </div>
                </div>
            </div>

            <!-- Text Mode -->
            <div class="bg-white/[0.03] border border-white/[0.08] rounded-2xl p-6">
                <h2 class="text-lg font-semibold text-white mb-4 inline-flex items-center gap-3">
                    <span class="w-8 h-8 rounded-xl bg-blue-500/20 flex items-center justify-center">
                        <i class="fa-solid fa-font text-blue-400"></i>
                    </span>
                    <span>Text Mode</span>
                </h2>
                <p class="text-white/40 text-sm mb-4">Chỉnh sửa, thêm hoặc thay đổi text trong ảnh.</p>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="model_text" class="block text-sm font-medium text-white/70 mb-2">Model</label>
                        <select name="model_text" id="model_text"
                            class="w-full px-4 py-3 rounded-xl bg-white/[0.03] border border-white/[0.08] text-white/90 text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500/40">
                            @foreach($models['text'] as $modelId => $modelName)
                                <option value="{{ $modelId }}" {{ $settings['model_text'] === $modelId ? 'selected' : '' }}>
                                    {{ $modelName }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="credit_cost_text" class="block text-sm font-medium text-white/70 mb-2">
                            <i class="fa-solid fa-coins text-yellow-400 mr-1"></i>Giá (Xu)
                        </label>
                        <input type="number" step="0.01" min="0" name="credit_cost_text" id="credit_cost_text"
                            value="{{ $settings['credit_cost_text'] }}"
                            class="w-full px-4 py-3 rounded-xl bg-white/[0.03] border border-white/[0.08] text-white/90 text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500/40">
                    </div>
                    <div>
                        <label for="prompt_prefix_text" class="block text-sm font-medium text-white/70 mb-2">Prompt
                            Prefix</label>
                        <input type="text" name="prompt_prefix_text" id="prompt_prefix_text"
                            value="{{ $settings['prompt_prefix_text'] }}" placeholder="(Để trống nếu không cần)"
                            class="w-full px-4 py-3 rounded-xl bg-white/[0.03] border border-white/[0.08] text-white/90 text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500/40">
                    </div>
                </div>
            </div>

            <!-- Background Mode -->
            <div class="bg-white/[0.03] border border-white/[0.08] rounded-2xl p-6">
                <h2 class="text-lg font-semibold text-white mb-4 inline-flex items-center gap-3">
                    <span class="w-8 h-8 rounded-xl bg-green-500/20 flex items-center justify-center">
                        <i class="fa-solid fa-image text-green-400"></i>
                    </span>
                    <span>Background Mode</span>
                </h2>
                <p class="text-white/40 text-sm mb-4">Thay đổi phông nền, giữ nguyên chủ thể.</p>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="model_background" class="block text-sm font-medium text-white/70 mb-2">Model</label>
                        <select name="model_background" id="model_background"
                            class="w-full px-4 py-3 rounded-xl bg-white/[0.03] border border-white/[0.08] text-white/90 text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500/40">
                            @foreach($models['fill'] as $modelId => $modelName)
                                <option value="{{ $modelId }}" {{ $settings['model_background'] === $modelId ? 'selected' : '' }}>
                                    {{ $modelName }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="credit_cost_background" class="block text-sm font-medium text-white/70 mb-2">
                            <i class="fa-solid fa-coins text-yellow-400 mr-1"></i>Giá (Xu)
                        </label>
                        <input type="number" step="0.01" min="0" name="credit_cost_background"
                            id="credit_cost_background" value="{{ $settings['credit_cost_background'] }}"
                            class="w-full px-4 py-3 rounded-xl bg-white/[0.03] border border-white/[0.08] text-white/90 text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500/40">
                    </div>
                    <div>
                        <label for="prompt_prefix_background"
                            class="block text-sm font-medium text-white/70 mb-2">Prompt Prefix</label>
                        <input type="text" name="prompt_prefix_background" id="prompt_prefix_background"
                            value="{{ $settings['prompt_prefix_background'] }}" placeholder="Keep the main subject..."
                            class="w-full px-4 py-3 rounded-xl bg-white/[0.03] border border-white/[0.08] text-white/90 text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500/40">
                    </div>
                </div>
                <p class="text-xs text-white/40 mt-3">
                    <i class="fa-solid fa-lightbulb mr-1 text-yellow-400"></i>
                    Mặc định: "Keep the main subject exactly as is. Change the background to:"
                </p>
            </div>

            <!-- Expand Mode -->
            <div class="bg-white/[0.03] border border-white/[0.08] rounded-2xl p-6">
                <h2 class="text-lg font-semibold text-white mb-4 inline-flex items-center gap-3">
                    <span class="w-8 h-8 rounded-xl bg-purple-500/20 flex items-center justify-center">
                        <i class="fa-solid fa-expand text-purple-400"></i>
                    </span>
                    <span>Expand Mode</span>
                </h2>
                <p class="text-white/40 text-sm mb-4">Mở rộng ảnh ra các hướng, tạo thêm nội dung mới.</p>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="model_expand" class="block text-sm font-medium text-white/70 mb-2">Model</label>
                        <select name="model_expand" id="model_expand"
                            class="w-full px-4 py-3 rounded-xl bg-white/[0.03] border border-white/[0.08] text-white/90 text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500/40">
                            @foreach($models['expand'] as $modelId => $modelName)
                                <option value="{{ $modelId }}" {{ $settings['model_expand'] === $modelId ? 'selected' : '' }}>
                                    {{ $modelName }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="credit_cost_expand" class="block text-sm font-medium text-white/70 mb-2">
                            <i class="fa-solid fa-coins text-yellow-400 mr-1"></i>Giá (Xu)
                        </label>
                        <input type="number" step="0.01" min="0" name="credit_cost_expand" id="credit_cost_expand"
                            value="{{ $settings['credit_cost_expand'] }}"
                            class="w-full px-4 py-3 rounded-xl bg-white/[0.03] border border-white/[0.08] text-white/90 text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500/40">
                    </div>
                    <div>
                        <label for="prompt_prefix_expand" class="block text-sm font-medium text-white/70 mb-2">Prompt
                            Prefix</label>
                        <input type="text" name="prompt_prefix_expand" id="prompt_prefix_expand"
                            value="{{ $settings['prompt_prefix_expand'] }}" placeholder="(Để trống nếu không cần)"
                            class="w-full px-4 py-3 rounded-xl bg-white/[0.03] border border-white/[0.08] text-white/90 text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500/40">
                    </div>
                </div>
            </div>

            <!-- Submit -->
            <div class="flex items-center gap-4">
                <button type="submit"
                    class="px-8 py-3 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-500 text-white font-semibold inline-flex items-center gap-2 hover:shadow-[0_8px_30px_rgba(6,182,212,0.5)] transition-all">
                    <i class="fa-solid fa-save" style="font-size: 14px;"></i>
                    <span>Lưu cài đặt</span>
                </button>
                <a href="{{ route('admin.settings.index') }}"
                    class="px-6 py-3 rounded-xl bg-white/10 text-white/70 hover:bg-white/20 transition-all">
                    Quay lại
                </a>
            </div>
        </form>
    </div>
</x-app-layout>