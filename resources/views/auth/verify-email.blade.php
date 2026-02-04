@php
    $locale = app()->getLocale();
    $isArabic = $locale === 'ar';
@endphp

<!DOCTYPE html>
<html lang="{{ $isArabic ? 'ar' : 'en' }}" dir="{{ $isArabic ? 'rtl' : 'ltr' }}" class="h-full">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ __('messages.thera') }}</title>

    <script src="https://cdn.tailwindcss.com?plugins=forms,typography,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />

    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: '#2b66f1',
                    },
                    fontFamily: {
                        display: ['Inter', 'sans-serif'],
                    },
                },
            },
        };
    </script>

    <style type="text/tailwindcss">
        .medical-bg {
            background:
                linear-gradient(rgba(235, 244, 255, 0.86), rgba(235, 244, 255, 0.86)),
                url('{{ asset('images/background-logo.png') }}');
            background-size: cover;
            background-position: center;
        }
        .dark .medical-bg {
            background:
                linear-gradient(rgba(15, 23, 42, 0.9), rgba(15, 23, 42, 0.9)),
                url('{{ asset('images/background-logo.png') }}');
            background-size: cover;
            background-position: center;
        }
        input:focus {
            outline: none;
            border-color: #2b66f1;
            box-shadow: 0 0 0 2px rgba(43, 102, 241, 0.2);
        }
        body {
            min-height: 100dvh;
        }
    </style>
</head>

<body class="font-display flex items-center justify-center p-4"
    style="background:url('{{ asset('images/background-logo.png') }}'); background-size: cover; background-position: center;">
    <div class="w-full max-w-md bg-white dark:bg-slate-900 rounded-[2rem] shadow-2xl p-8 md:p-10 transition-colors duration-300">
        <div class="flex flex-col items-center mb-8">
            <div class="relative flex items-center mb-2">
                <div class="flex items-center">
                    <div class="relative w-12 h-12">
                        <img src="{{ asset('images/hospital-logo.png') }}" alt="Thera Logo" class="w-12 h-12" />
                    </div>
                    <span class="text-3xl font-bold text-primary {{ $isArabic ? 'mr-2' : 'ml-2' }} tracking-tight">{{ __('messages.thera') }}</span>
                    <form id="langForm" method="GET" action="/lang/{{ $isArabic ? 'en' : 'ar' }}">
                        <button
                            type="submit"
                            id="langToggle"
                            class="fixed bottom-6 right-24 p-3 bg-white dark:bg-slate-800 shadow-lg rounded-full text-slate-600 dark:text-slate-300 z-50 font-bold text-base"
                            aria-label="Toggle language"
                        >
                            <span>{{ $isArabic ? 'EN' : 'AR' }}</span>
                        </button>
                    </form>
                </div>
            </div>
            <p class="text-slate-500 dark:text-slate-400 text-sm font-medium border-b border-slate-200 dark:border-slate-700 pb-1 mt-2">
                {{ __('messages.subtitle') }}
            </p>
        </div>

        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white mb-2">{{ __('messages.verify_email_notice') }}</h1>
            @if (session('status') == 'verification-link-sent')
                <div class="mx-auto mb-4 w-16 h-16 rounded-full bg-green-50 dark:bg-green-900/30 flex items-center justify-center">
                    <span class="material-symbols-outlined text-green-600 dark:text-green-400 text-3xl">check_circle</span>
                </div>
                <p class="text-green-600 dark:text-green-400 text-sm font-semibold mb-2">{{ __('messages.verification_link_sent') }}</p>
            @endif
        </div>

        <div class="flex flex-col gap-4">
            <form method="POST" action="{{ route('verification.send') }}" class="w-full">
                @csrf
                <button type="submit" class="w-full bg-primary hover:bg-blue-700 text-white font-bold py-3.5 px-4 rounded-xl shadow-lg shadow-blue-500/30 transform active:scale-[0.98] transition-all duration-200 flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-base">mail</span>
                    <span>{{ __('messages.resend_verification_email') }}</span>
                </button>
            </form>
            <form method="POST" action="{{ route('logout') }}" class="w-full">
                @csrf
                <button type="submit" class="w-full bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-200 font-bold py-3.5 px-4 rounded-xl shadow transition-colors duration-200 flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-base">logout</span>
                    <span>{{ __('messages.logout') }}</span>
                </button>
            </form>
        </div>

        <div class="mt-12 text-center">
            <p class="text-xs text-slate-400 dark:text-slate-500 flex items-center justify-center gap-1">
                <span>{{ __('messages.footer_left') }}</span>
                <span class="w-1 h-1 bg-slate-300 dark:bg-slate-600 rounded-full"></span>
                <span>{{ __('messages.footer_right') }}</span>
            </p>
        </div>
    </div>

    <button
        type="button"
        id="themeToggle"
        class="fixed bottom-6 right-6 p-3 bg-white dark:bg-slate-800 shadow-lg rounded-full text-slate-600 dark:text-slate-300"
        aria-label="Toggle theme"
    >
        <span class="material-symbols-outlined block dark:hidden">dark_mode</span>
        <span class="material-symbols-outlined hidden dark:block">light_mode</span>
    </button>

    <script>
        (function () {
            const themeKey = 'thera_theme';
            const root = document.documentElement;
            const themeBtn = document.getElementById('themeToggle');

            const applyTheme = (value) => {
                if (value === 'dark') {
                    root.classList.add('dark');
                } else {
                    root.classList.remove('dark');
                }
            };

            applyTheme(localStorage.getItem(themeKey) || 'light');

            if (themeBtn) {
                themeBtn.addEventListener('click', () => {
                    const next = root.classList.contains('dark') ? 'light' : 'dark';
                    localStorage.setItem(themeKey, next);
                    applyTheme(next);
                });
            }
        })();
    </script>
</body>
</html>
