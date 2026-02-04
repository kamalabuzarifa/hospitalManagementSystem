<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('وصفاتي الطبية') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if(isset($patient))
                        <div class="space-y-4">
                            @forelse($prescriptions as $appointment)
                                @if($appointment->prescription)
                                    <div class="border border-gray-200 rounded-lg p-6">
                                        <div class="flex justify-between items-start mb-4">
                                            <div>
                                                <h3 class="text-lg font-semibold text-gray-900">
                                                    {{ $appointment->appointment_date->format('Y-m-d') }}
                                                </h3>
                                                <p class="text-sm text-gray-600 mt-1">د. {{ $appointment->doctor->user->name }}</p>
                                                <p class="text-sm text-gray-600">{{ $appointment->doctor->specialization }}</p>
                                            </div>
                                            <span class="px-3 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-800">
                                                وصفة طبية
                                            </span>
                                        </div>

                                        <div class="border-t pt-4">
                                            <p class="text-sm font-semibold text-gray-700 mb-2">الأدوية:</p>
                                            <div class="bg-gray-50 p-3 rounded-md">
                                                <p class="text-sm text-gray-900 whitespace-pre-line">{{ Str::limit($appointment->prescription->medications, 200) }}</p>
                                            </div>
                                        </div>

                                        <div class="mt-4 flex justify-end">
                                            <a href="{{ route('patient.prescriptions.show', $appointment->id) }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                                عرض التفاصيل الكاملة →
                                            </a>
                                        </div>
                                    </div>
                                @endif
                            @empty
                                <div class="text-center py-12">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">لا توجد وصفات طبية</h3>
                                    <p class="mt-1 text-sm text-gray-500">ليس لديك أي وصفات طبية حالياً</p>
                                </div>
                            @endforelse
                        </div>

                        @if($prescriptions->hasPages())
                            <div class="mt-6">
                                <div class="div-all-accessLogs p-4 border-t border-gray-100">{{ $prescriptions->links() }}</div>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-12">
                            <p class="text-gray-600">يرجى التواصل مع الاستقبال لإنشاء ملف مريض</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
