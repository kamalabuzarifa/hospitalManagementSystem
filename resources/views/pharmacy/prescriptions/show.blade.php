
@extends('layouts.pharmacy')
@section('title',__('messages.thera') . ' - ' . __('messages.pharmacy') . ' - ' .  __('messages.prescription_details') . ' - ' . (($prescription->appointment->patient->name ??"") ))

@section('content')
<main class="flex-1">
    <div class="container mx-auto px-4" style="padding-bottom: 16px !important;">
        <h2 class="text-2xl font-bold mb-8">{{ __('messages.prescription_details') }}</h2>
        <div>
            @if (session('success'))
                <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-6">
                    <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                </div>
            @endif
            @if (session('error'))
                <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
                    <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                </div>
            @endif

            <!-- Patient Information -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('messages.patient_information') }}</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('messages.name') }}</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $prescription->appointment->patient->name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('messages.phone') }}</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $prescription->appointment->patient->phone }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('messages.doctor') }}</label>
                            <p class="mt-1 text-sm text-gray-900">{{ __('messages.dr') }} {{ $prescription->doctor?->user?->name ?? '' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('messages.date') }}</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $prescription->created_at->format('Y-m-d') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Prescription Details -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('messages.prescription_details_heading') }}</h3>
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.medicines_label') }}</label>
                        @if($prescription->medicines->count() > 0)
                            <ul class="list-disc list-inside space-y-1">
                                @foreach($prescription->medicines as $medicine)
                                    <li class="text-sm text-gray-900">{{ $medicine->name }} - {{ $medicine->sku }}</li>
                                @endforeach
                            </ul>
                        @else
                            <div class="bg-gray-50 p-4 rounded-md">
                                <p class="text-sm text-gray-900 whitespace-pre-line">{{ $prescription->medicine }}</p>
                            </div>
                        @endif
                    </div>

                    @if($prescription->notes)
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.notes') }}</label>
                            <div class="bg-gray-50 p-4 rounded-md">
                                <p class="text-sm text-gray-900 whitespace-pre-line">{{ $prescription->notes }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Fulfillment Status -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('messages.fulfillment_status') }}</h3>
                    @if($prescription->medicines->count() > 0)
                        <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                    <div class="inline-gap-end">
                                    <p class="text-sm font-medium text-green-800">
                                        {{ __('messages.dispensed_label') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                    <div class="inline-gap-end">
                                    <p class="text-sm font-medium text-yellow-800">
                                        {{ __('messages.pending_dispense_label') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="mt-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <a href="{{ route('pharmacy.prescriptions.index') }}" class="w-full sm:w-40 h-10 inline-flex items-center justify-center bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-md font-medium">
                            ← {{ __('messages.back_to_list') }}
                        </a>

                        <div class="flex w-full sm:w-auto gap-3 justify-end">
                            @if($prescription->medicines->count() === 0)
                                <form method="POST" action="{{ route('pharmacy.prescriptions.dispense', $prescription) }}" class="w-full sm:w-auto">
                                    @csrf
                                    <button class="w-full sm:w-40 h-10 inline-flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-md">
                                        {{ __('messages.dispense_prescription') }}
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection
