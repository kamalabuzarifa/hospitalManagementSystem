@extends('layouts.app')

@section('title', __('messages.manage_patients') . ' | ' . config('app.name', 'Hospital Management'))

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">{{ __('messages.manage_patients') }}</h1>
        <a href="{{ route('patients.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            + {{ __('messages.add_patient') }}
        </a>
    </div>

    @if (session('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif

    <div class="mb-6">
        <form action="{{ route('patients.index') }}" method="GET" class="flex gap-2">
            <input type="text" name="search" placeholder="{{ __('messages.search_by_name_phone') }}" value="{{ request('search') }}" 
                   class="flex-1 px-4 py-2 border rounded">
            <button type="submit" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                {{ __('messages.search') }}
            </button>
        </form>
    </div>

    <div class="bg-white rounded shadow overflow-x-auto">
        <table class="min-w-full">
            <thead class="bg-gray-100 border-b">
                <tr>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">#</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('messages.name') }}</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('messages.phone') }}</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('messages.age') }}</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('messages.gender') }}</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('messages.appointments') }}</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('messages.actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($patients as $patient)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $patient->id }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $patient->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $patient->phone ?? '—' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $patient->age ?? '—' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            @if($patient->gender == 'male') {{ __('messages.male') }}
                            @elseif($patient->gender == 'female') {{ __('messages.female') }}
                            @elseif($patient->gender == 'other') {{ __('messages.other') }}
                            @else — @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $patient->appointments->count() }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <a href="{{ route('patients.show', $patient) }}" class="text-blue-600 hover:text-blue-900 inline-gap-end">{{ __('messages.view') }}</a>
                            <a href="{{ route('patients.edit', $patient) }}" class="text-yellow-600 hover:text-yellow-900 inline-gap-end">{{ __('messages.edit') }}</a>
                            <form action="{{ route('patients.destroy', $patient) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('messages.are_you_sure') }}')">\n                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900">{{ __('messages.delete') }}</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">{{ __('messages.no_data') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        <div class="div-all-accessLogs p-4 border-t border-gray-100">{{ $patients->links() }}</div>
    </div>
</div>
@endsection
