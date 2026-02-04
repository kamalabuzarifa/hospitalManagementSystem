
@extends('layouts.pharmacy')
@section('title',__('messages.thera') . ' - ' . __('messages.pharmacy') . ' - ' . __('messages.settings'))


@section('content')
<main class="flex-1">
    <div class="container mx-auto px-4" style="padding-bottom: 10px;">
        <h1 class="text-title text-3xl font-extrabold text-gray-800 mb-8 tracking-tight">{{ __('messages.settings') }}</h1>
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
        <form method="POST" action="{{ route('pharmacy.settings.update') }}" class="space-y-8">
            @csrf
            
            {{-- Notifications --}}
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-black text-xl font-semibold text-gray-900">{{ __('messages.notifications') }}</h2>
                </div>
                <div class="p-6 space-y-4">
                    {{-- Pending Prescriptions --}}
                    <div>
                        <label for="notify_pending_prescriptions" class="flex w-full items-center">
                            <input type="hidden" name="notify_pending_prescriptions" value="0">
                            <input
                                id="notify_pending_prescriptions"
                                type="checkbox"
                                name="notify_pending_prescriptions"
                                value="1"
                                class="form-checkbox h-5 w-5 text-blue-600"
                                {{ old('notify_pending_prescriptions', (bool)($settings->notify_pending_prescriptions ?? true)) ? 'checked' : '' }}
                            />
                            <span class="ml-2 text-titlest">{{ __('messages.notify_me_about_pending_prescriptions') }}</span>
                        </label>
                    </div>
                    
                    {{--Dispensed Prescriptions--}}
                    <div>
                        <label for="notify_dispensed_prescriptions" class="flex w-full items-center">
                            <input type="hidden" name="notify_dispensed_prescriptions" value="0">
                            <input
                                id="notify_dispensed_prescriptions"
                                type="checkbox"
                                name="notify_dispensed_prescriptions"
                                value="1"
                                class="form-checkbox h-5 w-5 text-blue-600"
                                {{ old('notify_dispensed_prescriptions', (bool)($settings->notify_dispensed_prescriptions ?? true)) ? 'checked' : '' }}
                            />
                            <span class="ml-2 text-titlest">{{ __('messages.notify_me_about_dispensed_prescriptions') }}</span>
                        </label>
                    </div>

                    {{-- Inventory Status --}}
                    <div>
                        <label for="notify_inventory_status" class="flex w-full items-center">
                            <input type="hidden" name="notify_inventory_status" value="0">
                            <input
                                id="notify_inventory_status"
                                type="checkbox"
                                name="notify_inventory_status"
                                value="1"
                                class="form-checkbox h-5 w-5 text-blue-600"
                                {{ old('notify_inventory_status', (bool)($settings->notify_inventory_status ?? true)) ? 'checked' : '' }}
                            />
                            <span class="ml-2 text-titlest">{{ __('messages.notify_me_about_inventory_status_changes') }}</span>
                        </label>
                    </div>

                    {{-- Notifications --}}
                    <div>
                        <label for="pharmacy_dashboard_notifications" class="flex w-full items-center">
                            <input type="hidden" name="pharmacy_dashboard_notifications" value="0">
                            <input
                                id="pharmacy_dashboard_notifications"
                                type="checkbox"
                                name="pharmacy_dashboard_notifications"
                                value="1"
                                class="form-checkbox h-5 w-5 text-blue-600"
                                {{ old('pharmacy_dashboard_notifications', (bool)($settings->pharmacy_dashboard_notifications ?? true)) ? 'checked' : '' }}
                            />
                            <span id="pharmacy_dashboard_notifications_text" class="ml-2 text-titlest">
                                {{ old('pharmacy_dashboard_notifications', (bool)($settings->pharmacy_dashboard_notifications ?? true))
                                    ? 'Hide notifications from the dashboard page'
                                    : 'Show notifications from the dashboard page' }}
                            </span>
                        </label>
                    </div>
                </div>
            </div>
                        
            {{-- Stock Alerts --}}
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-black text-xl font-semibold text-gray-900">{{ __('messages.stock_alerts') }}</h2>
                </div>
                {{-- Expired --}}
                <div class="p-6 space-y-4">
                    <div>
                        <label for="notify_expired_medicines" class="flex w-full items-center">
                            <input type="hidden" name="notify_expired_medicines" value="0">
                            <input
                                id="notify_expired_medicines"
                                type="checkbox"
                                name="notify_expired_medicines"
                                value="1"
                                class="form-checkbox h-5 w-5 text-blue-600"
                                {{ old('notify_expired_medicines', (bool)($settings->notify_expired_medicines ?? true)) ? 'checked' : '' }}
                            />
                            <span class="ml-2 text-titlest">{{ __('messages.notify_me_about_expired_medicines') }}</span>
                        </label>
                    </div>
                    {{-- Low Stock --}}
                    <div>
                        <label for="notify_low_stock_medicines" class="flex w-full items-center">
                            <input type="hidden" name="notify_low_stock_medicines" value="0">
                            <input
                                id="notify_low_stock_medicines"
                                type="checkbox"
                                name="notify_low_stock_medicines"
                                value="1"
                                class="form-checkbox h-5 w-5 text-blue-600"
                                {{ old('notify_low_stock_medicines', (bool)($settings->notify_low_stock_medicines ?? true)) ? 'checked' : '' }}
                            />
                            <span class="ml-2 text-titlest">{{ __('messages.notify_me_about_low_stock_medicines') }}</span>
                        </label>
                    </div>
                </div>
                {{-- Low Stock Threshold --}}
                <div class="px-6 pb-6">
                    <label for="low_stock_threshold" class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.low_stock_threshold') }}</label>
                    <input
                        id="low_stock_threshold"
                        type="number"
                        name="low_stock_threshold"
                        min="1"
                        value="{{ old('low_stock_threshold', $settings->low_stock_threshold ?? 10) }}"
                        class="text-titlest w-32 px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                    <p class="mt-2 text-sm text-gray-500">{{ __('messages.low_stock_threshold_help_text') }}</p>
                </div>
            </div>
            
            {{-- System Policies --}}
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-black text-xl font-semibold text-gray-900">{{ __('messages.system_policies') }}</h2>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.language') }}</label>
                        <select name="language" class="text-titlest w-full px-3 py-2 border border-gray-300 rounded-md">
                            <option class="text-titlest" value="ar" {{ old('language', app()->getLocale()) === 'ar' ? 'selected' : '' }}>{{ __('messages.arabic') }}</option>
                            <option class="text-titlest" value="en" {{ old('language', app()->getLocale()) === 'en' ? 'selected' : '' }}>{{ __('messages.english') }}</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 transition text-white px-8 py-2 rounded-lg font-semibold shadow">{{ __('messages.save_settings') }}</button>
            </div>
        </form>
        <!-- Floating theme toggle (local only) -->
        <div style="position:relative; display:inline-block;z-index:100;">
            <button type="button" id="themeToggle" class="button-light fixed p-3 dark:bg-slate-800 shadow-lg rounded-full text-slate-600 dark:text-slate-300 inline-flex items-center justify-center" style="position:relative;left:auto;bottom:43px!important;z-index:99999;width:48px;height:48px;border:1px solid rgba(0,0,0,0.06); background-color: #000000 ;color: #ffffff;" aria-label="Toggle theme">
                <span class="material-symbols-outlined block dark:hidden">dark_mode</span>
                <span class="material-symbols-outlined hidden dark:block">light_mode</span>
            </button>
        </div>
    </div>        
</main>

<script>
        document.addEventListener('DOMContentLoaded', function () {
            const checkbox = document.getElementById('pharmacy_dashboard_notifications');
            const text = document.getElementById('pharmacy_dashboard_notifications_text');
            if (!checkbox || !text) return;

            const hideText = {!! json_encode(__('messages.hide_notifications_from_dashboard')) !!};
            const showText = {!! json_encode(__('messages.show_notifications_from_dashboard')) !!};

            const sync = () => {
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
