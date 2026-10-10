@extends('layouts.app')

@section('title', 'Admin Sign In - ' . $siteSettings->brand_name)
@section('robots', 'noindex, nofollow')

@section('content')
<div class="py-20 px-4 sm:px-6 lg:px-8 max-w-md mx-auto">
    <div class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
        <div class="text-center mb-8">
            <div class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-brand-primary text-white font-extrabold text-xl shadow-none mb-4">
                A
            </div>
            <h1 class="text-2xl font-bold text-slate-800">Admin Access</h1>
            <p class="text-xs text-slate-500 mt-1 font-medium">Authenticate to manage site settings and publications</p>
        </div>

        <form action="{{ route('admin.login') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-1.5">Email Address</label>
                <input type="email" name="email" required value="admin@smviewer.org" class="w-full rounded-xl border border-slate-200 bg-slate-50/50 py-3.5 px-4 text-xs sm:text-sm text-slate-800 placeholder-slate-400 focus:border-brand-primary focus:outline-none focus:ring-2 focus:ring-brand-primary/10 transition-all font-semibold">
                @error('email')
                    <p class="text-xs text-red-500 mt-1.5 font-bold flex items-center gap-1">
                        <i class="ph ph-warning-circle"></i>
                        <span>{{ $message }}</span>
                    </p>
                @enderror
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-1.5">Password</label>
                <input type="password" name="password" required value="admin12345" class="w-full rounded-xl border border-slate-200 bg-slate-50/50 py-3.5 px-4 text-xs sm:text-sm text-slate-800 placeholder-slate-400 focus:border-brand-primary focus:outline-none focus:ring-2 focus:ring-brand-primary/10 transition-all font-semibold">
            </div>

            <!-- Remember Device -->
            <div class="flex items-center">
                <input id="remember" name="remember" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-brand-primary focus:ring-brand-primary/10">
                <label for="remember" class="ml-2 block text-xs text-slate-500 font-bold cursor-pointer select-none">Remember this device</label>
            </div>

            <button type="submit" class="w-full rounded-xl bg-brand-primary py-4 text-xs sm:text-sm font-bold text-white shadow-md shadow-brand-primary/10 hover:opacity-95 transition-all cursor-pointer">
                Sign In
            </button>
        </form>
    </div>
</div>
@endsection
