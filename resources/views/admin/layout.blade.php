@extends('layouts.base')

@section('content')
<div class="min-h-screen bg-slate-50 flex flex-col pb-20 md:pb-8">

    <!-- Top Navigation Header -->
    <header class="bg-white border-b border-slate-200/80 sticky top-0 z-40 px-4 md:px-8 py-3.5 flex items-center justify-between shadow-sm">
        <div class="flex items-center space-x-3">
            <div class="flex items-center space-x-1 font-black text-lg tracking-wider">
                <span class="text-blue-600">INDO</span><span class="text-red-500">POS</span>
                <span class="text-[10px] font-bold px-2 py-0.5 bg-purple-100 text-purple-700 rounded-md border border-purple-200 ml-1">ADMIN</span>
            </div>
            <!-- Read-Only Indicator Badge -->
            <span class="hidden sm:inline-flex items-center text-[11px] font-semibold px-2.5 py-1 bg-amber-50 text-amber-800 rounded-full border border-amber-200">
                <i class="fa-solid fa-lock text-amber-600 mr-1.5 text-xs"></i> Mode Read-Only (Hanya Baca)
            </span>
        </div>

        <!-- Desktop Navigation Links -->
        <nav class="hidden md:flex items-center space-x-1 text-xs font-semibold text-slate-600">
            <a href="{{ route('admin.dashboard') }}"
                class="px-3.5 py-2 rounded-xl transition {{ request()->routeIs('admin.dashboard') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/20' : 'hover:bg-slate-100 text-slate-700' }}">
                <i class="fa-solid fa-chart-pie mr-1.5"></i> Dashboard
            </a>
            <a href="{{ route('admin.reports.sales') }}"
                class="px-3.5 py-2 rounded-xl transition {{ request()->routeIs('admin.reports.sales*') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/20' : 'hover:bg-slate-100 text-slate-700' }}">
                <i class="fa-solid fa-receipt mr-1.5"></i> Laporan Penjualan
            </a>
            <a href="{{ route('admin.reports.profit') }}"
                class="px-3.5 py-2 rounded-xl transition {{ request()->routeIs('admin.reports.profit*') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/20' : 'hover:bg-slate-100 text-slate-700' }}">
                <i class="fa-solid fa-arrow-trend-up mr-1.5"></i> Laba & Rugi (HPP)
            </a>
            <a href="{{ route('admin.reports.inventory') }}"
                class="px-3.5 py-2 rounded-xl transition {{ request()->routeIs('admin.reports.inventory*') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/20' : 'hover:bg-slate-100 text-slate-700' }}">
                <i class="fa-solid fa-boxes-stacked mr-1.5"></i> Persediaan & Valuasi
            </a>
        </nav>

        <!-- User & Logout -->
        <div class="flex items-center space-x-3">
            <div class="hidden sm:block text-right">
                <div class="text-xs font-bold text-slate-800">{{ Auth::user()->name }}</div>
                <div class="text-[10px] text-slate-500">Eksekutif Pemilik / Admin</div>
            </div>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="p-2 text-slate-500 hover:text-rose-600 transition text-sm rounded-xl hover:bg-slate-100" title="Keluar">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                </button>
            </form>
        </div>
    </header>

    <!-- Read-Only Banner on Mobile -->
    <div class="sm:hidden bg-amber-50 border-b border-amber-200 px-4 py-1.5 text-center text-[11px] text-amber-800 font-semibold flex items-center justify-center space-x-1">
        <i class="fa-solid fa-lock text-amber-600 text-xs"></i>
        <span>Mode Admin Read-Only · Akses Mutasi Operasional Dikunci</span>
    </div>

    <!-- Main Container -->
    <main class="flex-1 max-w-7xl w-full mx-auto p-4 md:p-8 space-y-6">
        @yield('admin_content')
    </main>

    <!-- Bottom Mobile Navigation Bar (Fixed for Mobile Web) -->
    <nav class="md:hidden fixed bottom-0 left-0 right-0 z-50 bg-white/95 backdrop-blur-md border-t border-slate-200 px-3 py-2 flex justify-around items-center shadow-lg">
        <a href="{{ route('admin.dashboard') }}"
            class="flex flex-col items-center justify-center py-1 px-2 rounded-xl text-center transition {{ request()->routeIs('admin.dashboard') ? 'text-blue-600 font-bold' : 'text-slate-500' }}">
            <i class="fa-solid fa-chart-pie text-lg mb-0.5"></i>
            <span class="text-[10px]">Dashboard</span>
        </a>
        <a href="{{ route('admin.reports.sales') }}"
            class="flex flex-col items-center justify-center py-1 px-2 rounded-xl text-center transition {{ request()->routeIs('admin.reports.sales*') ? 'text-blue-600 font-bold' : 'text-slate-500' }}">
            <i class="fa-solid fa-receipt text-lg mb-0.5"></i>
            <span class="text-[10px]">Penjualan</span>
        </a>
        <a href="{{ route('admin.reports.profit') }}"
            class="flex flex-col items-center justify-center py-1 px-2 rounded-xl text-center transition {{ request()->routeIs('admin.reports.profit*') ? 'text-blue-600 font-bold' : 'text-slate-500' }}">
            <i class="fa-solid fa-arrow-trend-up text-lg mb-0.5"></i>
            <span class="text-[10px]">Laba HPP</span>
        </a>
        <a href="{{ route('admin.reports.inventory') }}"
            class="flex flex-col items-center justify-center py-1 px-2 rounded-xl text-center transition {{ request()->routeIs('admin.reports.inventory*') ? 'text-blue-600 font-bold' : 'text-slate-500' }}">
            <i class="fa-solid fa-boxes-stacked text-lg mb-0.5"></i>
            <span class="text-[10px]">Stok Aset</span>
        </a>
    </nav>

</div>
@endsection
