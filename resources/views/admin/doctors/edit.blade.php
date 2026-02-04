<x-admin-layout>
@section('title', __('messages.edit_doctor') . ' | ' . config('app.name', 'Hospital Management'))
<div class="py-8 px-8">
    <div class="max-w-3xl mx-auto">
        <div class="bg-white shadow-md rounded px-8 pt-6 pb-8">
            <h1 class="text-3xl font-bold mb-6">تعديل بيانات الطبيب</h1>

    @if ($errors->any())
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.doctors.update', $doctor) }}" method="POST" class="space-y-4">
        @csrf
        @method('PUT')

        <div class="mb-4 p-4 bg-gray-50 rounded">
            <p class="text-sm text-gray-600"><strong>الطبيب:</strong> {{ $doctor->user->name }}</p>
            <p class="text-sm text-gray-600"><strong>البريد:</strong> {{ $doctor->user->email }}</p>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2">التخصص *</label>
            <input type="text" name="specialization" value="{{ old('specialization', $doctor->specialization) }}" required
                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 @error('specialization') border-red-500 @enderror">
            @error('specialization') <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2">ساعات العمل</label>
            <input type="text" name="working_hours" value="{{ old('working_hours', $doctor->working_hours) }}"
                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 @error('working_hours') border-red-500 @enderror">
            @error('working_hours') <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="mb-6">
            <label class="block text-gray-700 text-sm font-bold mb-2">السيرة الذاتية</label>
            <textarea name="bio" rows="4"
                      class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 @error('bio') border-red-500 @enderror">{{ old('bio', $doctor->bio) }}</textarea>
            @error('bio') <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="flex items-center justify-between pt-4">
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                حفظ التغييرات
            </button>
            <a href="{{ route('admin.doctors.show', $doctor) }}" class="inline-block font-bold text-sm text-gray-500 hover:text-gray-800">
                إلغاء
            </a>
        </div>
    </form>
        </div>
    </div>
</div>
</x-admin-layout>
