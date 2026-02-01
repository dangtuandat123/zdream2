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

            <!-- OpenRouter API Settings -->
            <div class="bg-white/[0.03] border border-white/[0.08] rounded-2xl p-6">
                <h2 class="text-lg font-semibold text-white mb-4 inline-flex items-center gap-2">
                    <i class="fa-solid fa-robot text-green-400" style="font-size: 18px;"></i>
                    <span>OpenRouter API (Translation)</span>
                </h2>

                <div class="space-y-4">
                    <div>
                        <label for="openrouter_api_key" class="block text-sm font-medium text-white/70 mb-2">
                            OpenRouter API Key
                        </label>
                        <div class="relative">
                            <input id="openrouter_api_key" type="password" name="openrouter_api_key"
                                placeholder="sk-or-v1-xxx (để trống nếu không đổi)"
                                class="w-full px-4 py-3 rounded-xl bg-white/[0.03] border border-white/[0.08] text-white/90 font-mono text-sm focus:outline-none focus:ring-2 focus:ring-green-500/40 transition-all pr-12">
                            <button type="button" onclick="togglePassword('openrouter_api_key')"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-white/40 hover:text-white">
                                <i class="fa-solid fa-eye" style="font-size: 14px;"></i>
                            </button>
                        </div>
                        <p class="text-white/40 text-xs mt-1">
                            @php
                                $orApiKey = \App\Models\Setting::get('openrouter_api_key', '');
                            @endphp
                            @if(!empty($orApiKey))
                                <span class="text-green-400">✓ Đã có API key</span>
                                @php
                                    try {
                                        $orService = app(\App\Services\OpenRouterService::class);
                                        $balance = $orService->checkBalance();
                                    } catch (\Exception $e) {
                                        $balance = ['error' => $e->getMessage()];
                                    }
                                @endphp
                                @if(isset($balance['success']) && $balance['success'])
                                    <span class="ml-2 text-blue-400">
                                        | Limit: ${{ number_format($balance['limit'] ?? 0, 2) }}
                                        | Remaining: ${{ number_format($balance['limit_remaining'] ?? 0, 2) }}
                                    </span>
                                @endif
                            @else
                                <span class="text-yellow-400">⚠ Chưa có API key (cần cho tính năng dịch prompt)</span>
                            @endif
                        </p>
                        <p class="text-white/30 text-xs mt-1">
                            <i class="fa-solid fa-info-circle mr-1"></i>
                            <a href="https://openrouter.ai/keys" target="_blank"
                                class="text-green-400 hover:underline">Lấy key tại đây</a>
                        </p>
                        @error('openrouter_api_key')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="translation_model" class="block text-sm font-medium text-white/70 mb-2">
                            Model dịch ngôn ngữ
                        </label>
                        <input id="translation_model" type="text" name="translation_model"
                            value="{{ \App\Models\Setting::get('translation_model', 'google/gemma-2-9b-it:free') }}"
                            placeholder="google/gemma-2-9b-it:free"
                            class="w-full px-4 py-3 rounded-xl bg-white/[0.03] border border-white/[0.08] text-white/90 font-mono text-sm focus:outline-none focus:ring-2 focus:ring-green-500/40 transition-all">
                        <p class="text-white/30 text-xs mt-1">
                            <i class="fa-solid fa-info-circle mr-1"></i>
                            Model free: google/gemma-2-9b-it:free | Tốt hơn: google/gemini-2.0-flash-001
                        </p>
                        @error('translation_model')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="translation_system_prompt" class="block text-sm font-medium text-white/70 mb-2">
                            System Prompt (chuyển đổi prompt)
                        </label>
                        @php
                            $defaultPrompt = "You are an AI image prompt expert acting as a translator/enhancer. Convert user input to a high-quality English prompt for FLUX AI.
                            Rules:
                            1. Translate to English.
                            2. Context is year 2026: Interpret generic terms as modern versions (e.g., \"phone\" = \"sleek 2026 bezel-less smartphone\").
                            3. add \"photorealistic, 8k, highly detailed\" style.
                            4. Auto-enhance vague requests with realistic details.
                            5. Only output the final prompt text. No explanations.";
                        @endphp
                        <textarea id="translation_system_prompt" name="translation_system_prompt" rows="6"
                            placeholder="You are an AI image prompt expert..."
                            class="w-full px-4 py-3 rounded-xl bg-white/[0.03] border border-white/[0.08] text-white/90 text-sm focus:outline-none focus:ring-2 focus:ring-green-500/40 transition-all resize-none">{{ \App\Models\Setting::get('translation_system_prompt', $defaultPrompt) }}</textarea>
                        <p class="text-white/30 text-xs mt-1">
                            <i class="fa-solid fa-info-circle mr-1"></i>
                            Hướng dẫn AI chuyển đổi prompt người dùng thành prompt tối ưu cho tạo ảnh
                        </p>
                        @error('translation_system_prompt')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
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

            <!-- Edit Studio Settings (Separate Page) -->
            <div class="bg-white/[0.03] border border-white/[0.08] rounded-2xl p-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="w-10 h-10 rounded-xl bg-cyan-500/20 flex items-center justify-center">
                            <i class="fa-solid fa-wand-magic-sparkles text-cyan-400"></i>
                        </span>
                        <div>
                            <h2 class="text-lg font-semibold text-white">Edit Studio Settings</h2>
                            <p class="text-white/40 text-sm">Cấu hình models và prompts cho chức năng chỉnh sửa ảnh AI
                            </p>
                        </div>
                    </div>
                    <a href="{{ route('admin.edit-studio.index') }}"
                        class="px-4 py-2 rounded-xl bg-cyan-500/20 text-cyan-400 hover:bg-cyan-500/30 transition-all inline-flex items-center gap-2">
                        <span>Cấu hình</span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>
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