<x-app-layout>
        <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('messages.patient_dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if($patient)
                <!-- Welcome Section -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-2xl font-bold text-gray-900">{{ __('messages.welcome_patient', ['name' => $patient->name]) }}</h3>
                        <p class="text-gray-600 mt-1">{{ __('messages.phone') }}: {{ $patient->phone }}</p>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <!-- Upcoming Appointments -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-600">{{ __('messages.upcoming_appointments') }}</p>
                                    <p class="text-2xl font-bold text-gray-900">{{ $upcomingAppointments }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Completed Appointments -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-600">{{ __('messages.completed_appointments') }}</p>
                                    <p class="text-2xl font-bold text-gray-900">{{ $completedAppointments }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('messages.quick_actions') }}</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <a href="{{ route('patient.appointments.index') }}" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                                <svg class="h-8 w-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <div class="mr-4">
                                    <p class="font-semibold text-gray-900">{{ __('messages.my_appointments') }}</p>
                                    <p class="text-sm text-gray-600">{{ __('messages.view_all_appointments') }}</p>
                                </div>
                            </a>

                            <a href="{{ route('patient.prescriptions.index') }}" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                                <svg class="h-8 w-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <div class="mr-4">
                                    <p class="font-semibold text-gray-900">{{ __('messages.prescriptions') }}</p>
                                    <p class="text-sm text-gray-600">{{ __('messages.view_my_prescriptions') }}</p>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Upcoming Appointments -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">{{ __('messages.upcoming_appointments') }}</h3>
                            <a href="{{ route('patient.appointments.index') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                {{ __('messages.view_all') }}
                            </a>
                        </div>
                        <div class="space-y-4">
                            @forelse($recentAppointments as $appointment)
                                <div class="border border-gray-200 rounded-lg p-4">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <p class="font-medium text-gray-900">{{ $appointment->appointment_date->format('Y-m-d') }} - {{ $appointment->appointment_time }}</p>
                                            <p class="text-sm text-gray-600">د. {{ $appointment->doctor->user->name }}</p>
                                            <p class="text-sm text-gray-600 mt-1">التخصص: {{ $appointment->doctor->specialization }}</p>
                                            <p class="text-sm text-gray-700 mt-2">رقم المريض: {{ $patient->patient_code ?? $patient->id ?? '—' }}</p>
                                        </div>
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            {{ __('messages.status_scheduled') }}
                                        </span>
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-gray-500 text-center py-4">{{ __('messages.no_upcoming_appointments') }}</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            @else
                <!-- No Patient Profile -->
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="inline-gap-end">
                            <h3 class="text-sm font-medium text-yellow-800">{{ __('messages.no_patient_profile') }}</h3>
                            <p class="mt-2 text-sm text-yellow-700">
                                {{ __('messages.contact_reception_create_patient') }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
