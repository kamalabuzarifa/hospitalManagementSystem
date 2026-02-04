@extends('layouts.reception')
@section('title', __('messages.thera') . ' - ' . __('messages.reception') . ' - ' . __('messages.show_patient', ['name' => $patient->name]))

@section('content')
<main>
    <div class="container mx-auto px-4" style="padding-bottom: 16px;">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold">{{ __('messages.patient_details') }}</h1>
            <div class="flex gap-2">
                @php($canEditPatients = ($appSettings?->allow_edit_patients ?? true))
                @if($canEditPatients)
                    <a href="{{ route('reception.patients.edit', $patient) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">{{ __('messages.edit') }}</a>
                @else
                    <button type="button" disabled title="{{ __('messages.patient_changes_disabled') }}" class="bg-gray-300 text-gray-600 font-bold py-2 px-4 rounded cursor-not-allowed">{{ __('messages.edit') }}</button>
                @endif
                <a href="{{ route('reception.patients.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-2 px-4 rounded">{{ __('messages.back') }}</a>
            </div>
        </div>

        <!-- Basic Information Section -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-black text-2xl font-semibold mb-4 border-b pb-2">{{ __('messages.basic_information') }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-gray-600 font-semibold mb-1">{{ __('messages.name') }}</label>
                    <p class="text-gray-900 text-lg">{{ $patient->name }}</p>
                </div>
                <div>
                    <label class="block text-gray-600 font-semibold mb-1">{{ __('messages.patient_code') }}</label>
                    <p class="text-gray-900 text-lg">{{ $patient->patient_code ?? '—' }}</p>
                </div>
                <div>
                    <label class="block text-gray-600 font-semibold mb-1">{{ __('messages.date_of_birth') }}</label>
                    <p class="text-gray-900">{{ $patient->dob ? \Carbon\Carbon::parse($patient->dob)->format('Y-m-d') : '—' }}</p>
                </div>
                <div>
                    <label class="block text-gray-600 font-semibold mb-1">{{ __('messages.age') }}</label>
                    <p class="text-gray-900">{{ $patient->dob ? \Carbon\Carbon::parse($patient->dob)->age . ' ' . __('messages.years') : '—' }}</p>
                </div>
                <div>
                    <label class="block text-gray-600 font-semibold mb-1">{{ __('messages.gender') }}</label>
                    <p class="text-gray-900 capitalize">{{ $patient->gender ?? '—' }}</p>
                </div>
            </div>
        </div>

        <!-- Contact Information Section -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-black text-2xl font-semibold mb-4 border-b pb-2">{{ __('messages.contact_information') }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-gray-600 font-semibold mb-1">{{ __('messages.phone') }}</label>
                    <p class="text-gray-900">{{ $patient->phone ?? '—' }}</p>
                </div>
                <div>
                    <label class="block text-gray-600 font-semibold mb-1">{{ __('messages.address') }}</label>
                    <p class="text-gray-900">{{ $patient->address ?? '—' }}</p>
                </div>
            </div>
        </div>

        <!-- Medical Information Section -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-black text-2xl font-semibold mb-4 border-b pb-2">{{ __('messages.medical_information') }}</h2>

            <!-- Allergies -->
            <div class="mb-6">
                <label class="block text-gray-600 font-semibold mb-2">{{ __('messages.allergies') }}</label>
                <div class="flex items-center gap-3 mb-2">
                        @if($patient->has_allergies)
                        <span class="inline-block bg-yellow-100 text-yellow-800 px-3 py-1 rounded font-semibold">{{ __('messages.yes') }}</span>
                    @else
                        <span class="inline-block bg-gray-100 text-gray-700 px-3 py-1 rounded font-semibold">{{ __('messages.no') }}</span>
                    @endif
                </div>
                @if($patient->allergy_details)
                    <div class="mt-2 p-3 bg-gray-50 rounded border">
                        <p class="text-gray-700">{{ $patient->allergy_details }}</p>
                    </div>
                @endif
            </div>

            <!-- Chronic Diseases -->
            <div>
                <label class="block text-gray-600 font-semibold mb-2">{{ __('messages.chronic_diseases') }}</label>
                <div class="flex items-center gap-3 mb-2">
                        @if($patient->has_chronic)
                        <span class="inline-block bg-red-100 text-red-800 px-3 py-1 rounded font-semibold">{{ __('messages.yes') }}</span>
                    @else
                        <span class="inline-block bg-gray-100 text-gray-700 px-3 py-1 rounded font-semibold">{{ __('messages.no') }}</span>
                    @endif
                </div>
                @if($patient->chronic_details)
                    <div class="mt-2 p-3 bg-gray-50 rounded border">
                        <p class="text-gray-700">{{ $patient->chronic_details }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Appointments Section -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-black text-2xl font-semibold mb-4 border-b pb-2">{{ __('messages.appointments') }}</h2>
            @if($patient->appointments->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-3 text-left text-gray-600 font-semibold">{{ __('messages.booking_date') }}</th>
                                <th class="px-4 py-3 text-left text-gray-600 font-semibold">{{ __('messages.time') }}</th>
                                <th class="px-4 py-3 text-left text-gray-600 font-semibold">{{ __('messages.doctor') }}</th>
                                <th class="px-4 py-3 text-left text-gray-600 font-semibold">{{ __('messages.status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($patient->appointments as $appointment)
                                <tr class="hover:bg-gray-50 border-b">
                                    <td class="text-titlest px-4 py-2">
                                        {{ $appointment->scheduled_at
                                            ? \Carbon\Carbon::parse($appointment->scheduled_at)->format('Y-m-d')
                                            : ($appointment->date ? \Carbon\Carbon::parse($appointment->date)->format('Y-m-d') : '—')
                                        }}
                                    </td>
                                    <td class="text-titlest px-4 py-2">
                                        {{ $appointment->scheduled_at
                                            ? \Carbon\Carbon::parse($appointment->scheduled_at)->format('H:i')
                                            : ($appointment->time ?? '—')
                                        }}
                                    </td>
                                    <td class="text-titlest px-4 py-2">{{ $appointment->doctor?->user?->name ?? '—' }}</td>
                                    <td class="text-titlest px-4 py-2">
                                        <span class="px-3 py-1 rounded text-sm font-semibold
                                            {{ $appointment->status === 'scheduled' ? 'bg-blue-100 text-blue-800' : '' }}
                                            {{ $appointment->status === 'checked_in' ? 'bg-green-100 text-green-800' : '' }}
                                            {{ $appointment->status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}
                                            {{ $appointment->status === 'completed' ? 'bg-gray-200 text-gray-800' : '' }}
                                            {{ $appointment->status === 'pending' || $appointment->status === 'missed' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                            {{ in_array($appointment->status, ['scheduled','checked_in','cancelled','completed','pending','missed'], true) ? '' : 'bg-gray-100 text-gray-700' }}
                                        ">
                                            {{ __("messages.status_" . $appointment->status) ?: ucfirst($appointment->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-gray-500 text-center py-4">No appointments found.</p>
            @endif
        </div>
    </div>

</main>
@endsection
