@extends('layouts.base')

@section('content')
<div class="min-h-screen flex bg-slate-100">
    <!-- Desktop Sidebar -->
    <aside class="w-64 bg-slate-900 text-slate-300 flex flex-col shrink-0 border-r border-slate-800">
        <!-- Brand Header -->
        <div class="p-6 border-b border-slate-800 flex items-center justify-between">
            <div class="flex items-center space-x-2 font-black text-xl tracking-wider text-white">
                <span class="text-blue-400">INDO</span><span class="text-red-500">POS</span>
                <span class="text-[10px] font-bold px-2 py-0.5 bg-amber-500/20 text-amber-400 rounded border border-amber-500/30">GUDANG</span>
            </div>
        </div>

        <!-- Navigation Menu -->
        <nav class="flex-1 p-4 space-y-1.5 text-sm overflow-y-auto">
            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500 px-3 py-1">Manajemen Stok</div>

            <a href="{{ route('warehouse.dashboard') }}"
                class="flex items-center px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('warehouse.dashboard') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/30' : 'hover:bg-slate-800 hover:text-white text-slate-300' }}">
                <i class="fa-solid fa-chart-pie w-5 text-center mr-3"></i>
                <span>Dashboard Gudang</span>
            </a>

            <a href="{{ route('warehouse.products.index') }}"
                class="flex items-center px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('warehouse.products.*') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/30' : 'hover:bg-slate-800 hover:text-white text-slate-300' }}">
                <i class="fa-solid fa-boxes-stacked w-5 text-center mr-3"></i>
                <span>Master Data Barang</span>
            </a>

            <div class="pt-4 text-[11px] font-bold uppercase tracking-wider text-slate-500 px-3 py-1">Operasional Logistik</div>

            <a href="{{ route('warehouse.receipts.create') }}"
                class="flex items-center px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('warehouse.receipts.*') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/30' : 'hover:bg-slate-800 hover:text-white text-slate-300' }}">
                <i class="fa-solid fa-truck-ramp-box w-5 text-center mr-3"></i>
                <span>Penerimaan Barang</span>
            </a>

            <a href="{{ route('warehouse.adjustments.create') }}"
                class="flex items-center px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('warehouse.adjustments.*') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/30' : 'hover:bg-slate-800 hover:text-white text-slate-300' }}">
                <i class="fa-solid fa-clipboard-check w-5 text-center mr-3"></i>
                <span>Koreksi & Opname Stok</span>
            </a>

            <a href="{{ route('warehouse.movements') }}"
                class="flex items-center px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('warehouse.movements') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/30' : 'hover:bg-slate-800 hover:text-white text-slate-300' }}">
                <i class="fa-solid fa-book-journal-whills w-5 text-center mr-3"></i>
                <span>Buku Mutasi (Audit Trail)</span>
            </a>
        </nav>

        <!-- User Profile & Logout -->
        <div class="p-4 border-t border-slate-800 bg-slate-950/60">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3 overflow-hidden">
                    <div class="w-9 h-9 rounded-xl bg-amber-500/20 text-amber-400 border border-amber-500/30 flex items-center justify-center font-bold text-sm shrink-0">
                        <i class="fa-solid fa-warehouse"></i>
                    </div>
                    <div class="overflow-hidden">
                        <div class="text-xs font-bold text-white truncate">{{ Auth::user()->name }}</div>
                        <div class="text-[10px] text-slate-400">Petugas Gudang</div>
                    </div>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="p-2 text-slate-400 hover:text-rose-400 transition" title="Logout">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main Content Body -->
    <main class="flex-1 flex flex-col min-w-0 overflow-y-auto">
        <!-- Top Navigation Header -->
        <header class="bg-white border-b border-slate-200 px-8 py-4 flex items-center justify-between shrink-0 shadow-sm">
            <div>
                <h1 class="text-xl font-bold text-slate-800 tracking-tight">@yield('page_title', 'Dashboard')</h1>
                <p class="text-xs text-slate-500 mt-0.5">@yield('page_subtitle', 'Sistem Manajemen Gudang & Stok Terpadu')</p>
            </div>
            <div class="flex items-center space-x-3">
                @yield('header_actions')
            </div>
        </header>

        <!-- Page Inner Content -->
        <div class="p-8 flex-1">
            @yield('warehouse_content')
        </div>
    </main>
</div>
@endsection
