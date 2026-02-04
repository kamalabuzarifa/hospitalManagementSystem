@extends('layouts.reception')
@section('title', __('messages.thera') . ' - ' . __('messages.reception') . ' - ' . __('messages.show_appointment', ['name' => $appointment->patient?->name ?? '—']))

@section('content')
    <main>
        <div class="container mx-auto px-4 " style="padding-bottom: 16px;">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-title text-3xl font-bold">{{ __('messages.appointment_details') }}</h1>
                <div class="flex gap-2">
                    @php($canEditAppointments = ($appSettings?->allow_edit_appointments ?? true))
                    @if($canEditAppointments)
                        <a href="{{ route('reception.appointments.edit', $appointment) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">{{ __('messages.edit') }}</a>
                    @else
                        <button type="button" disabled title="{{ __('messages.appointment_changes_disabled') }}" class="bg-gray-300 text-gray-600 font-bold py-2 px-4 rounded cursor-not-allowed">{{ __('messages.edit') }}</button>
                    @endif
                    <a href="{{ route('reception.appointments.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-2 px-4 rounded">{{ __('messages.back') }}</a>
                </div>
            </div>

            @if (session('success'))
                <div class="mb-4 p-4 rounded bg-green-100 text-green-800 border border-green-300 text-center font-semibold">
                    {{ session('success') }}
                </div>
            @endif

            <div class="grid grid-cols-2 gap-6 mb-6">
                <div class="bg-white p-6 rounded shadow">
                    <h3 class="text-black text-xl font-bold mb-4">{{ __('messages.patient_info') }}</h3>
                    <div class="mb-3">
                        <span class="text-black font-bold">{{ __('messages.patient_name') }}:
                            <p class="inline-flex items-center gap-2 text-titlest">{{ $appointment->patient->name }}</p>
                        </span>
                    </div>
                    <div class="mb-3">
                        <span class="text-black font-bold">{{ __('messages.phone') }}:
                            <p class="inline-flex items-center gap-2 text-titlest">{{ $appointment->patient->phone ?? '—' }}</p>
                        </span>
                    </div>
                    <div class="mb-3">
                        <span class="text-black font-bold">{{ __('messages.age') }}:
                             <p class="inline-flex items-center gap-2 text-titlest">{{ $appointment->patient->age ?? '—' }}</p>
                        </span>
                    </div>
                    <div class="mb-3">
                        <span class="text-black font-bold">{{ __('messages.gender') }}: 
                            <p class="inline-flex items-center gap-2 text-titlest">
                                @if($appointment->patient->gender)
                                    {{ $appointment->patient->gender == 'male' ? __('messages.male') : ($appointment->patient->gender == 'female' ? __('messages.female') : __('messages.other')) }}
                                @else
                                    —
                                @endif
                            </p>
                        </span>
                    </div>
                </div>

                <div class="bg-white p-6 rounded shadow">
                    <h3 class="text-black text-xl font-bold mb-4">{{ __('messages.appointment_info') }}</h3>
                    <div class="mb-3">
                        <span class="text-black font-bold">{{ __('messages.booking_date') }}:
                            <p class="inline-flex items-center gap-2 text-titlest">{{ $appointment->date ? $appointment->date->format('Y-m-d') : '—' }}</p>
                        </span> 
                    </div>
                    <div class="mb-3">
                        <span class="text-black font-bold">{{ __('messages.time') }}:
                            <p class="inline-flex items-center gap-2 text-titlest">{{ $appointment->time ?? '—' }}</p>
                        </span>
                    </div>
                    <div class="mb-3">
                        <span class="text-black font-bold">{{ __('messages.doctor') }}:
                            <p class="inline-flex items-center gap-2 text-titlest">{{ $appointment->doctor?->user?->name ?? '—' }}</p>
                        </span>
                    </div>
                    <div class="mb-3">
                        <span class="text-black font-bold">{{ __('messages.status') }}:
                            <span @class([
                                'px-3 py-1 rounded text-sm font-semibold inline-flex items-center gap-2 text-titlest',
                                'bg-blue-100 text-blue-800' => $appointment->status === 'scheduled',
                                'bg-green-100 text-green-800' => $appointment->status === 'checked_in',
                                'bg-gray-200 text-gray-800' => $appointment->status === 'completed',
                                'bg-red-100 text-red-800' => $appointment->status === 'cancelled',
                                'bg-yellow-100 text-yellow-800' => in_array($appointment->status, ['pending','missed']),
                            ])>
                                {{ \Illuminate\Support\Facades\Lang::has('messages.status_' . $appointment->status)
                                    ? __('messages.status_' . $appointment->status)
                                    : ucfirst(str_replace('_', ' ', $appointment->status))
                                }}
                            </span>
                        </span>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded shadow mb-6">
                <h3 class="text-black text-xl font-bold mb-4">{{ __('messages.quick_actions') }}</h3>
                @if(!$canEditAppointments)
                    <p class="text-sm text-gray-500 mb-3">{{ __('messages.appointment_changes_disabled') }}</p>
                @endif

                <div class="flex gap-2">
                    @if($appointment->status === 'scheduled')
                        <form action="{{ route('reception.appointments.checkin', $appointment) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" @if(!$canEditAppointments) disabled title="{{ __('messages.appointment_changes_disabled') }}" class="bg-gray-300 text-gray-600 font-bold py-2 px-4 rounded cursor-not-allowed" @else class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded" @endif>
                                {{ __('messages.check_in_patient') }}
                            </button>
                        </form>
                    @endif

                    @if($appointment->status === 'checked_in')
                        <form action="{{ route('reception.appointments.complete', $appointment) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" @if(!$canEditAppointments) disabled title="{{ __('messages.appointment_changes_disabled') }}" class="bg-gray-300 text-gray-600 font-bold py-2 px-4 rounded cursor-not-allowed" @else class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded" @endif>
                                {{ __('messages.complete_appointment') }}
                            </button>
                        </form>
                    @endif

                    @if($appointment->status !== 'completed' && $appointment->status !== 'cancelled')
                        <form action="{{ route('reception.appointments.cancel', $appointment) }}" method="POST" class="inline" @if($canEditAppointments) onsubmit="return confirm('Are you sure you want to cancel this appointment?');" @endif>
                            @csrf
                            <button type="submit" @if(!$canEditAppointments) disabled title="{{ __('messages.appointment_changes_disabled') }}" class="bg-gray-300 text-gray-600 font-bold py-2 px-4 rounded cursor-not-allowed" @else class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded" @endif>
                                {{ __('messages.check_out_patient') }}
                            </button>
                        </form>
                    @endif
                </div>
            </div>

        </div>
    </main>
@endsection
