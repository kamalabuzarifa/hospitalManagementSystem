@extends('layouts.reception')
@section('title', __('messages.thera') . ' - ' . __('messages.reception') . ' - ' . __('messages.edit_p_appointment', ['name' => $appointment->patient?->name ?? '—']))

@section('content')
<main>
    <div class="container mx-auto px-4" style="padding-bottom: 16px !important;">
        <h1 class="text-title text-2xl font-bold mb-6">{{ __('messages.edit_appointment') }}</h1>

        @if (session('success'))
            <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-6">
                <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
                <ul class="list-disc list-inside text-sm text-red-800">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('reception.appointments.update', $appointment) }}" class="space-y-6">
            @csrf
            @method('PUT')

            @php
                $dateValue = old('date');
                $timeValue = old('time');

                if (empty($dateValue)) {
                    if (!empty($appointment->date) && $appointment->date instanceof \Carbon\CarbonInterface) {
                        $dateValue = $appointment->date->format('Y-m-d');
                    } elseif (!empty($appointment->date)) {
                        try {
                            $dateValue = \Carbon\Carbon::parse($appointment->date)->format('Y-m-d');
                        } catch (\Exception $e) {
                            $dateValue = '';
                        }
                    } else {
                        $dateValue = '';
                    }
                }

                if (empty($timeValue)) {
                    if (!empty($appointment->time) && ($appointment->time instanceof \Carbon\CarbonInterface)) {
                        $timeValue = $appointment->time->format('H:i');
                    } elseif (!empty($appointment->time)) {
                        $timeValue = date('H:i', strtotime($appointment->time));
                    } else {
                        $timeValue = '';
                    }
                }
            @endphp

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex flex-col space-y-4">
                    @php
                        $modalPatientId = old('patient_id', $appointment->patient_id ?? '');
                        $modalDoctorId = old('doctor_id', $appointment->doctor_id ?? '');
                        $modalStatus = old('status', $appointment->status ?? 'scheduled');
                        $selectedPatientGender = '';
                        if ($modalPatientId) {
                            $found = collect($patients)->firstWhere('id', (int)$modalPatientId);
                            if ($found) {
                                $selectedPatientGender = $found->gender ? ($found->gender == 'male' ? 'Male' : ($found->gender == 'female' ? 'Female' : 'Other')) : '';
                            }
                        }
                    @endphp

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.patient') }}</label>
                        <select name="patient_id" id="patient-select" required class="text-titlest w-full px-4 py-2 border rounded @error('patient_id') border-red-500 @enderror">
                            @foreach($patients as $patient)
                                <option value="{{ $patient->id }}" data-gender="{{ $patient->gender ? ($patient->gender == 'male' ? __('messages.male') : ($patient->gender == 'female' ? __('messages.female') : __('messages.other'))) : '' }}" {{ (string)$modalPatientId === (string)$patient->id ? 'selected' : '' }}>
                                    {{ $patient->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('patient_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.gender') }}</label>
                        <input type="text" id="selected-patient-gender" class="text-titlest w-full px-4 py-2 border rounded bg-gray-100" value="{{ $selectedPatientGender }}" readonly>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.doctor') }}</label>
                        <select name="doctor_id" class="text-titlest w-full px-4 py-2 border rounded @error('doctor_id') border-red-500 @enderror" @if(!($appSettings->auto_assign_doctor ?? false)) required @endif>
                            @if(($appSettings->auto_assign_doctor ?? false))
                                <option value="">{{ __('messages.auto_assign_general') }}</option>
                            @else
                                <option value="">{{ __('messages.select_doctor') }}</option>
                            @endif
                            @foreach($doctors as $doctor)
                                <option value="{{ $doctor->id }}" {{ (string)$modalDoctorId === (string)$doctor->id ? 'selected' : '' }}>
                                    {{ $doctor->user->name ?? '' }}
                                </option>
                            @endforeach
                        </select>
                        @if(($appSettings->auto_assign_doctor ?? false))
                            <p class="mt-1 text-xs text-gray-500">{{ __('messages.auto_assign_info') }}</p>
                        @else
                            <p class="mt-1 text-xs text-gray-500">{{ __('messages.auto_assign_disabled_info') }}</p>
                        @endif
                        @error('doctor_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2" for="date">{{ __('messages.date') }}</label>
                        <input
                            type="date"
                            id="date"
                            name="date"
                            value="{{ $dateValue }}"
                            class="text-titlest w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-200"
                            required
                        />
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2" for="time">{{ __('messages.time') }}</label>
                        <input
                            type="time"
                            id="time"
                            name="time"
                            value="{{ $timeValue }}"
                            step="{{ (int)($appSettings->default_appointment_duration ?? 20) * 60 }}"
                            class="text-titlest w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-200"
                            required
                        />
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.status') }}</label>
                            <select name="status" required class="text-titlest w-full px-4 py-2 border rounded @error('status') border-red-500 @enderror">
                                <option value="">{{ __('messages.select_status') }}</option>
                                <option value="scheduled" {{ $modalStatus == 'scheduled' ? 'selected' : '' }}>{{ __('messages.scheduled') }}</option>
                                <option value="completed" {{ $modalStatus == 'completed' ? 'selected' : '' }}>{{ __('messages.completed') }}</option>
                                <option value="cancelled" {{ $modalStatus == 'cancelled' ? 'selected' : '' }}>{{ __('messages.cancelled') }}</option>
                                <option value="pending" {{ $modalStatus == 'pending' ? 'selected' : '' }}>{{ __('messages.pending') }}</option>
                            </select>
                        @error('status') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <div class="flex justify-between items-center">
                <div></div>
                <div class="flex items-center gap-4 mt-6 pt-4">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded">{{ __('messages.update') }}</button>
                    <a href="{{ route('reception.appointments.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-2 px-6 rounded">{{ __('messages.cancel') }}</a>
                </div>
            </div>
        </form>
    </div>
</main>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const patientSelect = document.getElementById('patient-select');
            const genderInput = document.getElementById('selected-patient-gender');
            if (patientSelect && genderInput) {
                function updateGender() {
                    const selected = patientSelect.options[patientSelect.selectedIndex];
                    genderInput.value = selected ? (selected.dataset.gender || '') : '';
                }
                patientSelect.addEventListener('change', updateGender);
                updateGender();
            }
        });
    </script>
@endsection
