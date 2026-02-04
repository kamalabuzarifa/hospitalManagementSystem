@extends('layouts.app')

@section('title', __('messages.edit_patient') . ' | ' . config('app.name', 'Hospital Management'))

@section('content')
<div class="container mx-auto px-4 py-8 max-w-2xl">
    <h1 class="text-3xl font-bold mb-6">تعديل بيانات المريض</h1>

    @if ($errors->any())
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('patients.update', $patient) }}" method="POST" class="bg-white shadow-md rounded px-8 pt-6 pb-8">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2">الاسم الكامل *</label>
            <input type="text" name="name" value="{{ old('name', $patient->name) }}" required
                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 @error('name') border-red-500 @enderror">
            @error('name') <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">تاريخ الميلاد</label>
                <input type="date" name="dob" value="{{ old('dob', $patient->dob?->format('Y-m-d')) }}"
                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 @error('dob') border-red-500 @enderror">
                @error('dob') <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">الجنس</label>
                <select name="gender" class="shadow border rounded w-full py-2 px-3 text-gray-700 @error('gender') border-red-500 @enderror">
                    <option value="">اختر...</option>
                    <option value="male" {{ old('gender', $patient->gender) == 'male' ? 'selected' : '' }}>ذكر</option>
                    <option value="female" {{ old('gender', $patient->gender) == 'female' ? 'selected' : '' }}>أنثى</option>
                    <option value="other" {{ old('gender', $patient->gender) == 'other' ? 'selected' : '' }}>آخر</option>
                </select>
                @error('gender') <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2">رقم الهاتف</label>
            <input type="tel" name="phone" value="{{ old('phone', $patient->phone) }}"
                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 @error('phone') border-red-500 @enderror">
            @error('phone') <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2">العنوان</label>
            <textarea name="address" rows="2"
                      class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 @error('address') border-red-500 @enderror">{{ old('address', $patient->address) }}</textarea>
            @error('address') <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="mb-6">
            <label class="block text-gray-700 text-sm font-bold mb-2">التاريخ المرضي</label>
            <textarea name="medical_history" rows="4"
                      class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 @error('medical_history') border-red-500 @enderror">{{ old('medical_history', $patient->medical_history) }}</textarea>
            @error('medical_history') <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="flex items-center justify-between">
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                حفظ التغييرات
            </button>
            <a href="{{ route('patients.show', $patient) }}" class="inline-block font-bold text-sm text-gray-500 hover:text-gray-800">
                إلغاء
            </a>
        </div>
    </form>
</div>
@endsection
