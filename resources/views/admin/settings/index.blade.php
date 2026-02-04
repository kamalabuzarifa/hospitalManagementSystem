@extends('layouts.admin')

@section('title',__('messages.thera') . ' - ' . __('messages.administration') . ' - ' . __('messages.system_settings') )

@section('content')
    <main>
        <div class="py-8 px-4 sm:px-6 md:px-8">
            <div class="max-w-7xl mx-auto">
                <style>
                    @media (max-width: 900px) {
                        .text-3xl { font-size: 1.5rem !important; }
                        .text-xl { font-size: 1.1rem !important; }
                        .p-6 { padding: 1rem !important; }
                        .mb-8 { margin-bottom: 1.5rem !important; }
                    }
                    @media (max-width: 600px) {
                        .py-8 { padding-top: 1.2rem !important; padding-bottom: 1.2rem !important; }
                        .px-4, .sm\:px-6, .md\:px-8 { padding-left: 0.5rem !important; padding-right: 0.5rem !important; }
                        .gap-6 { gap: 1rem !important; }
                        .space-y-4 > :not([hidden]) ~ :not([hidden]) { margin-top: 1.2rem !important; }
                        .overflow-x-auto { overflow-x: auto !important; }
                        table { font-size: 0.95rem !important; }
                    }
                </style>
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-title text-3xl font-bold text-gray-900">{{ __('messages.system_settings') }}</h1>
                    <p class="text-titlesticky text-gray-600 mt-2">{{ __('messages.manage_system') }}</p>
                </div>
                @if(session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                        {{ session('success') }}
                    </div>
                @endif
                <form action="{{ route('admin.settings.update') }}" method="POST">
                    @csrf
                    <!-- Notifications -->
                    <div class="bg-white rounded-lg shadow mb-6">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-xl font-semibold text-gray-900">{{ __('messages.notifications') }}</h2>
                        </div>
                        <div class="p-6 space-y-3">
                            @php
                                $dashboardNotificationsEnabled = (bool) (($settings->reception_dashboard_notifications ?? true) && ($settings->pharmacy_dashboard_notifications ?? true));
                            @endphp
                            <label class="flex items-center">
                                <input id="admin_dashboard_notifications" type="checkbox" name="dashboard_notifications" value="1" {{ $dashboardNotificationsEnabled ? 'checked' : '' }} class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                <span id="admin_dashboard_notifications_text" class="text-titlest inline-gap-start text-sm text-gray-700">
                                    {{ $dashboardNotificationsEnabled
                                        ? 'Hide notifications from the dashboard page'
                                        : 'Show notifications from the dashboard page' }}
                                </span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="notification_new_appointment" value="1" {{ $settings->notification_new_appointment ? 'checked' : '' }} class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                <span class="text-titlest inline-gap-start text-sm text-gray-700">{{ __('messages.new_appointment') }}</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="notification_new_patient" value="1" {{ $settings->notification_new_patient ? 'checked' : '' }} class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                <span class="text-titlest inline-gap-start text-sm text-gray-700">{{ __('messages.new_patient') }}</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="dashboard_recent_appointments" value="1" {{ ($settings->dashboard_recent_appointments ?? true) ? 'checked' : '' }} class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                <span class="text-titlest inline-gap-start text-sm text-gray-700">{{ __('messages.recent_appointments') }}</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="dashboard_recent_patient" value="1" {{ ($settings->dashboard_recent_patient ?? true) ? 'checked' : '' }} class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                <span class="text-titlest inline-gap-start text-sm text-gray-700">{{ __('messages.recent_patient') }}</span>
                            </label>
                        </div>
                    </div>
                    <!-- Backup & Security -->
                    <div class="bg-white rounded-lg shadow mb-6">
                        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                            <h2 class="text-xl font-semibold text-gray-900">{{ __('messages.backup_security') }}</h2>
                        </div>
                        <div class="p-6 space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.backup_frequency') }}</label>
                                <select name="backup_frequency" class="text-titlest w-full px-3 py-2 border border-gray-300 rounded-md bg-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option class="text-titlest" value="daily" {{ $settings->backup_frequency === 'daily' ? 'selected' : '' }}>{{ __('messages.daily') }}</option>
                                    <option class="text-titlest" value="weekly" {{ $settings->backup_frequency === 'weekly' ? 'selected' : '' }}>{{ __('messages.weekly') }}</option>
                                    <option class="text-titlest" value="monthly" {{ $settings->backup_frequency === 'monthly' ? 'selected' : '' }}>{{ __('messages.monthly') }}</option>
                                </select>
                                @error('backup_frequency')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.backup_destination') }}</label>
                                <input type="text" name="backup_destination" value="{{ $settings->backup_destination }}" class="text-titlest w-full px-3 py-2 border border-gray-300 rounded-md bg-white focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="{{ __('messages.enter_backup_path') }}">
                                @error('backup_destination')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </div>
                    @if(session('backup_output'))
                        <div class="bg-gray-100 border border-gray-400 text-gray-800 px-4 py-3 rounded mb-6">
                            <pre class="responsive-pre text-titlest">{{ __('messages.backup_success') }}</pre>
                            <style>
                                .responsive-pre {
                                    background: #f3f4f6;
                                    padding: 10px;
                                    border-radius: 6px;
                                    font-size: 1rem;
                                    white-space: pre-wrap;
                                    word-break: break-word;
                                }
                                @media (max-width: 600px) {
                                    .responsive-pre {
                                        font-size: 0.92rem;
                                        padding: 6px 4px;
                                    }
                                }
                            </style>
                        </div>
                    @endif
                    <!-- Admin Roles & Permissions -->
                    <div class="bg-white rounded-lg shadow mb-6">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-xl font-semibold text-gray-900">{{ __('messages.admin_roles') }}</h2>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-50 border-b border-gray-200">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">{{ __('messages.role') }}</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">{{ __('messages.permissions') }}</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">{{ __('messages.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @php
                                        $editRole = request('edit_role');
                                    @endphp
                                    @foreach($roles as $role)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">{{ $role['label'] }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if($editRole === $role['key'])
                                                    <form action="{{ route('admin.settings.updateRolePermission') }}" method="POST" class="flex items-center space-x-2">
                                                        @csrf
                                                        <input type="hidden" name="role" value="{{ $role['key'] }}">
                                                        <select name="permission" class="px-2 py-1 border border-gray-300 rounded-md">
                                                            @php($level = ($role['permission_level'] ?? 'limited_access'))
                                                            @php($level = $level === 'read_only' ? 'limited_access' : $level)
                                                            <option value="full_access" {{ $level === 'full_access' ? 'selected' : '' }}>{{ __('messages.full_access') }}</option>
                                                            <option value="limited_access" {{ $level === 'limited_access' ? 'selected' : '' }}>{{ __('messages.limited_access') }}</option>
                                                        </select>
                                                        <button type="submit" class="ml-2 px-3 py-1 text-sm text-white bg-blue-600 rounded hover:bg-blue-700">{{ __('messages.save') ?? 'Save' }}</button>
                                                        <a href="{{ route('admin.settings.index') }}" class="ml-2 px-3 py-1 text-sm text-gray-600 hover:underline">{{ __('messages.cancel') ?? 'Cancel' }}</a>
                                                    </form>
                                                @else
                                                    <span class="text-sm text-blue-600">{{ $role['permission_label'] }}</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <a href="{{ route('admin.settings.index', ['edit_role' => $role['key']]) }}" class="px-3 py-1 text-sm text-blue-600 hover:underline">{{ __('messages.edit') }}</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!-- System Policies -->
                    <div class="bg-white rounded-lg shadow mb-6">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-xl font-semibold text-gray-900">{{ __('messages.system_policies') }}</h2>
                        </div>
                        <div class="p-6 space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.language') }}</label>
                                <select name="language" class="text-titlest w-full px-3 py-2 border border-gray-300 rounded-md">
                                    <option class="text-titlest" value="en" {{ old('language', app()->getLocale()) === 'en' ? 'selected' : '' }}>{{ __('messages.english') }}</option>
                                    <option class="text-titlest" value="ar" {{ old('language', app()->getLocale()) === 'ar' ? 'selected' : '' }}>{{ __('messages.arabic') }}</option>
                                </select>
                                @error('language')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.time_format') }}</label>
                                <select name="time_format" class="text-titlest w-full px-3 py-2 border border-gray-300 rounded-md">
                                    <option class="text-titlest" value="12" {{ $settings->time_format === '12' ? 'selected' : '' }}>{{ __('messages.hour_12') }}</option>
                                    <option class="text-titlest" value="24" {{ $settings->time_format === '24' ? 'selected' : '' }}>{{ __('messages.hour_24') }}</option>
                                </select>
                                @error('time_format')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.date_format') }}</label>
                                <select name="date_format" class="text-titlest w-full px-3 py-2 border border-gray-300 rounded-md">
                                    <option class="text-titlest" value="DD/MM/YYYY" {{ $settings->date_format === 'DD/MM/YYYY' ? 'selected' : '' }}>DD/MM/YYYY</option>
                                    <option class="text-titlest" value="YYYY-MM-DD" {{ $settings->date_format === 'YYYY-MM-DD' ? 'selected' : '' }}>YYYY-MM-DD</option>
                                </select>
                                @error('date_format')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div style="display: none">
                                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.dark_mode') }}</label>
                                <label class="flex items-center">
                                    <input id="dark_mode" type="checkbox" name="dark_mode" value="1" {{ ($settings->dark_mode ?? false) ? 'checked' : '' }} class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                    <span class="inline-gap-start text-sm text-gray-700">{{ __('messages.enable_dark_mode') }}</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <!-- Save Button -->
                    <div class="flex justify-end">
                        <button type="submit" class="px-6 py-3 bg-blue-600 text-white rounded-md hover:bg-blue-700 font-semibold">
                            {{ __('messages.save_settings') }}
                        </button>
                    </div>
                </form>
            </div>
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
                    'Hide notifications from the dashboard page',
                    'Show notifications from the dashboard page'
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
