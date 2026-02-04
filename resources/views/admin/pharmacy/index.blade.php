@extends('layouts.app')

@section('title', __('messages.manage_medicines') . ' | ' . config('app.name', 'Hospital Management'))

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">إدارة الصيدلية والمخزون</h1>
        <a href="{{ route('admin.pharmacy.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            + إضافة دواء
        </a>
    </div>

    @if (session('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <form action="{{ route('admin.pharmacy.index') }}" method="GET" class="grid grid-cols-3 gap-3">
            <input type="text" name="search" placeholder="ابحث عن اسم أو SKU..." value="{{ request('search') }}" 
                   class="px-4 py-2 border rounded">
            
            <div class="flex items-center">
                <input type="checkbox" name="low_stock" id="low_stock" value="1" {{ request('low_stock') ? 'checked' : '' }}
                       class="mr-2">
                <label for="low_stock" class="text-sm">عرض المخزون المنخفض فقط</label>
            </div>

            <button type="submit" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                بحث
            </button>
        </form>
    </div>

    <div class="bg-white rounded-lg shadow overflow-x-auto">
        <table class="min-w-full">
            <thead class="bg-gray-100 border-b">
                <tr>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الاسم</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">SKU</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">السعر</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الكمية</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الحد الأدنى</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">تاريخ الانتهاء</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الحالة</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الإجراءات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($medicines as $medicine)
                    <tr class="hover:bg-gray-50 {{ $medicine->isLowStock() ? 'bg-red-50' : '' }}">
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $medicine->name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $medicine->sku }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ number_format($medicine->unit_price, 2) }} ₪</td>
                        <td class="px-6 py-4 text-sm text-gray-900 font-bold">
                            {{ $medicine->inventory->quantity ?? 0 }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ $medicine->inventory->minimum_threshold ?? 0 }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ $medicine->expiry_date ? $medicine->expiry_date->format('Y-m-d') : '—' }}
                        </td>
                        <td class="px-6 py-4 text-sm">
                            @if($medicine->isExpired())
                                <span class="px-2 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800">
                                    منتهي الصلاحية
                                </span>
                            @elseif($medicine->isLowStock())
                                <span class="px-2 py-1 rounded-full text-xs font-semibold bg-orange-100 text-orange-800">
                                    مخزون منخفض
                                </span>
                            @else
                                <span class="px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                    متوفر
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <a href="{{ route('admin.pharmacy.show', $medicine) }}" class="text-blue-600 hover:text-blue-900 mr-2">عرض</a>
                            <a href="{{ route('admin.pharmacy.edit', $medicine) }}" class="text-yellow-600 hover:text-yellow-900 mr-2">تعديل</a>
                            <form action="{{ route('admin.pharmacy.destroy', $medicine) }}" method="POST" class="inline" onsubmit="return confirm('هل أنت متأكد؟')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900">حذف</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-4 text-center text-gray-500">لا توجد أدوية</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        <div class="div-all-accessLogs p-4 border-t border-gray-100">{{ $medicines->links() }}</div>
    </div>
</div>
@endsection
