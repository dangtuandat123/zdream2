<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0a0a0f">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

    <title>{{ $title ?? App\Models\Setting::get('site_name', 'ZDream') . ' - Biến Ảnh Thường Thành Tác Phẩm AI' }}
    </title>
    <meta name="description" content="Chọn style → Upload ảnh → Nhận kết quả. Chỉ 3 bước, không cần prompt!">

    <!-- PWA -->
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/images/icon-192.svg">
    <meta name="mobile-web-app-capable" content="yes">

    <!-- Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- Alpine Plugins -->
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/intersect@3.x.x/dist/cdn.min.js"></script>

    <!-- Vite Assets (CSS + JS với Alpine) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Livewire Styles -->
    @livewireStyles

    <style>
        html {
            overflow-x: hidden;
            overflow-y: scroll;
            /* Always show scrollbar space */
            scrollbar-gutter: stable;
            /* Prevent layout shift */
        }

        body {
            overflow-x: hidden;
            overflow-y: hidden !important;
            /* Never show body scrollbar - only html */
            max-width: 100%;
            width: 100%;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #0a0a0f;
        }

        /* ========== SPA PAGE TRANSITIONS ========== */
        [wire\:navigate] {
            cursor: pointer;
        }

        /* Simple loading state - just dim content slightly */
        body.livewire-navigating main {
            opacity: 0.6;
            pointer-events: none;
            transition: opacity 0.1s ease;
        }

        /* ========== LOADING SKELETON ========== */
        .skeleton {
            background: linear-gradient(90deg,
                    rgba(255, 255, 255, 0.05) 25%,
                    rgba(255, 255, 255, 0.1) 50%,
                    rgba(255, 255, 255, 0.05) 75%);
            background-size: 200% 100%;
            animation: skeleton-shimmer 1.5s infinite;
            border-radius: 8px;
        }

        @keyframes skeleton-shimmer {
            0% {
                background-position: 200% 0;
            }

            100% {
                background-position: -200% 0;
            }
        }


        /* Modal backdrop - blur transition not supported, using static blur */
        .modal-backdrop-animate {
            backdrop-filter: blur(40px);
            -webkit-backdrop-filter: blur(40px);
        }

        /* ========== MICRO-INTERACTIONS ========== */
        /* Button hover effects */
        .btn-glow {
            position: relative;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .btn-glow:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 40px -10px currentColor;
        }

        .btn-glow:active {
            transform: translateY(0) scale(0.98);
        }

        /* Card hover lift effect */
        .card-lift {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .card-lift:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px -15px rgba(139, 92, 246, 0.3);
        }

        /* Icon bounce on hover */
        .icon-bounce:hover i,
        .icon-bounce:hover svg {
            animation: icon-bounce 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        @keyframes icon-bounce {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.2);
            }
        }

        /* Ripple effect for buttons */
        .ripple {
            position: relative;
            overflow: hidden;
        }

        .ripple::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at var(--ripple-x, 50%) var(--ripple-y, 50%),
                    rgba(255, 255, 255, 0.3) 0%, transparent 60%);
            opacity: 0;
            transform: scale(0);
            transition: transform 0.5s ease, opacity 0.3s ease;
        }

        .ripple:active::before {
            opacity: 1;
            transform: scale(2);
        }

        /* Smooth link underline animation */
        .link-underline {
            position: relative;
        }

        .link-underline::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: linear-gradient(90deg, #a855f7, #ec4899);
            transition: width 0.3s ease;
        }

        .link-underline:hover::after {
            width: 100%;
        }

        /* Prevent double scrollbar - main should NEVER have its own scrollbar */
        main {
            overflow: visible !important;
            overflow-y: visible !important;
        }

        /* Force all scrollable containers inside main to not create extra scrollbars */
        main>* {
            max-width: 100%;
        }

        /* ========== IMAGE LAZY LOADING ========== */
        /* Only apply to images that need fade-in, use .lazy-fade class */
        img.lazy-fade {
            opacity: 0;
        }

        img.lazy-fade.loaded {
            opacity: 1;
            transition: opacity 0.3s ease;
        }

        /* Placeholder while image loads */
        .img-placeholder {
            background: linear-gradient(90deg,
                    rgba(255, 255, 255, 0.03) 25%,
                    rgba(255, 255, 255, 0.06) 50%,
                    rgba(255, 255, 255, 0.03) 75%);
            background-size: 200% 100%;
            animation: skeleton-shimmer 1.5s infinite;
        }

        .ambient-bg {
            position: fixed;
            inset: -10%;
            z-index: -1;
            will-change: transform, opacity;
            background:
                radial-gradient(40% 30% at 20% 20%, rgba(56, 189, 248, 0.08), transparent 70%),
                radial-gradient(35% 30% at 80% 30%, rgba(232, 121, 249, 0.06), transparent 70%),
                radial-gradient(40% 35% at 50% 80%, rgba(34, 197, 94, 0.04), transparent 72%),
                linear-gradient(120deg, rgba(10, 10, 15, 0.9), rgba(5, 5, 10, 0.95));
            opacity: 0.6;
            animation: ambient-drift 10s ease-in-out infinite;
            pointer-events: none;
        }

        .ambient-bg::after {
            content: '';
            position: absolute;
            inset: 10%;
            background:
                radial-gradient(30% 25% at 30% 60%, rgba(34, 211, 238, 0.05), transparent 70%),
                radial-gradient(30% 25% at 70% 40%, rgba(244, 114, 182, 0.04), transparent 70%);
            opacity: 0.3;
        }

        @keyframes ambient-drift {
            0% {
                transform: translate3d(0, 0, 0);
            }

            50% {
                transform: translate3d(1.5%, -1.5%, 0);
            }

            100% {
                transform: translate3d(0, 0, 0);
            }
        }

        @media (prefers-reduced-motion: reduce) {

            .ambient-bg,
            .ambient-bg::after {
                animation: none;
            }
        }

        .anim-float-slow {
            animation: float-slow 8s ease-in-out infinite;
        }

        .anim-float-slower {
            animation: float-slower 12s ease-in-out infinite;
        }

        .anim-pulse-soft {
            animation: pulse-soft 4.5s ease-in-out infinite;
        }

        .btn-glow {
            position: relative;
            overflow: hidden;
        }

        .btn-glow::after {
            content: '';
            position: absolute;
            inset: -120% -30%;
            background: linear-gradient(120deg, transparent 35%, rgba(255, 255, 255, 0.45) 50%, transparent 65%);
            transform: translateX(-60%);
            transition: transform 0.6s ease;
            pointer-events: none;
        }

        .btn-glow:hover::after {
            transform: translateX(60%);
        }

        .btn-pop {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .btn-pop:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(255, 255, 255, 0.08);
        }

        .card-anim {
            position: relative;
            overflow: hidden;
        }

        .card-anim::after {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(60% 60% at 30% 20%, rgba(255, 255, 255, 0.08), transparent 60%);
            opacity: 0;
            transition: opacity 0.4s ease;
            pointer-events: none;
        }

        .card-anim:hover::after {
            opacity: 0.6;
        }

        .hero-blob {
            position: absolute;
            width: 280px;
            height: 280px;
            border-radius: 9999px;
            filter: blur(24px);
            opacity: 0.8;
            mix-blend-mode: screen;
            animation: hero-blob 14s ease-in-out infinite;
        }

        .hero-blob-1 {
            top: -80px;
            right: 10%;
            background: radial-gradient(circle at 30% 30%, rgba(34, 211, 238, 0.55), rgba(14, 116, 144, 0.0) 60%);
        }

        .hero-blob-2 {
            bottom: -90px;
            left: 8%;
            width: 320px;
            height: 320px;
            background: radial-gradient(circle at 40% 40%, rgba(232, 121, 249, 0.5), rgba(124, 58, 237, 0.0) 60%);
            animation-duration: 18s;
        }

        .hero-blob-3 {
            top: 30%;
            left: 55%;
            width: 220px;
            height: 220px;
            background: radial-gradient(circle at 40% 40%, rgba(74, 222, 128, 0.45), rgba(16, 185, 129, 0.0) 60%);
            animation-duration: 16s;
        }

        .hero-sheen {
            position: absolute;
            inset: -30%;
            background: conic-gradient(from 180deg, rgba(255, 255, 255, 0.06), transparent 40%, rgba(255, 255, 255, 0.06) 60%, transparent);
            animation: hero-rotate 20s linear infinite;
            opacity: 0.35;
            mix-blend-mode: soft-light;
        }

        .hero-art {
            position: absolute;
            inset: -10%;
            background:
                radial-gradient(40% 30% at 10% 30%, rgba(34, 211, 238, 0.22), transparent 70%),
                radial-gradient(35% 30% at 90% 20%, rgba(232, 121, 249, 0.2), transparent 70%),
                radial-gradient(40% 35% at 60% 80%, rgba(74, 222, 128, 0.16), transparent 72%),
                conic-gradient(from 0deg, rgba(255, 255, 255, 0.06), transparent 30%, rgba(255, 255, 255, 0.06) 60%, transparent);
            opacity: 0.6;
            animation: hero-pan 10s ease-in-out infinite;
            pointer-events: none;
            will-change: transform, opacity;
        }

        .styles-hero {
            position: relative;
            overflow: hidden;
            border-radius: 1.5rem;
            isolation: isolate;
            background: radial-gradient(120% 120% at 100% 0%, rgba(34, 211, 238, 0.18) 0%, rgba(10, 10, 15, 0.92) 55%, rgba(10, 10, 15, 1) 100%);
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.45), inset 0 0 0 1px rgba(255, 255, 255, 0.08);
        }

        .styles-hero::before {
            content: '';
            position: absolute;
            inset: -8%;
            background-image: url('/images/hero/styles-hero.png');
            background-size: cover;
            background-position: 45% 50%;
            opacity: 0.28;
            filter: saturate(1.1) contrast(1.05);
            transform: scale(1.03);
            pointer-events: none;
            z-index: 0;
            animation: styles-hero-pan 14s ease-in-out infinite;
        }

        .styles-hero::after {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(60% 60% at 15% 20%, rgba(94, 234, 212, 0.1), transparent 60%),
                radial-gradient(50% 60% at 85% 10%, rgba(251, 113, 133, 0.08), transparent 60%),
                linear-gradient(180deg, rgba(10, 10, 15, 0.12), rgba(10, 10, 15, 0.48));
            opacity: 0.45;
            pointer-events: none;
            z-index: 0;
        }

        .styles-hero-grid {
            position: absolute;
            inset: 0;
            opacity: 0.32;
            background-image:
                linear-gradient(120deg, rgba(255, 255, 255, 0.06) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.04) 1px, transparent 1px);
            background-size: 26px 26px;
            pointer-events: none;
            z-index: 0;
            animation: styles-grid-drift 12s linear infinite;
        }

        .styles-hero-motion {
            position: absolute;
            inset: -20%;
            background:
                radial-gradient(40% 40% at 15% 20%, rgba(244, 114, 182, 0.25), transparent 60%),
                radial-gradient(35% 35% at 85% 30%, rgba(139, 92, 246, 0.22), transparent 60%),
                radial-gradient(30% 30% at 60% 80%, rgba(59, 130, 246, 0.2), transparent 60%);
            opacity: 0.5;
            mix-blend-mode: screen;
            animation: styles-motion 10s ease-in-out infinite;
            pointer-events: none;
            z-index: 0;
        }

        .styles-hero-sheen {
            position: absolute;
            inset: -20%;
            background: conic-gradient(from 120deg, rgba(255, 255, 255, 0.06), transparent 35%, rgba(255, 255, 255, 0.08) 50%, transparent 65%);
            opacity: 0.55;
            mix-blend-mode: soft-light;
            animation: styles-sheen-rotate 10s linear infinite;
            pointer-events: none;
            z-index: 0;
        }

        .styles-hero-orb {
            position: absolute;
            width: 260px;
            height: 260px;
            border-radius: 9999px;
            filter: blur(26px);
            opacity: 0.7;
            mix-blend-mode: screen;
            animation: styles-orb-float 9s ease-in-out infinite;
            pointer-events: none;
            z-index: 0;
        }

        .styles-hero-orb-1 {
            top: -120px;
            right: 12%;
            background: radial-gradient(circle at 30% 30%, rgba(244, 114, 182, 0.55), rgba(168, 85, 247, 0) 60%);
        }

        .styles-hero-orb-2 {
            bottom: -140px;
            left: 10%;
            width: 320px;
            height: 320px;
            background: radial-gradient(circle at 35% 35%, rgba(59, 130, 246, 0.45), rgba(37, 99, 235, 0) 60%);
            animation-duration: 18s;
        }

        @keyframes styles-grid-drift {
            0% {
                background-position: 0 0;
            }

            100% {
                background-position: 320px 220px;
            }
        }

        @keyframes styles-sheen-rotate {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        @keyframes styles-motion {
            0% {
                transform: translate3d(-3%, 2%, 0) rotate(0deg);
            }

            50% {
                transform: translate3d(2%, -2%, 0) rotate(2deg);
            }

            100% {
                transform: translate3d(-3%, 2%, 0) rotate(0deg);
            }
        }

        @keyframes styles-hero-pan {
            0% {
                background-position: 45% 50%;
                transform: scale(1.04) translate3d(0, 0, 0);
            }

            50% {
                background-position: 70% 30%;
                transform: scale(1.1) translate3d(12px, -10px, 0);
            }

            100% {
                background-position: 45% 50%;
                transform: scale(1.04) translate3d(0, 0, 0);
            }
        }

        @keyframes styles-orb-float {
            0% {
                transform: translate3d(0, 0, 0) scale(1);
            }

            50% {
                transform: translate3d(46px, -26px, 0) scale(1.1);
            }

            100% {
                transform: translate3d(0, 0, 0) scale(1);
            }
        }

        .styles-hero-inner {
            position: relative;
            z-index: 2;
        }

        @keyframes hero-blob {
            0% {
                transform: translate3d(0, 0, 0) scale(1);
            }

            50% {
                transform: translate3d(18px, -14px, 0) scale(1.06);
            }

            100% {
                transform: translate3d(0, 0, 0) scale(1);
            }
        }

        @keyframes hero-rotate {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        @keyframes hero-pan {
            0% {
                transform: translate3d(-2%, -1%, 0) scale(1);
                background-position: 0% 50%;
            }

            50% {
                transform: translate3d(2%, -3%, 0) scale(1.03);
                background-position: 100% 50%;
            }

            100% {
                transform: translate3d(-2%, -1%, 0) scale(1);
                background-position: 0% 50%;
            }
        }

        @keyframes float-slow {
            0% {
                transform: translate3d(0, 0, 0);
            }

            50% {
                transform: translate3d(0, -10px, 0);
            }

            100% {
                transform: translate3d(0, 0, 0);
            }
        }

        @keyframes float-slower {
            0% {
                transform: translate3d(0, 0, 0);
            }

            50% {
                transform: translate3d(8px, -6px, 0);
            }

            100% {
                transform: translate3d(0, 0, 0);
            }
        }

        @keyframes pulse-soft {

            0%,
            100% {
                box-shadow: 0 0 0 rgba(34, 211, 238, 0.0);
            }

            50% {
                box-shadow: 0 0 24px rgba(34, 211, 238, 0.25);
            }
        }

        @media (prefers-reduced-motion: reduce) {

            .anim-float-slow,
            .anim-float-slower,
            .anim-pulse-soft,
            .btn-glow::after,
            .hero-blob,
            .hero-sheen,
            .hero-art,
            .styles-hero-grid,
            .styles-hero-motion,
            .styles-hero-sheen,
            .styles-hero-orb,
            .styles-hero::before {
                animation: none;
                transition: none;
            }
        }

        body.is-scrolling .ambient-bg,
        body.is-scrolling .ambient-bg::after,
        body.is-scrolling .hero-art,
        body.is-scrolling .hero-blob,
        body.is-scrolling .hero-sheen,
        body.is-scrolling .styles-hero-grid,
        body.is-scrolling .styles-hero-motion,
        body.is-scrolling .styles-hero-sheen,
        body.is-scrolling .styles-hero-orb,
        body.is-scrolling .styles-hero::before,
        body.is-scrolling .anim-float-slow,
        body.is-scrolling .anim-float-slower,
        body.is-scrolling .anim-pulse-soft {
            animation-play-state: paused;
        }

        /* Select2 Dark Theme */
        .select2-container--default .select2-selection--single {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 0.75rem;
            height: 48px;
            padding: 10px 12px;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: rgba(255, 255, 255, 0.9);
            line-height: 28px;
            padding-left: 0;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 46px;
            right: 8px;
        }

        @media (min-width: 640px) {
            .select2-container--default .select2-selection--single {
                height: 44px;
                padding: 8px 12px;
            }

            .select2-container--default .select2-selection--single .select2-selection__rendered {
                line-height: 26px;
            }

            .select2-container--default .select2-selection--single .select2-selection__arrow {
                height: 42px;
            }
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow b {
            border-color: rgba(255, 255, 255, 0.5) transparent transparent transparent;
        }

        .select2-container--default.select2-container--open .select2-selection--single .select2-selection__arrow b {
            border-color: transparent transparent rgba(255, 255, 255, 0.5) transparent;
        }

        .select2-dropdown {
            background: #1a1a24;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 0.75rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
            box-sizing: border-box;
        }

        .select2-container--default .select2-search--dropdown .select2-search__field {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 0.75rem;
            color: white;
            padding: 8px 12px;
        }

        .select2-container--default .select2-results__option {
            color: rgba(255, 255, 255, 0.8);
            padding: 10px 14px;
            background: transparent;
        }

        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background: linear-gradient(135deg, #a855f7, #ec4899) !important;
            color: white !important;
        }

        /* Option đã selected - luôn giữ màu tím */
        .select2-container--default .select2-results__option[aria-selected=true]:not(.select2-results__option--highlighted) {
            background: rgba(168, 85, 247, 0.2) !important;
            color: #c084fc !important;
            font-weight: 500;
        }

        /* Option đã selected + đang hover */
        .select2-container--default .select2-results__option--highlighted.select2-results__option[aria-selected=true] {
            background: linear-gradient(135deg, #a855f7, #ec4899) !important;
            color: white !important;
        }

        .select2-container--default .select2-selection--single:focus,
        .select2-container--default.select2-container--open .select2-selection--single {
            border-color: rgba(168, 85, 247, 0.5);
            box-shadow: 0 0 0 2px rgba(168, 85, 247, 0.2);
            outline: none;
        }

        .select2-container {
            width: 100% !important;
        }

        .styles-filter .select2-container {
            width: 100% !important;
        }

        .styles-filter .select2-dropdown {
            min-width: 0 !important;
            max-width: 100% !important;
        }

        .history-filters .select2-container {
            width: 100% !important;
        }

        .history-filters .select2-dropdown {
            min-width: 0 !important;
            max-width: 100vw !important;
            left: 0 !important;
            right: auto !important;
        }

        /* Prevent horizontal scroll when Select2 dropdown opens */
        .select2-container--open .select2-dropdown {
            max-width: calc(100vw - 32px) !important;
        }
    </style>
</head>

<body class="min-h-screen text-white antialiased"
    x-data="{ authPromptOpen: @js((bool) session('open_auth_modal')) }"
    @open-auth-modal.window="authPromptOpen = true">
    @persist('ambient-bg')
    <div class="ambient-bg" aria-hidden="true"></div>
    @endpersist

    @if (session('error') || session('success') || session('status'))
        @php
            $flashMessage = session('error') ?? session('success') ?? session('status');
            $isError = (bool) session('error');
        @endphp
        <div x-data="{ show: true }" x-show="show" x-transition.opacity x-init="setTimeout(() => show = false, 6500)"
            class="fixed top-4 right-4 z-[10001] max-w-sm rounded-xl border px-4 py-3 shadow-2xl"
            :class="{{ $isError ? '\'bg-red-500/15 border-red-500/40 text-red-100\'' : '\'bg-emerald-500/15 border-emerald-500/40 text-emerald-100\'' }}">
            <div class="flex items-start gap-3">
                <i class="fa-solid {{ $isError ? 'fa-triangle-exclamation text-red-300' : 'fa-circle-check text-emerald-300' }} mt-0.5"></i>
                <p class="text-sm leading-5">{{ $flashMessage }}</p>
                <button type="button" @click="show = false" class="ml-auto text-white/60 hover:text-white">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        </div>
    @endif

    <!-- ========== MOBILE HEADER ========== -->
    @persist('mobile-header')
    <header id="header" class="md:hidden fixed top-0 left-0 right-0 z-50 bg-[#0a0a0f]/95 backdrop-blur-xl border-b border-white/10">
        <div class="flex items-center justify-between h-14 px-4">
            <!-- Logo -->
            <a href="{{ route('home') }}" wire:navigate class="flex items-center gap-2">
                <i class="fa-solid fa-wand-magic-sparkles text-purple-400"></i>
                <span
                    class="text-lg font-bold bg-gradient-to-r from-purple-400 to-pink-500 bg-clip-text text-transparent">ZDream</span>
            </a>
            <div class="flex items-center gap-2">
                @auth
                    <a href="{{ route('wallet.index') }}" wire:navigate
                        class="flex items-center gap-1.5 px-3 h-9 rounded-full bg-white/5 border border-white/10">
                        <i class="fa-solid fa-gem text-cyan-400 text-sm"></i>
                        <span class="font-semibold text-sm"><livewire:header-credits /></span>
                    </a>
                @else
                    <a href="{{ route('login') }}" wire:navigate
                        class="px-4 py-2 rounded-full bg-gradient-to-r from-purple-500 to-pink-500 text-white text-sm font-medium">
                        Đăng nhập
                    </a>
                @endauth

                <button id="menu-btn" type="button"
                    class="w-9 h-9 rounded-lg bg-white/5 border border-white/10 text-white/70 hover:text-white hover:bg-white/10 flex items-center justify-center transition-colors"
                    aria-label="Open menu">
                    <i id="menu-icon-bars" class="fa-solid fa-bars w-4 h-4"></i>
                    <i id="menu-icon-xmark" class="fa-solid fa-xmark w-4 h-4" style="display:none"></i>
                </button>
            </div>
        </div>
    </header>
    @endpersist

    <!-- ========== DESKTOP LEFT SIDEBAR (Compact Icon Style) ========== -->
    @persist('desktop-sidebar')
    <aside
        class="hidden md:flex fixed left-0 top-0 bottom-0 z-50 w-[72px] flex-col bg-[#0a0a0f]/95 border-r border-white/10">
        <!-- Logo -->
        <a href="{{ route('home') }}" wire:navigate
            class="flex items-center justify-center h-16 border-b border-white/5">
            <div
                class="w-10 h-10 rounded-xl bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center shadow-lg shadow-purple-500/30">
                <i class="fa-solid fa-wand-magic-sparkles text-white"></i>
            </div>
        </a>

        <!-- Navigation -->
        <nav class="flex-1 flex flex-col items-center py-4 gap-1 overflow-y-auto"
            x-data="{ currentPath: window.location.pathname }" @popstate.window="currentPath = window.location.pathname"
            x-init="document.addEventListener('livewire:navigated', () => { currentPath = window.location.pathname })">
            <a href="{{ route('home') }}" wire:navigate
                class="flex flex-col items-center justify-center w-14 h-14 rounded-xl transition-all"
                :class="currentPath === '/' || currentPath === '/home' ? 'bg-purple-500/20 text-white' : 'text-white/50 hover:text-white hover:bg-white/5'">
                <i class="fa-solid fa-house text-lg mb-1"></i>
                <span class="text-[10px] font-medium">Trang chủ</span>
            </a>
            <a href="{{ route('styles.index') }}" wire:navigate
                class="flex flex-col items-center justify-center w-14 h-14 rounded-xl transition-all"
                :class="currentPath.startsWith('/styles') || currentPath.startsWith('/studio') ? 'bg-purple-500/20 text-white' : 'text-white/50 hover:text-white hover:bg-white/5'">
                <i class="fa-solid fa-palette text-lg mb-1"></i>
                <span class="text-[10px] font-medium">Styles</span>
            </a>
            @auth
                <a href="{{ route('history.index') }}" wire:navigate
                    class="flex flex-col items-center justify-center w-14 h-14 rounded-xl transition-all"
                    :class="currentPath.startsWith('/history') ? 'bg-purple-500/20 text-white' : 'text-white/50 hover:text-white hover:bg-white/5'">
                    <i class="fa-solid fa-images text-lg mb-1"></i>
                    <span class="text-[10px] font-medium">Thư viện</span>
                </a>
            @endauth

            <!-- Spacer -->
            <div class="flex-1"></div>

            <!-- Settings -->
            <a href="{{ route('profile.edit') }}" wire:navigate
                class="flex flex-col items-center justify-center w-14 h-14 rounded-xl transition-all"
                :class="currentPath.startsWith('/profile') ? 'bg-purple-500/20 text-white' : 'text-white/50 hover:text-white hover:bg-white/5'">
                <i class="fa-solid fa-gear text-lg mb-1"></i>
                <span class="text-[10px] font-medium">Cài đặt</span>
            </a>
        </nav>

        <!-- Bottom: Credits + User -->
        <div class="flex flex-col items-center py-3 border-t border-white/5 gap-2">
            @auth
                <!-- Credits -->
                <a href="{{ route('wallet.index') }}" wire:navigate
                    class="flex flex-col items-center justify-center w-14 py-2 rounded-xl bg-gradient-to-b from-purple-500/10 to-transparent hover:bg-purple-500/20 transition-all">
                    <i class="fa-solid fa-gem text-cyan-400 text-sm mb-0.5"></i>
                    <span class="text-[11px] font-bold"><livewire:header-credits /></span>
                </a>

                <!-- Upgrade -->
                <a href="{{ route('wallet.index') }}" wire:navigate
                    class="flex items-center justify-center w-14 h-8 rounded-lg bg-gradient-to-r from-purple-500 to-pink-500 text-white text-[10px] font-semibold shadow-lg shadow-purple-500/30">
                    Nạp Xu
                </a>

                <!-- User Avatar -->
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open"
                        class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-white font-bold text-sm hover:scale-105 transition-transform">
                        @if(auth()->user()->avatar)
                            <img src="{{ auth()->user()->avatar }}" alt="{{ auth()->user()->name }}"
                                class="h-10 w-10 rounded-full object-cover border border-white/20">
                        @else
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        @endif
                    </button>
                    <!-- Dropdown -->
                    <div x-show="open" @click.outside="open = false" x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                        class="absolute left-full bottom-0 ml-2 w-48 bg-[#16161d] border border-white/10 rounded-xl shadow-xl overflow-hidden">
                        <div class="p-3 border-b border-white/5">
                            <p class="text-sm font-medium truncate">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-white/50 truncate">{{ auth()->user()->email }}</p>
                        </div>
                        <a href="{{ route('profile.edit') }}" wire:navigate
                            class="flex items-center gap-2 px-3 py-2.5 text-sm text-white/70 hover:text-white hover:bg-white/5">
                            <i class="fa-solid fa-user w-4 text-blue-400"></i> Hồ sơ
                        </a>
                        @if(auth()->user()->is_admin)
                            <a href="{{ route('admin.dashboard') }}"
                                class="flex items-center gap-2 px-3 py-2.5 text-sm text-cyan-400 hover:bg-cyan-500/10">
                                <i class="fa-solid fa-crown w-4"></i> Admin
                            </a>
                        @endif
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                class="w-full flex items-center gap-2 px-3 py-2.5 text-sm text-red-400 hover:bg-red-500/10">
                                <i class="fa-solid fa-right-from-bracket w-4"></i> Đăng xuất
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <a href="{{ route('login') }}" wire:navigate
                    class="flex flex-col items-center justify-center w-14 h-14 rounded-xl text-white/50 hover:text-white hover:bg-white/5 transition-all">
                    <i class="fa-solid fa-right-to-bracket text-lg mb-1"></i>
                    <span class="text-[10px] font-medium">Đăng nhập</span>
                </a>
            @endauth
        </div>
    </aside>
    @endpersist

    <!-- ========== MOBILE MENU OVERLAY ========== -->
    <div id="menu-overlay"
        class="fixed inset-0 z-[60] md:hidden opacity-0 pointer-events-none transition-opacity duration-300">
        <div class="absolute inset-0 bg-black/60 "></div>
        <div id="mobile-menu"
            class="mobile-menu closed absolute right-0 top-0 h-full w-72 max-w-[85vw] bg-[#0a0a0f] backdrop-blur-[24px] border-l border-[#2a2a35] overflow-y-auto overscroll-contain">
            <div class="sticky top-0 z-10 p-4 border-b border-[#222230] bg-[#0a0a0f] flex items-center justify-between">
                <span class="text-white/80 font-medium">Menu</span>
                <button id="close-menu-btn"
                    class="w-8 h-8 rounded-lg bg-white/[0.05] flex items-center justify-center text-white/60 hover:text-white">
                    <i class="fa-solid fa-xmark w-4 h-4"></i>
                </button>
            </div>
            <div class="p-4 space-y-2">
                @auth
                    <a href="{{ route('wallet.index') }}" wire:navigate
                        class="block p-4 rounded-xl bg-gradient-to-br from-purple-500/10 to-pink-500/10 border border-purple-500/20">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-white/60 text-sm">Số dư</span>
                            <i class="fa-solid fa-gem w-4 h-4 text-cyan-400"></i>
                        </div>
                        <div class="text-2xl font-bold text-white mb-3"><livewire:header-credits /> Xu</div>
                        <div
                            class="w-full py-2.5 rounded-lg bg-gradient-to-r from-purple-500 to-pink-500 text-white font-medium text-sm flex items-center justify-center gap-2 shadow-lg shadow-purple-500/25">
                            <i class="fa-solid fa-plus w-3.5 h-3.5"></i> Nạp thêm Xu
                        </div>
                    </a>
                @endauth
                <div class="h-px bg-white/[0.05] my-4"></div>
                <a href="{{ route('home') }}" wire:navigate
                    class="flex items-center justify-between px-4 py-3 rounded-xl transition-all {{ request()->routeIs('home') ? 'bg-purple-500/10 border-purple-500/30 text-white' : 'bg-[#13131a] hover:bg-white/[0.05] border-[#222230] text-white/80 hover:text-white' }} border">
                    <span class="flex items-center gap-3"><i class="fa-solid fa-house w-4 h-4 text-purple-400"></i>
                        Trang chủ</span>
                    <i class="fa-solid fa-chevron-right w-3 h-3 text-white/30"></i>
                </a>
                <a href="{{ route('styles.index') }}" wire:navigate
                    class="flex items-center justify-between px-4 py-3 rounded-xl transition-all {{ request()->routeIs('styles.*') || request()->routeIs('studio.*') ? 'bg-purple-500/10 border-purple-500/30 text-white' : 'bg-[#13131a] hover:bg-white/[0.05] border-[#222230] text-white/80 hover:text-white' }} border">
                    <span class="flex items-center gap-3"><i class="fa-solid fa-palette w-4 h-4 text-purple-400"></i>
                        Styles</span>
                    <i class="fa-solid fa-chevron-right w-3 h-3 text-white/30"></i>
                </a>
                {{-- <a href="{{ route('edit.index') }}"
                    class="flex items-center justify-between px-4 py-3 rounded-xl bg-[#13131a] hover:bg-white/[0.05] border border-[#222230] text-white/80 hover:text-white transition-all">
                    <span class="flex items-center gap-3"><i
                            class="fa-solid fa-wand-magic-sparkles w-4 h-4 text-pink-400"></i>
                        Magic Edit</span>
                    <i class="fa-solid fa-chevron-right w-3 h-3 text-white/30"></i>
                </a> --}}
                @auth
                    <a href="{{ route('history.index') }}" wire:navigate
                        class="flex items-center justify-between px-4 py-3 rounded-xl transition-all {{ request()->routeIs('history.*') ? 'bg-purple-500/10 border-purple-500/30 text-white' : 'bg-[#13131a] hover:bg-white/[0.05] border-[#222230] text-white/80 hover:text-white' }} border">
                        <span class="flex items-center gap-3"><i class="fa-solid fa-images w-4 h-4 text-purple-400"></i> Ảnh
                            của tôi</span>
                        <i class="fa-solid fa-chevron-right w-3 h-3 text-white/30"></i>
                    </a>

                    <!-- User Menu Dropdown -->
                    <div x-data="{ userMenuOpen: false }" class="mt-2">
                        <button @click="userMenuOpen = !userMenuOpen"
                            class="w-full flex items-center justify-between px-4 py-3 rounded-xl bg-gradient-to-r from-purple-500/10 to-pink-500/10 hover:from-purple-500/20 hover:to-pink-500/20 border border-purple-500/30 text-white hover:text-white transition-all">
                            <span class="flex items-center gap-3">
                                <div
                                    class="w-7 h-7 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-white text-xs font-bold shadow-lg shadow-purple-500/30">
                                    @if(auth()->user()->avatar)
                                        <img src="{{ auth()->user()->avatar }}" alt="{{ auth()->user()->name }}"
                                            class="h-7 w-7 rounded-full object-cover border border-white/20">
                                    @else
                                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                    @endif
                                </div>
                                <span
                                    class="truncate max-w-[150px] font-medium">{{ str_contains(auth()->user()->name, '@') ? Str::before(auth()->user()->name, '@') : auth()->user()->name }}</span>
                            </span>
                            <i class="fa-solid fa-chevron-down w-3 h-3 text-purple-400 transition-transform duration-200"
                                :class="{ 'rotate-180': userMenuOpen }"></i>
                        </button>

                        <!-- Dropdown Items -->
                        <div x-show="userMenuOpen" x-collapse class="mt-1 ml-4 space-y-1">
                            <a href="{{ route('dashboard') }}" wire:navigate
                                class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm text-white/70 hover:text-white hover:bg-white/[0.05] transition-colors">
                                <i class="fa-solid fa-gauge w-4 text-purple-400"></i>
                                Dashboard
                            </a>
                            <a href="{{ route('profile.edit') }}" wire:navigate
                                class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm text-white/70 hover:text-white hover:bg-white/[0.05] transition-colors">
                                <i class="fa-solid fa-user w-4 text-blue-400"></i>
                                Hồ sơ cá nhân
                            </a>
                            <a href="{{ route('wallet.index') }}" wire:navigate
                                class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm text-white/70 hover:text-white hover:bg-white/[0.05] transition-colors">
                                <i class="fa-solid fa-wallet w-4 text-yellow-400"></i>
                                Ví tiền
                                <span class="ml-auto text-xs text-cyan-400 font-medium"><livewire:header-credits />
                                    Xu</span>
                            </a>
                        </div>
                    </div>
                    @if(auth()->user()->is_admin)
                        <a href="{{ route('admin.dashboard') }}"
                            class="flex items-center justify-between px-4 py-3 rounded-xl bg-cyan-500/10 border border-cyan-500/20 text-cyan-400 hover:bg-cyan-500/15 transition-all">
                            <span class="flex items-center gap-3"><i class="fa-solid fa-crown w-4 h-4"></i> Admin Panel</span>
                            <i class="fa-solid fa-chevron-right w-3 h-3 text-cyan-400/50"></i>
                        </a>
                    @endif
                    <div class="h-px bg-white/[0.05] my-4"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="w-full py-3 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 font-medium flex items-center justify-center gap-2 hover:bg-red-500/15 transition-colors">
                            <i class="fa-solid fa-right-from-bracket w-4 h-4"></i> Đăng xuất
                        </button>
                    </form>
                @else
                    <div class="h-px bg-white/[0.05] my-4"></div>
                    <a href="{{ route('register') }}" wire:navigate
                        class="w-full py-3 rounded-xl bg-white text-gray-900 font-medium inline-flex items-center justify-center gap-2 hover:bg-gray-100 transition-colors">
                        <i class="fa-solid fa-crown" style="font-size: 14px;"></i>
                        <span>Đăng ký miễn phí</span>
                    </a>
                    <a href="{{ route('login') }}" wire:navigate
                        class="w-full py-3 rounded-xl bg-white/[0.05] border border-white/[0.1] text-white/80 font-medium inline-flex items-center justify-center gap-2 hover:bg-white/[0.1] transition-colors mt-2">
                        <i class="fa-solid fa-right-to-bracket" style="font-size: 14px;"></i>
                        <span>Đăng nhập</span>
                    </a>
                @endauth
            </div>
        </div>
    </div>

    <!-- ========== MAIN CONTENT ========== -->
    <main class="pt-14 md:pt-0 pb-20 md:pb-0 md:ml-[72px]">
        @if(isset($slot))
            {{ $slot }}
        @else
            @yield('content')
        @endif
    </main>

    <!-- ========== MOBILE BOTTOM TAB BAR ========== -->
    @persist('mobile-bottom-nav')
    <nav class="fixed bottom-0 left-0 right-0 z-50 md:hidden bg-[#0a0a0f]/95 backdrop-blur-xl border-t border-white/10 safe-area-bottom"
        x-data="{ currentPath: window.location.pathname }" @popstate.window="currentPath = window.location.pathname"
        x-init="document.addEventListener('livewire:navigated', () => { currentPath = window.location.pathname })">
        <div class="flex items-center justify-around h-16 px-2 max-w-lg mx-auto">
            <!-- Home -->
            <a href="{{ route('home') }}" wire:navigate
                class="relative flex flex-col items-center justify-center gap-1 py-2 px-4 rounded-xl transition-all"
                :class="currentPath === '/' || currentPath === '/home' ? 'text-white' : 'text-white/50'">
                <div x-show="currentPath === '/' || currentPath === '/home'"
                    class="absolute -top-0.5 w-8 h-1 rounded-full bg-gradient-to-r from-purple-500 to-pink-500"></div>
                <i class="fa-solid fa-house text-lg"></i>
                <span class="text-[10px] font-medium">Trang chủ</span>
            </a>

            <!-- Styles -->
            <a href="{{ route('styles.index') }}" wire:navigate
                class="relative flex flex-col items-center justify-center gap-1 py-2 px-4 rounded-xl transition-all"
                :class="currentPath.startsWith('/styles') || currentPath.startsWith('/studio') ? 'text-white' : 'text-white/50'">
                <div x-show="currentPath.startsWith('/styles') || currentPath.startsWith('/studio')"
                    class="absolute -top-0.5 w-8 h-1 rounded-full bg-gradient-to-r from-purple-500 to-pink-500"></div>
                <i class="fa-solid fa-palette text-lg"></i>
                <span class="text-[10px] font-medium">Styles</span>
            </a>

            @auth
                <!-- History -->
                <a href="{{ route('history.index') }}" wire:navigate
                    class="relative flex flex-col items-center justify-center gap-1 py-2 px-4 rounded-xl transition-all"
                    :class="currentPath.startsWith('/history') ? 'text-white' : 'text-white/50'">
                    <div x-show="currentPath.startsWith('/history')"
                        class="absolute -top-0.5 w-8 h-1 rounded-full bg-gradient-to-r from-purple-500 to-pink-500"></div>
                    <i class="fa-solid fa-images text-lg"></i>
                    <span class="text-[10px] font-medium">Ảnh của tôi</span>
                </a>

                <!-- Profile -->
                <a href="{{ route('profile.edit') }}" wire:navigate
                    class="relative flex flex-col items-center justify-center gap-1 py-2 px-4 rounded-xl transition-all"
                    :class="currentPath.startsWith('/profile') ? 'text-white' : 'text-white/50'">
                    <div x-show="currentPath.startsWith('/profile')"
                        class="absolute -top-0.5 w-8 h-1 rounded-full bg-gradient-to-r from-purple-500 to-pink-500"></div>
                    <i class="fa-solid fa-user text-lg"></i>
                    <span class="text-[10px] font-medium">Tài khoản</span>
                </a>
            @else
                <!-- Login -->
                <a href="{{ route('login') }}" wire:navigate
                    class="flex flex-col items-center justify-center gap-1 py-2 px-4 rounded-xl transition-all text-white/50">
                    <i class="fa-solid fa-right-to-bracket text-lg"></i>
                    <span class="text-[10px] font-medium">Đăng nhập</span>
                </a>
            @endauth
        </div>
    </nav>
    @endpersist
    <!-- ========== AUTH PROMPT MODAL (GOOGLE ONLY) ========== -->
    @guest
        @php
            $lastGoogleName = (string) request()->cookie('zd_last_google_name', '');
            $lastGoogleAvatar = (string) request()->cookie('zd_last_google_avatar', '');
            $lastGoogleInitial = strtoupper(substr($lastGoogleName !== '' ? $lastGoogleName : 'G', 0, 1));
        @endphp
        <div x-show="authPromptOpen" x-cloak
            class="fixed inset-0 z-[9998] flex items-center justify-center p-4"
            x-transition.opacity
            @click.self="authPromptOpen = false">
            <div class="absolute inset-0 bg-black/70 backdrop-blur-sm"></div>
            <div class="relative w-full max-w-md overflow-hidden rounded-2xl border border-white/10 bg-[#111218] p-6 shadow-2xl"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 translate-y-2 scale-95">
                <div class="pointer-events-none absolute -right-16 -top-16 h-44 w-44 rounded-full bg-purple-500/20 blur-2xl"></div>
                <div class="pointer-events-none absolute -bottom-12 -left-12 h-36 w-36 rounded-full bg-pink-500/20 blur-2xl"></div>

                <button type="button" @click="authPromptOpen = false"
                    class="absolute right-3 top-3 h-8 w-8 rounded-full bg-white/5 text-white/60 hover:bg-white/10 hover:text-white">
                    <i class="fa-solid fa-xmark"></i>
                </button>

                <div class="mb-5">
                    <div class="mb-4 inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-white/10 text-white shadow-lg shadow-purple-500/20">
                        <i class="fa-solid fa-user-shield text-base"></i>
                    </div>
                    <h3 class="text-xl font-semibold leading-tight text-white">Đăng nhập để tiếp tục</h3>
                    <p class="mt-2 text-sm leading-6 text-white/65">Đồng bộ lịch sử ảnh, ví xu và thiết lập cá nhân của bạn trên mọi thiết bị.</p>
                </div>

                @if ($lastGoogleName !== '')
                    <div class="mb-4 rounded-2xl border border-white/10 bg-white/[0.04] p-3.5">
                        <p class="mb-2.5 text-[11px] font-semibold uppercase tracking-wide text-white/45">Tài khoản đã dùng gần nhất</p>
                        <div class="flex items-center gap-3">
                            @if ($lastGoogleAvatar !== '')
                                <img src="{{ $lastGoogleAvatar }}" alt="{{ $lastGoogleName }}"
                                    class="h-11 w-11 rounded-full border border-white/20 object-cover shadow-md shadow-black/30">
                            @else
                                <div
                                    class="h-11 w-11 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 text-white text-sm font-semibold flex items-center justify-center shadow-md shadow-purple-500/30">
                                    {{ $lastGoogleInitial }}
                                </div>
                            @endif
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-white">{{ $lastGoogleName }}</p>
                                <p class="text-xs text-white/55">Google Account</p>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="mb-5 grid grid-cols-1 gap-2 text-xs">
                    <div class="flex items-center gap-2.5 rounded-lg bg-white/[0.03] px-3 py-2 text-white/70">
                        <i class="fa-solid fa-clock-rotate-left text-purple-300"></i>
                        <span>Lưu lịch sử tạo ảnh tự động</span>
                    </div>
                    <div class="flex items-center gap-2.5 rounded-lg bg-white/[0.03] px-3 py-2 text-white/70">
                        <i class="fa-solid fa-gem text-cyan-300"></i>
                        <span>Đồng bộ số dư xu và giao dịch</span>
                    </div>
                </div>

                <a href="{{ route('auth.google.redirect') }}"
                    class="group inline-flex w-full items-center justify-center gap-3 rounded-xl bg-gradient-to-r from-white to-white/95 px-4 py-3.5 font-semibold text-[#1a1a1a] transition hover:scale-[1.01] hover:shadow-xl hover:shadow-white/15">
                    <span class="flex h-7 w-7 items-center justify-center rounded-full bg-[#111218]/10">
                        <i class="fa-brands fa-google text-sm"></i>
                    </span>
                    @if ($lastGoogleName !== '')
                        <span>Tiếp tục với {{ \Illuminate\Support\Str::limit($lastGoogleName, 24) }}</span>
                    @else
                        <span>Tiếp tục với Google</span>
                    @endif
                </a>
            </div>
        </div>
    @endguest

    <!-- Custom Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const menuBtn = document.getElementById('menu-btn');
            const closeMenuBtn = document.getElementById('close-menu-btn');
            const menuOverlay = document.getElementById('menu-overlay');
            const mobileMenu = document.getElementById('mobile-menu');
            const menuIconBars = document.getElementById('menu-icon-bars');
            const menuIconXmark = document.getElementById('menu-icon-xmark');
            let menuIsOpen = false;
            function openMenu() {
                if (!menuOverlay || !mobileMenu) return;
                menuOverlay.classList.remove('opacity-0', 'pointer-events-none');
                menuOverlay.classList.add('opacity-100');
                mobileMenu.classList.remove('closed');
                mobileMenu.classList.add('open');
                document.body.classList.add('overflow-hidden');
                // Toggle icon
                if (menuIconBars) menuIconBars.style.display = 'none';
                if (menuIconXmark) menuIconXmark.style.display = 'inline-flex';
                menuIsOpen = true;
            }
            function closeMenu() {
                if (!menuOverlay || !mobileMenu) return;
                menuOverlay.classList.add('opacity-0', 'pointer-events-none');
                menuOverlay.classList.remove('opacity-100');
                mobileMenu.classList.add('closed');
                mobileMenu.classList.remove('open');
                document.body.classList.remove('overflow-hidden');
                // Toggle icon
                if (menuIconBars) menuIconBars.style.display = 'inline-flex';
                if (menuIconXmark) menuIconXmark.style.display = 'none';
                menuIsOpen = false;
            }
            function toggleMenu() {
                if (menuIsOpen) {
                    closeMenu();
                } else {
                    openMenu();
                }
            }
            if (menuBtn) menuBtn.addEventListener('click', toggleMenu);
            if (closeMenuBtn) closeMenuBtn.addEventListener('click', closeMenu);
            if (menuOverlay) menuOverlay.addEventListener('click', function (e) {
                if (e.target === menuOverlay) closeMenu();
            });

            // Header scroll effect
            const header = document.getElementById('header');
            if (header) {
                window.addEventListener('scroll', function () {
                    if (window.scrollY > 50) {
                        header.classList.add('scrolled');
                    } else {
                        header.classList.remove('scrolled');
                    }
                });
            }
        });
    </script>
    <script>
        (function () {
            let scrollTimer = null;
            window.addEventListener('scroll', function () {
                document.body.classList.add('is-scrolling');
                if (scrollTimer) {
                    clearTimeout(scrollTimer);
                }
                scrollTimer = setTimeout(function () {
                    document.body.classList.remove('is-scrolling');
                }, 160);
            }, { passive: true });
        })();
    </script>

    <!-- Global Lightbox Script -->
    <script>
        let lightboxOpen = false;
        let lightboxImages = [];
        let lightboxImageData = []; // For actions (download, delete)
        let lightboxIndex = 0;
        let lightboxHasActions = false;

        // Simple lightbox (just images)
        function openLightbox(index, images) {
            lightboxImages = images;
            lightboxImageData = [];
            lightboxIndex = index;
            lightboxOpen = true;
            lightboxHasActions = false;
            renderLightbox();
            document.documentElement.classList.add('overflow-hidden');
            document.body.classList.add('overflow-hidden');
        }

        // Lightbox with actions (download, delete)
        function openLightboxWithActions(index, imageData) {
            lightboxImageData = imageData;
            lightboxImages = imageData.map(d => d.url);
            lightboxIndex = index;
            lightboxOpen = true;
            lightboxHasActions = true;
            renderLightbox();
            document.documentElement.classList.add('overflow-hidden');
            document.body.classList.add('overflow-hidden');
        }

        function closeLightbox() {
            lightboxOpen = false;
            const el = document.getElementById('global-lightbox');
            if (el) el.remove();
            document.documentElement.classList.remove('overflow-hidden');
            document.body.classList.remove('overflow-hidden');
            document.removeEventListener('keydown', handleLightboxKeydown);
        }

        function lightboxPrev() {
            lightboxIndex = (lightboxIndex - 1 + lightboxImages.length) % lightboxImages.length;
            updateLightboxImage();
        }

        function lightboxNext() {
            lightboxIndex = (lightboxIndex + 1) % lightboxImages.length;
            updateLightboxImage();
        }

        function updateLightboxImage() {
            const img = document.getElementById('lightbox-main-image');
            const counter = document.getElementById('lightbox-counter');
            const downloadBtn = document.getElementById('lightbox-download-btn');
            const deleteBtn = document.getElementById('lightbox-delete-btn');
            if (img) img.src = lightboxImages[lightboxIndex];
            if (counter) counter.textContent = `${lightboxIndex + 1} / ${lightboxImages.length}`;
            // Update action buttons
            if (lightboxHasActions && lightboxImageData[lightboxIndex]) {
                if (downloadBtn) downloadBtn.href = lightboxImageData[lightboxIndex].download;
                if (deleteBtn) deleteBtn.onclick = () => deleteLightboxImage(lightboxImageData[lightboxIndex].delete);
            }
            updateThumbnails();
        }

        function deleteLightboxImage(deleteUrl) {
            if (confirm('Bạn có chắc muốn xóa ảnh này?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = deleteUrl;
                form.innerHTML = `
                    <input type="hidden" name="_token" value="${document.querySelector('meta[name=csrf-token]').content}">
                    <input type="hidden" name="_method" value="DELETE">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function updateThumbnails() {
            const thumbs = document.querySelectorAll('.lightbox-thumb');
            thumbs.forEach((thumb, idx) => {
                if (idx === lightboxIndex) {
                    thumb.style.transform = 'scale(1.1)';
                    thumb.style.opacity = '1';
                    thumb.style.boxShadow = '0 0 0 3px #a855f7';
                    // Scroll thumbnail vào giữa màn hình
                    thumb.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
                } else {
                    thumb.style.transform = 'scale(1)';
                    thumb.style.opacity = '0.6';
                    thumb.style.boxShadow = 'none';
                }
            });
        }

        function renderLightbox() {
            const existing = document.getElementById('global-lightbox');
            if (existing) existing.remove();
            const currentData = lightboxHasActions ? lightboxImageData[lightboxIndex] : null;
            const html = `
                <div id="global-lightbox" style="position: fixed; inset: 0; z-index: 999999; background: rgba(0,0,0,0.95); display: flex; flex-direction: column;">
                    <!-- Top Bar -->
                    <div style="height: 70px; display: flex; align-items: center; justify-content: space-between; padding: 0 20px; flex-shrink: 0;">
                        <div id="lightbox-counter" style="background: white; color: black; padding: 8px 16px; border-radius: 9999px; font-weight: bold; font-size: 14px;">
                            ${lightboxIndex + 1} / ${lightboxImages.length}
                        </div>
                        
                        <div style="display: flex; gap: 10px; align-items: center;">
                            ${lightboxHasActions && currentData ? `
                                <a id="lightbox-download-btn" href="${currentData.download}" style="width: 44px; height: 44px; border-radius: 50%; background: #22c55e; color: white; border: none; cursor: pointer; font-size: 16px; display: flex; align-items: center; justify-content: center; text-decoration: none; box-shadow: 0 4px 20px rgba(0,0,0,0.3);">
                                    <i class="fa-solid fa-download"></i>
                                </a>
                                <button id="lightbox-delete-btn" onclick="deleteLightboxImage('${currentData.delete}')" style="width: 44px; height: 44px; border-radius: 50%; background: #ef4444; color: white; border: none; cursor: pointer; font-size: 16px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 20px rgba(0,0,0,0.3);">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            ` : ''}
                            <button onclick="closeLightbox()" style="width: 50px; height: 50px; border-radius: 50%; background: white; color: black; border: none; cursor: pointer; font-size: 24px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 20px rgba(0,0,0,0.3);">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Main Image Area -->
                    <div style="flex: 1; display: flex; align-items: center; justify-content: center; position: relative; padding: 20px; min-height: 0;" onclick="closeLightbox()">
                        ${lightboxImages.length > 1 ? `
                        <button onclick="event.stopPropagation(); lightboxPrev();" style="position: absolute; left: 20px; width: 50px; height: 50px; border-radius: 50%; background: white; color: black; border: none; cursor: pointer; font-size: 20px; display: flex; align-items: center; justify-content: center; z-index: 10; box-shadow: 0 4px 20px rgba(0,0,0,0.3);">
                            <i class="fa-solid fa-chevron-left"></i>
                        </button>
                        ` : ''}
                        
                        <img 
                            id="lightbox-main-image"
                            src="${lightboxImages[lightboxIndex]}" 
                            style="max-height: 100%; max-width: calc(100% - 140px); object-fit: contain; border-radius: 12px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);"
                            onclick="event.stopPropagation();"
                            onerror="this.src='/images/placeholder.svg'"
                        >
                        
                        ${lightboxImages.length > 1 ? `
                        <button onclick="event.stopPropagation(); lightboxNext();" style="position: absolute; right: 20px; width: 50px; height: 50px; border-radius: 50%; background: white; color: black; border: none; cursor: pointer; font-size: 20px; display: flex; align-items: center; justify-content: center; z-index: 10; box-shadow: 0 4px 20px rgba(0,0,0,0.3);">
                            <i class="fa-solid fa-chevron-right"></i>
                        </button>
                        ` : ''}
                    </div>
                    
                    ${lightboxImages.length > 1 ? `
                    <div style="height: 100px; display: flex; align-items: center; justify-content: center; padding: 10px; flex-shrink: 0;">
                        <div style="display: flex; gap: 10px; padding: 10px; background: rgba(255,255,255,0.9); border-radius: 16px; max-width: 90vw; overflow-x: auto;">
                            ${lightboxImages.map((img, idx) => `
                                <button 
                                    onclick="event.stopPropagation(); lightboxIndex = ${idx}; updateLightboxImage();" 
                                    class="lightbox-thumb"
                                    style="width: 60px; height: 60px; border-radius: 8px; overflow: hidden; flex-shrink: 0; border: none; padding: 0; cursor: pointer; transition: all 0.2s; ${idx === lightboxIndex ? 'transform: scale(1.1); box-shadow: 0 0 0 3px #a855f7;' : 'opacity: 0.6;'}"
                                >
                                    <img src="${img}" style="width: 100%; height: 100%; object-fit: cover;">
                                </button>
                            `).join('')}
                        </div>
                    </div>
                    ` : ''}
                </div>
            `;
            document.body.insertAdjacentHTML('beforeend', html);
            document.addEventListener('keydown', handleLightboxKeydown);
            // Scroll thumbnail đang active vào giữa sau khi render
            setTimeout(() => {
                const activeThumb = document.querySelectorAll('.lightbox-thumb')[lightboxIndex];
                if (activeThumb) {
                    activeThumb.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
                }
            }, 100);
        }

        function handleLightboxKeydown(e) {
            if (!lightboxOpen) return;
            if (e.key === 'Escape') closeLightbox();
            if (e.key === 'ArrowLeft') lightboxPrev();
            if (e.key === 'ArrowRight') lightboxNext();
        }
    </script>

    <!-- jQuery (required for Select2) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- Initialize Select2 -->
    <script>
        $(document).ready(function () {
            // Initialize Select2 for all select elements
            $('select').each(function () {
                if ($(this).data('no-select2')) {
                    return;
                }
                const minResults = $(this).data('min-results-for-search');
                const $parent = $(this).parent();
                $(this).select2({
                    minimumResultsForSearch: (minResults !== undefined ? minResults : 5),
                    dropdownAutoWidth: false,
                    width: '100%',
                    dropdownParent: $parent
                });
            });
            // Handle form submit on change for filter selects
            $('.filter-select').on('select2:select', function () {
                $(this).closest('form').submit();
            });
        });
        // Re-initialize Select2 after Livewire updates
        document.addEventListener('livewire:load', function () {
            Livewire.hook('message.processed', (message, component) => {
                $('select').each(function () {
                    if (!$(this).hasClass('select2-hidden-accessible')) {
                        if ($(this).data('no-select2')) {
                            return;
                        }
                        const minResults = $(this).data('min-results-for-search');
                        const $parent = $(this).parent();
                        $(this).select2({
                            minimumResultsForSearch: (minResults !== undefined ? minResults : 5),
                            dropdownAutoWidth: false,
                            width: '100%',
                            dropdownParent: $parent
                        });
                    }
                });
            });
        });
    </script>

    <script>
        // Global Alpine component for Select2 + Livewire integration
        document.addEventListener('alpine:init', () => {
            Alpine.data('select2Livewire', ({ model, minResults = 5 }) => ({
                model,
                minResults,
                init() {
                    const $select = $(this.$refs.select);
                    const $dropdownParent = $select.parent();
                    $select.select2({
                        minimumResultsForSearch: this.minResults,
                        dropdownAutoWidth: false,
                        width: '100%',
                        dropdownParent: $dropdownParent
                    });

                    $select.val(this.model).trigger('change.select2');

                    $select.on('change', (event) => {
                        this.model = event.target.value;
                    });

                    this.$watch('model', (value) => {
                        if ($select.val() !== value) {
                            $select.val(value).trigger('change.select2');
                        }
                    });
                }
            }));
        });
    </script>

    @stack('scripts')

    <!-- Service Worker Registration -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then((registration) => {
                        console.log('[SW] Registered:', registration.scope);
                    })
                    .catch((error) => {
                        console.log('[SW] Registration failed:', error);
                    });
            });
        }
    </script>

    <!-- Livewire Scripts (REQUIRED for wire:click) -->
    @livewireScripts
</body>

</html>
