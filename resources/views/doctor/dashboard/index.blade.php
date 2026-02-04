@extends('layouts.doctor')
@section('title', __('messages.thera') . ' - ' . __('messages.doctor') . ' - ' . __('messages.dashboard'))

@section('content')
    <main class="flex-1">
        <div class="container mx-auto px-4" style="padding-bottom: 10px;">
            @if(session('success'))
                <div class="mb-4 p-4 rounded bg-green-100 text-green-800 border border-green-300 text-center font-semibold">
                    {{ session('success') }}
                </div>
            @endif
            <div class="flex flex-col gap-1 mb-6">
                <h1 class="text-title text-3xl font-bold text-gray-900">{{ __('messages.dashboard') }}</h1>
                <p class="text-titlesticky text-sm text-gray-500">{{ __('messages.welcome_back', ['name' => $doctor->user->name]) }}</p>
            </div>

            <div class="mb-8">
                <h2 class="text-titlesticky text-xl font-semibold text-gray-900 mb-6">{{ __('messages.overview') }}</h2>
                <div class="space-y-4">
                    <div class="bg-white rounded-lg border p-6">
                        <div class="text-gray-500 text-sm mb-2">{{ __('messages.upcoming_patients') }}</div>
                        <div class="text-2xl font-bold text-gray-900 mb-1">{{ $upcomingAppointments }}</div>
                        @if(isset($upcomingAppointmentsChange))
                            <div class="text-green-600 text-sm">{{ $upcomingAppointmentsChange > 0 ? '+' : '' }}{{ $upcomingAppointmentsChange }}%</div>
                        @endif
                    </div>

                    <div class="bg-white rounded-lg border p-6">
                        <div class="text-gray-500 text-sm mb-2">{{ __('messages.todays_patients') }}</div>
                        <div class="text-2xl font-bold text-gray-900 mb-1">{{ $todayAppointments }}</div>
                        @if(isset($todayAppointmentsChange))
                            <div class="text-red-600 text-sm">{{ $todayAppointmentsChange > 0 ? '+' : '' }}{{ $todayAppointmentsChange }}%</div>
                        @endif
                    </div>

                    <div class="bg-white rounded-lg border p-6">
                        <div class="text-gray-500 text-sm mb-2">{{ __('messages.pending_prescriptions') }}</div>
                        <div class="text-2xl font-bold text-gray-900 mb-1">{{ $pendingPrescriptions }}</div>
                        @if(isset($pendingPrescriptionsChange))
                            <div class="text-green-600 text-sm">{{ $pendingPrescriptionsChange > 0 ? '+' : '' }}{{ $pendingPrescriptionsChange }}%</div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Notifications -->
            @if(($doctor->dashboard_notifications ?? true))
                <div class="mb-8 bg-white rounded-lg border p-6">
                    <div class="font-bold text-gray-900 mb-4">{{ __('messages.notifications') }}</div>

                    @php
                        $items = $unreadNotifications ?? collect();
                        $isArabic = app()->getLocale() === 'ar';
                    @endphp

                    @if($items->isEmpty())
                        <div class="py-2 text-gray-400">{{ __('messages.no_notifications') }}</div>
                    @else
                        <div class="space-y-3">
                            @foreach($items as $notification)
                                @php
                                    $data = (array) ($notification->data ?? []);
                                    $type = $data['type'] ?? null;
                                    $message = $isArabic ? ($data['message_ar'] ?? null) : ($data['message_en'] ?? null);
                                    $appointmentId = $data['appointment_id'] ?? null;
                                    $patientId = $data['patient_id'] ?? null;
                                @endphp

                                <div class="bg-gray-50 rounded-xl px-5 py-4 flex items-center justify-between gap-4">
                                    <div class="min-w-0">
                                        <div class="font-semibold text-gray-900 truncate">{{ $message ?? 'New notification' }}</div>
                                        <div class="text-gray-500 text-sm truncate">{{ $notification->created_at?->diffForHumans() ?? '' }}</div>
                                    </div>

                                    <div class="flex items-center gap-2 flex-none">
                                        @if($appointmentId && $patientId)
                                            <a href="{{ route('doctor.patients.show', ['patient' => $patientId, 'appointment_id' => $appointmentId, 'back' => url()->full()]) }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-4 rounded-lg">{{ __('messages.view') }}</a>
                                        @endif

                                        <form method="POST" action="{{ route('doctor.notifications.read', $notification->id) }}">
                                            @csrf
                                            <button type="submit" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-4 rounded-lg">{{ __('messages.mark_read') }}</button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif
            
            <!-- Quick Actions -->
            <div class="mb-16 bg-white rounded-lg border p-6">
                <div class="font-bold text-gray-900 mb-4">{{ __('messages.quick_actions') }}</div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                    <a href="{{ route('doctor.patients-records') }}" class="inline-flex items-center justify-center w-full px-5 py-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-800 font-semibold text-sm transition focus:outline-none focus:ring-2 focus:ring-gray-200 whitespace-nowrap">{{ __('messages.search_patient') }}</a>
                    <a href="{{ route('doctor.patient-queue') }}" class="inline-flex items-center justify-center w-full px-5 py-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-800 font-semibold text-sm transition focus:outline-none focus:ring-2 focus:ring-gray-200 whitespace-nowrap">{{ __('messages.today_appointments') }}</a>
                    <a href="{{ route('doctor.prescriptions.index') }}" class="inline-flex items-center justify-center w-full px-5 py-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-800 font-semibold text-sm transition focus:outline-none focus:ring-2 focus:ring-gray-200 whitespace-nowrap">{{ __('messages.create_new_prescription') }}</a>
                </div>
            </div>
        </div>
    </main>
@endsection
