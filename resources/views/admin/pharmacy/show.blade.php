@extends('layouts.app')

@section('title', __('messages.view_medicine') . ' | ' . config('app.name', 'Hospital Management'))

@section('content')
<div class="container mx-auto px-4 py-8 max-w-4xl">
    <div class="mb-6">
        <a href="{{ route('pharmacy.index') }}" class="text-blue-500 hover:text-blue-700">&larr; العودة للقائمة</a>
    </div>

    @if (session('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-lg p-8">
        <div class="flex justify-between items-start mb-6">
            <div>
                <h1 class="text-3xl font-bold mb-2">{{ $pharmacy->name }}</h1>
                <p class="text-gray-600">SKU: {{ $pharmacy->sku }}</p>
            </div>
            <div class="flex gap-2">
                @if($pharmacy->isExpired())
                    <span class="px-3 py-1 rounded-full text-sm font-semibold bg-red-100 text-red-800">
                        منتهي الصلاحية
                    </span>
                @elseif($pharmacy->isLowStock())
                    <span class="px-3 py-1 rounded-full text-sm font-semibold bg-orange-100 text-orange-800">
                        مخزون منخفض
                    </span>
                @else
                    <span class="px-3 py-1 rounded-full text-sm font-semibold bg-green-100 text-green-800">
                        متوفر
                    </span>
                @endif
                <a href="{{ route('pharmacy.edit', $pharmacy) }}" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded">
                    تعديل
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-6">
            <div>
                <h3 class="text-lg font-bold mb-4 text-gray-700 border-b pb-2">معلومات الدواء</h3>
                <dl class="space-y-3">
                    <div class="flex justify-between">
                        <dt class="font-semibold text-gray-600">الاسم:</dt>
                        <dd class="text-gray-900">{{ $pharmacy->name }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="font-semibold text-gray-600">SKU:</dt>
                        <dd class="text-gray-900 font-mono">{{ $pharmacy->sku }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="font-semibold text-gray-600">سعر الوحدة:</dt>
                        <dd class="text-gray-900 font-bold text-lg">{{ number_format($pharmacy->unit_price, 2) }} ₪</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="font-semibold text-gray-600">تاريخ الانتهاء:</dt>
                        <dd class="text-gray-900 {{ $pharmacy->isExpired() ? 'text-red-600 font-bold' : '' }}">
                            {{ $pharmacy->expiry_date ? $pharmacy->expiry_date->format('Y-m-d') : '—' }}
                        </dd>
                    </div>
                </dl>
            </div>

            <div>
                <h3 class="text-lg font-bold mb-4 text-gray-700 border-b pb-2">معلومات المخزون</h3>
                <dl class="space-y-3">
                    <div class="flex justify-between">
                        <dt class="font-semibold text-gray-600">الكمية الحالية:</dt>
                        <dd class="text-gray-900 font-bold text-2xl {{ $pharmacy->isLowStock() ? 'text-orange-600' : 'text-green-600' }}">
                            {{ $pharmacy->inventory->quantity ?? 0 }}
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="font-semibold text-gray-600">الحد الأدنى:</dt>
                        <dd class="text-gray-900">{{ $pharmacy->inventory->minimum_threshold ?? 0 }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="font-semibold text-gray-600">آخر تحديث:</dt>
                        <dd class="text-gray-900">{{ $pharmacy->inventory ? $pharmacy->inventory->updated_at->format('Y-m-d H:i') : '—' }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        @if($pharmacy->isLowStock())
            <div class="bg-orange-50 border-l-4 border-orange-400 p-4 mb-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-orange-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="inline-gap-end">
                        <p class="text-sm text-orange-700">
                            <strong>تحذير:</strong> المخزون منخفض! الكمية الحالية ({{ $pharmacy->inventory->quantity }}) 
                            أقل من أو يساوي الحد الأدنى ({{ $pharmacy->inventory->minimum_threshold }})
                        </p>
                    </div>
                </div>
            </div>
        @endif

        @if($pharmacy->isExpired())
            <div class="bg-red-50 border-l-4 border-red-400 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="inline-gap-end">
                        <p class="text-sm text-red-700">
                            <strong>تنبيه:</strong> هذا الدواء منتهي الصلاحية منذ {{ $pharmacy->expiry_date->diffForHumans() }}
                        </p>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
