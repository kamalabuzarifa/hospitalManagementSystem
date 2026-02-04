@extends('layouts.reception')

@section('title', __('messages.thera') . ' - ' . __('messages.reception') . ' - ' . __('messages.settings') )

@section('content')
<main>
    <div class="container mx-auto px-4" style="padding-bottom: 16px;">
        <h1 class="text-3xl font-bold mb-8">{{ __('messages.settings') }}</h1>

        @if(session('success'))
            <div class="mb-4 p-4 rounded bg-green-100 text-green-800 border border-green-300">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <ul class="mb-4">
                @foreach ($errors->all() as $error)
                    <li class="text-red-500">{{ $error }}</li>
                @endforeach
            </ul>
        @endif

        <form method="POST" action="{{ route('reception.settings.update') }}" class="space-y-6">
            @csrf

            {{-- Appointments Management (UI only for now) --}}
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-black text-lg font-semibold mb-4">{{ __('messages.appointments_management') }}</h2>
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <label class="flex w-full items-center">
                            <input
                                type="checkbox"
                                name="allow_edit_appointments"
                                value="1"
                                class="form-checkbox h-5 w-5 text-blue-600"
                                {{ old('allow_edit_appointments', (bool)($settings->allow_edit_appointments ?? true)) ? 'checked' : '' }}
                            />
                            <span class="text-titlest ml-2">{{ __('messages.allow_edit_appointments_label') }}</span>
                        </label>
                    </div>

                    <div>
                        <label class="text-titlest block text-sm font-medium mb-1">{{ __('messages.default_appointment_duration_label') }}</label>
                        <input
                            type="number"
                            name="default_appointment_duration"
                            min="5"
                            max="240"
                            step="5"
                            value="{{ old('default_appointment_duration', $settings->default_appointment_duration ?? 20) }}"
                            class="w-full border rounded-lg px-4 py-2 bg-gray-100 focus:bg-white focus:ring-2 focus:ring-blue-200 focus:outline-none text-black"
                        />
                        <p class="text-xs text-gray-500 mt-2">{{ __('messages.default_appointment_duration_helper') }}</p>
                    </div>
                </div>
            </div>

            {{-- Patients Management (UI only for now) --}}
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-black text-lg font-semibold mb-4">{{ __('messages.patients_management') }}</h2>
                <div class="space-y-3">
                    <label class="flex w-full items-center">
                        <input
                            type="checkbox"
                            name="allow_edit_patients"
                            value="1"
                            class="form-checkbox h-5 w-5 text-blue-600"
                            {{ old('allow_edit_patients', (bool)($settings->allow_edit_patients ?? true)) ? 'checked' : '' }}
                        />
                        <span class="text-titlest ml-2">{{ __('messages.allow_edit_patients_label') }}</span>
                    </label>

                    <label class="flex w-full items-center">
                        <input
                            type="checkbox"
                            name="auto_assign_doctor"
                            value="1"
                            class="form-checkbox h-5 w-5 text-blue-600"
                            {{ old('auto_assign_doctor', (bool)($settings->auto_assign_doctor ?? false)) ? 'checked' : '' }}
                        />
                        <span class="text-titlest ml-2">{{ __('messages.auto_select_doctor_label') }}</span>
                    </label>
                </div>
            </div>

            {{-- Reception Notifications (saved to DB) --}}
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-black text-lg font-semibold mb-4">{{ __('messages.notifications') }}</h2>
                <div class="space-y-3">
                    <label class="flex w-full items-center">
                        <input
                            id="reception_dashboard_notifications"
                            type="checkbox"
                            name="reception_dashboard_notifications"
                            value="1"
                            class="form-checkbox h-5 w-5 text-blue-600"
                            {{ old('reception_dashboard_notifications', (bool)($settings->reception_dashboard_notifications ?? true)) ? 'checked' : '' }}
                        />
                        <span id="reception_dashboard_notifications_text" class="text-titlest ml-2"
                              data-hide-text="{{ __('messages.hide_notifications_from_dashboard') }}"
                              data-show-text="{{ __('messages.show_notifications_from_dashboard') }}">
                            {{ old('reception_dashboard_notifications', (bool)($settings->reception_dashboard_notifications ?? true))
                                ? __('messages.hide_notifications_from_dashboard')
                                : __('messages.show_notifications_from_dashboard') }}
                        </span>
                    </label>

                    <label class="flex w-full items-center">
                        <input
                            type="checkbox"
                            name="notification_new_appointment"
                            value="1"
                            class="form-checkbox h-5 w-5 text-blue-600"
                            {{ old('notification_new_appointment', (bool)($settings->notification_new_appointment ?? false)) ? 'checked' : '' }}
                        />
                        <span class="text-titlest ml-2">{{ __('messages.new_appointment') }}</span>
                    </label>

                    <label class="flex w-full items-center">
                        <input
                            type="checkbox"
                            name="notification_new_patient"
                            value="1"
                            class="form-checkbox h-5 w-5 text-blue-600"
                            {{ old('notification_new_patient', (bool)($settings->notification_new_patient ?? false)) ? 'checked' : '' }}
                        />
                        <span class="text-titlest ml-2">{{ __('messages.new_patient') }}</span>
                    </label>
                </div>
            </div>

            {{-- General Settings (language saved to DB) --}}
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-black text-xl font-semibold text-gray-900">{{ __('messages.general_settings') }}</h2>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="text-titlest block text-sm font-medium text-gray-700 mb-2">{{ __('messages.language') }}</label>
                        <select name="language" class="text-titlest w-full px-3 py-2 border border-gray-300 rounded-md">
                            <option class="text-titlest" value="ar" {{ old('language', app()->getLocale()) === 'ar' ? 'selected' : '' }}>{{ __('messages.arabic') }}</option>
                            <option class="text-titlest" value="en" {{ old('language', app()->getLocale()) === 'en' ? 'selected' : '' }}>{{ __('messages.english') }}</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded font-semibold hover:bg-blue-700">
                    {{ __('messages.save_settings') }}
                </button>
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

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const checkbox = document.getElementById('reception_dashboard_notifications');
        const text = document.getElementById('reception_dashboard_notifications_text');
        if (!checkbox || !text) return;

        const sync = () => {
            const hideText = text.getAttribute('data-hide-text') || '{{ __('messages.hide_notifications_from_dashboard') }}';
            const showText = text.getAttribute('data-show-text') || '{{ __('messages.show_notifications_from_dashboard') }}';
            text.textContent = checkbox.checked ? hideText : showText;
        };

        checkbox.addEventListener('change', sync);
        sync();
    });
    (function () {
        const key = 'thera_theme';
        const root = document.documentElement;
        const btn = document.getElementById('themeToggle');
        const apply = (v) => {
            if (v === 'dark') { root.classList.add('dark'); document.body.style.backgroundColor = '#0f172a'; }
            else { root.classList.remove('dark'); document.body.style.backgroundColor = ''; }
            const iconDark = btn?.querySelector('.material-symbols-outlined.block');
            const iconLight = btn?.querySelector('.material-symbols-outlined.hidden');
            if (iconDark && iconLight) {
                if (v === 'dark') { iconDark.style.display = 'none'; iconLight.style.display = 'block'; }
                else { iconDark.style.display = 'block'; iconLight.style.display = 'none'; }
            }
        };
        try {
            const stored = localStorage.getItem(key) || 'light';
            apply(stored);
        } catch (e) {}

        btn?.addEventListener('click', () => {
            const next = root.classList.contains('dark') ? 'light' : 'dark';
            try { localStorage.setItem(key, next); } catch (e) {}
            apply(next);
            // optional: notify server (route must exist)
            try {
                fetch("{{ route('admin.settings.toggle-theme') }}", {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
                    body: JSON.stringify({ value: next === 'dark' })
                }).catch(()=>{});
            } catch (e) {}
        });
    })();
</script>
@endsection
