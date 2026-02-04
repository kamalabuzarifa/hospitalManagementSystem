@extends('layouts.admin')

@section('title', __('messages.thera') . ' - ' . __('messages.administration') . ' - ' . __('messages.reports_analytics'))

@section('content')
    <main>
        <div class="py-8 px-4 sm:px-6 md:px-8">
            <div class="max-w-7xl mx-auto space-y-8">
                <div>
                    <h1 class="text-title text-3xl font-bold text-gray-900">{{ __('messages.hospital_reports_analytics') }}</h1>
                    <p class="text-titlesticky text-gray-600 mt-2">{{ __('messages.reports_subtitle') }}</p>
                </div>

                <div class="space-y-4">
                    <h2 class="text-titlesticky text-lg font-semibold text-gray-900">{{ __('messages.charts') }}</h2>
                    @php
                        $labels = $weeklySeries['labels'] ?? [];
                        $apptData = $weeklySeries['appointments'] ?? [];
                        $patientData = $weeklySeries['patients'] ?? [];
                        $apptMax = max($apptData ?: [0]);
                        $patientMax = max($patientData ?: [0]);
                        $apptScale = $apptMax > 0 ? 100 / $apptMax : 0;
                        $patientScale = $patientMax > 0 ? 100 / $patientMax : 0;
                    @endphp
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Appointments per Period -->
                        <div class="bg-white rounded-lg shadow border border-gray-100 p-6">
                            <div class="flex items-start justify-between mb-6">
                                <div>
                                    @php
                                        $apptTitle = match(($seriesUnitSingular ?? 'Week')) {
                                            'Day' => __('messages.appointments_per_day'),
                                            'Month' => __('messages.appointments_per_month'),
                                            default => __('messages.appointments_per_week'),
                                        };
                                    @endphp
                                    <p class="text-sm text-titlesticky text-gray-600">{{ $apptTitle }}</p>
                                    <div class="text-3xl font-bold text-gray-900">{{ number_format(array_sum($apptData)) }}</div>
                                    <p class="text-sm text-gray-500">{{ $timeframeLabel ?? __('messages.last_7_weeks') }}</p>
                                </div>
                                <div class="h-10 w-10 rounded-full bg-blue-50 flex items-center justify-center text-blue-600 font-semibold">↗</div>
                            </div>
                            <div class="mt-2">
                                <div class="flex items-end gap-4 h-36">
                                    @foreach($labels as $i => $label)
                                        @php
                                            $h = $apptScale ? max(6, ceil(($apptData[$i] ?? 0) * $apptScale)) : 6;
                                        @endphp
                                        <div class="flex flex-col items-center justify-end space-y-2 h-full">
                                            <div class="w-6 bg-gray-200 rounded-t" style="height: {{ $h }}px"></div>
                                            <span class="text-xs text-gray-500">{{ $label }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- Patients per Period -->
                        <div class="bg-white rounded-lg shadow border border-gray-100 p-6">
                            <div class="flex items-start justify-between mb-6">
                                <div>
                                    @php
                                        $ptTitle = match(($seriesUnitSingular ?? 'Week')) {
                                            'Day' => __('messages.patients_per_day'),
                                            'Month' => __('messages.patients_per_month'),
                                            default => __('messages.patients_per_week'),
                                        };
                                    @endphp
                                    <p class="text-sm text-gray-600">{{ $ptTitle }}</p>
                                    <div class="text-3xl font-bold text-gray-900">{{ number_format(array_sum($patientData)) }}</div>
                                    <p class="text-sm text-gray-500">{{ $timeframeLabel ?? __('messages.last_7_weeks') }}</p>
                                </div>
                                <div class="h-10 w-10 rounded-full bg-blue-50 flex items-center justify-center text-blue-600 font-semibold">↗</div>
                            </div>
                            <div class="flex items-end gap-4 h-36">
                                @foreach($labels as $i => $label)
                                    @php
                                        $h = $patientScale ? max(6, ceil(($patientData[$i] ?? 0) * $patientScale)) : 6;
                                    @endphp
                                    <div class="flex flex-col items-center justify-end space-y-2 h-full">
                                        <div class="w-6 bg-gray-200 rounded-t" style="height: {{ $h }}px"></div>
                                        <span class="text-xs text-gray-500">{{ $label }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Most Visited Doctors -->
                    <div class="bg-white rounded-lg shadow border border-gray-100 p-6">
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <p class="text-sm text-gray-600">{{ __('messages.most_visited_doctors') }}</p>
                                @if($topDoctors->count() > 0)
                                    <div class="text-2xl font-bold text-gray-900">{{ $topDoctors->first()->name }}</div>
                                    <p class="text-sm text-gray-500">{{ $timeframeLabel ?? __('messages.last_7_weeks') }} <span class="text-green-600 font-semibold">+{{ $topDoctors->first()->total }} {{ __('messages.appointments') }}</span></p>
                                @else
                                    <div class="text-2xl font-bold text-gray-900">{{ __('messages.no_data') }}</div>
                                    <p class="text-sm text-gray-500">{{ $timeframeLabel ?? __('messages.last_7_weeks') }}</p>
                                @endif
                            </div>
                            <div class="h-10 w-10 rounded-full bg-green-50 flex items-center justify-center text-green-600 font-semibold">★</div>
                        </div>
                        <div class="space-y-4">
                            @forelse($topDoctors as $doctor)
                                <div>
                                    <div class="flex items-center justify-between text-sm text-gray-700 mb-1">
                                        <span>{{ $doctor->name }}</span>
                                        <span class="text-gray-500">{{ $doctor->percentage }}%</span>
                                    </div>
                                    <div class="w-full h-2 rounded bg-gray-100">
                                        <div class="h-2 rounded bg-blue-400" style="width: {{ $doctor->percentage }}%"></div>
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-gray-500">{{ __('messages.no_doctor_data') }}</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="space-y-3">
                    <h2 class="text-titlesticky text-lg font-semibold text-gray-900">{{ __('messages.export_reports') }}</h2>
                    <div class="flex flex-wrap gap-3">
                            <style>
                                @media (max-width: 900px) {
                                    .text-3xl { font-size: 1.5rem !important; }
                                    .text-2xl { font-size: 1.2rem !important; }
                                    .text-lg { font-size: 1rem !important; }
                                    .p-6 { padding: 1rem !important; }
                                    .mb-8 { margin-bottom: 1.5rem !important; }
                                }
                                @media (max-width: 600px) {
                                    .py-8 { padding-top: 1.2rem !important; padding-bottom: 1.2rem !important; }
                                    .px-4, .sm\:px-6, .md\:px-8 { padding-left: 0.5rem !important; padding-right: 0.5rem !important; }
                                    .gap-6 { gap: 1rem !important; }
                                    .space-y-8 > :not([hidden]) ~ :not([hidden]) { margin-top: 1.2rem !important; }
                                }
                            </style>
                        <a href="{{ route('admin.reports.export-pdf') }}" class="px-4 py-2 bg-red-600 text-white rounded-md font-semibold hover:bg-red-700 transition">{{ __('messages.export_pdf') }}</a>
                        <a href="{{ route('admin.reports.export-excel') }}" class="px-4 py-2 bg-green-600 text-white rounded-md font-semibold hover:bg-green-700 transition">{{ __('messages.export_excel') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection
