@extends('layouts.doctor')

@section('title', __('messages.thera') . ' - ' . __('messages.doctor') . ' - ' . __('messages.patients_queue'))


@section('content')
    <main class="flex-1">
        <div class="container mx-auto px-4 " style="padding-bottom: 10px;">

            <h1 class="text-title text-3xl font-bold text-gray-900 mb-6">{{ __('messages.patients_queue') }}</h1>

            @php
                $isRtl = app()->getLocale() === 'ar';
            @endphp

            <div class="bg-white rounded-lg border p-6 mb-6">
                <form method="GET" action="{{ route('doctor.patient-queue') }}" autocomplete="off">
                    <label class="sr-only" for="search">{{ __('messages.search') }}</label>
                    <div class="flex flex-col md:flex-row md:items-center gap-3">
                        <div class="relative flex-1">
                            <span class="pointer-events-none absolute inset-y-0 {{ $isRtl ? 'right-3' : 'left-3' }} flex items-center text-gray-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 104.5 4.5a7.5 7.5 0 0012.15 12.15z" />
                                </svg>
                            </span>
                            <input
                                id="search"
                                name="search"
                                value="{{ $search ?? '' }}"
                                type="text"
                                placeholder="{{ __('messages.search_by_patient_name') }}"
                                class="w-full {{ $isRtl ? 'pr-10 pl-4' : 'pl-10 pr-4' }} py-3 border-0 rounded-lg bg-gray-100 focus:bg-white focus:ring-2 focus:ring-blue-200 focus:outline-none text-gray-700 placeholder-gray-400"
                            />
                        </div>

                        @if(!empty($search))
                            <a href="{{ route('doctor.patient-queue') }}" class="inline-flex items-center justify-center px-5 py-3 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold transition focus:outline-none focus:ring-2 focus:ring-gray-200 whitespace-nowrap">
                                {{ __('messages.reset') }}
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <div class="flex flex-col gap-1 mb-4">
                <h2 class="text-title text-xl font-semibold text-gray-900">{{ __('messages.todays_patients') }}</h2>
            </div>

            <div class="bg-white rounded-lg border">
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 table-fixed">
                            <colgroup>
                                <col style="width:40%">
                                <col style="width:20%">
                                <col style="width:20%">
                                <col style="width:20%">
                            </colgroup>
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 {{ $isRtl ? 'text-right' : 'text-left' }} text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.patient_name') }}</th>
                                    <th class="px-6 py-3 {{ $isRtl ? 'text-right' : 'text-left' }} text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.time') }}</th>
                                    <th class="px-6 py-3 {{ $isRtl ? 'text-right' : 'text-left' }} text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.status') }}</th>
                                    <th class="px-6 py-3 {{ $isRtl ? 'text-right' : 'text-left' }} text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.action') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @if (count($appointments) === 0)
                                    <tr>
                                        <td colspan="4" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">{{ __('messages.no_appointments_found') }}</td>
                                    </tr>
                                @else
                                    @foreach($appointments as $appointment)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 {{ $isRtl ? 'text-right' : 'text-left' }}">
                                            <div class="max-w-xs truncate">{{ $appointment->patient->name ?? '-' }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 {{ $isRtl ? 'text-right' : 'text-left' }}">
                                            @php
                                                $timeDisplay = '-';

                                                if (!empty($appointment->scheduled_at)) {
                                                    $timeDisplay = $appointment->scheduled_at->format('g:i A');
                                                } else {
                                                    $appointmentDate = $appointment->date
                                                        ? ($appointment->date instanceof \Carbon\CarbonInterface ? $appointment->date->format('Y-m-d') : (string) $appointment->date)
                                                        : null;
                                                    $appointmentTime = $appointment->time ? substr((string) $appointment->time, 0, 5) : null;

                                                    if ($appointmentDate && $appointmentTime) {
                                                        $timeDisplay = \Carbon\Carbon::parse($appointmentDate . ' ' . $appointmentTime)->format('g:i A');
                                                    } elseif ($appointmentTime) {
                                                        $timeDisplay = \Carbon\Carbon::createFromFormat('H:i', $appointmentTime)->format('g:i A');
                                                    }
                                                }
                                            @endphp
                                            {{ $timeDisplay }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap {{ $isRtl ? 'text-right' : 'text-left' }}">
                                            <span class="inline-flex px-2 text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-700">
                                                {{ __("messages.status_" . strtolower($appointment->status)) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium {{ $isRtl ? 'text-right' : 'text-left' }}">
                                            @php
                                                $patientId = $appointment->patient_id ?? ($appointment->patient->id ?? null);
                                            @endphp

                                            @if($patientId)
                                                <a
                                                    href="{{ route('doctor.patients.show', ['patient' => $patientId, 'appointment_id' => $appointment->id, 'back' => url()->full()]) }}"
                                                    class="text-blue-600 hover:text-blue-900"
                                                >
                                                    {{ __('messages.view') }}
                                                </a>
                                            @else
                                                <span class="text-gray-400">{{ __('messages.view') }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                @endif
                            
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection
