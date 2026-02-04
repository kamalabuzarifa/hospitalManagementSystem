<x-admin-layout>
@section('title', __('messages.manage_doctors') . ' | ' . config('app.name', 'Hospital Management'))
<div class="py-8 px-8">
    <div class="max-w-7xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">{{ __('messages.manage_doctors') }}</h1>
        <a href="{{ route('admin.doctors.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            + {{ __('messages.add_doctor') }}
        </a>
    </div>

    @if (session('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($doctors as $doctor)
            <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition">
                <div class="flex items-center mb-4">
                    <div class="w-16 h-16 rounded-full bg-blue-100 flex items-center justify-center text-2xl font-bold text-blue-600">
                        {{ strtoupper(substr($doctor->user->name, 0, 1)) }}
                    </div>
                    <div class="mr-4">
                        <h3 class="text-lg font-bold">د. {{ $doctor->user->name }}</h3>
                        <p class="text-sm text-gray-600">{{ $doctor->specialization }}</p>
                    </div>
                </div>

                <div class="mb-4">
                    <p class="text-sm text-gray-600">
                        <span class="font-semibold">{{ __('messages.working_hours') }}:</span> {{ $doctor->working_hours ?? __('messages.not_specified') }}
                    </p>
                </div>

                @if($doctor->bio)
                    <p class="text-sm text-gray-700 mb-4 line-clamp-2">{{ $doctor->bio }}</p>
                @endif

                <div class="flex justify-between items-center pt-4 border-t">
                    <span class="text-sm text-gray-500">
                        {{ $doctor->appointments->count() }} {{ __('messages.appointment') }}
                    </span>
                    <div class="flex gap-2">
                        <a href="{{ route('admin.doctors.show', $doctor) }}" class="text-blue-600 hover:text-blue-900 text-sm font-medium">
                            {{ __('messages.view') }}
                        </a>
                        <a href="{{ route('admin.doctors.edit', $doctor) }}" class="text-yellow-600 hover:text-yellow-900 text-sm font-medium">
                            {{ __('messages.edit') }}
                        </a>
                        <form action="{{ route('admin.doctors.destroy', $doctor) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('messages.are_you_sure') }}')">\n                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-900 text-sm font-medium">{{ __('messages.delete') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-3 text-center py-12">
                <p class="text-gray-500 text-lg">{{ __('messages.no_doctors') }}</p>
            </div>
        @endforelse
        </div>

        <div class="mt-6">
            <div class="div-all-accessLogs p-4 border-t border-gray-100">{{ $doctors->links() }}</div>
        </div>
    </div>
</div>
</x-admin-layout>
