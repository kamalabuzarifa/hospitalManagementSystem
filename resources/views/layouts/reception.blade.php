<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" class="{{ ($appSettings->dark_mode ?? false) ? 'dark' : '' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'Hospital Management'))</title>
    <link rel="icon" type="image/png" href="/images/hospital-logo.png" sizes="64x64" />
    <link rel="icon" type="image/png" href="/images/hospital-logo.png" sizes="96x96" />
    <link rel="icon" type="image/png" href="/images/hospital-logo.png" sizes="128x128" />
    <link rel="icon" type="image/png" href="/images/hospital-logo.png" sizes="256x256" />
    <link rel="icon" type="image/png" href="/images/hospital-logo.png" sizes="512x512" />
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* Unified spacing and RTL flip for sidebar items */
        .nav-item {
            display: flex !important;
            align-items: center;
            gap: 0.75rem;
        }

        [dir="rtl"] .nav-item {
            /* In RTL, keep row so first child (svg) renders on the right */
            flex-direction: row !important;
            text-align: right;
        }

        [dir="ltr"] .nav-item {
            flex-direction: row !important;
            text-align: left;
        }

        .nav-item svg {
            flex-shrink: 0;
        }

        .nav-item span {
            flex: 1;
        }

        /* Brand title with icon */
        .brand {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .brand img { width: 1.5rem; height: 1.5rem; object-fit: contain; }
        /* Make brand icon visibly larger than title text */
        .brand .brand-icon { width: 4rem; height: 4rem; object-fit: contain; }

        /* Logical margins for inline gaps */
        .inline-gap-start { margin-inline-start: 0.75rem; }
        .inline-gap-end { margin-inline-end: 0.75rem; }
        /* RTL table alignment - ensure all cells align right */
        [dir="rtl"] table { text-align: right; }
        [dir="rtl"] th,
        [dir="rtl"] td {
            text-align: right !important;
            padding-right: 1.5rem !important;
            padding-left: 0.375rem !important;
        }
        [dir="rtl"] .text-left { text-align: right; }
        /* RTL form layout */
        [dir="rtl"] .flex-col.lg\:flex-row { flex-direction: row-reverse; }
        [dir="rtl"] .lg\:justify-between { justify-content: space-between; }
        /* Search icon positioning */
        [dir="rtl"] .absolute.inset-y-0.left-3 { left: auto; right: 0.75rem; }
        [dir="rtl"] .pl-10 { padding-left: 1rem; padding-right: 2.5rem; }
        /* Action buttons alignment */
        [dir="rtl"] .flex.justify-around { flex-direction: row-reverse; justify-content: space-around; }
        /* Strong header swap using grid for better control */
        .staff-header { display: grid; gap: 40rem; align-items: center; margin-bottom: 2rem; }
        [dir="ltr"] .staff-header { grid-template-columns: 1fr auto; }
        [dir="rtl"] .staff-header { grid-template-columns: auto 1fr; grid-auto-flow: column; gap: 55rem; }
        .staff-header > button { white-space: nowrap; padding-left: 1.5rem; padding-right: 1.5rem; width: fit-content; }
        [dir="rtl"] .staff-header > button { margin-left: auto; }
        .staff-header > div { min-width: 0; }

        .px-6 { padding-left: 1.5rem !important; padding-right: 1.5rem !important; padding-top: 3px !important; padding-bottom: 3px !important; }

        /* Responsive sidebar overlay */
        @media (max-width: 1023px) {
            #admin-sidebar {
                position: fixed; z-index: 40; top: 0; bottom: 0; left: 0; width: 16rem;
                transform: translateX(-100%); transition: transform 0.35s ease;
                box-shadow: 2px 0 8px rgba(0,0,0,0.08); background: white; height: 100vh;
            }
            [dir="rtl"] #admin-sidebar { left: auto; right: 0; transform: translateX(100%); }
            #admin-sidebar.open { transform: translateX(0); z-index: 10000; }
            #admin-sidebar-backdrop { display: none; position: fixed; z-index: 30; inset: 0; background: rgba(0,0,0,0.3); }
            #admin-sidebar.open ~ #admin-sidebar-backdrop { display: block; }
            #admin-layout { flex-direction: column !important; }
        }
        @media (max-width: 1023px) { #admin-main-content { width: 100vw; } }
        @media (max-width: 640px) { .px-6 { padding-left: 0.75rem !important; padding-right: 0.75rem !important; } }
        @media (max-width: 767px) { #admin-main-content main { top: 13px; position: relative; } }

        .left-200 { left: 200px; }
        .right-200 { right: 200px; }

        /* Prevent page-level scrolling; make only the main content scrollable */
        html, body, #admin-layout, #admin-main-content { height: 100%; }
        body { overflow: hidden; }
        #admin-main-content { overflow: auto; }
        #admin-main-content main { min-height: 100%; }

        @media (min-width: 845px) { #sidebar-open-btn { display: none !important; } }
    </style>
    <script>
        // Set the HTML dir attribute based on the locale
        document.documentElement.dir = "{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}";

        document.addEventListener('DOMContentLoaded', () => {
            const sidebar = document.getElementById('admin-sidebar');
            const backdrop = document.getElementById('admin-sidebar-backdrop');
            const openBtn = document.getElementById('sidebar-open-btn');
            const closeBtn = document.getElementById('sidebar-close-btn');

            const HIDDEN = 'opacity-0';
            const BLOCKED = 'pointer-events-none';

            function updateSidebarButtons() {
                if (!sidebar) return;
                if (window.innerWidth < 1024 && sidebar.classList.contains('open')) {
                    openBtn?.classList.add(HIDDEN, BLOCKED);
                    closeBtn?.classList.remove(HIDDEN, BLOCKED);
                } else {
                    openBtn?.classList.remove(HIDDEN, BLOCKED);
                    closeBtn?.classList.add(HIDDEN, BLOCKED);
                }
            }

            openBtn?.addEventListener('click', () => {
                sidebar?.classList.add('open');
                updateSidebarButtons();
            });

            closeBtn?.addEventListener('click', () => {
                sidebar?.classList.remove('open');
                updateSidebarButtons();
            });

            backdrop?.addEventListener('click', () => {
                sidebar?.classList.remove('open');
                updateSidebarButtons();
            });

            window.addEventListener('resize', updateSidebarButtons);

            if (sidebar) {
                const observer = new MutationObserver(updateSidebarButtons);
                observer.observe(sidebar, { attributes: true, attributeFilter: ['class'] });
            }

            updateSidebarButtons();
        });
    </script>

</head>
<body class="font-sans antialiased bg-gray-50 text-gray-900 dark:bg-gray-900 dark:text-gray-100" style="direction: {{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }};">
    <div id="admin-layout" style="display: flex; height: 100vh; overflow: hidden; flex-direction: row;">
        <!-- Mobile sidebar open button -->
        <!-- Sidebar open (menu) button -->
        <button id="sidebar-open-btn" class="sidebar-all-btn-open lg:hidden fixed {{ app()->getLocale() === 'ar' ? 'right-4' : 'left-4' }} z-50 bg-blue-600 border border-gray-200 rounded-full p-2 shadow focus:outline-none" style="top:5px;left:5px; width: fit-content;" aria-label="{{ __('messages.open_sidebar') }}">
            <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" /></svg>
        </button>
        <!-- Sidebar -->
        <aside id="admin-sidebar" class="w-64 bg-white shadow-lg flex-shrink-0 flex flex-col">
            <!-- Close button attached to the aside edge (absolute, supports RTL) -->
            <button id="sidebar-close-btn" class="sidebar-all-btn-closed lg:hidden absolute top-4 z-50 bg-blue-600 border border-gray-200 rounded-full p-2 shadow focus:outline-none opacity-0 pointer-events-none {{ app()->getLocale() === 'ar' ? 'right-200' : 'left-200' }}" aria-label="{{ __('messages.close_sidebar') }}">
                <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <!-- Logo/Brand -->
            <div class="logo-brand px-6 py-6 border-b border-gray-200">
                <div class="brand">
                    @if(file_exists(public_path('images/hospital-logo.png')))
                        <img src="{{ asset('images/hospital-logo.png') }}" alt="{{ __('messages.brand') }}" class="brand-icon">
                    @else
                        <svg class="brand-icon" viewBox="0 0 24 24" aria-hidden="true">
                            <defs>
                                <linearGradient id="brandGradient" x1="0" y1="0" x2="1" y2="1">
                                    <stop offset="0%" stop-color="#ff4d4f"/>
                                    <stop offset="25%" stop-color="#ff9f0a"/>
                                    <stop offset="50%" stop-color="#34c759"/>
                                    <stop offset="75%" stop-color="#0a84ff"/>
                                    <stop offset="100%" stop-color="#af52de"/>
                                </linearGradient>
                            </defs>
                            <path fill="url(#brandGradient)" d="M12 2 L15 9 L22 12 L15 15 L12 22 L9 15 L2 12 L9 9 Z"/>
                            <title>{{ __('messages.brand') }}</title>
                        </svg>
                    @endif
                    <h1 class="text-title text-xl font-bold text-gray-800">{{ __('messages.brand') }}</h1>
                </div>
            </div>

            <!-- Navigation Menu -->
            <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
                @permission('view_dashboard')
                <a href="{{ route('reception.dashboard') }}" class="nav-item nav-link flex items-center px-4 py-3 rounded-lg transition {{ request()->routeIs('reception.dashboard') ? 'active' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span class="font-medium">{{ __('messages.dashboard') }}</span>
                </a>
                @endpermission
                
                @anyPermission('appointments.view', 'manage_appointments')
                <a href="{{ route('reception.appointments.index') }}" class="nav-item nav-link flex items-center px-4 py-3 rounded-lg transition {{ request()->routeIs('reception.appointments.*') ? 'active' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span class="font-medium">{{ __('messages.appointments') }}</span>
                </a>
                @endanyPermission

                @anyPermission('patients.view', 'manage_patients')
                <a href="{{ route('reception.patients.index') }}" class="nav-item nav-link flex items-center px-4 py-3 rounded-lg transition {{ request()->routeIs('reception.patients.*') ? 'active' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 15c2.5 0 4.847.655 6.879 1.804M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span class="font-medium">{{ __('messages.patients') }}</span>
                </a>
                @endanyPermission
                @permission('edit_profile')
                <a href="{{ route('reception.settings') }}" class="nav-item nav-link flex items-center px-4 py-3 rounded-lg transition {{ request()->routeIs('reception.settings') ? 'active' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span class="font-medium">{{ __('messages.settings') }}</span>
                </a>
                @endpermission
            </nav>
            <!-- Logout - في الأسفل -->
            <div class="px-4 py-4 border-t border-gray-200 mt-auto">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                        <button type="submit" class="nav-item flex items-center w-full px-4 py-3 text-red-600 rounded-lg hover:bg-red-50 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        <span class="font-medium">{{ __('messages.logout') }}</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Sidebar backdrop for mobile -->
        <div id="admin-sidebar-backdrop" class="lg:hidden" tabindex="-1"></div>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col overflow-hidden" style="top:20px; position:relative;">
            <!-- Main Content -->
            <main class="flex-1 overflow-y-auto bg-gray-50">
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
