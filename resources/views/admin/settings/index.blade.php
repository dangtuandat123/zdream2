<x-app-layout>
    <x-slot name="title">Cài đặt hệ thống - Admin | ZDream</x-slot>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 py-6 sm:py-8">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-white">Cài đặt hệ thống</h1>
            <p class="text-white/50 text-sm">Quản lý API keys và cấu hình chung</p>
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

        <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- API Settings -->
            <div class="bg-white/[0.03] border border-white/[0.08] rounded-2xl p-6">
                <h2 class="text-lg font-semibold text-white mb-4 inline-flex items-center gap-2">
                    <i class="fa-solid fa-key text-yellow-400" style="font-size: 18px;"></i>
                    <span>API Configuration</span>
                </h2>

                <div class="space-y-4">
                    <div>
                        <label for="bfl_api_key" class="block text-sm font-medium text-white/70 mb-2">
                            BFL API Key
                        </label>
                        <div class="relative">
                            <input id="bfl_api_key" type="password" name="bfl_api_key"
                                placeholder="bfl_xxx (để trống nếu không đổi)"
                                class="w-full px-4 py-3 rounded-xl bg-white/[0.03] border border-white/[0.08] text-white/90 font-mono text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/40 transition-all pr-12">
                            <button type="button" onclick="togglePassword('bfl_api_key')"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-white/40 hover:text-white">
                                <i class="fa-solid fa-eye" style="font-size: 14px;"></i>
                            </button>
                        </div>
                        <p class="text-white/40 text-xs mt-1">
                            {{-- MEDIUM-04 FIX: Check if value is not empty, not just if row exists --}}
                            @php
                                $apiKey = \App\Models\Setting::get('bfl_api_key', '');
                            @endphp
                            @if(!empty($apiKey))
                                <span class="text-green-400">✓ Đã có API key được lưu</span>
                            @else
                                <span class="text-yellow-400">⚠ Chưa có API key</span>
                            @endif
                        </p>
                        @error('bfl_api_key')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="bfl_base_url" class="block text-sm font-medium text-white/70 mb-2">
                            BFL Base URL
                        </label>
                        <input id="bfl_base_url" type="url" name="bfl_base_url"
                            value="{{ \App\Models\Setting::get('bfl_base_url', 'https://api.bfl.ai') }}"
                            class="w-full px-4 py-3 rounded-xl bg-white/[0.03] border border-white/[0.08] text-white/90 font-mono text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/40 transition-all">
                        @error('bfl_base_url')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div
                        class="flex items-center justify-between gap-4 rounded-xl bg-white/[0.03] border border-white/[0.08] px-4 py-3">
                        <div>
                            <p class="text-sm text-white/80">Model list</p>
                            <p class="text-xs text-white/40">Refresh danh sách model BFL.</p>
                        </div>
                        <button type="submit" form="refresh-models-form"
                            class="px-4 py-2 rounded-xl bg-white/10 text-white/80 hover:bg-white/20 transition-all">
                            Refresh models
                        </button>
                    </div>
                </div>
            </div>

            <!-- General Settings -->
            <div class="bg-white/[0.03] border border-white/[0.08] rounded-2xl p-6">
                <h2 class="text-lg font-semibold text-white mb-4 inline-flex items-center gap-2">
                    <i class="fa-solid fa-cog text-purple-400" style="font-size: 18px;"></i>
                    <span>Cài đặt chung</span>
                </h2>

                <div class="space-y-4">
                    <div>
                        <label for="site_name" class="block text-sm font-medium text-white/70 mb-2">
                            Tên website
                        </label>
                        <input id="site_name" type="text" name="site_name"
                            value="{{ \App\Models\Setting::get('site_name', 'ZDream AI') }}"
                            class="w-full px-4 py-3 rounded-xl bg-white/[0.03] border border-white/[0.08] text-white/90 focus:outline-none focus:ring-2 focus:ring-purple-500/40 transition-all">
                        @error('site_name')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="default_credits" class="block text-sm font-medium text-white/70 mb-2">
                            Xu mặc định (tặng khi đăng ký)
                        </label>
                        <input id="default_credits" type="number" name="default_credits"
                            value="{{ \App\Models\Setting::get('default_credits', 10) }}" min="0"
                            class="w-full px-4 py-3 rounded-xl bg-white/[0.03] border border-white/[0.08] text-white/90 focus:outline-none focus:ring-2 focus:ring-purple-500/40 transition-all">
                        @error('default_credits')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="credit_exchange_rate" class="block text-sm font-medium text-white/70 mb-2">
                            Tỉ lệ VND / 1 Xu
                        </label>
                        <input id="credit_exchange_rate" type="number" name="credit_exchange_rate"
                            value="{{ \App\Models\Setting::get('credit_exchange_rate', 1000) }}" min="1"
                            class="w-full px-4 py-3 rounded-xl bg-white/[0.03] border border-white/[0.08] text-white/90 focus:outline-none focus:ring-2 focus:ring-purple-500/40 transition-all">
                        <p class="mt-1 text-sm text-white/40">
                            Ví dụ: 1000 nghĩa là 1.000 VND = 1 Xu.
                        </p>
                        @error('credit_exchange_rate')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="image_expiry_days" class="block text-sm font-medium text-white/70 mb-2">
                            Số ngày lưu ảnh (trước khi tự động xóa)
                        </label>
                        <input id="image_expiry_days" type="number" name="image_expiry_days"
                            value="{{ \App\Models\Setting::get('image_expiry_days', 30) }}" min="1" max="365"
                            class="w-full px-4 py-3 rounded-xl bg-white/[0.03] border border-white/[0.08] text-white/90 focus:outline-none focus:ring-2 focus:ring-purple-500/40 transition-all">
                        <p class="mt-1 text-sm text-white/40">
                            <i class="fa-solid fa-info-circle mr-1"></i>
                            Ảnh sẽ bị xóa tự động sau số ngày này. Mặc định: 30 ngày.
                        </p>
                        @error('image_expiry_days')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Edit Studio Settings -->
            <div class="bg-white/[0.03] border border-white/[0.08] rounded-2xl p-6">
                <h2 class="text-lg font-semibold text-white mb-4 inline-flex items-center gap-2">
                    <i class="fa-solid fa-wand-magic-sparkles text-cyan-400" style="font-size: 18px;"></i>
                    <span>Edit Studio Settings</span>
                </h2>
                <p class="text-white/40 text-sm mb-6">Cấu hình models và prompts cho chức năng chỉnh sửa ảnh AI.</p>

                <div class="space-y-6">
                    <!-- Replace Mode -->
                    <div class="bg-white/[0.02] rounded-xl p-4 border border-white/[0.05]">
                        <h3 class="text-sm font-medium text-white/80 mb-3 flex items-center gap-2">
                            <span class="w-6 h-6 rounded-lg bg-red-500/20 flex items-center justify-center">
                                <i class="fa-solid fa-eraser text-red-400 text-xs"></i>
                            </span>
                            Replace Mode
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="edit_studio_model_replace"
                                    class="block text-xs font-medium text-white/50 mb-1">Model</label>
                                <select name="edit_studio_model_replace" id="edit_studio_model_replace"
                                    class="w-full px-3 py-2 rounded-lg bg-white/[0.05] border border-white/[0.1] text-white/90 text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500/40">
                                    @foreach($editModels['fill'] as $modelId => $modelName)
                                        <option value="{{ $modelId }}" {{ $editStudioSettings['model_replace'] === $modelId ? 'selected' : '' }}>
                                            {{ $modelName }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="edit_studio_prompt_prefix_replace"
                                    class="block text-xs font-medium text-white/50 mb-1">Prompt Prefix</label>
                                <input type="text" name="edit_studio_prompt_prefix_replace"
                                    id="edit_studio_prompt_prefix_replace"
                                    value="{{ $editStudioSettings['prompt_prefix_replace'] }}"
                                    placeholder="(Để trống nếu không cần)"
                                    class="w-full px-3 py-2 rounded-lg bg-white/[0.05] border border-white/[0.1] text-white/90 text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500/40">
                            </div>
                        </div>
                    </div>

                    <!-- Text Mode -->
                    <div class="bg-white/[0.02] rounded-xl p-4 border border-white/[0.05]">
                        <h3 class="text-sm font-medium text-white/80 mb-3 flex items-center gap-2">
                            <span class="w-6 h-6 rounded-lg bg-blue-500/20 flex items-center justify-center">
                                <i class="fa-solid fa-font text-blue-400 text-xs"></i>
                            </span>
                            Text Mode
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="edit_studio_model_text"
                                    class="block text-xs font-medium text-white/50 mb-1">Model</label>
                                <select name="edit_studio_model_text" id="edit_studio_model_text"
                                    class="w-full px-3 py-2 rounded-lg bg-white/[0.05] border border-white/[0.1] text-white/90 text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500/40">
                                    @foreach($editModels['text'] as $modelId => $modelName)
                                        <option value="{{ $modelId }}" {{ $editStudioSettings['model_text'] === $modelId ? 'selected' : '' }}>
                                            {{ $modelName }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="edit_studio_prompt_prefix_text"
                                    class="block text-xs font-medium text-white/50 mb-1">Prompt Prefix</label>
                                <input type="text" name="edit_studio_prompt_prefix_text"
                                    id="edit_studio_prompt_prefix_text"
                                    value="{{ $editStudioSettings['prompt_prefix_text'] }}"
                                    placeholder="(Để trống nếu không cần)"
                                    class="w-full px-3 py-2 rounded-lg bg-white/[0.05] border border-white/[0.1] text-white/90 text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500/40">
                            </div>
                        </div>
                    </div>

                    <!-- Background Mode -->
                    <div class="bg-white/[0.02] rounded-xl p-4 border border-white/[0.05]">
                        <h3 class="text-sm font-medium text-white/80 mb-3 flex items-center gap-2">
                            <span class="w-6 h-6 rounded-lg bg-green-500/20 flex items-center justify-center">
                                <i class="fa-solid fa-image text-green-400 text-xs"></i>
                            </span>
                            Background Mode
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="edit_studio_model_background"
                                    class="block text-xs font-medium text-white/50 mb-1">Model</label>
                                <select name="edit_studio_model_background" id="edit_studio_model_background"
                                    class="w-full px-3 py-2 rounded-lg bg-white/[0.05] border border-white/[0.1] text-white/90 text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500/40">
                                    @foreach($editModels['fill'] as $modelId => $modelName)
                                        <option value="{{ $modelId }}" {{ $editStudioSettings['model_background'] === $modelId ? 'selected' : '' }}>
                                            {{ $modelName }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="edit_studio_prompt_prefix_background"
                                    class="block text-xs font-medium text-white/50 mb-1">Prompt Prefix</label>
                                <input type="text" name="edit_studio_prompt_prefix_background"
                                    id="edit_studio_prompt_prefix_background"
                                    value="{{ $editStudioSettings['prompt_prefix_background'] }}"
                                    placeholder="Keep the main subject..."
                                    class="w-full px-3 py-2 rounded-lg bg-white/[0.05] border border-white/[0.1] text-white/90 text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500/40">
                            </div>
                        </div>
                        <p class="text-xs text-white/40 mt-2">
                            <i class="fa-solid fa-lightbulb mr-1 text-yellow-400"></i>
                            Mặc định: "Keep the main subject exactly as is. Change the background to:"
                        </p>
                    </div>

                    <!-- Expand Mode -->
                    <div class="bg-white/[0.02] rounded-xl p-4 border border-white/[0.05]">
                        <h3 class="text-sm font-medium text-white/80 mb-3 flex items-center gap-2">
                            <span class="w-6 h-6 rounded-lg bg-purple-500/20 flex items-center justify-center">
                                <i class="fa-solid fa-expand text-purple-400 text-xs"></i>
                            </span>
                            Expand Mode
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="edit_studio_model_expand"
                                    class="block text-xs font-medium text-white/50 mb-1">Model</label>
                                <select name="edit_studio_model_expand" id="edit_studio_model_expand"
                                    class="w-full px-3 py-2 rounded-lg bg-white/[0.05] border border-white/[0.1] text-white/90 text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500/40">
                                    @foreach($editModels['expand'] as $modelId => $modelName)
                                        <option value="{{ $modelId }}" {{ $editStudioSettings['model_expand'] === $modelId ? 'selected' : '' }}>
                                            {{ $modelName }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="edit_studio_prompt_prefix_expand"
                                    class="block text-xs font-medium text-white/50 mb-1">Prompt Prefix</label>
                                <input type="text" name="edit_studio_prompt_prefix_expand"
                                    id="edit_studio_prompt_prefix_expand"
                                    value="{{ $editStudioSettings['prompt_prefix_expand'] }}"
                                    placeholder="(Để trống nếu không cần)"
                                    class="w-full px-3 py-2 rounded-lg bg-white/[0.05] border border-white/[0.1] text-white/90 text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500/40">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit -->
            <div class="flex items-center gap-4">
                <button type="submit"
                    class="px-8 py-3 rounded-xl bg-gradient-to-r from-purple-500 to-pink-500 text-white font-semibold inline-flex items-center gap-2 hover:shadow-[0_8px_30px_rgba(168,85,247,0.5)] transition-all">
                    <i class="fa-solid fa-save" style="font-size: 14px;"></i>
                    <span>Lưu cài đặt</span>
                </button>
            </div>
        </form>

        <form id="refresh-models-form" method="POST" action="{{ route('admin.settings.refresh-models') }}">
            @csrf
        </form>
    </div>

    <script>
        function togglePassword(id) {
            const input = document.getElementById(id);
            input.type = input.type === 'password' ? 'text' : 'password';
        }
    </script>
</x-app-layout>