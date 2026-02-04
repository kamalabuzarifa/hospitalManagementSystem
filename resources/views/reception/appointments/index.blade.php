@extends('layouts.reception')
@section('title', __('messages.thera') . ' - ' . __('messages.reception') . ' - ' . __('messages.appointments') )

@section('content')
    <main>
        <div class="container mx-auto px-4 " style="padding-bottom: 16px;">
             @if(session('success'))
                <div class="mb-4 p-4 rounded bg-green-100 text-green-800 border border-green-300 text-center font-semibold">
                    {{ session('success') }}
                </div>
            @endif
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-title text-3xl font-bold">{{ __('messages.appointments') }}</h1>
                @php $canEditAppointments = ($appSettings?->allow_edit_appointments ?? true); @endphp
                <button
                    type="button"
                    @if($canEditAppointments)
                        onclick="document.getElementById('appointmentModal').classList.remove('hidden')"
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                    @else
                        disabled
                        title="{{ __('messages.appointment_changes_disabled') }}"
                        class="bg-gray-300 text-gray-600 font-bold py-2 px-4 rounded cursor-not-allowed"
                    @endif
                >
                    + {{ __('messages.new_appointment') }}
                </button>
            </div>

            <div class="mb-6">
                <form action="{{ route('reception.appointments.index') }}" method="GET" class="flex items-center bg-gray-100 rounded px-3 py-2">
                    <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 104.5 4.5a7.5 7.5 0 0012.15 12.15z" /></svg>
                    <input id="appointment-search" type="text" name="search" placeholder="{{ __('messages.search_patients_doctors') }}" value="{{ request('search') }}" class="text-titlest flex-1 bg-transparent outline-none px-2 py-1" />
                </form>
            </div>
            
            <div class="mt-6">
                <h2 class="text-titlesticky text-lg font-bold mb-4">{{ __('messages.upcoming_appointments') }}</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white border rounded-lg">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-3 text-left text-gray-600 font-semibold">{{ __('messages.patient') }}</th>
                                <th class="px-4 py-3 text-left text-gray-600 font-semibold">{{ __('messages.gender') }}</th>
                                <th class="px-4 py-3 text-left text-gray-600 font-semibold">{{ __('messages.doctor') }}</th>
                                <th class="px-4 py-3 text-left text-gray-600 font-semibold">{{ __('messages.date_booking') }}</th>
                                <th class="px-4 py-3 text-left text-gray-600 font-semibold">{{ __('messages.time') }}</th>
                                <th class="px-4 py-3 text-left text-gray-600 font-semibold">{{ __('messages.status') }}</th>
                                <th class="px-4 py-3 text-center text-gray-600 font-semibold">{{ __('messages.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($appointments as $appointment)
                                <tr class="hover:bg-gray-50 border-b">
                                    <td class="text-titlest px-4 py-2">{{ $appointment->patient->name ?? '—' }}</td>
                                    <td class="text-titlest px-4 py-2">{{ $appointment->patient->gender ? ($appointment->patient->gender == 'male' ? __('messages.male') : ($appointment->patient->gender == 'female' ? __('messages.female') : __('messages.other'))) : '—' }}</td>
                                    <td class="text-titlest px-4 py-2">{{ $appointment->doctor?->user?->name ?? '—' }}</td>
                                    <td class=" text-titlest px-4 py-2">{{ $appointment->date ? $appointment->date->format('Y-m-d') : '—' }}</td>
                                    <td class="text-titlest px-4 py-2">{{ $appointment->time ? date('H:i', strtotime($appointment->time)) : '—' }}</td>
                                    <td class="text-titlest px-4 py-2">
                                            @php
                                            $statusKey = strtolower(str_replace(' ', '_', $appointment->status));
                                            // Prefer keys like messages.status_checked_in, fallback to messages.checked_in
                                            if (\Illuminate\Support\Facades\Lang::has('messages.status_' . $statusKey)) {
                                                $statusText = __('messages.status_' . $statusKey);
                                            } elseif (\Illuminate\Support\Facades\Lang::has('messages.' . $statusKey)) {
                                                $statusText = __('messages.' . $statusKey);
                                            } else {
                                                $statusText = ucfirst($appointment->status);
                                            }
                                            @endphp
                                        <span class="px-3 py-1 rounded text-sm font-semibold
                                            {{ $appointment->status === 'scheduled' ? 'bg-blue-100 text-blue-800' : '' }}
                                            {{ $appointment->status === 'checked_in' ? 'bg-green-100 text-green-800' : '' }}
                                            {{ $appointment->status === 'completed' ? 'bg-gray-200 text-gray-800' : '' }}
                                            {{ $appointment->status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}
                                            {{ $appointment->status === 'pending' || $appointment->status === 'missed' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                        ">
                                            {{ $statusText }}
                                        </span>
                                    </td>
                                    <td class="text-titlest px-4 py-2 text-center whitespace-nowrap text-sm">
                                        <div class="flex items-center justify-center gap-3 {{ app()->getLocale() === 'ar' ? 'flex-row-reverse' : '' }}">
                                            <a href="{{ route('reception.appointments.show', $appointment) }}" class="text-blue-600 hover:underline font-semibold">{{ __('messages.view') }}</a>
                                            @php($canEditAppointments = ($appSettings?->allow_edit_appointments ?? true))
                                            @if($canEditAppointments)
                                                <a href="{{ route('reception.appointments.edit', $appointment) }}" class="text-blue-600 hover:underline font-semibold">{{ __('messages.edit') }}</a>
                                            @else
                                                <span class="text-gray-400 font-semibold cursor-not-allowed" title="{{ __('messages.appointment_changes_disabled') }}">{{ __('messages.edit') }}</span>
                                            @endif

                                            <form action="{{ route('reception.appointments.destroy', $appointment) }}" method="POST" class="inline" @if($canEditAppointments) onsubmit="return confirm('{{ __('messages.are_you_sure') }}')" @endif>
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" @if(!$canEditAppointments) disabled title="{{ __('messages.appointment_changes_disabled') }}" class="text-gray-400 font-semibold cursor-not-allowed" @else class="text-red-600 hover:underline font-semibold" @endif>
                                                    {{ __('messages.delete') }}
                                                </button>
                                            </form>
                                        </div>
                                  </td>
                                </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-4 py-4 text-center text-gray-400">{{ __('messages.no_appointments_found') }}</td>
                                    </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-6">
                    <div class="div-all-accessLogs p-4 border-t border-gray-100">{{ $appointments->links() }}</div>
                </div>
            </div>
        </div>
        <!-- Modal -->
        @if(($appSettings->allow_edit_appointments ?? true))
            <div id="appointmentModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 z-50 overflow-y-auto overscroll-contain flex items-start justify-center p-4 pt-16 md:pt-20 pb-8">
                <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl max-h-[calc(100vh-6rem)] overflow-hidden flex flex-col animate-fadeIn">
                    <div class="sticky top-0 bg-white border-b px-6 py-4 flex items-center justify-between">
                        <h3 class="text-2xl font-bold text-gray-900">{{ __('messages.schedule_new_appointment') }}</h3>
                        <button onclick="closeAppointmentModal(event)" class="text-gray-400 hover:text-gray-600 text-3xl font-bold leading-none">&times;</button>
                    </div>
                    <form action="{{ route('reception.appointments.store') }}" method="POST" class="flex-1 overflow-y-auto overscroll-contain px-6 py-4 pr-4" style="scrollbar-gutter: stable;">
                        @csrf
                        <div class="mb-4">
                            <label class="block text-gray-700 font-bold mb-2">{{ __('messages.patient') }} *</label>
                            <select name="patient_id" id="patient-select" required class="text-titlest w-full px-4 py-2 border rounded @error('patient_id') border-red-500 @enderror">
                                <option class="text-titlest" value="" >{{ __('messages.select_patient') }}</option>
                                @foreach($patients as $patient)
                                    <option class="text-titlest" value="{{ $patient->id }}" data-gender="{{ $patient->gender ? ($patient->gender == 'male' ? __('messages.male') : ($patient->gender == 'female' ? __('messages.female') : __('messages.other'))) : '' }}" {{ old('patient_id') == $patient->id ? 'selected' : '' }}>
                                        {{ $patient->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('patient_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 font-bold mb-2">{{ __('messages.gender') }}</label>
                            <input type="text" id="selected-patient-gender" class="text-titlest w-full px-4 py-2 border rounded bg-gray-100" value="" readonly>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 font-bold mb-2">
                                {{ __('messages.doctor') }} @if(!($appSettings->auto_assign_doctor ?? false)) * @endif
                            </label>
                            <select name="doctor_id" class="text-titlest w-full px-4 py-2 border rounded @error('doctor_id') border-red-500 @enderror" @if(!($appSettings->auto_assign_doctor ?? false)) required @endif>
                                @if(($appSettings->auto_assign_doctor ?? false))
                                    <option class="text-titlest" value="" >{{ __('messages.auto_assign_general') }}</option>
                                @else
                                    <option class="text-titlest" value="">{{ __('messages.select_doctor') }}</option>
                                @endif
                                @foreach($doctors as $doctor)
                                    <option  class="text-titlest" value="{{ $doctor->id }}" {{ old('doctor_id') == $doctor->id ? 'selected' : '' }}>
                                        {{ $doctor->user->name ?? '' }}
                                    </option>
                                @endforeach
                            </select>
                            @if(($appSettings->auto_assign_doctor ?? false))
                                <p class="mt-1 text-xs text-gray-500">{{ __('messages.auto_assign_info') }}</p>
                            @else
                                <p class="mt-1 text-xs text-gray-500">{{ __('messages.auto_assign_disabled_info') }}</p>
                            @endif
                            @error('doctor_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 font-bold mb-2">{{ __('messages.date_booking') }} *</label>
                            <input type="date" name="date" value="{{ old('date') }}" required
                                class="text-titlest w-full px-4 py-2 border rounded @error('date') border-red-500 @enderror">
                            @error('date') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 font-bold mb-2">{{ __('messages.time') }} *</label>
                            <input type="time" name="time" value="{{ old('time') }}" required
                                step="{{ (int)($appSettings->default_appointment_duration ?? 20) * 60 }}"
                                class="text-titlest w-full px-4 py-2 border rounded @error('time') border-red-500 @enderror">
                            @error('time') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 font-bold mb-2">{{ __('messages.status') }} *</label>
                            <select name="status" required class="text-titlest w-full px-4 py-2 border rounded @error('status') border-red-500 @enderror">
                                <option class="text-titlest" value="">{{ __('messages.select_status') }}</option>
                                <option class="text-titlest" value="scheduled" {{ old('status') == 'scheduled' ? 'selected' : '' }}>{{ __('messages.scheduled') }}</option>
                                <option class="text-titlest" value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>{{ __('messages.completed') }}</option>
                                <option class="text-titlest" value="cancelled" {{ old('status') == 'cancelled' ? 'selected' : '' }}>{{ __('messages.cancelled') }}</option>
                                <option class="text-titlest" value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>{{ __('messages.pending') }}</option>
                            </select>
                            @error('status') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        <div class="flex items-center gap-4 mt-6 pt-4 border-t">
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded">{{ __('messages.create') }}</button>
                            <button type="button" onclick="closeAppointmentModal(event)" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-2 px-6 rounded">{{ __('messages.cancel') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
        <style>
            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(40px) scale(0.98); }
                to { opacity: 1; transform: translateY(0) scale(1); }
            }
            .animate-fadeIn {
                animation: fadeIn 0.3s cubic-bezier(.4,0,.2,1);
            }
        </style>
        <script>
            function closeAppointmentModal(e) {
                if(e) e.preventDefault();
                document.getElementById('appointmentModal').classList.add('hidden');
            }
            // Close modal when clicking outside
            window.onclick = function(event) {
                const modal = document.getElementById('appointmentModal');
                if (event.target == modal) {
                    closeAppointmentModal(event);
                }
            }

            // Auto-fill gender field based on selected patient
            document.addEventListener('DOMContentLoaded', function() {
                const patientSelect = document.getElementById('patient-select');
                const genderInput = document.getElementById('selected-patient-gender');
                function updateGender() {
                    const selected = patientSelect.options[patientSelect.selectedIndex];
                    genderInput.value = selected.dataset.gender || '';
                }
                patientSelect.addEventListener('change', updateGender);
                // Set initial value if editing/old
                updateGender();
            });
            // Auto-submit search input with debounce
            document.addEventListener('DOMContentLoaded', function() {
                const search = document.getElementById('appointment-search');
                if (!search) return;
                const form = search.closest('form');
                let timer = null;
                search.addEventListener('input', function () {
                    clearTimeout(timer);
                    timer = setTimeout(function () {
                        try {
                            if (typeof form.requestSubmit === 'function') form.requestSubmit();
                            else form.submit();
                        } catch (e) {
                            // fallback
                            form.submit();
                        }
                    }, 600);
                });
            });
        </script>
    </main>
@endsection
