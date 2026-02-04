<x-app-layout>
        <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('messages.my_appointments') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if(isset($patient))
                        <div class="space-y-4">
                            @forelse($appointments as $appointment)
                                <div class="border border-gray-200 rounded-lg p-6">
                                    <div class="flex justify-between items-start mb-4">
                                        <div>
                                            <h3 class="text-lg font-semibold text-gray-900">
                                                {{ $appointment->appointment_date->format('Y-m-d') }} - {{ $appointment->appointment_time }}
                                            </h3>
                                            <p class="text-sm text-gray-600 mt-1">د. {{ $appointment->doctor->user->name }}</p>
                                            <p class="text-sm text-gray-600">{{ $appointment->doctor->specialization }}</p>
                                        </div>
                                        <div>
                                            @if($appointment->status === 'scheduled')
                                                <span class="px-3 py-1 text-sm font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                    {{ __('messages.status_scheduled') }}
                                                </span>
                                            @elseif($appointment->status === 'completed')
                                                <span class="px-3 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-800">
                                                    {{ __('messages.status_completed') }}
                                                </span>
                                            @elseif($appointment->status === 'cancelled')
                                                <span class="px-3 py-1 text-sm font-semibold rounded-full bg-red-100 text-red-800">
                                                    {{ __('messages.status_cancelled') }}
                                                </span>
                                            @else
                                                <span class="px-3 py-1 text-sm font-semibold rounded-full bg-gray-100 text-gray-800">
                                                    {{ $appointment->status }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="border-t pt-4">
                                        <p class="text-sm text-gray-700"><strong>{{ __('messages.patient_id_label') }}</strong></p>
                                        <p class="text-sm text-gray-900 mt-1">{{ $patient->patient_code ?? $patient->id ?? '—' }}</p>
                                    </div>

                                    @if($appointment->prescription)
                                        <div class="border-t mt-4 pt-4">
                                            <div class="flex justify-between items-center">
                                                <div>
                                                    <p class="text-sm font-semibold text-gray-700">{{ __('messages.prescription_label') }}</p>
                                                    <p class="text-sm text-gray-600">{{ __('messages.prescription_issued_for_appointment') }}</p>
                                                </div>
                                                <a href="{{ route('patient.prescriptions.show', $appointment->id) }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                                    {{ __('messages.view_prescription') }}
                                                </a>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <div class="text-center py-12">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('messages.no_appointments') }}</h3>
                                    <p class="mt-1 text-sm text-gray-500">{{ __('messages.no_appointments_you_have') }}</p>
                                </div>
                            @endforelse
                        </div>

                        @if($appointments->hasPages())
                            <div class="mt-6">
                                <div class="div-all-accessLogs p-4 border-t border-gray-100">{{ $appointments->links() }}</div>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-12">
                            <p class="text-gray-600">{{ __('messages.contact_reception_create_patient') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
