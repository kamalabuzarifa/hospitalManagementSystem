@extends('layouts.app')

@section('title', __('messages.edit_prescription') . ' | ' . config('app.name', 'Hospital Management'))

@section('content')
<div class="container mx-auto px-4 py-8 max-w-2xl">
    <h1 class="text-3xl font-bold mb-6">تعديل الوصفة الطبية</h1>

    @if ($errors->any())
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('prescriptions.update', $prescription) }}" method="POST" class="bg-white shadow-md rounded px-8 pt-6 pb-8">
        @csrf
        @method('PUT')

        <div class="mb-4 p-4 bg-gray-50 rounded">
            <p class="text-sm text-gray-700"><strong>المريض:</strong> {{ $prescription->appointment->patient->name }}</p>
            <p class="text-sm text-gray-700"><strong>الطبيب:</strong> د. {{ $prescription->doctor?->user?->name ?? '' }}</p>
            <p class="text-sm text-gray-700"><strong>تاريخ الموعد:</strong> {{ $prescription->appointment->scheduled_at->format('Y-m-d') }}</p>
        </div>

        <div class="mb-6">
            <label class="block text-gray-700 text-sm font-bold mb-2">الوصفة الطبية والملاحظات *</label>
            <textarea name="notes" rows="15" required
                      class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 font-mono text-sm @error('notes') border-red-500 @enderror">{{ old('notes', $prescription->notes) }}</textarea>
            @error('notes') <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="flex items-center justify-between">
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                حفظ التغييرات
            </button>
            <a href="{{ route('prescriptions.show', $prescription) }}" class="inline-block font-bold text-sm text-gray-500 hover:text-gray-800">
                إلغاء
            </a>
        </div>
    </form>
</div>
@endsection
