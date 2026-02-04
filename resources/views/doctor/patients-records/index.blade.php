@extends('layouts.doctor')

@section('title', __('messages.thera') . ' - ' . __('messages.doctor') . ' - ' . __('messages.patients_records'))


@section('content')
    <main class="flex-1">
        <div class="container mx-auto px-4 " style="padding-bottom: 10px;">
            <h1 class="text-title text-3xl font-bold text-gray-900 mb-6">{{ __('messages.patients_records') }}</h1>
            @php $isRtl = app()->getLocale() === 'ar'; @endphp
            <div class="bg-white rounded-lg border p-6 mb-6" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
                <form method="GET" action="{{ route('doctor.patients-records') }}" autocomplete="off">
                    <div class="flex flex-col md:flex-row md:items-center gap-3">
                        <div class="relative flex-1">
                            <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-gray-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 104.5 4.5a7.5 7.5 0 0012.15 12.15z" />
                                </svg>
                            </span>
                            <input
                                id="search"
                                name="search"
                                value="{{ request('search') }}"
                                type="text"
                                placeholder="{{ __('messages.search_by_name_or_id') }}"
                                class="w-full pl-10 pr-4 py-3 border-0 rounded-lg bg-gray-100 focus:bg-white focus:ring-2 focus:ring-blue-200 focus:outline-none text-gray-700 placeholder-gray-400"
                            />
                        </div>

                        @if(request()->filled('search'))
                            <a href="{{ route('doctor.patients-records') }}" class="inline-flex items-center justify-center px-5 py-3 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold transition focus:outline-none focus:ring-2 focus:ring-gray-200 whitespace-nowrap">
                                {{ __('messages.reset') }}
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <div class="bg-white rounded-lg border">
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full table-fixed divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="w-28 px-6 py-3 {{ $isRtl ? 'text-right' : 'text-left' }} text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.patient_code') }}</th>
                                    <th class="w-1/3 px-6 py-3 {{ $isRtl ? 'text-right' : 'text-left' }} text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.patient_name') }}</th>
                                    <th class="w-36 px-6 py-3 {{ $isRtl ? 'text-right' : 'text-left' }} text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.date_of_birth') }}</th>
                                    <th class="w-28 px-6 py-3 {{ $isRtl ? 'text-right' : 'text-left' }} text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.gender') }}</th>
                                    <th class="w-36 px-6 py-3 {{ $isRtl ? 'text-right' : 'text-left' }} text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.status') }}</th>
                                    <th class="w-28 px-6 py-3 {{ $isRtl ? 'text-right' : 'text-left' }} text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.action') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($patients as $patient)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $patient->patient_code ?? $patient->id }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-900 max-w-xs truncate">{{ $patient->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $patient->dob ? $patient->dob->format('Y-m-d') : '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $patient->gender ? __("messages.gender_" . strtolower($patient->gender)) : '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <span class="inline-flex px-2 text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-700">
                                                {{ !empty($patient->latest_appointment_status) ? __('messages.status_' . strtolower($patient->latest_appointment_status)) : '-' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="{{ route('doctor.patients.show', ['patient' => $patient->id, 'back' => url()->full()]) }}" class="text-blue-600 hover:text-blue-900">{{ __('messages.view') }}</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-6 text-center text-sm text-gray-500">{{ __('messages.no_patient_records_found') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if(method_exists($patients, 'links'))
                        <div class="mt-4">
                            <div class="div-all-accessLogs p-4 border-t border-gray-100">{{ $patients->links() }}</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </main>
@endsection
