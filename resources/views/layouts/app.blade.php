<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Store Billing & POS System')</title>
    
    <!-- Google Fonts: Inter & JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'system-ui', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace'],
                    },
                    boxShadow: {
                        'card': '0 1px 3px 0 rgba(0, 0, 0, 0.05), 0 1px 2px -1px rgba(0, 0, 0, 0.05)',
                        'card-hover': '0 10px 15px -3px rgba(0, 0, 0, 0.07), 0 4px 6px -4px rgba(0, 0, 0, 0.05)',
                        'glow-green': '0 4px 14px 0 rgba(22, 163, 74, 0.35)',
                    }
                }
            }
        }
    </script>
    
    <!-- Custom Application CSS with Cache Busting -->
    <link rel="stylesheet" href="{{ asset('css/billing.css') }}?v={{ file_exists(public_path('css/billing.css')) ? filemtime(public_path('css/billing.css')) : time() }}">
    @stack('styles')
</head>
<body class="bg-slate-100/80 text-slate-800 antialiased font-sans min-h-screen flex flex-col selection:bg-indigo-500 selection:text-white">

    <!-- Top Navigation Header Partial -->
    @include('partials.header')

    <!-- Main Container Content Slot -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
        @yield('content')
    </main>

    <!-- Global Modals Partial -->
    @include('partials.order-history-modal')

    <!-- Custom Application JavaScript Module with Cache Busting -->
    <script src="{{ asset('js/billing.js') }}?v={{ file_exists(public_path('js/billing.js')) ? filemtime(public_path('js/billing.js')) : time() }}"></script>
    @stack('scripts')
</body>
</html>
