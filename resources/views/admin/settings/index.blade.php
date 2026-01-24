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
                        <label for="openrouter_api_key" class="block text-sm font-medium text-white/70 mb-2">
                            OpenRouter API Key
                        </label>
                        <div class="relative">
                            <input 
                                id="openrouter_api_key" 
                                type="password" 
                                name="openrouter_api_key" 
                                placeholder="sk-or-v1-xxxxx (để trống nếu không đổi)"
                                class="w-full px-4 py-3 rounded-xl bg-white/[0.03] border border-white/[0.08] text-white/90 font-mono text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/40 transition-all pr-12">
                            <button type="button" onclick="togglePassword('openrouter_api_key')" class="absolute right-3 top-1/2 -translate-y-1/2 text-white/40 hover:text-white">
                                <i class="fa-solid fa-eye" style="font-size: 14px;"></i>
                            </button>
                        </div>
                        <p class="text-white/40 text-xs mt-1">
                            @if(\App\Models\Setting::where('key', 'openrouter_api_key')->exists())
                                <span class="text-green-400">✓ Đã có API key được lưu</span>
                            @else
                                <span class="text-yellow-400">⚠ Chưa có API key</span>
                            @endif
                        </p>
                        @error('openrouter_api_key')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="openrouter_base_url" class="block text-sm font-medium text-white/70 mb-2">
                            OpenRouter Base URL
                        </label>
                        <input 
                            id="openrouter_base_url" 
                            type="url" 
                            name="openrouter_base_url" 
                            value="{{ \App\Models\Setting::get('openrouter_base_url', 'https://openrouter.ai/api/v1') }}"
                            class="w-full px-4 py-3 rounded-xl bg-white/[0.03] border border-white/[0.08] text-white/90 font-mono text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/40 transition-all">
                        @error('openrouter_base_url')
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
                        <input 
                            id="site_name" 
                            type="text" 
                            name="site_name" 
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
                        <input 
                            id="default_credits" 
                            type="number" 
                            name="default_credits" 
                            value="{{ \App\Models\Setting::get('default_credits', 10) }}"
                            min="0"
                            class="w-full px-4 py-3 rounded-xl bg-white/[0.03] border border-white/[0.08] text-white/90 focus:outline-none focus:ring-2 focus:ring-purple-500/40 transition-all">
                        @error('default_credits')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Submit -->
            <div class="flex items-center gap-4">
                <button type="submit" class="px-8 py-3 rounded-xl bg-gradient-to-r from-purple-500 to-pink-500 text-white font-semibold inline-flex items-center gap-2 hover:shadow-[0_8px_30px_rgba(168,85,247,0.5)] transition-all">
                    <i class="fa-solid fa-save" style="font-size: 14px;"></i>
                    <span>Lưu cài đặt</span>
                </button>
            </div>
        </form>
    </div>

    <script>
        function togglePassword(id) {
            const input = document.getElementById(id);
            input.type = input.type === 'password' ? 'text' : 'password';
        }
    </script>
</x-app-layout>
