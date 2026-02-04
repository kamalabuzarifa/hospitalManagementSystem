@extends('layouts.doctor')

@section('title', __('messages.appointments')) 

@section('content')
<main class="flex-1">
    <div class="container mx-auto px-4 py-8">
        <div class="flex items-start justify-between gap-4 mb-6">
            <div class="min-w-0">
                <h1 class="text-3xl font-bold text-gray-900">{{ __('messages.appointments') }}</h1>
                @if(!empty($patient))
                    <div class="text-gray-500 text-sm mt-1 truncate">{{ $patient->name ?? '—' }}</div>
                @endif
            </div>

            @if(!empty($patient))
                <a
                    href="{{ route('doctor.patients.show', ['patient' => $patient->id, 'back' => url()->full()]) }}"
                    class="inline-flex items-center justify-center px-4 py-2 rounded-md bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium transition focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                >
                    {{ __('messages.back') }}
                </a>
            @endif
        </div>

        @if(!request()->filled('patient_id'))
            <!-- Filters -->
            <div class="bg-white rounded-lg border p-6 mb-6">
                <form method="GET" action="{{ route('doctor.appointments.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.status') }}</label>
                        <select name="status" id="status" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">—</option>
                            <option value="scheduled" {{ request('status') === 'scheduled' ? 'selected' : '' }}>{{ __('messages.status_scheduled') }}</option>
                            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>{{ __('messages.status_completed') }}</option>
                            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>{{ __('messages.status_cancelled') }}</option>
                            <option value="no-show" {{ request('status') === 'no-show' ? 'selected' : '' }}>No show</option>
                        </select>
                    </div>

                    <div>
                        <label for="date" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.date') }}</label>
                        <input type="date" name="date" id="date" value="{{ request('date') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div class="flex items-end">
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            {{ __('messages.apply') }}
                        </button>
                    </div>
                </form>
            </div>
        @endif

        <!-- Table -->
        <div class="bg-white rounded-lg border">
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.patient_name') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.phone') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.date') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.time') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.patient_code') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.status') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($appointments as $appointment)
                                @php
                                    $dateLabel = '-';
                                    $timeLabel = '-';

                                    if (!empty($appointment->scheduled_at)) {
                                        $dateLabel = $appointment->scheduled_at->format('Y-m-d');
                                        $timeLabel = $appointment->scheduled_at->format('g:i A');
                                    } else {
                                        $d = $appointment->date ?? ($appointment->appointment_date ?? null);
                                        $t = $appointment->time ?? ($appointment->appointment_time ?? null);

                                        $appointmentDate = $d
                                            ? ($d instanceof \Carbon\CarbonInterface ? $d->format('Y-m-d') : (string) $d)
                                            : null;
                                        $appointmentTime = $t ? substr((string) $t, 0, 5) : null;

                                        if ($appointmentDate) {
                                            $dateLabel = $appointmentDate;
                                        }

                                        if ($appointmentDate && $appointmentTime) {
                                            $timeLabel = \Carbon\Carbon::parse($appointmentDate . ' ' . $appointmentTime)->format('g:i A');
                                        } elseif ($appointmentTime) {
                                            $timeLabel = \Carbon\Carbon::createFromFormat('H:i', $appointmentTime)->format('g:i A');
                                        }
                                    }
                                @endphp

                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $appointment->id }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $appointment->patient->name ?? '—' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $appointment->patient->phone ?? '—' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $dateLabel }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $timeLabel }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $appointment->patient->patient_code ?? $appointment->patient_id ?? '—' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-700">
                                            {{ __('messages.status_' . strtolower($appointment->status)) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="{{ route('doctor.patients.show', ['patient' => $appointment->patient_id, 'back' => url()->full()]) }}" class="text-blue-600 hover:text-blue-900">{{ __('messages.view') }}</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-6 text-center text-sm text-gray-500">{{ __('messages.no_appointments_found') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    <div class="div-all-accessLogs p-4 border-t border-gray-100">{{ $appointments->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection
