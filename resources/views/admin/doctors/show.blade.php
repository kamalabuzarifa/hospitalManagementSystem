<x-admin-layout>
@section('title', __('messages.view_doctor') . ' | ' . config('app.name', 'Hospital Management'))
<div class="py-8 px-8">
    <div class="max-w-5xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.doctors.index') }}" class="text-blue-500 hover:text-blue-700">&larr; العودة للقائمة</a>
    </div>

    @if (session('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-lg p-8 mb-6">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center">
                <div class="w-20 h-20 rounded-full bg-blue-100 flex items-center justify-center text-3xl font-bold text-blue-600">
                    {{ strtoupper(substr($doctor->user->name, 0, 1)) }}
                </div>
                <div class="mr-4">
                    <h1 class="text-3xl font-bold">د. {{ $doctor->user->name }}</h1>
                    <p class="text-lg text-gray-600">{{ $doctor->specialization }}</p>
                </div>
            </div>
            <a href="{{ route('admin.doctors.edit', $doctor) }}" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded">
                تعديل البيانات
            </a>
        </div>

        <div class="grid grid-cols-2 gap-6">
            <div>
                <h3 class="font-semibold text-gray-700 mb-2">البريد الإلكتروني</h3>
                <p class="text-gray-900">{{ $doctor->user->email }}</p>
            </div>
            <div>
                <h3 class="font-semibold text-gray-700 mb-2">ساعات العمل</h3>
                <p class="text-gray-900">{{ $doctor->working_hours ?? 'غير محدد' }}</p>
            </div>
        </div>

        @if($doctor->bio)
            <div class="mt-6">
                <h3 class="font-semibold text-gray-700 mb-2">السيرة الذاتية</h3>
                <p class="text-gray-700 whitespace-pre-line">{{ $doctor->bio }}</p>
            </div>
        @endif
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-2xl font-bold mb-4">المواعيد القادمة</h2>
        @if($upcomingAppointments->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">التاريخ والوقت</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">المريض</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">رقم المريض</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الحالة</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($upcomingAppointments as $appointment)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $appointment->scheduled_at->format('Y-m-d H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $appointment->patient->name }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $appointment->patient->patient_code ?? $appointment->patient_id ?? '—' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold
                                        {{ $appointment->status === 'scheduled' ? 'bg-blue-100 text-blue-800' : '' }}
                                        {{ $appointment->status === 'checked_in' ? 'bg-green-100 text-green-800' : '' }}
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
            <p class="text-gray-500 text-center py-8">لا توجد مواعيد قادمة</p>
        @endif
    </div>
    </div>
</div>
</x-admin-layout>
