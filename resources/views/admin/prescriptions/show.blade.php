@extends('layouts.app')

@section('title', __('messages.view_prescription') . ' | ' . config('app.name', 'Hospital Management'))

@section('content')
<div class="container mx-auto px-4 py-8 max-w-4xl">
    <div class="mb-6">
        <a href="{{ route('prescriptions.index') }}" class="text-blue-500 hover:text-blue-700">&larr; العودة للقائمة</a>
    </div>

    @if (session('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-lg p-8">
        <div class="flex justify-between items-start mb-6">
            <div>
                <h1 class="text-3xl font-bold mb-2">وصفة طبية #{{ $prescription->id }}</h1>
                <p class="text-gray-600">{{ $prescription->created_at->format('Y-m-d H:i') }}</p>
            </div>
            <a href="{{ route('prescriptions.edit', $prescription) }}" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded">
                تعديل
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6 pb-6 border-b">
            <div>
                <h3 class="text-lg font-bold mb-3 text-gray-700">معلومات المريض</h3>
                <dl class="space-y-2">
                    <div>
                        <dt class="font-semibold text-gray-600 text-sm">الاسم:</dt>
                        <dd class="text-gray-900">{{ $prescription->appointment->patient->name }}</dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-gray-600 text-sm">العمر:</dt>
                        <dd class="text-gray-900">{{ $prescription->appointment->patient->age ?? '—' }} سنة</dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-gray-600 text-sm">الهاتف:</dt>
                        <dd class="text-gray-900">{{ $prescription->appointment->patient->phone ?? '—' }}</dd>
                    </div>
                </dl>
            </div>

            <div>
                <h3 class="text-lg font-bold mb-3 text-gray-700">معلومات الطبيب</h3>
                <dl class="space-y-2">
                    <div>
                        <dt class="font-semibold text-gray-600 text-sm">الطبيب:</dt>
                        <dd class="text-gray-900">د. {{ $prescription->doctor?->user?->name ?? '' }}</dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-gray-600 text-sm">تاريخ الموعد:</dt>
                        <dd class="text-gray-900">{{ $prescription->appointment->scheduled_at->format('Y-m-d H:i') }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        <div>
            <h3 class="text-xl font-bold mb-4 text-gray-700">الوصفة الطبية</h3>
            <div class="bg-gray-50 p-6 rounded-lg border-2 border-gray-200">
                <pre class="whitespace-pre-wrap text-gray-800 font-mono text-sm">{{ $prescription->notes }}</pre>
            </div>
        </div>

        @if($prescription->appointment->patient->medical_history)
            <div class="mt-6 pt-6 border-t">
                <h3 class="text-lg font-bold mb-3 text-gray-700">التاريخ المرضي</h3>
                <p class="text-gray-700 bg-yellow-50 p-4 rounded border border-yellow-200">
                    {{ $prescription->appointment->patient->medical_history }}
                </p>
            </div>
        @endif
    </div>
</div>
@endsection
