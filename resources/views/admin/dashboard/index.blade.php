@extends('layouts.admin')

@section('title',__('messages.thera') . ' - ' . __('messages.administration') . ' - ' . __('messages.dashboard'))


@section('content')
    <main>
        <div class="py-8 px-4 sm:px-6 md:px-8">
            <div class="max-w-7xl mx-auto">
                <!-- Page Title -->
                <h1 class=" text-admain text-3xl font-bold text-gray-900 mb-8">{{ __('messages.dashboard') }}</h1>

                <!-- Main Statistics Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6 mb-8">
                    <!-- Total Patients -->
                    <div class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm font-medium">{{ __('messages.total_patients') }}</p>
                                <p id="totalPatients" class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($totalPatients) }}</p>
                            </div>
                            <div class="bg-blue-100 rounded-full p-4">
                                <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Total Doctors -->
                    <div class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm font-medium">{{ __('messages.total_doctors') }}</p>
                                <p id="totalDoctors" class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($totalDoctors ?? 0) }}</p>
                            </div>
                            <div class="bg-green-100 rounded-full p-4">
                                <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                    <!-- Total Staff -->
                    <div class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm font-medium">{{ __('messages.total_staff') }}</p>
                                <p id="totalStaff" class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($totalStaff ?? 0) }}</p>
                            </div>
                            <div class="bg-orange-100 rounded-full p-4">
                                <svg class="h-8 w-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Total Appointments -->
                    <div class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm font-medium">{{ __('messages.total_appointments') }}</p>
                                <p id="totalAppointments" class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($totalAppointments ?? 0) }}</p>
                            </div>
                            <div class="bg-purple-100 rounded-full p-4">
                                <svg class="h-8 w-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Data Overview -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <!-- Recent Appointments -->
                    @if(($settings->dashboard_recent_appointments ?? true))
                        <div class="bg-gray-50 rounded-lg shadow p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('messages.recent_appointments') }}</h3>
                            <div class="space-y-3">
                                @forelse($recentAppointments as $appt)
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="font-semibold text-gray-900">{{ optional($appt->doctor)->name ?? '—' }}</p>
                                            <p class="text-sm text-gray-500">{{ __('messages.patient') }}: {{ optional($appt->patient)->name ?? '—' }}</p>
                                        </div>
                                        <span class="text-xs text-gray-400">{{ $appt->created_at?->diffForHumans() }}</span>
                                    </div>
                                @empty
                                    <p class="text-sm text-gray-500">{{ __('messages.no_data') }}</p>
                                @endforelse
                            </div>
                        </div>
                    @endif

                    <!-- Recent Patients -->
                    @if(($settings->dashboard_recent_patient ?? true))
                        <div class="bg-gray-50 rounded-lg shadow p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('messages.recent_patient') }}</h3>
                            <div class="space-y-3">
                                @forelse($recentPatients as $patient)
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="font-semibold text-gray-900">{{ $patient->name }}</p>
                                            <p class="text-sm text-gray-500">{{ __('messages.registered') }}</p>
                                        </div>
                                        <span class="text-xs text-gray-400">{{ $patient->created_at?->diffForHumans() }}</span>
                                    </div>
                                @empty
                                    <p class="text-sm text-gray-500">{{ __('messages.no_data') }}</p>
                                @endforelse
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Notifications Section -->
                @php
                    $dashboardNotificationsEnabled = (bool) (($settings->reception_dashboard_notifications ?? true) && ($settings->pharmacy_dashboard_notifications ?? true));
                    $showAdminNotifications = $dashboardNotificationsEnabled
                        && (($settings->notification_new_appointment ?? true) || ($settings->notification_new_patient ?? true));
                @endphp
                @if($showAdminNotifications)
                    <div class="bg-white rounded-lg shadow p-6 mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('messages.notifications') }}</h3>
                        <div class="space-y-4">
                            @if(empty($notifications))
                                <p class="text-sm text-gray-500">{{ __('messages.no_notifications') }}</p>
                            @else
                                @foreach($notifications as $notif)
                                    @if($notif['type'] === 'appointment' && ($settings->notification_new_appointment ?? true))
                                    <div class="flex items-start">
                                        <div class="bg-blue-100 rounded-full p-2 mr-4">
                                            <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                        <div class="flex-1">
                                            <p class="font-semibold text-gray-900">{{ $notif['title'] }}</p>
                                            <p class="text-sm text-gray-500">{{ __('messages.doctor') }}: {{ $notif['doctor'] }} · {{ __('messages.patient') }}: {{ $notif['patient'] }}</p>
                                        </div>
                                        <span class="text-xs text-gray-400">
                                            {{ $notif['time'] ? $notif['time']->diffForHumans() : '-' }}
                                        </span>
                                    </div>
                                @elseif($notif['type'] === 'patient' && ($settings->notification_new_patient ?? true))
                                    <div class="flex items-start">
                                        <div class="bg-green-100 rounded-full p-2 mr-4">
                                            <svg class="h-5 w-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                            </svg>
                                        </div>
                                        <div class="flex-1">
                                            <p class="font-semibold text-gray-900">{{ $notif['title'] }}</p>
                                            <p class="text-sm text-gray-500">{{ __('messages.patient') }}: {{ $notif['patient'] }}</p>
                                        </div>
                                        <span class="text-xs text-gray-400">
                                            {{ $notif['time'] ? $notif['time']->diffForHumans() : '-' }}
                                        </span>
                                    </div>
                                @endif
                                @endforeach
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Appointment Status + Logout -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <style>
                            @media (max-width: 900px) {
                                .text-3xl { font-size: 1.5rem !important; }
                                .text-2xl { font-size: 1.2rem !important; }
                                .text-lg { font-size: 1rem !important; }
                                .p-6 { padding: 1rem !important; }
                                .mb-8 { margin-bottom: 1.5rem !important; }
                            }
                            @media (max-width: 600px) {
                                .py-8 { padding-top: 1.2rem !important; padding-bottom: 1.2rem !important; }
                                .px-4, .sm\:px-6, .md\:px-8 { padding-left: 0.5rem !important; padding-right: 0.5rem !important; }
                                .gap-6 { gap: 1rem !important; }
                            }
                        </style>
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('messages.appointment_status') }}</h3>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                            @php
                                $statusMap = [
                                    'scheduled' => ['label' => __('messages.scheduled'), 'color' => 'bg-blue-100 text-blue-800'],
                                    'completed' => ['label' => __('messages.completed'), 'color' => 'bg-green-100 text-green-800'],
                                    'cancelled' => ['label' => __('messages.cancelled'), 'color' => 'bg-red-100 text-red-800'],
                                    'pending' => ['label' => __('messages.pending'), 'color' => 'bg-yellow-100 text-yellow-800'],
                                ];
                            @endphp
                            @foreach($statusMap as $key => $meta)
                                <div class="bg-gray-50 rounded-lg p-4 flex flex-col gap-2 items-center text-center min-h-[110px]">
                                    <span class="text-sm text-gray-500 tracking-wide">{{ $meta['label'] }}</span>
                                    <span id="status{{ ucfirst($key) }}" class="text-2xl md:text-3xl font-bold text-gray-900">{{ number_format($statusStats[$key] ?? 0) }}</span>
                                    <span class="inline-flex items-center justify-center px-3 py-1 rounded-full mt-2 text-xs font-semibold {{ $meta['color'] }}">{{ $meta['label'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="bg-gray-50 rounded-lg shadow p-6 flex items-center justify-between">
                        <div>
                            <p class="text-gray-900 font-semibold">{{ auth()->user()->name }}</p>
                            <p class="text-sm text-gray-500">{{ __('messages.administrator') }}</p>
                        </div>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-red-600 hover:text-red-800 font-semibold">
                                ← {{ __('messages.logout') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection

@push('scripts')
<script>
// Auto-refresh dashboard counts every 10 seconds
setInterval(function() {
    fetch('{{ route("admin.dashboard.counts") }}')
        .then(res => res.json())
        .then(data => {
            // Update totals
            if (data.totals) {
                document.getElementById('totalPatients').textContent = Number(data.totals.patients).toLocaleString();
                document.getElementById('totalDoctors').textContent = Number(data.totals.doctors).toLocaleString();
                document.getElementById('totalAppointments').textContent = Number(data.totals.appointments).toLocaleString();
                document.getElementById('totalStaff').textContent = Number(data.totals.staff).toLocaleString();
            }
            // Update status counts
            if (data.status) {
                ['scheduled', 'completed', 'cancelled', 'pending'].forEach(key => {
                    const el = document.getElementById('status' + key.charAt(0).toUpperCase() + key.slice(1));
                    if (el) {
                        el.textContent = Number(data.status[key] || 0).toLocaleString();
                    }
                });
            }
        })
        .catch(err => console.error('Dashboard refresh error:', err));
}, 10000); // 10 seconds
</script>
@endpush
