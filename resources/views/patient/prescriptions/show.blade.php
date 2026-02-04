<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('تفاصيل الوصفة الطبية') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Appointment Info -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">معلومات الموعد</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">التاريخ</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $appointment->appointment_date->format('Y-m-d') }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">الوقت</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $appointment->appointment_time }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">الطبيب</label>
                            <p class="mt-1 text-sm text-gray-900">د. {{ $appointment->doctor->user->name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">التخصص</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $appointment->doctor->specialization }}</p>
                        </div>
                    </div>
                </div>
            </div>

            @if($appointment->prescription)
                <!-- Prescription Details -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">الوصفة الطبية</h3>
                        
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">الأدوية والجرعات</label>
                            <div class="bg-blue-50 border border-blue-200 p-4 rounded-md">
                                <p class="text-sm text-gray-900 whitespace-pre-line">{{ $appointment->prescription->medications }}</p>
                            </div>
                        </div>

                        @if($appointment->prescription->instructions)
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">التعليمات</label>
                                <div class="bg-green-50 border border-green-200 p-4 rounded-md">
                                    <p class="text-sm text-gray-900 whitespace-pre-line">{{ $appointment->prescription->instructions }}</p>
                                </div>
                            </div>
                        @endif

                        @if($appointment->prescription->notes)
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">ملاحظات الطبيب</label>
                                <div class="bg-yellow-50 border border-yellow-200 p-4 rounded-md">
                                    <p class="text-sm text-gray-900 whitespace-pre-line">{{ $appointment->prescription->notes }}</p>
                                </div>
                            </div>
                        @endif

                        <div class="bg-gray-50 border-l-4 border-gray-400 p-4 mt-6">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="inline-gap-end">
                                    <p class="text-sm text-gray-700">
                                        <strong>ملاحظة:</strong> يُرجى الالتزام بالجرعات والتعليمات المذكورة. في حالة وجود أي آثار جانبية أو استفسارات، يُرجى التواصل مع الطبيب فوراً.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Print Button -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center">
                            <a href="{{ route('patient.prescriptions.index') }}" class="text-gray-600 hover:text-gray-900">
                                ← العودة للقائمة
                            </a>
                            <button
                                onclick="window.print()"
                                class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md flex items-center"
                            >
                                <svg class="h-5 w-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                </svg>
                                طباعة الوصفة
                            </button>
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
                            <p class="text-sm text-yellow-700">
                                لا توجد وصفة طبية لهذا الموعد
                            </p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
