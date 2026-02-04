@extends('layouts.admin')

@section('title',__('messages.thera') . ' - ' . __('messages.administration') . ' - ' . __('messages.access_logs') )

@section('content')
    <main>
        <div class="py-8 px-4 sm:px-6 md:px-8">
            <div class="max-w-7xl mx-auto space-y-6">
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
                            .space-y-6 > :not([hidden]) ~ :not([hidden]) { margin-top: 1.2rem !important; }
                            .overflow-x-auto { overflow-x: auto !important; }
                            table { font-size: 0.95rem !important; }
                        }
                    </style>
                <div>
                    <h1 class="text-title text-3xl font-bold text-gray-900">{{ __('messages.access_logs') }}</h1>
                    <p class="text-titlesticky text-gray-600 mt-2">{{ __('messages.track_user_activity') }}</p>
                </div>

                <div class="bg-white rounded-lg shadow p-4 border border-gray-100 space-y-3">
                    <form method="GET" action="{{ route('admin.access-logs.index') }}" class="flex flex-row flex-wrap items-end lg:justify-between gap-4" style="{{ app()->getLocale() === 'ar' ? 'flex-direction: row-reverse;' : '' }}">
                        <div class="flex-1">
                            <label class="sr-only" for="search">{{ __('messages.search') }}</label>
                            <div class="relative">
                                <span class="text-titlest pointer-events-none absolute inset-y-0 left-3 flex items-center text-gray-400" style="{{ app()->getLocale() === 'ar' ? 'left: auto; right: 0.75rem;' : '' }}">
                                    <svg class="text-titlest w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 104.5 4.5a7.5 7.5 0 0012.15 12.15z" />
                                    </svg>
                                </span>
                                <input id="search" name="search" value="{{ $filters['search'] ?? '' }}" type="text" placeholder="{{ __('messages.search_logs') }}" class="text-titlest w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md bg-gray-50 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-transparent" style="{{ app()->getLocale() === 'ar' ? 'padding-left: 1rem; padding-right: 2.5rem;' : '' }}" />
                            </div>
                        </div>
                        <div class="flex flex-wrap items-center gap-3">
                            <div>
                                <label class="text-titlest sr-only text-titlest" for="date_from">{{ __('messages.date_from') }}</label>
                                <input class="text-titlest px-3 py-2 border border-gray-300 rounded-md text-sm" type="date" id="date_from" name="date_from" value="{{ $filters['date_from'] ?? '' }}" />
                            </div>
                            <div>
                                <label class="sr-only text-titlest" for="date_to">{{ __('messages.date_to') }}</label>
                                <input class="text-titlest px-3 py-2 border border-gray-300 rounded-md text-sm" type="date" id="date_to" name="date_to" value="{{ $filters['date_to'] ?? '' }}" />
                            </div>
                            <input class="text-titlest" type="hidden" name="tz_offset" value="{{ $tzOffset ?? 0 }}">
                            <div>
                                <label class="sr-only text-titlest" for="role">{{ __('messages.role') }}</label>
                                <select id="role" name="role" class="text-titlest px-3 py-2 border border-gray-300 rounded-md text-sm bg-white">
                                    <option class="text-titlest" value="">{{ __('messages.all_roles') }}</option>
                                    @foreach($roles ?? [] as $role)
                                        <option class="text-titlest" value="{{ $role->name }}" @selected(($filters['role'] ?? '') === $role->name)>{{ ucfirst($role->name) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="px-4 py-2 bg-gray-900 text-white text-sm font-semibold rounded-md hover:bg-gray-800 transition">{{ __('messages.apply') }}</button>
                            <a href="{{ route('admin.access-logs.index') }}" class="px-4 py-2 border border-gray-300 text-sm font-semibold text-gray-700 rounded-md hover:bg-gray-50 transition">{{ __('messages.reset') }}</a>
                            <a id="download-logs-link" href="{{ route('admin.access-logs.download', array_filter([
                                'search' => $filters['search'] ?? null,
                                'role' => $filters['role'] ?? null,
                                'date_from' => $filters['date_from'] ?? null,
                                'date_to' => $filters['date_to'] ?? null,
                                'tz_offset' => $tzOffset ?? 0,
                                'locale' => app()->getLocale(),
                            ])) }}" class="px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-md hover:bg-blue-700 transition">{{ __('messages.download_logs') }}</a>
                        </div>
                    </form>
                </div>

                <div class="bg-white rounded-lg shadow border border-gray-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">{{ __('messages.user') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">{{ __('messages.role') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">{{ __('messages.time') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">{{ __('messages.action_performed') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse($logs ?? [] as $log)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 text-sm text-gray-900">{{ $log->user ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 text-sm text-blue-600 font-semibold">{{ $log->role ?? 'N/A' }}</td>
                                        @php
                                            // local time = UTC time - offsetMinutes (browser offset: minutes to add to local to reach UTC)
                                            $offset = $tzOffset ?? 0;
                                            $localTime = $log->time ? \Carbon\Carbon::parse($log->time)->utc()->subMinutes($offset)->format('Y-m-d H:i') : '';
                                        @endphp
                                        <td class="px-6 py-4 text-sm text-gray-900">{{ $localTime ? $localTime . ' (' . ($tzLabel ?? 'UTC') . ')' : '' }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-900">
                                            @php
                                                $key = 'messages.action_' . $log->action;
                                                $translated = __($key);
                                                if ($translated === $key) {
                                                    $translated = ucfirst(str_replace('_', ' ', $log->action));
                                                }
                                                $details = '';
                                                if ($log->meta) {
                                                    $meta = json_decode($log->meta, true);
                                                    if (is_array($meta)) {
                                                        if ($log->action === 'updated_doctor_settings' && isset($meta['changes']) && is_array($meta['changes'])) {
                                                            $changes = $meta['changes'];
                                                            $lines = [];
                                                            $fieldLabels = [
                                                                'name' => ['en' => 'Name', 'ar' => 'الاسم'],
                                                                'specialization' => ['en' => 'Specialization', 'ar' => 'التخصص'],
                                                                'contact_number' => ['en' => 'Contact Number', 'ar' => 'رقم التواصل'],
                                                                'start_time' => ['en' => 'Start time', 'ar' => 'ساعة البدء'],
                                                                'end_time' => ['en' => 'End time', 'ar' => 'ساعة الانتهاء'],
                                                                'email_notifications' => ['en' => 'Email notifications', 'ar' => 'إشعارات البريد الإلكتروني'],
                                                                'sms_notifications' => ['en' => 'SMS notifications', 'ar' => 'إشعارات الرسائل النصية'],
                                                                'avatar' => ['en' => 'Profile picture', 'ar' => 'الصورة الشخصية'],
                                                            ];
                                                            $locale = app()->getLocale();
                                                            foreach ($changes as $field => $change) {
                                                                $old = $change['old'] ?? '';
                                                                $new = $change['new'] ?? '';
                                                                // تحويل القيم المنطقية إلى Yes/No/نعم/لا
                                                                if (in_array($field, ['email_notifications', 'sms_notifications'])) {
                                                                    if ($locale === 'ar') {
                                                                        $old = $old === 1 || $old === true || $old === '1' ? 'نعم' : ($old === 0 || $old === false || $old === '0' ? 'لا' : $old);
                                                                        $new = $new === 1 || $new === true || $new === '1' ? 'نعم' : ($new === 0 || $new === false || $new === '0' ? 'لا' : $new);
                                                                    } else {
                                                                        $old = $old === 1 || $old === true || $old === '1' ? 'Yes' : ($old === 0 || $old === false || $old === '0' ? 'No' : $old);
                                                                        $new = $new === 1 || $new === true || $new === '1' ? 'Yes' : ($new === 0 || $new === false || $new === '0' ? 'No' : $new);
                                                                    }
                                                                }
                                                                $label = $fieldLabels[$field][$locale] ?? ucfirst(str_replace('_', ' ', $field));
                                                                // عرض الصورة الشخصية كصورة مصغرة
                                                                if ($field === 'avatar') {
                                                                    $oldImg = $old ? '<img src="' . e($old) . '" alt="old avatar" style="max-width:40px;max-height:40px;border-radius:6px;border:1px solid #ccc;margin:0 4px;vertical-align:middle">' : '-';
                                                                    $newImg = $new ? '<img src="' . e($new) . '" alt="new avatar" style="max-width:40px;max-height:40px;border-radius:6px;border:1px solid #ccc;margin:0 4px;vertical-align:middle">' : '-';
                                                                    if ($locale === 'ar') {
                                                                        $lines[] = 'تم تغيير <b>' . e($label) . '</b> من ' . $oldImg . ' إلى ' . $newImg;
                                                                    } else {
                                                                        $lines[] = 'Changed <b>' . e($label) . '</b> from ' . $oldImg . ' to ' . $newImg;
                                                                    }
                                                                } else {
                                                                    if ($locale === 'ar') {
                                                                        $lines[] = 'تم تغيير <b>' . e($label) . '</b> من <span style="color:#b91c1c">' . (e($old) === '' ? '-' : e($old)) . '</span> إلى <span style="color:#166534">' . (e($new) === '' ? '-' : e($new)) . '</span>';
                                                                    } else {
                                                                        $lines[] = 'Changed <b>' . e($label) . '</b> from <span style="color:#b91c1c">' . (e($old) === '' ? '-' : e($old)) . '</span> to <span style="color:#166534">' . (e($new) === '' ? '-' : e($new)) . '</span>';
                                                                    }
                                                                }
                                                            }
                                                            $details = '<ul style="margin:0;padding-left:18px;list-style-type:disc">';
                                                            foreach ($lines as $line) {
                                                                $details .= '<li style="margin-bottom:2px">' . $line . '</li>';
                                                            }
                                                            $details .= '</ul>';
                                                        } else if ($log->action === 'changed_language') {
                                                            $toLang = $meta['to_language'] ?? null;
                                                            if ($toLang) {
                                                                if ($toLang === 'ar') {
                                                                    $langName = app()->getLocale() === 'ar' ? 'العربية' : 'Arabic';
                                                                } else {
                                                                    $langName = app()->getLocale() === 'ar' ? 'الإنجليزية' : 'English';
                                                                }
                                                                $translated .= ' ' . $langName;
                                                            }
                                                        } elseif (in_array($log->action, ['changed_time_format', 'changed_date_format', 'changed_backup_frequency'])) {
                                                            $value = array_values($meta)[0] ?? null;
                                                            if ($value) {
                                                                $translated .= ' ' . $value;
                                                            }
                                                        } elseif ($log->action === 'toggled_notification') {
                                                            $notification = $meta['notification'] ?? null;
                                                            $status = $meta['status'] ?? null;
                                                            if ($notification && $status) {
                                                                $translated .= ' - ' . $notification . ' (' . $status . ')';
                                                            }
                                                        }
                                                    }
                                                }
                                            @endphp
                                            {!! $translated !!}{!! $details !!}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-4 text-sm text-gray-500 text-center">{{ __('messages.no_access_logs') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if(isset($logs) && method_exists($logs, 'links'))
                        <div class="div-all-accessLogs p-4 border-t border-gray-100">
                            {{ $logs->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </main>
@endsection

@push('scripts')
    <script>
        (function() {
            const offset = new Date().getTimezoneOffset(); // minutes from UTC
            const tzInput = document.querySelector('input[name="tz_offset"]');
            const downloadLink = document.getElementById('download-logs-link');
            if (tzInput) {
                tzInput.value = offset;
            }
            if (downloadLink) {
                try {
                    const url = new URL(downloadLink.href);
                    url.searchParams.set('tz_offset', offset);
                    downloadLink.href = url.toString();
                } catch (e) {
                    // ignore URL parsing errors
                }
            }
        })();
    </script>
@endpush
