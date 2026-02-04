@extends('layouts.app')

@section('title', __('messages.view_patient') . ' | ' . config('app.name', 'Hospital Management'))

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <a href="{{ route('patients.index') }}" class="text-blue-500 hover:text-blue-700">&larr; العودة للقائمة</a>
    </div>

    @if (session('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-gray-500 text-sm font-semibold uppercase mb-2">إجمالي المواعيد</h3>
            <p class="text-3xl font-bold text-blue-600">{{ $patient->appointments->count() }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-gray-500 text-sm font-semibold uppercase mb-2">المواعيد القادمة</h3>
            <p class="text-3xl font-bold text-green-600">
                {{ $patient->appointments->where('scheduled_at', '>', now())->count() }}
            </p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-gray-500 text-sm font-semibold uppercase mb-2">المواعيد المكتملة</h3>
            <p class="text-3xl font-bold text-gray-600">
                {{ $patient->appointments->where('status', 'completed')->count() }}
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold">المعلومات الشخصية</h2>
                <a href="{{ route('patients.edit', $patient) }}" class="text-blue-500 hover:text-blue-700 text-sm">تعديل</a>
            </div>
            <dl class="space-y-3">
                <div class="flex justify-between">
                    <dt class="font-semibold text-gray-700">الاسم:</dt>
                    <dd class="text-gray-900">{{ $patient->name }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="font-semibold text-gray-700">تاريخ الميلاد:</dt>
                    <dd class="text-gray-900">{{ $patient->dob ? $patient->dob->format('Y-m-d') : '—' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="font-semibold text-gray-700">العمر:</dt>
                    <dd class="text-gray-900">{{ $patient->age ?? '—' }} سنة</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="font-semibold text-gray-700">الجنس:</dt>
                    <dd class="text-gray-900">
                        @if($patient->gender == 'male') ذكر
                        @elseif($patient->gender == 'female') أنثى
                        @elseif($patient->gender == 'other') آخر
                        @else — @endif
                    </dd>
                </div>
                <div class="flex justify-between">
                    <dt class="font-semibold text-gray-700">الهاتف:</dt>
                    <dd class="text-gray-900">{{ $patient->phone ?? '—' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="font-semibold text-gray-700">العنوان:</dt>
                    <dd class="text-gray-900">{{ $patient->address ?? '—' }}</dd>
                </div>
            </dl>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-bold mb-4">التاريخ المرضي</h2>
            <p class="text-gray-700 whitespace-pre-line">{{ $patient->medical_history ?? 'لا توجد معلومات' }}</p>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-bold mb-4">سجل المواعيد</h2>
        @if($patient->appointments->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">التاريخ</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الطبيب</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">رقم المريض</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الحالة</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($patient->appointments->sortByDesc('scheduled_at') as $appointment)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $appointment->scheduled_at->format('Y-m-d H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $appointment->doctor?->user?->name ?? '—' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $patient->patient_code ?? $patient->id ?? '—' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold
                                        {{ $appointment->status === 'scheduled' ? 'bg-blue-100 text-blue-800' : '' }}
                                        {{ $appointment->status === 'checked_in' ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $appointment->status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}
                                        {{ $appointment->status === 'completed' ? 'bg-gray-100 text-gray-800' : '' }}
                                    ">
                                        {{ $appointment->status_label }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-gray-500 text-center py-4">لا توجد مواعيد</p>
        @endif
    </div>
</div>
@endsection
