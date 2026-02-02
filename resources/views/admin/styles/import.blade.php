<x-app-layout>
    <x-slot name="title">Import Styles - Admin | ZDream</x-slot>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 py-6 sm:py-8">
        <div class="flex items-center gap-4 mb-6">
            <a href="{{ route('admin.styles.index') }}" class="w-10 h-10 rounded-xl bg-white/[0.05] border border-white/[0.1] flex items-center justify-center text-white/60 hover:text-[#d3d6db] hover:bg-white/[0.1] transition-all">
                <i class="fa-solid fa-arrow-left w-4 h-4"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-[#d3d6db]">Import Styles</h1>
                <p class="text-white/50 text-sm">Nh?p hàng lo?t style t? JSON</p>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 rounded-xl bg-green-500/10 border border-green-500/30 text-green-400 flex items-center gap-2">
                <i class="fa-solid fa-check-circle w-5 h-5"></i>
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 p-4 rounded-xl bg-red-500/10 border border-red-500/30 text-red-400 flex items-center gap-2">
                <i class="fa-solid fa-exclamation-circle w-5 h-5"></i>
                {{ session('error') }}
            </div>
        @endif

        <div class="bg-white/[0.03] border border-white/[0.08] rounded-2xl p-6">
            <h2 class="text-lg font-semibold text-[#d3d6db] mb-2">Hu?ng d?n nhanh</h2>
            <p class="text-sm text-white/50 mb-4">
                Ð?nh d?ng JSON theo m?u bên du?i. Xem thêm file hu?ng d?n: <span class="text-white/70 font-mono">v? d? án/STYLE_GUIDE.md</span>.
            </p>

            <div class="bg-black/30 border border-white/[0.08] rounded-xl p-4 text-xs text-white/70 font-mono mb-6 whitespace-pre-wrap">
{
  "version": 1,
  "styles": [
    {
      "name": "Portrait Studio GenZ",
      "slug": "portrait-studio-genz",
      "thumbnail_url": "https://...",
      "description": "Phong cách chân dung tr? trung",
      "price": 2,
      "sort_order": 0,
      "bfl_model_id": "flux-dev",
      "base_prompt": "Professional portrait, clean background",
      "tag": "HOT",
      "allow_user_custom_prompt": true,
      "is_active": true,
      "config_payload": {
        "aspect_ratio": "1:1",
        "prompt_strategy": "standard",
        "prompt_defaults": {
          "lighting": "soft studio light",
          "mood": "fresh, confident"
        }
      },
      "options": [
        { "label": "Áo hoodie", "group_name": "Trang ph?c", "prompt_fragment": "hoodie", "is_default": true }
      ],
      "image_slots": [
        { "key": "image", "label": "?nh g?c", "description": "?nh c?n ch?nh s?a", "required": true },
        { "key": "mask", "label": "Mask (tùy ch?n)", "description": "Ðen = gi?, tr?ng = ch?nh", "required": false }
      ],
      "system_images": [
        { "label": "M?u n?n", "description": "Gi? tone màu", "url": "https://..." },
        { "label": "System image (blob)", "description": "?nh tham chi?u luu trên BFL blob", "blob_path": "blob:xxxxx/xxxxx" }
      ]
    }
  ]
}
            </div>

            <form method="POST" action="{{ route('admin.styles.import.store') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-white/70 mb-2">T?i file JSON</label>
                    <input type="file" name="import_file" accept=".json,.txt"
                           class="w-full px-4 py-3 rounded-xl bg-white/[0.03] border border-white/[0.08] text-white/80 focus:outline-none focus:ring-2 focus:ring-purple-500/40 focus:border-purple-500/40 transition-all">
                </div>

                <div>
                    <label class="block text-sm font-medium text-white/70 mb-2">Ho?c dán JSON tr?c ti?p</label>
                    <textarea name="import_text" rows="10"
                              class="w-full px-4 py-3 rounded-xl bg-white/[0.03] border border-white/[0.08] text-white/90 font-mono text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/40 focus:border-purple-500/40 transition-all resize-none"
                              placeholder="Dán JSON vào dây...">{{ old('import_text') }}</textarea>
                </div>

                <div class="flex items-center gap-2">
                    <input id="dry_run" type="checkbox" name="dry_run" value="1" class="rounded border-white/20 bg-white/5 text-purple-400 focus:ring-purple-500/40">
                    <label for="dry_run" class="text-sm text-white/60">Ch?y th? (không luu vào DB)</label>
                </div>

                <div class="flex items-center gap-3">
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-purple-500 to-pink-500 text-[#d3d6db] font-medium hover:shadow-[0_8px_30px_rgba(168,85,247,0.5)] transition-all">
                        <i class="fa-solid fa-file-import mr-2"></i> Import styles
                    </button>
                    <a href="{{ route('admin.styles.index') }}" class="px-5 py-2.5 rounded-xl bg-white/[0.05] border border-white/[0.1] text-white/80 font-medium hover:bg-white/[0.1] transition-all">
                        Quay l?i
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>


