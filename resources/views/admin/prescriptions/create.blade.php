@extends('layouts.app')

@section('title', __('messages.add_prescription') . ' | ' . config('app.name', 'Hospital Management'))

@section('content')
<div class="container mx-auto px-4 py-8 max-w-2xl">
    <h1 class="text-3xl font-bold mb-6">إضافة وصفة طبية</h1>

    @if ($errors->any())
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('prescriptions.store') }}" method="POST" class="bg-white shadow-md rounded px-8 pt-6 pb-8">
        @csrf

        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2">الموعد *</label>
            <select name="appointment_id" required class="shadow border rounded w-full py-2 px-3 text-gray-700 @error('appointment_id') border-red-500 @enderror">
                <option value="">-- اختر موعد مكتمل --</option>
                @foreach($appointments as $appointment)
                    <option value="{{ $appointment->id }}" {{ old('appointment_id') == $appointment->id ? 'selected' : '' }}>
                        {{ $appointment->patient->name }} - د. {{ $appointment->doctor?->user?->name }} 
                        ({{ $appointment->scheduled_at->format('Y-m-d') }})
                    </option>
                @endforeach
            </select>
            @error('appointment_id') <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2">الطبيب *</label>
            <select name="doctor_id" required class="shadow border rounded w-full py-2 px-3 text-gray-700 @error('doctor_id') border-red-500 @enderror">
                <option value="">-- اختر طبيب --</option>
                @foreach(\App\Models\User::whereHas('role', fn($q) => $q->where('name', 'doctor'))->get() as $doctor)
                    <option value="{{ $doctor->id }}" {{ old('doctor_id') == $doctor->id ? 'selected' : '' }}>
                        د. {{ $doctor->name }}
                    </option>
                @endforeach
            </select>
            @error('doctor_id') <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="mb-6">
            <label class="block text-gray-700 text-sm font-bold mb-2">الوصفة الطبية والملاحظات *</label>
            <textarea name="notes" rows="10" required
                      placeholder="التشخيص، الأدوية، الجرعات، التعليمات..."
                      class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 @error('notes') border-red-500 @enderror">{{ old('notes') }}</textarea>
            @error('notes') <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="flex items-center justify-between">
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                حفظ الوصفة
            </button>
            <a href="{{ route('prescriptions.index') }}" class="inline-block font-bold text-sm text-gray-500 hover:text-gray-800">
                إلغاء
            </a>
        </div>
    </form>
</div>
@endsection
