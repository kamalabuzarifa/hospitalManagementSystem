@extends('layouts.doctor')

@section('title', __('messages.patient'))

@section('content')
<main class="flex-1">
    <div class="container mx-auto px-4 " style="padding-bottom: 16px;">
        <div class="flex items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-title text-3xl font-bold text-gray-900">{{ __('messages.patient') }}</h1>
                @if(!empty($patient?->name))
                    <div class="text-titlesticky  text-gray-500 text-sm">{{ $patient->name }}</div>
                @endif
            </div>
            <div class="flex items-center gap-2">
                {{-- <a href="#appointments" onclick="document.getElementById('appointments')?.scrollIntoView({ behavior: 'smooth', block: 'start' }); return false;" class="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-semibold transition focus:outline-none focus:ring-2 focus:ring-blue-200 whitespace-nowrap">
                    {{ __('messages.appointments') }}
                </a> --}}
                @php
                    $currentAppointment = $todaysAppointment ?? null;
                    $canCreatePrescription = false;
                    if ($currentAppointment && empty($currentAppointment->prescription)) {
                        $appointmentAt = $currentAppointment->scheduled_at;
                        if (empty($appointmentAt) && !empty($currentAppointment->date) && !empty($currentAppointment->time)) {
                            try {
                                $appointmentAt = \Carbon\Carbon::parse($currentAppointment->date . ' ' . $currentAppointment->time);
                            } catch (\Throwable $e) {
                                $appointmentAt = null;
                            }
                        }

                        $allowedStatus = in_array((string) $currentAppointment->status, ['scheduled', 'checked_in', 'pending'], true);
                        $inTimeWindow = $appointmentAt
                            ? now()->between($appointmentAt->copy()->subMinutes(15), $appointmentAt->copy()->addMinutes(40))
                            : false;

                        $canCreatePrescription = $allowedStatus && $inTimeWindow;
                    }
                @endphp

                @if($canCreatePrescription)
                    <a
                        href="{{ route('doctor.prescriptions.index', ['patient_id' => $patient->id, 'appointment_id' => $currentAppointment->id, 'open' => 1]) }}"
                        class="inline-flex items-center px-4 py-2 rounded-md bg-blue-600 hover:bg-blue-700 text-white font-medium"
                    >
                        {{ __('messages.create_prescription') }}
                    </a>
                @endif
                @php
                    $requestedBackUrl = request('back');
                    $safeBackUrl = null;

                    if (is_string($requestedBackUrl) && $requestedBackUrl !== '') {
                        $appHost = parse_url(config('app.url'), PHP_URL_HOST);
                        $backHost = parse_url($requestedBackUrl, PHP_URL_HOST);
                        $backPath = parse_url($requestedBackUrl, PHP_URL_PATH);

                        $sameHost = empty($backHost) || (!empty($appHost) && strcasecmp((string) $backHost, (string) $appHost) === 0);
                        $doctorPath = !empty($backPath) && str_starts_with((string) $backPath, '/doctor');

                        if ($sameHost && $doctorPath) {
                            $safeBackUrl = $requestedBackUrl;
                        }
                    }

                    if (!$safeBackUrl) {
                        $previousUrl = url()->previous();
                        $appHost = parse_url(config('app.url'), PHP_URL_HOST);
                        $prevHost = parse_url($previousUrl, PHP_URL_HOST);
                        $prevPath = parse_url($previousUrl, PHP_URL_PATH);

                        $sameHost = empty($prevHost) || (!empty($appHost) && strcasecmp((string) $prevHost, (string) $appHost) === 0);
                        $doctorPath = !empty($prevPath) && str_starts_with((string) $prevPath, '/doctor');

                        $safeBackUrl = ($sameHost && $doctorPath) ? $previousUrl : route('doctor.patient-queue');
                    }
                @endphp
                <a href="{{ $safeBackUrl }}" class="inline-flex items-center justify-center px-4 py-2 rounded-md bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium transition focus:outline-none focus:ring-2 focus:ring-gray-200 whitespace-nowrap">
                    {{ __('messages.back') }}
                </a>
            </div>
        </div>
        {{-- info. --}}
        @php
            $dob = $patient->dob ?? null;
            if (!empty($dob) && !($dob instanceof \Carbon\CarbonInterface)) {
                try {
                    $dob = \Carbon\Carbon::parse((string) $dob);
                } catch (\Throwable $e) {
                    $dob = null;
                }
            }

            $dobLabel = $dob ? $dob->format('Y-m-d') : '';
            $ageLabel = $dob ? (string) $dob->age : '';

            $hasContactInfo = !empty($patient?->phone) || !empty($patient?->address);
            $hasMedicalInfo = !is_null($patient?->has_allergies)
                || !is_null($patient?->has_chronic)
                || !empty($patient?->allergy_details)
                || !empty($patient?->chronic_details)
                || !empty($patient?->last_visit)
                || !empty($patient?->medical_history);
        @endphp

        <div class="space-y-6">
            <!-- Basic Information Section -->
            <div class="bg-white rounded-lg border p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4 border-b border-gray-200 pb-2">{{ __('messages.basic_information') }}</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @if(!empty($patient?->name))
                        <div>
                            <label class="block text-gray-600 font-semibold mb-1">{{ __('messages.full_name') }}</label>
                            <p class="text-gray-900 text-lg">{{ $patient->name }}</p>
                        </div>
                    @endif

                    @if(!empty($patient?->patient_code))
                        <div>
                            <label class="block text-gray-600 font-semibold mb-1">{{ __('messages.patient_code') }}</label>
                            <p class="text-gray-900 text-lg">{{ $patient->patient_code }}</p>
                        </div>
                    @endif

                    @if(!empty($dob))
                        <div>
                            <label class="block text-gray-600 font-semibold mb-1">{{ __('messages.date_of_birth') }}</label>
                            <p class="text-gray-900">{{ $dobLabel }}</p>
                        </div>
                        <div>
                            <label class="block text-gray-600 font-semibold mb-1">{{ __('messages.age') }}</label>
                            <p class="text-gray-900">{{ $ageLabel }}</p>
                        </div>
                    @endif

                    @if(!empty($patient?->gender))
                        <div>
                            <label class="block text-gray-600 font-semibold mb-1">{{ __('messages.gender') }}</label>
                            <p class="text-gray-900 capitalize">{{ __('messages.gender_' . $patient->gender) }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Contact Information Section -->
            @if($hasContactInfo)
                <div class="bg-white rounded-lg border p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4 border-b border-gray-200 pb-2">{{ __('messages.contact_information') }}</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @if(!empty($patient?->phone))
                            <div>
                                <label class="block text-gray-600 font-semibold mb-1">{{ __('messages.phone') }}</label>
                                <p class="text-gray-900">{{ $patient->phone }}</p>
                            </div>
                        @endif
                        @if(!empty($patient?->address))
                            <div>
                                <label class="block text-gray-600 font-semibold mb-1">{{ __('messages.address') }}</label>
                                <p class="text-gray-900 whitespace-pre-line">{{ $patient->address }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Medical Information Section -->
            @if($hasMedicalInfo)
                <div class="bg-white rounded-lg border p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4 border-b border-gray-200 pb-2">{{ __('messages.medical_information') }}</h2>

                    @if(!is_null($patient?->has_allergies))
                        <div class="mb-6">
                            <label class="block text-gray-600 font-semibold mb-2">{{ __('messages.has_allergies') }}</label>
                            <div class="flex items-center gap-3 mb-2">
                                <span class="inline-block bg-yellow-100 text-yellow-800 px-3 py-1 rounded font-semibold">
                                    {{ $patient->has_allergies ? __('messages.yes') : __('messages.no') }}
                                </span>
                            </div>
                            @if(!empty($patient?->allergy_details))
                                <div class="mt-2 p-3 bg-gray-50 rounded border">
                                    <p class="text-gray-700 whitespace-pre-line">{{ $patient->allergy_details }}</p>
                                </div>
                            @endif
                        </div>
                    @endif

                    @if(!is_null($patient?->has_chronic))
                        <div>
                            <label class="block text-gray-600 font-semibold mb-2">{{ __('messages.has_chronic') }}</label>
                            <div class="flex items-center gap-3 mb-2">
                                <span class="inline-block bg-gray-100 text-gray-700 px-3 py-1 rounded font-semibold">
                                    {{ $patient->has_chronic ? __('messages.yes') : __('messages.no') }}
                                </span>
                            </div>
                            @if(!empty($patient?->chronic_details))
                                <div class="mt-2 p-3 bg-gray-50 rounded border">
                                    <p class="text-gray-700 whitespace-pre-line">{{ $patient->chronic_details }}</p>
                                </div>
                            @endif
                        </div>
                    @endif

                    @if(!empty($patient?->last_visit))
                        <div class="mt-6">
                            <label class="block text-gray-600 font-semibold mb-1">{{ __('messages.last_visit') }}</label>
                            <p class="text-gray-900">{{ $patient->last_visit?->format('Y-m-d') }}</p>
                        </div>
                    @endif

                    @if(!empty($patient?->medical_history))
                        <div class="mt-6">
                            <label class="block text-gray-600 font-semibold mb-1">{{ __('messages.medical_history') }}</label>
                            <p class="text-gray-900 whitespace-pre-line">{{ $patient->medical_history }}</p>
                        </div>
                    @endif
                </div>
            @endif
        </div>

        <!-- Table -->
        <div id="appointments" class="mt-8 bg-white rounded-lg border p-6" style="margin-bottom: 40px">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900">{{ __('messages.appointments_history') }}</h2>
            </div>

            <div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.appointment_id') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.date_booking') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.time') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.status') }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($appointmentsHistory as $appointment)
                                @php
                                    $dateLabel = '';
                                    $timeLabel = '';

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
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $dateLabel }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $timeLabel }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-700">
                                            {{ __('messages.status_' . strtolower($appointment->status)) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-6 text-center text-sm text-gray-500">{{ __('messages.no_appointments_found') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            try {
                const params = new URLSearchParams(window.location.search);
                const apptId = params.get('appointment_id');
                const back = params.get('back');
                if (apptId) {
                    // Try to open appointment modal via common hooks (if available)
                    try {
                        // dispatch a custom event listeners in case other scripts handle it
                        window.dispatchEvent(new CustomEvent('openAppointmentById', { detail: { id: apptId } }));
                    } catch (e) { /* ignore */ }

                    // Try to click any element that might be wired to open the appointment
                    try {
                        const el = document.querySelector('[data-appointment-id="' + apptId + '"]');
                        if (el) el.click();
                    } catch (e) { /* ignore */ }

                    // Remove the parameters from the URL to keep it clean
                    try {
                        const u = new URL(window.location.href);
                        u.searchParams.delete('appointment_id');
                        u.searchParams.delete('back');
                        history.replaceState(null, '', u.toString());
                    } catch (e) { /* ignore */ }
                }
            } catch (e) { /* ignore */ }
        });
    </script>
@endpush
