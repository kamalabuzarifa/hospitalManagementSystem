<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" class="{{ ($appSettings->dark_mode ?? false) ? 'dark' : '' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'Doctor Dashboard'))</title>
    <link rel="icon" type="image/png" href="/images/hospital-logo.png" sizes="64x64" />
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .doctor-nav-item {
            display: flex !important;
            align-items: center;
            gap: 0.75rem;
        }
        [dir="rtl"] .doctor-nav-item {
            flex-direction: row !important;
            text-align: right;
        }
        [dir="ltr"] .doctor-nav-item {
            flex-direction: row !important;
            text-align: left;
        }
        .doctor-nav-item svg { flex-shrink: 0; }
        .doctor-nav-item span { flex: 1; }
        .doctor-brand {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .doctor-brand img { 
            width: 1.5rem;
            height: 1.5rem;
            object-fit: contain; 
        }
        .doctor-brand .brand-icon {
             width: 4rem; height: 4rem; object-fit: contain;
             height: 4rem;
             object-fit: contain; 
        }
        /* Prevent page-level scrolling; make only the main content scrollable */
        html, body, #doctor-layout, #admin-main-content {
             height: 100%; 
        }
        body {
            overflow: hidden; 
        }
        /* allow main content area to scroll */
        #admin-main-content {
             overflow: auto; 
        }
        #admin-main-content main {
             min-height: 100%; 
        }
        .left-200 {
            left: 200px;
        }
        .right-200 {
            right: 200px;
        }
        /* Responsive sidebar overlay (copied from admin layout for consistent open/close behavior) */
        @media (max-width: 1023px) {
            #admin-sidebar {
                position: fixed;
                z-index: 40;
                top: 0;
                bottom: 0;
                left: 0;
                width: 16rem;
                transform: translateX(-100%);
                transition: transform 0.35s ease;
                box-shadow: 2px 0 8px rgba(0,0,0,0.08);
                background: white;
                height: 100vh;
            }
            [dir="rtl"] #admin-sidebar {
                left: auto;
                right: 0;
                transform: translateX(100%);
            }
            #admin-sidebar.open {
                transform: translateX(0);
                z-index: 10000;
            }
            #admin-sidebar-backdrop {
                display: none;
                position: fixed; 
                z-index: 30; 
                inset: 0; 
                background: rgba(0,0,0,0.3);
            }
            #admin-sidebar.open ~ #admin-sidebar-backdrop {
                 display: block; 
            }
            #admin-layout { 
                flex-direction: column !important; 
            }
        }
        @media (max-width: 1023px) { 
            #admin-main-content {
                width: 100vw; 
            } 
        }
        @media (max-width: 640px) {
             .px-6 { 
                padding-left: 0.75rem !important;
                padding-right: 0.75rem !important; 
            } 
        }
        @media (max-width: 767px) { 
            #admin-main-content main { 
                top: 13px; position: relative; 
            }
        }
    </style>

    <script>
        // Sidebar toggle (same logic as admin layout)
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('admin-sidebar');
            const backdrop = document.getElementById('admin-sidebar-backdrop');
            const openBtn = document.getElementById('sidebar-open-btn');
            const closeBtn = document.getElementById('sidebar-close-btn');

            function updateSidebarButtons() {
                const a = 'opacity-0';
                const b = 'pointer-events-none';
                if (window.innerWidth < 1024 && sidebar && sidebar.classList.contains('open')) {
                    if (openBtn) { openBtn.classList.add(a); openBtn.classList.add(b); }
                    if (closeBtn) { closeBtn.classList.remove(a); closeBtn.classList.remove(b); }
                } else {
                    if (openBtn) { openBtn.classList.remove(a); openBtn.classList.remove(b); }
                    if (closeBtn) { closeBtn.classList.add(a); closeBtn.classList.add(b); }
                }
            }

            if (openBtn) openBtn.onclick = () => { sidebar && sidebar.classList.add('open'); updateSidebarButtons(); };
            if (closeBtn) closeBtn.onclick = () => { sidebar && sidebar.classList.remove('open'); updateSidebarButtons(); };
            if (backdrop) backdrop.onclick = () => { sidebar && sidebar.classList.remove('open'); updateSidebarButtons(); };

            window.addEventListener('resize', updateSidebarButtons);
            const observer = new MutationObserver(updateSidebarButtons);
            if (sidebar) observer.observe(sidebar, { attributes: true, attributeFilter: ['class'] });
            updateSidebarButtons();
        });
    </script>
