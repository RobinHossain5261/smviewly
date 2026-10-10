<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full" style="--primary-color: {{ $siteSettings->primary_color }}; --secondary-color: {{ $siteSettings->secondary_color }};">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Admin Dashboard - {{ $siteSettings->brand_name }}</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" href="{{ url('/favicon.ico') }}" sizes="48x48">
    <link rel="icon" type="image/png" sizes="48x48" href="{{ url('/favicon-48x48.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ url('/apple-touch-icon.png') }}">

    <link rel="stylesheet" href="{{ asset('fonts/icons/regular.css') }}">
    <link rel="stylesheet" href="{{ asset('fonts/icons/bold.css') }}">
    <link rel="stylesheet" href="{{ asset('fonts/icons/fill.css') }}">
    <!-- CSS / Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        button, a, select, input, textarea, .cursor-pointer {
            cursor: pointer !important;
        }
    </style>
</head>
<body class="ad-shell h-full bg-slate-50 text-slate-800 antialiased flex">

    <!-- Sidebar Backdrop Overlay -->
    <div id="admin-sidebar-backdrop" class="fixed inset-0 z-40 bg-slate-900/60 backdrop-blur-sm hidden opacity-0 transition-opacity duration-300 lg:hidden" onclick="toggleAdminSidebar()"></div>

    <!-- Admin Wrapper Grid -->
    <div class="flex w-full min-h-screen">
        
        <!-- Sidebar Navigation (Light Mode styling, Collapsible on Mobile) -->
        <aside id="admin-sidebar" class="fixed inset-y-0 left-0 z-50 w-64 border-r border-slate-200 bg-white flex flex-col justify-between shrink-0 transform -translate-x-full transition-transform duration-300 lg:translate-x-0 lg:static lg:z-auto">
            <div>
                <!-- Brand Title -->
                <div class="h-16 flex items-center justify-between px-6 border-b border-slate-100 shrink-0">
                    <a href="{{ route('home') }}" class="flex items-center gap-2 font-extrabold text-slate-900 text-base">
                        <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-brand-primary text-white font-extrabold text-xs">
                            {{ $siteSettings->brand_initial }}
                        </div>
                        <span>{{ $siteSettings->brand_name }} Admin</span>
                    </a>
                    <button type="button" aria-label="Close navigation" onclick="toggleAdminSidebar()" class="lg:hidden text-slate-400 hover:text-slate-900 cursor-pointer">
                        <i class="ph ph-x text-xl"></i>
                    </button>
                </div>

                <!-- Links list -->
                <nav class="p-4 space-y-1.5 text-xs sm:text-sm font-semibold">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-slate-50 transition-colors {{ request()->routeIs('admin.dashboard') ? 'bg-brand-primary/[0.06] text-brand-primary border-l-2 border-brand-primary' : 'text-slate-600 hover:text-slate-900' }}">
                        <i class="ph ph-squares-four text-lg"></i>
                        <span>Overview</span>
                    </a>
                    <a href="{{ route('admin.settings') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-slate-50 transition-colors {{ request()->routeIs('admin.settings') ? 'bg-brand-primary/[0.06] text-brand-primary border-l-2 border-brand-primary' : 'text-slate-600 hover:text-slate-900' }}">
                        <i class="ph ph-palette text-lg"></i>
                        <span>Site Settings</span>
                    </a>
                    <a href="{{ route('admin.posts.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-slate-50 transition-colors {{ request()->routeIs('admin.posts.*') ? 'bg-brand-primary/[0.06] text-brand-primary border-l-2 border-brand-primary' : 'text-slate-600 hover:text-slate-900' }}">
                        <i class="ph ph-note-pencil text-lg"></i>
                        <span>Blog Posts</span>
                    </a>
                    <a href="{{ route('admin.categories.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-slate-50 transition-colors {{ request()->routeIs('admin.categories.*') ? 'bg-brand-primary/[0.06] text-brand-primary border-l-2 border-brand-primary' : 'text-slate-600 hover:text-slate-900' }}">
                        <i class="ph ph-folder text-lg"></i>
                        <span>Categories</span>
                    </a>
                    <a href="{{ route('admin.faqs.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-slate-50 transition-colors {{ request()->routeIs('admin.faqs.*') ? 'bg-brand-primary/[0.06] text-brand-primary border-l-2 border-brand-primary' : 'text-slate-600 hover:text-slate-900' }}">
                        <i class="ph ph-question text-lg"></i>
                        <span>FAQ Manager</span>
                    </a>
                    <a href="{{ route('admin.contacts.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-slate-50 transition-colors {{ request()->routeIs('admin.contacts.*') ? 'bg-brand-primary/[0.06] text-brand-primary border-l-2 border-brand-primary' : 'text-slate-600 hover:text-slate-900' }}">
                        <i class="ph ph-envelope-simple text-lg"></i>
                        <span>Contacts</span>
                    </a>
                </nav>
            </div>

            <!-- Bottom Actions -->
            <div class="p-4 border-t border-slate-100 shrink-0">
                <a href="{{ route('home') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-600 hover:text-slate-900 hover:bg-slate-50 text-sm font-semibold transition-colors mb-2">
                    <i class="ph ph-arrow-left text-lg"></i>
                    <span>View Site</span>
                </a>
                
                <form action="{{ route('admin.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-red-600 hover:text-red-700 hover:bg-red-50 text-sm font-semibold transition-colors cursor-pointer">
                        <i class="ph ph-sign-out text-lg"></i>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content Area Wrapper -->
        <div class="flex-grow flex flex-col min-w-0">
            <!-- Mobile Header Bar -->
            <header class="lg:hidden h-16 border-b border-slate-200 bg-white flex items-center justify-between px-6 shrink-0">
                <div class="flex items-center gap-3">
                    <button type="button" id="admin-menu-toggle" aria-label="Open navigation" aria-controls="admin-sidebar" aria-expanded="false" onclick="toggleAdminSidebar()" class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 cursor-pointer">
                        <i class="ph ph-list text-xl"></i>
                    </button>
                    <span class="text-sm font-extrabold text-slate-950">Administration</span>
                </div>
                <a href="{{ route('home') }}" class="flex items-center gap-2 font-extrabold text-slate-900 text-sm">
                    <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-gradient-to-tr from-brand-primary to-brand-secondary text-white font-extrabold text-xs">
                        V
                    </div>
                    <span>{{ $siteSettings->brand_name }}</span>
                </a>
            </header>

            <!-- Main Content Area -->
            <main class="flex-grow p-4 sm:p-6 lg:p-8 overflow-y-auto">
                <!-- Success/Error Alerts (Light mode layout matching) -->
                @if(session('success'))
                    <div class="mb-6 p-4 rounded-xl bg-green-50 border border-green-200 text-xs sm:text-sm text-green-800 font-medium flex items-center gap-2">
                        <span class="text-green-500 font-bold">✓</span> {{ session('success') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-xs sm:text-sm text-red-800 font-medium">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>

    </div>

    <!-- Sidebar Animation Script Toggle -->
    <script>
        function toggleAdminSidebar() {
            const sidebar = document.getElementById('admin-sidebar');
            const backdrop = document.getElementById('admin-sidebar-backdrop');
            const opening = sidebar.classList.contains('-translate-x-full');
            document.getElementById('admin-menu-toggle')?.setAttribute('aria-expanded', String(opening));
            document.body.style.overflow = opening ? 'hidden' : '';
            if (opening) {
                sidebar.classList.remove('-translate-x-full');
                sidebar.classList.add('translate-x-0');
                backdrop.classList.remove('hidden');
                setTimeout(() => {
                    backdrop.classList.remove('opacity-0');
                    backdrop.classList.add('opacity-100');
                }, 10);
            } else {
                sidebar.classList.remove('translate-x-0');
                sidebar.classList.add('-translate-x-full');
                backdrop.classList.remove('opacity-100');
                backdrop.classList.add('opacity-0');
                setTimeout(() => {
                    backdrop.classList.add('hidden');
                }, 300);
            }
        }
        document.addEventListener('keydown', event => {
            if (event.key === 'Escape' && !document.getElementById('admin-sidebar').classList.contains('-translate-x-full')) {
                toggleAdminSidebar();
                document.getElementById('admin-menu-toggle').focus();
            }
        });
        window.matchMedia('(min-width: 1024px)').addEventListener('change', event => {
            if (event.matches && !document.getElementById('admin-sidebar').classList.contains('-translate-x-full')) toggleAdminSidebar();
        });
    </script>

</body>
</html>
