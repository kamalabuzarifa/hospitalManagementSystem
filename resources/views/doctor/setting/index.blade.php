@extends('layouts.doctor')

@section('title', __('messages.thera') . ' - ' . __('messages.doctor') . ' - ' . __('messages.settings'))


@section('content')
    <main class="flex-1">
        <div class="container mx-auto px-4 " style="padding-bottom: 10px;">
            <h1 class="text-title text-3xl font-bold mb-8">{{ __('messages.settings') }}</h1>
            @if(session('success'))
                <div class="mb-4 p-4 rounded bg-green-100 text-green-800 border border-green-300">
                    {{ session('success') }}
                </div>
            @endif
            <ul>
                @foreach ($errors->all() as $error)
                    <li class="text-red-500">{{ $error }}</li>
                @endforeach
            </ul>
            <form method="POST" action="{{ route('doctor.settings.update') }}" class="space-y-8" enctype="multipart/form-data">
                @csrf
                {{-- Profile Information --}}
                <div class="bg-white rounded-lg shadow p-6 mb-6">
                    <h2 class="text-black text-lg font-semibold mb-4">{{ __('messages.profile_information') }}</h2>
                    <div class="space-y-4">
                        <div class="flex items-center gap-6 mb-4">
                            <div>
                                <img src="{{ $doctor->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($doctor->name ?? '') . '&background=0D8ABC&color=fff' }}" alt="Profile Avatar" class="w-20 h-20 rounded-full object-cover border">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.change_profile_picture') }}</label>
                                <input type="file" name="avatar" accept="image/*" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.name') }}</label>
                            <input type="text" name="name" value="{{ old('name', auth()->user()->name ?? '') }}" placeholder="{{ auth()->user()->name ?? $doctor->name ?? 'e.g. Dr. John Smith' }}" class="w-full border rounded-lg px-4 py-2 bg-gray-100 focus:bg-white focus:ring-2 focus:ring-blue-200 focus:outline-none text-black" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.specialization') }}</label>
                            <input type="text" name="specialization" value="{{ old('specialization', $doctor->specialization ?? '') }}" placeholder="{{ $doctor->specialization ?? 'e.g. Emergency Medicine' }}" class="w-full border rounded-lg px-4 py-2 bg-gray-100 focus:bg-white focus:ring-2 focus:ring-blue-200 focus:outline-none text-black" readonly />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.phone') }}</label>
                            <input type="text" name="phone" value="{{ old('phone', auth()->user()->phone ?? '') }}" placeholder="{{ auth()->user()->phone ?? 'e.g. 0591234567' }}" class="w-full border rounded-lg px-4 py-2 bg-gray-100 focus:bg-white focus:ring-2 focus:ring-blue-200 focus:outline-none text-black"  />
                        </div>
                    </div>
                </div>
                {{-- Working Hours --}}
                <div class="bg-white rounded-lg shadow p-6 mb-6">
                    <h2 class="text-black text-lg font-semibold mb-4">{{ __('messages.working_hours') }}</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.start_time') }}</label>
                            @php
                                $startTimeValue = old('start_time', !empty($doctor->start_time) ? substr((string) $doctor->start_time, 0, 5) : '');
                            @endphp
                            <input type="time" name="start_time" value="{{ $startTimeValue }}" placeholder="{{ $doctor->start_time ?? 'e.g. 08:00' }}" class="w-full border rounded-lg px-4 py-2 bg-gray-100 focus:bg-white focus:ring-2 focus:ring-blue-200 focus:outline-none text-black" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.end_time') }}</label>
                            @php
                                $endTimeValue = old('end_time', !empty($doctor->end_time) ? substr((string) $doctor->end_time, 0, 5) : '');
                            @endphp
                            <input type="time" name="end_time" value="{{ $endTimeValue }}" placeholder="{{ $doctor->end_time ?? 'e.g. 16:00' }}" class="w-full border rounded-lg px-4 py-2 bg-gray-100 focus:bg-white focus:ring-2 focus:ring-blue-200 focus:outline-none text-black" />
                        </div>
                    </div>
                </div>
                {{--  Preferences --}}
                <div class="bg-white rounded-lg shadow p-6 mb-6">
                    <h2 class="text-black text-lg font-semibold mb-4">{{ __('messages.notification_preferences') }}</h2>
                    <div class="space-y-2">
                        <div>
                                <label class="inline-flex items-center">
                                <input id="doctor_dashboard_notifications" type="checkbox" name="dashboard_notifications" value="1" {{ old('dashboard_notifications', $doctor->dashboard_notifications ?? true) ? 'checked' : '' }} class="form-checkbox h-5 w-5 text-blue-600" />
                                <span id="doctor_dashboard_notifications_text" class="ml-2 block text-sm font-medium text-gray-700">{{ __('messages.show_notifications_from_dashboard') }}</span>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- General Settings (language saved to DB) --}}
                <div class="bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-black text-xl font-semibold text-gray-900">General Settings</h2>
                    </div>
                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Language</label>
                            <select name="language" class="text-black w-full px-3 py-2 border border-gray-300 rounded-md">
                                <option class="text-black" value="en" {{ old('language', app()->getLocale()) === 'en' ? 'selected' : '' }}>English</option>
                                <option class="text-black" value="ar" {{ old('language', app()->getLocale()) === 'ar' ? 'selected' : '' }}>Arabic</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{--  button save changes --}}
                <div class="flex justify-end">
                    <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded font-semibold">{{ __('messages.save_settings') }}</button>
                </div>
            </form>
            <!-- Floating theme toggle (local only) -->
                <div style="position:relative; display:inline-block;z-index:100;">
                    <button
                        type="button"
                        id="themeToggle"
                        class="button-light fixed p-3 dark:bg-slate-800 shadow-lg rounded-full text-slate-600 dark:text-slate-300 inline-flex items-center justify-center"
                        style="position:relative;left:auto;bottom:43px!important;z-index:99999;width:48px;height:48px;border:1px solid rgba(0,0,0,0.06); background-color: #000000 ;color: #ffffff;"
                        aria-label="Toggle theme"
                    >
                        <span class="material-symbols-outlined block dark:hidden">dark_mode</span>
                        <span class="material-symbols-outlined hidden dark:block">light_mode</span>
                    </button>
                </div>
        </div>
    </main>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const avatarInput = document.querySelector('input[name="avatar"]');
            if (avatarInput) {
                avatarInput.addEventListener('change', function(e) {
                    const [file] = e.target.files;
                    if (file) {
                        const img = e.target.closest('.flex').querySelector('img');
                        img.src = URL.createObjectURL(file);
                    }
                });
            }

            const dashboardCheckbox = document.getElementById('doctor_dashboard_notifications');
            const dashboardText = document.getElementById('doctor_dashboard_notifications_text');
            if (dashboardCheckbox && dashboardText) {
                const hideTextDoc = {!! json_encode(__('messages.hide_notifications_from_dashboard')) !!};
                const showTextDoc = {!! json_encode(__('messages.show_notifications_from_dashboard')) !!};
                const syncDashboardText = () => {
                    dashboardText.textContent = dashboardCheckbox.checked ? hideTextDoc : showTextDoc;
                };

                dashboardCheckbox.addEventListener('change', syncDashboardText);
                syncDashboardText();
            }
        });
        (function () {
            const init = () => {
                const wire = (checkboxId, textId, onText, offText) => {
                    const checkbox = document.getElementById(checkboxId);
                    const text = document.getElementById(textId);
                    if (!checkbox || !text) return;

                    const sync = () => {
                        text.textContent = checkbox.checked ? onText : offText;
                    };

                    checkbox.addEventListener('change', sync);
                    sync();
                };

                wire(
                    'admin_dashboard_notifications',
                    'admin_dashboard_notifications_text',
                    {!! json_encode(__('messages.hide_notifications_from_dashboard')) !!},
                    {!! json_encode(__('messages.show_notifications_from_dashboard')) !!}
                );

                const themeKey = 'thera_theme';
                const toggleThemeUrl = "{{ route('admin.settings.toggle-theme') }}";
                const root = document.documentElement;
                const themeBtn = document.getElementById('themeToggle');
                const darkCheckbox = document.getElementById('dark_mode');
                const iconDark = themeBtn ? themeBtn.querySelector('.material-symbols-outlined.block') : null;
                const iconLight = themeBtn ? themeBtn.querySelector('.material-symbols-outlined.hidden') : null;
                const statusEl = document.getElementById('themeToggleStatus');
                const statusDarkText = "{{ __('messages.theme_dark') }}";
                const statusLightText = "{{ __('messages.theme_light') }}";

                const applyTheme = (value) => {
                    if (value === 'dark') {
                        root.classList.add('dark');
                        // subtle immediate visual cue in case Tailwind build not yet updated
                        document.body.style.backgroundColor = '#0f172a';
                    } else {
                        root.classList.remove('dark');
                        document.body.style.backgroundColor = '';
                    }
                    if (darkCheckbox) darkCheckbox.checked = (value === 'dark');
                    if (statusEl) statusEl.textContent = (value === 'dark') ? statusDarkText : statusLightText;
                    if (iconDark && iconLight) {
                        if (value === 'dark') {
                            iconDark.style.display = 'none';
                            iconLight.style.display = 'block';
                        } else {
                            iconDark.style.display = 'block';
                            iconLight.style.display = 'none';
                        }
                    }
                };

                const stored = localStorage.getItem(themeKey);
                const initial = stored || (darkCheckbox && darkCheckbox.checked ? 'dark' : 'light');
                applyTheme(initial);

                const postTheme = (val) => {
                    try {
                        fetch(toggleThemeUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({ value: val })
                        }).catch(() => {});
                    } catch (e) {}
                };

                if (themeBtn) {
                    // immediate press feedback
                    const addPress = () => themeBtn.style.transform = 'scale(0.95)';
                    const removePress = () => themeBtn.style.transform = '';
                    themeBtn.addEventListener('mousedown', addPress);
                    themeBtn.addEventListener('touchstart', addPress, {passive: true});
                    themeBtn.addEventListener('mouseup', removePress);
                    themeBtn.addEventListener('mouseleave', removePress);
                    themeBtn.addEventListener('touchend', removePress);

                    themeBtn.addEventListener('click', () => {
                        const next = root.classList.contains('dark') ? 'light' : 'dark';
                        localStorage.setItem(themeKey, next);
                        applyTheme(next);
                        postTheme(next === 'dark');
                    });
                }

                if (darkCheckbox) {
                    darkCheckbox.addEventListener('change', () => {
                        const next = darkCheckbox.checked ? 'dark' : 'light';
                        localStorage.setItem(themeKey, next);
                        applyTheme(next);
                        postTheme(next === 'dark');
                    });
                }
            };

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', init);
            } else {
                init();
            }
        })();
    </script>
@endpush
