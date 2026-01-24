<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0a0a0f">

    <title>{{ $title ?? App\Models\Setting::get('site_name', 'ZDream') }}</title>

    <!-- Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="min-h-screen text-white antialiased">
    <!-- Background -->
    <div class="fixed inset-0 pointer-events-none">
        <div class="absolute inset-0 bg-[#0a0a0f]"></div>
        <div class="absolute top-0 left-1/4 w-64 sm:w-96 h-64 sm:h-96 bg-purple-600/20 rounded-full blur-[100px] sm:blur-[150px]"></div>
        <div class="absolute bottom-0 right-0 w-48 sm:w-80 h-48 sm:h-80 bg-pink-600/15 rounded-full blur-[80px] sm:blur-[130px]"></div>
    </div>

    <div class="relative min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 px-4">
        <!-- Logo -->
        <a href="{{ route('home') }}" class="flex items-center gap-2 group mb-6 sm:mb-8">
            <i class="fa-solid fa-wand-magic-sparkles w-6 h-6 text-purple-400 transition-transform duration-300 group-hover:rotate-12"></i>
            <span class="text-2xl font-bold bg-gradient-to-r from-purple-400 to-pink-500 bg-clip-text text-transparent">ZDream</span>
        </a>

        <!-- Card -->
        <div class="w-full sm:max-w-md bg-white/[0.03] backdrop-blur-xl border border-white/[0.08] rounded-2xl p-6 sm:p-8">
            {{ $slot }}
        </div>

        <!-- Footer -->
        <p class="mt-6 text-white/40 text-sm">© {{ date('Y') }} ZDream.vn</p>
    </div>
</body>
</html>
