@extends('layouts.doctor')

@section('title', __('messages.thera') . ' - ' . __('messages.doctor') . ' - ' . __('messages.patient'))


@section('content')
<main class="flex-1">
    <div class="container mx-auto px-4 " style="padding-bottom: 16px;">
        <div class="flex items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-title text-3xl font-bold text-gray-900">{{ __('messages.patient_record') }}</h1>
                @if(!empty($appointment?->patient?->name))
                    <div class="text-titlesticky  text-gray-500 text-sm">{{ $appointment->patient->name }}</div>
                @endif
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('doctor.patients-records') }}" class="inline-flex items-center justify-center px-4 py-2 rounded-md bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium transition focus:outline-none focus:ring-2 focus:ring-gray-200 whitespace-nowrap">
                    {{ __('messages.back') }}
                </a>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-lg border p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4 border-b border-gray-200 pb-2">{{ __('messages.appointment_details') }}</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-gray-600 font-semibold mb-1">{{ __('messages.appointment_id') }}</label>
                        <p class="text-gray-900">{{ $appointment->id }}</p>
                    </div>
                    <div>
                        <label class="block text-gray-600 font-semibold mb-1">{{ __('messages.date_booking') }}</label>
                        <p class="text-gray-900">{{ optional($appointment->scheduled_at)->format('Y-m-d') ?? ( $appointment->date ? $appointment->date : '-' ) }}</p>
                    </div>
                    <div>
                        <label class="block text-gray-600 font-semibold mb-1">{{ __('messages.time') }}</label>
                        <p class="text-gray-900">{{ optional($appointment->scheduled_at)->format('g:i A') ?? ( $appointment->time ? substr($appointment->time,0,5) : '-' ) }}</p>
                    </div>
                    <div>
                        <label class="block text-gray-600 font-semibold mb-1">{{ __('messages.status') }}</label>
                        <p class="text-gray-900">{{ __('messages.status_' . strtolower($appointment->status)) }}</p>
                    </div>
                </div>
            </div>

            <div id="appointments" class="mt-8 bg-white rounded-lg border p-6" style="margin-bottom: 40px">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-900">{{ __('messages.appointments_history') }}</h2>
                </div>

                <div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.appointment_id') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.date_booking') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.time') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.status') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($patientHistory as $h)
                                    @php
                                        $dateLabel = '';
                                        $timeLabel = '';

                                        if (!empty($h->scheduled_at)) {
                                            $dateLabel = $h->scheduled_at->format('Y-m-d');
                                            $timeLabel = $h->scheduled_at->format('g:i A');
                                        } else {
                                            $d = $h->date ?? null;
                                            $t = $h->time ?? null;

                                            if ($d) $dateLabel = $d instanceof \Carbon\CarbonInterface ? $d->format('Y-m-d') : (string) $d;
                                            if ($d && $t) $timeLabel = \Carbon\Carbon::parse($d . ' ' . $t)->format('g:i A');
                                            elseif ($t) $timeLabel = \Carbon\Carbon::createFromFormat('H:i', substr((string)$t,0,5))->format('g:i A');
                                        }
                                    @endphp

                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $h->id }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $dateLabel }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $timeLabel }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-700">
                                                {{ __('messages.status_' . strtolower($h->status)) }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-6 text-center text-sm text-gray-500">{{ __('messages.no_appointments_found') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection
