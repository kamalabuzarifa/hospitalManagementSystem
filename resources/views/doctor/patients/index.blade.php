@extends('layouts.doctor')

@section('title', __('messages.patients'))

@section('content')
<main class="flex-1">
    <div class="container mx-auto px-4" style="padding-bottom: 10px;">
        <div class="flex items-center justify-between gap-4 mb-6">
            <h1 class="text-3xl font-bold text-gray-900">{{ __('messages.patients') }}</h1>
            <a href="{{ route('doctor.patient-queue') }}" class="inline-flex items-center px-4 py-2 rounded-md bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium">
                {{ __('messages.patients_queue') }}
            </a>
        </div>

        @php($isRtl = app()->getLocale() === 'ar')

        <div class="bg-white rounded-lg border p-6 mb-6">
            <form method="GET" action="{{ route('doctor.patients.index') }}" autocomplete="off">
                <label class="sr-only" for="search">{{ __('messages.search') }}</label>
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 {{ $isRtl ? 'right-3' : 'left-3' }} flex items-center text-gray-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 104.5 4.5a7.5 7.5 0 0012.15 12.15z" />
                        </svg>
                    </span>
                    <input
                        id="search"
                        name="search"
                        value="{{ $search ?? request('search') }}"
                        type="text"
                        placeholder="{{ __('messages.search_by_name_or_id') }}"
                        class="w-full {{ $isRtl ? 'pr-10 pl-4' : 'pl-10 pr-4' }} py-3 border-0 rounded-lg bg-gray-100 focus:bg-white focus:ring-2 focus:ring-blue-200 focus:outline-none text-gray-700 placeholder-gray-400"
                    />
                </div>
            </form>
        </div>

        <div class="bg-white rounded-lg border">
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.patient_name') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.id') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.phone') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.action') }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($patients as $patient)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $patient->name ?? '—' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $patient->patient_code ?? $patient->id ?? '—' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $patient->phone ?? '—' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="{{ route('doctor.patients.show', ['patient' => $patient->id, 'back' => url()->full()]) }}" class="text-blue-600 hover:text-blue-900">{{ __('messages.view') }}</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-6 text-center text-sm text-gray-500">{{ __('messages.no_patient_records_found') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if(method_exists($patients, 'links'))
                    <div class="mt-4 div-all-accessLogs p-4 border-t border-gray-100">{{ $patients->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</main>
@endsection
