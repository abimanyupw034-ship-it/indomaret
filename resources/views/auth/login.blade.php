@extends('layouts.base')

@section('title', 'Login Sistem POS')

@section('content')
<div class="min-h-screen flex items-center justify-center p-4 bg-gradient-to-br from-slate-900 via-blue-950 to-slate-900 relative overflow-hidden">
    <!-- Ambient Background Blobs -->
    <div class="absolute -top-32 -left-32 w-96 h-96 bg-blue-600/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-32 -right-32 w-96 h-96 bg-red-600/20 rounded-full blur-3xl pointer-events-none"></div>

    <div class="w-full max-w-md relative z-10">
        <!-- Logo Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center space-x-2 bg-white/10 backdrop-blur-md px-5 py-2.5 rounded-2xl border border-white/15 shadow-xl mb-4">
                <div class="flex items-center space-x-1 font-black text-2xl tracking-wider">
                    <span class="text-blue-400">INDO</span><span class="text-red-500">POS</span>
                </div>
                <span class="text-xs font-semibold px-2 py-0.5 bg-yellow-400/20 text-yellow-300 rounded-md border border-yellow-400/30">RETAIL</span>
            </div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Point of Sale & Inventory</h1>
            <p class="text-sm text-slate-400 mt-1">Sistem Terpadu Kasir, Gudang, & Analisis Admin</p>
        </div>

        <!-- Login Card -->
        <div class="bg-white/95 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/40 p-8">
            <h2 class="text-lg font-bold text-slate-800 mb-6 flex items-center">
                <i class="fa-solid fa-lock text-blue-600 mr-2.5"></i> Masuk ke Akun Anda
            </h2>

            <form action="{{ route('login.post') }}" method="POST" id="loginForm" class="space-y-5">
                @csrf
                <div>
                    <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Alamat Email</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i class="fa-solid fa-envelope"></i>
                        </div>
                        <input type="email" name="email" id="email" required
                            class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:bg-white transition"
                            placeholder="nama@pos.com" value="{{ old('email', 'kasir@pos.com') }}">
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Kata Sandi</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i class="fa-solid fa-key"></i>
                        </div>
                        <input type="password" name="password" id="password" required
                            class="w-full pl-10 pr-10 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:bg-white transition"
                            placeholder="••••••••" value="password">
                        <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600">
                            <i class="fa-solid fa-eye" id="togglePasswordIcon"></i>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between text-xs">
                    <label class="flex items-center text-slate-600 cursor-pointer">
                        <input type="checkbox" name="remember" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500 mr-2">
                        Ingat Saya
                    </label>
                    <span class="text-slate-400">Default password: <code class="font-mono text-slate-600">password</code></span>
                </div>

                <button type="submit" class="w-full py-3.5 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-bold rounded-xl shadow-lg shadow-blue-600/30 transition transform active:scale-[0.98] flex items-center justify-center space-x-2">
                    <span>Masuk ke Sistem</span>
                    <i class="fa-solid fa-arrow-right text-xs"></i>
                </button>
            </form>

            <!-- Quick Role Selectors for testing -->
            <div class="mt-8 pt-6 border-t border-slate-100">
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 text-center mb-3">Akses Cepat Pengujian (1-Click Demo)</p>
                <div class="grid grid-cols-3 gap-2">
                    <button type="button" onclick="fillAndSubmit('kasir@pos.com', 'password')"
                        class="p-2.5 bg-blue-50 hover:bg-blue-100 border border-blue-200 rounded-xl text-center transition group">
                        <i class="fa-solid fa-cash-register text-blue-600 text-base mb-1 block group-hover:scale-110 transition"></i>
                        <span class="text-xs font-bold text-blue-900 block">Kasir</span>
                        <span class="text-[10px] text-blue-600/80">Desktop POS</span>
                    </button>
                    <button type="button" onclick="fillAndSubmit('gudang@pos.com', 'password')"
                        class="p-2.5 bg-amber-50 hover:bg-amber-100 border border-amber-200 rounded-xl text-center transition group">
                        <i class="fa-solid fa-warehouse text-amber-600 text-base mb-1 block group-hover:scale-110 transition"></i>
                        <span class="text-xs font-bold text-amber-900 block">Gudang</span>
                        <span class="text-[10px] text-amber-600/80">Inventory</span>
                    </button>
                    <button type="button" onclick="fillAndSubmit('admin@pos.com', 'password')"
                        class="p-2.5 bg-purple-50 hover:bg-purple-100 border border-purple-200 rounded-xl text-center transition group">
                        <i class="fa-solid fa-chart-line text-purple-600 text-base mb-1 block group-hover:scale-110 transition"></i>
                        <span class="text-xs font-bold text-purple-900 block">Admin</span>
                        <span class="text-[10px] text-purple-600/80">Read-Only</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Footer Info -->
        <div class="mt-6 text-center text-xs text-slate-500">
            <p>Single Source of Truth · Transactional Stock Lock · Audit Trail Mutasi</p>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function togglePassword() {
        const input = document.getElementById('password');
        const icon = document.getElementById('togglePasswordIcon');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }

    function fillAndSubmit(email, password) {
        document.getElementById('email').value = email;
        document.getElementById('password').value = password;
        document.getElementById('loginForm').submit();
    }
</script>
@endpush
@endsection
