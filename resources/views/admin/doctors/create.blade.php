<x-admin-layout>
@section('title', __('messages.add_doctor') . ' | ' . config('app.name', 'Hospital Management'))
<div class="py-8 px-8">
    <div class="max-w-3xl mx-auto">
        <div class="bg-white shadow-md rounded px-8 pt-6 pb-8">
            <h1 class="text-3xl font-bold mb-6">إضافة طبيب جديد</h1>
    <h1 class="text-3xl font-bold mb-6">إضافة طبيب جديد</h1>

    @if ($errors->any())
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.doctors.store') }}" method="POST" class="space-y-4">
        @csrf

        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2">المستخدم (حساب الطبيب) *</label>
            <select name="user_id" required class="shadow border rounded w-full py-2 px-3 text-gray-700 @error('user_id') border-red-500 @enderror">
                <option value="">-- اختر مستخدم --</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                        {{ $user->name }} ({{ $user->email }})
                    </option>
                @endforeach
            </select>
            @error('user_id') <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2">التخصص *</label>
            <input type="text" name="specialization" value="{{ old('specialization') }}" required
                   placeholder="مثال: طب عام، أطفال، جراحة..."
                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 @error('specialization') border-red-500 @enderror">
            @error('specialization') <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2">ساعات العمل</label>
            <input type="text" name="working_hours" value="{{ old('working_hours') }}"
                   placeholder="مثال: 9 ص - 5 م، السبت - الخميس"
                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 @error('working_hours') border-red-500 @enderror">
            @error('working_hours') <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="mb-6">
            <label class="block text-gray-700 text-sm font-bold mb-2">السيرة الذاتية</label>
            <textarea name="bio" rows="4"
                      placeholder="نبذة مختصرة عن الطبيب وخبراته..."
                      class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 @error('bio') border-red-500 @enderror">{{ old('bio') }}</textarea>
            @error('bio') <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="flex items-center justify-between pt-4">
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                حفظ
            </button>
            <a href="{{ route('admin.doctors.index') }}" class="inline-block font-bold text-sm text-gray-500 hover:text-gray-800">
                إلغاء
            </a>
        </div>
    </form>
        </div>
    </div>
</div>
</x-admin-layout>
