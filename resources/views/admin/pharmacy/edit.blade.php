@extends('layouts.app')

@section('title', __('messages.edit_medicine') . ' | ' . config('app.name', 'Hospital Management'))

@section('content')
<div class="container mx-auto px-4 py-8 max-w-2xl">
    <h1 class="text-3xl font-bold mb-6">تعديل بيانات الدواء</h1>

    @if ($errors->any())
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('pharmacy.update', $pharmacy) }}" method="POST" class="bg-white shadow-md rounded px-8 pt-6 pb-8">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2">اسم الدواء *</label>
            <input type="text" name="name" value="{{ old('name', $pharmacy->name) }}" required
                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 @error('name') border-red-500 @enderror">
            @error('name') <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">SKU *</label>
                <input type="text" name="sku" value="{{ old('sku', $pharmacy->sku) }}" required
                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 @error('sku') border-red-500 @enderror">
                @error('sku') <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">السعر (₪) *</label>
                <input type="number" name="unit_price" value="{{ old('unit_price', $pharmacy->unit_price) }}" required step="0.01" min="0"
                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 @error('unit_price') border-red-500 @enderror">
                @error('unit_price') <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2">تاريخ انتهاء الصلاحية</label>
            <input type="date" name="expiry_date" value="{{ old('expiry_date', $pharmacy->expiry_date?->format('Y-m-d')) }}"
                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 @error('expiry_date') border-red-500 @enderror">
            @error('expiry_date') <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="border-t pt-4 mb-4">
            <h3 class="text-lg font-bold mb-3">معلومات المخزون</h3>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">الكمية الحالية *</label>
                    <input type="number" name="quantity" value="{{ old('quantity', $pharmacy->inventory->quantity ?? 0) }}" required min="0"
                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 @error('quantity') border-red-500 @enderror">
                    @error('quantity') <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">الحد الأدنى للتنبيه *</label>
                    <input type="number" name="minimum_threshold" value="{{ old('minimum_threshold', $pharmacy->inventory->minimum_threshold ?? 10) }}" required min="0"
                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 @error('minimum_threshold') border-red-500 @enderror">
                    @error('minimum_threshold') <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="flex items-center justify-between">
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                حفظ التغييرات
            </button>
            <a href="{{ route('pharmacy.show', $pharmacy) }}" class="inline-block font-bold text-sm text-gray-500 hover:text-gray-800">
                إلغاء
            </a>
        </div>
    </form>
</div>
@endsection
