@extends('layouts.reception')

@section('title', __('messages.thera') . ' - ' . __('messages.reception') . ' - ' . __('messages.dashboard') )

@section('content')
    <main>
        <div class="container mx-auto px-4 " style="padding-bottom: 16px;">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-title text-3xl font-bold">{{ __('messages.dashboard') }}</h1>
                <div class="text-title text-sm text-gray-500">{{ $today }}</div>
            </div>

            <!-- Today's Patients Table (from appointments) -->
            <div class="mb-8 bg-white rounded-lg shadow p-6">
                <h2 class="text-black text-xl font-semibold mb-4">{{ __('messages.todays_patients') }}</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white border rounded-lg">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-3 text-left text-gray-600 font-semibold">{{ __('messages.patient') }}</th>
                                <th class="px-4 py-3 text-left text-gray-600 font-semibold">{{ __('messages.time') }}</th>
                                <th class="px-4 py-3 text-left text-gray-600 font-semibold">{{ __('messages.doctor') }}</th>
                                <th class="px-4 py-3 text-left text-gray-600 font-semibold">{{ __('messages.department') }}</th>
                                <th class="px-4 py-3 text-left text-gray-600 font-semibold">{{ __('messages.status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($todaysAppointments as $appointment)
                                <tr class="hover:bg-gray-50 border-b">
                                    <td class="text-titlest px-4 py-2 hover:text-blue-500 hover:underline font-semibold">
                                        @if($appointment->patient)
                                            <a href="{{ route('reception.patients.show', $appointment->patient) }}" >
                                                {{ $appointment->patient->name }}
                                            </a>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="text-titlest px-4 py-2 font-medium text-gray-900">
                                        {{ $appointment->time ? date('H:i', strtotime($appointment->time)) : '—' }}
                                    </td>
                                    <td class="text-titlest px-4 py-2">{{ $appointment->doctor?->user?->name ?? '—' }}</td>
                                    <td class="text-titlest px-4 py-2">{{ $appointment->doctor?->specialization ?? '—' }}</td>
                                    <td class="text-titlest px-4 py-2">
                                        <span class="px-3 py-1 rounded text-sm font-semibold
                                            {{ $appointment->status === 'scheduled' ? 'bg-blue-100 text-blue-800' : '' }}
                                            {{ $appointment->status === 'checked_in' ? 'bg-green-100 text-green-800' : '' }}
                                            {{ $appointment->status === 'checked_out' ? 'bg-purple-100 text-purple-800' : '' }}
                                            {{ $appointment->status === 'completed' ? 'bg-gray-200 text-gray-800' : '' }}
                                            {{ $appointment->status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}
                                            {{ $appointment->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                            {{ in_array($appointment->status, ['scheduled','checked_in','checked_out','completed','cancelled','pending'], true) ? '' : 'bg-gray-100 text-gray-700' }}
                                        ">
                                            {{ ucfirst($appointment->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-4 text-center text-gray-400">{{ __('messages.no_appointments_for_today') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="mb-8 bg-white rounded-lg shadow p-6">
                <h2 class="text-black text-xl font-semibold mb-4">{{ __('messages.quick_actions') }}</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <a href="{{ route('reception.appointments.index', ['date' => $today, 'status' => 'scheduled']) }}" class="block border rounded-lg p-5 hover:bg-gray-50">
                        <div class="text-titlest font-semibold text-lg mb-2">{{ __('messages.check_in_patient') }}</div>
                        <div class="text-gray-500 text-sm">{{ __('messages.check_in_description') }}</div>
                    </a>
                    <a href="{{ route('reception.appointments.index', ['date' => $today, 'status' => 'checked_in']) }}" class="block border rounded-lg p-5 hover:bg-gray-50">
                        <div class="text-titlest font-semibold text-lg mb-2">{{ __('messages.check_out_patient') }}</div>
                        <div class="text-gray-500 text-sm">{{ __('messages.check_out_description') }}</div>
                    </a>
                </div>
            </div>

            <!-- Notifications -->
            @if(($settings->reception_dashboard_notifications ?? true))
                <div class="mb-8 bg-white rounded-lg shadow p-6">
                    <h2 class="text-black text-xl font-semibold mb-4">{{ __('messages.notifications') }}</h2>

                    @php
                        $items = $unreadNotifications ?? collect();
                        $isArabic = app()->getLocale() === 'ar';
                    @endphp

                    @if($items->isEmpty())
                        <div class="py-2 text-gray-400">{{ __('messages.no_new_notifications') }}</div>
                    @else
                        <div class="space-y-3">
                            @foreach($items as $notification)
                                @php
                                    $data = (array) ($notification->data ?? []);
                                    $patientId = $data['patient_id'] ?? null;
                                    $message = $isArabic ? ($data['message_ar'] ?? null) : ($data['message_en'] ?? null);
                                @endphp

                                <div class="bg-gray-50 rounded-xl px-5 py-4 flex items-center justify-between gap-4">
                                    <div class="min-w-0">
                                        <div class="font-semibold text-gray-900 truncate">
                                            {{ $message ?? __('messages.new_notification') }}
                                        </div>
                                        <div class="text-gray-500 text-sm truncate">
                                            {{ $notification->created_at?->diffForHumans() ?? '' }}
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2 flex-none">
                                        @if($patientId)
                                            <a href="{{ route('reception.patients.show', $patientId) }}" class="bg-white border border-gray-200 hover:bg-gray-100 text-gray-700 font-medium py-2 px-4 rounded-lg">{{ __('messages.view') }}</a>
                                        @endif

                                        <form method="POST" action="{{ route('reception.notifications.read', $notification->id) }}">
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

            <!-- Urgent Requests -->
            <div class="bg-white rounded-lg shadow p-6" style="margin-bottom: 16px;">
                <h2 class="text-black text-xl font-semibold mb-4">{{ __('messages.urgent_requests') }}</h2>
                <div class="space-y-3">
                    @forelse($urgentAppointments as $appointment)
                        @php
                            $appointmentDate = $appointment->date ? $appointment->date->format('Y-m-d') : null;
                            $appointmentTime = $appointment->time ? substr((string) $appointment->time, 0, 5) : null;
                            $appointmentDateTime = ($appointmentDate && $appointmentTime)
                                ? \Carbon\Carbon::parse($appointmentDate . ' ' . $appointmentTime)
                                : null;
                            $isOverdue = $appointmentDateTime ? $appointmentDateTime->isPast() : false;

                            $patientName = $appointment->patient->name ?? '—';
                            $timeLabel = $appointment->time ? date('H:i', strtotime($appointment->time)) : '—';
                            $doctorName = $appointment->doctor?->user?->name ?? '—';
                        @endphp

                        <div class="bg-gray-50 rounded-xl px-5 py-4 flex items-center justify-between gap-4">
                            <div class="flex items-center gap-4 min-w-0">
                                <div class="h-10 w-10 rounded-full bg-white border border-gray-200 flex items-center justify-center flex-none">
                                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </div>

                                <div class="min-w-0">
                                    <div class="font-semibold text-gray-900 truncate">{{ __('messages.urgent_request') }}</div>
                                    <div class="text-gray-500 text-sm truncate">
                                        {{ $isOverdue ? __('messages.overdue') : '' }}{{ __('messages.request_for') }} {{ $patientName }}{{ $timeLabel !== '—' ? (' ' . __('messages.at') . ' ' . $timeLabel) : '' }}{{ $doctorName !== '—' ? (' — ' . $doctorName) : '' }}
                                    </div>
                                </div>
                            </div>

                            <a href="{{ route('reception.appointments.show', $appointment) }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-4 rounded-lg flex-none text-center">{{ __('messages.view') }}</a>
                        </div>
                    @empty
                        <div class="py-4 text-gray-400">{{ __('messages.no_urgent_requests_found') }}</div>
                    @endforelse
                </div>
            </div>
        </div>
    </main>
@endsection
