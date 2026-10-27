<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Retail POS & Inventory System') - Indomaret POS</title>
    
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Tailwind CSS (Tailwind Play CDN for rich full styling) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace'],
                    },
                    colors: {
                        brand: {
                            blue: '#0056b3',
                            'blue-light': '#0284c7',
                            red: '#dc2626',
                            yellow: '#f59e0b',
                            dark: '#0f172a',
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- Chart.js for Admin Analytics -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            -webkit-tap-highlight-color: transparent;
        }
        /* Custom scrollbars */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: white !important;
                color: black !important;
            }
        }
    </style>
    @stack('styles')
</head>
<body class="bg-slate-100 text-slate-800 min-h-screen antialiased flex flex-col selection:bg-blue-500 selection:text-white">

    <!-- Flash Messages Container -->
    <div id="flash-alerts" class="fixed top-4 right-4 z-50 space-y-2 max-w-md w-full px-4 no-print pointer-events-none">
        @if (session('success'))
            <div class="pointer-events-auto flex items-center p-4 bg-emerald-600 text-white rounded-xl shadow-lg shadow-emerald-600/20 text-sm font-medium animate-bounce-once">
                <i class="fa-solid fa-circle-check text-lg mr-3"></i>
                <div class="flex-1">{{ session('success') }}</div>
                <button onclick="this.parentElement.remove()" class="ml-2 text-white/80 hover:text-white"><i class="fa-solid fa-xmark"></i></button>
            </div>
        @endif
        @if (session('error'))
            <div class="pointer-events-auto flex items-center p-4 bg-rose-600 text-white rounded-xl shadow-lg shadow-rose-600/20 text-sm font-medium">
                <i class="fa-solid fa-triangle-exclamation text-lg mr-3"></i>
                <div class="flex-1">{{ session('error') }}</div>
                <button onclick="this.parentElement.remove()" class="ml-2 text-white/80 hover:text-white"><i class="fa-solid fa-xmark"></i></button>
            </div>
        @endif
        @if ($errors->any())
            <div class="pointer-events-auto p-4 bg-rose-600 text-white rounded-xl shadow-lg shadow-rose-600/20 text-sm">
                <div class="flex items-center font-bold mb-1">
                    <i class="fa-solid fa-circle-xmark mr-2"></i> Perhatian:
                </div>
                <ul class="list-disc list-inside space-y-0.5 text-xs">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    @yield('content')

    <script>
        // Setup CSRF header for Ajax requests
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        // Helper to format Rupiah
        function formatRupiah(number) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(number);
        }

        // Auto dismiss flash alerts after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('#flash-alerts > div');
            alerts.forEach(el => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(-10px)';
                el.style.transition = 'all 0.4s ease';
                setTimeout(() => el.remove(), 400);
            });
        }, 5000);
    </script>
    @stack('scripts')
</body>
</html>
