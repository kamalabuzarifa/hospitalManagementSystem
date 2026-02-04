@extends('layouts.app')

@section('title', __('messages.manage_prescriptions') . ' | ' . config('app.name', 'Hospital Management'))

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Doctor Prescriptions Only
            <span class="ml-2 inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full align-middle">Doctor Only</span>
        </h1>
        <a href="{{ route('prescriptions.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            + New Prescription
        </a>
    </div>

    @if (session('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow overflow-x-auto">
        <table class="min-w-full">
            <thead class="bg-gray-100 border-b">
                <tr>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">ID</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Patient</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Doctor</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Appointment Date</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Created Date</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($prescriptions as $prescription)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $prescription->id }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $prescription->appointment->patient->name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">Dr. {{ $prescription->doctor?->user?->name ?? '' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $prescription->appointment->scheduled_at->format('Y-m-d') }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $prescription->created_at->format('Y-m-d') }}</td>
                        <td class="px-6 py-4 text-sm">
                            <a href="{{ route('prescriptions.show', $prescription) }}" class="text-blue-600 hover:text-blue-900 mr-2">{{ __('messages.view') }}</a>
                            <a href="{{ route('prescriptions.edit', $prescription) }}" class="text-yellow-600 hover:text-yellow-900 mr-2">{{ __('messages.edit') }}</a>
                            <form action="{{ route('prescriptions.destroy', $prescription) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('messages.are_you_sure') }}')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900">{{ __('messages.delete') }}</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">No prescriptions found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        <div class="div-all-accessLogs p-4 border-t border-gray-100">{{ $prescriptions->links() }}</div>
    </div>
</div>
@endsection