</head>
<body class="font-sans antialiased bg-gray-50 text-gray-900 dark:bg-gray-900 dark:text-gray-100" style="direction: {{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }};">
    <div id="doctor-layout" style="display: flex; min-height: 100vh; flex-direction: row;">
        <!-- Mobile sidebar open button (same id as admin for shared behavior) -->
        <button id="sidebar-open-btn" class="sidebar-all-btn-open lg:hidden fixed {{ app()->getLocale() === 'ar' ? 'right-4' : 'left-4' }} z-50 bg-blue-600 border border-gray-200 rounded-full p-2 shadow focus:outline-none" style="top:5px;left:5px; width: fit-content;" aria-label="{{ __('messages.open_sidebar') }}">
            <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" /></svg>
        </button>

        <!-- Sidebar (attach close button inside) -->
        <aside id="admin-sidebar" class="relative w-64 h-screen bg-white shadow-lg flex-shrink-0 flex flex-col">
            <!-- Close button attached to the aside edge (absolute, supports RTL) -->
            <button id="sidebar-close-btn" class="sidebar-all-btn-closed lg:hidden absolute top-4 z-50 bg-blue-600 border border-gray-200 rounded-full p-2 shadow focus:outline-none opacity-0 pointer-events-none {{ app()->getLocale() === 'ar' ? 'right-200' : 'left-200' }}" aria-label="{{ __('messages.close_sidebar') }}">
                <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            <!-- Doctor Avatar & Info -->
            <div class="avatar-doctor p-6 border-b flex items-center space-x-3">
                @php
                    $avatar = auth()->user()->doctor && auth()->user()->doctor->avatar_url
                        ? (Str::startsWith(auth()->user()->doctor->avatar_url, '/storage/')
                            ? auth()->user()->doctor->avatar_url
                            : '/storage/' . ltrim(auth()->user()->doctor->avatar_url, '/'))
                        : 'https://ui-avatars.com/api/?name=' . urlencode(auth()->user()->name) . '&background=0D8ABC&color=fff';
                @endphp
                <img src="{{ $avatar }}" class="w-12 h-12 rounded-full" alt="Doctor Avatar">
                <div>
                    <div class="text-title font-semibold text-gray-900">{{ __('messages.dr') }} {{ auth()->user()->name }}</div>
                    <div class="text-sm text-blue-400" style="color:#00b6ff;">{{ __("messages.specialization_" . Str::slug(auth()->user()->doctor->specialization, '_')) }}</div>
                </div>
                
            </div>
            <!-- Navigation Menu -->
            <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
                @permission('view_dashboard')
                <a href="{{ route('doctor.dashboard') }}" class="doctor-nav-item w-full flex items-center px-4 py-3 rounded-lg transition text-gray-600 hover:bg-gray-100 {{ request()->routeIs('doctor.dashboard') ? 'active' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                    <span class="font-medium">{{ __('messages.dashboard') }}</span>
                </a>
                @endpermission
                @permission('view_dashboard')
                <a href="{{ route('doctor.patient-queue') }}" class="doctor-nav-item w-full flex items-center px-4 py-3 rounded-lg transition text-gray-600 hover:bg-gray-100 {{ request()->routeIs('doctor.patient-queue') ? 'active' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                    <span class="font-medium">{{ __('messages.patients_queue') }}</span>
                </a>
                @endpermission
                @anyPermission('patients.view', 'manage_patients')
                <a href="{{ route('doctor.patients-records') }}" class="doctor-nav-item w-full flex items-center px-4 py-3 rounded-lg transition text-gray-600 hover:bg-gray-100 {{ (request()->routeIs('doctor.patients-records') || request()->routeIs('doctor.patients.*')) ? 'active' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 15c2.485 0 4.797.657 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    <span class="font-medium">{{ __('messages.patients_records') }}</span>
                </a>
                @endanyPermission
                @anyPermission('prescriptions.view', 'manage_prescriptions')
                <a href="{{ route('doctor.prescriptions.index') }}" class="doctor-nav-item w-full flex items-center px-4 py-3 rounded-lg transition text-gray-600 hover:bg-gray-100 {{ request()->routeIs('doctor.prescriptions.*') ? 'active' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2a2 2 0 012-2h2a2 2 0 012 2v2m-6 4h6a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                    <span class="font-medium">{{ __('messages.prescriptions') }}</span>
                </a>
                @endanyPermission
                @permission('edit_profile')
                <a href="{{ route('doctor.settings') }}" class="doctor-nav-item w-full flex items-center px-4 py-3 rounded-lg transition text-gray-600 hover:bg-gray-100 {{ request()->routeIs('doctor.settings') ? 'active' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>                    
                    <span class="font-medium">{{ __('messages.setting') }}</span>
                </a>
                @endpermission
            </nav>
            <!-- Logout -->
            <div class="px-4 py-4 border-t border-gray-200 mt-auto">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="doctor-nav-item flex items-center w-full px-4 py-3 text-red-600 rounded-lg hover:bg-red-50 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                        <span class="font-medium">{{ __('messages.logout') }}</span>
                    </button>
                </form>
            </div>
        </aside>
        <!-- Sidebar backdrop for mobile -->
        <div id="admin-sidebar-backdrop" class="lg:hidden" tabindex="-1"></div>

        <!-- Main Content Area -->
        <div id="admin-main-content" class="flex-1 flex flex-col overflow-y-auto" style="height: 100vh; top:20px; position:relative;">
            <main class="flex-1 bg-gray-50">
                @yield('content')
            </main>
        </div>
    </div>
    @stack('scripts')
</body>
</html>
