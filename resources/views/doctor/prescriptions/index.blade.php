@extends('layouts.doctor')

@section('title', __('messages.thera') .' - ' . __('messages.doctor') . ' - ' . __('messages.medical_prescriptions'))


@section('content')
    <main class="flex-1">
        <div class="container mx-auto px-4" style="padding-bottom: 10px;">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-title text-3xl font-bold text-gray-900">{{ __('messages.medical_prescription') }}</h1>
            </div>
            @php
                $rawTimeFormat = isset($settings) && $settings ? trim((string) $settings->time_format) : '24';
                $is12Hour = str_starts_with($rawTimeFormat, '12');
                $timeFormat = $is12Hour ? 'h:iA' : 'H:i';
            @endphp
        <!-- Prescription Modal -->
        <div id="prescriptionModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 flex items-start justify-center z-50 p-4 overflow-y-auto">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-xl max-h-[80vh] overflow-y-auto mt-16">
                <div class="sticky top-0 bg-white border-b px-6 py-4 flex items-center justify-between">
                    <h3 class="text-2xl font-bold text-gray-900">{{ __('messages.create_new_prescription') }}</h3>
                    <button onclick="closePrescriptionModal()" class="text-gray-400 hover:text-gray-600 text-3xl font-bold leading-none">&times;</button>
                </div>
                <form method="POST" action="{{ route('doctor.prescriptions.store') }}" class="p-6">
                    @csrf
                    @if(!empty($prefillAppointmentId))
                        <input type="hidden" name="appointment_id" value="{{ $prefillAppointmentId }}">
                    @endif
                    <div class="space-y-4">
                        <div>
                            <label class="text-black  block text-sm font-medium text-gray-700 mb-1">{{ __('messages.select_patient') }} *</label>
                            <select id="create_patient_id" name="patient_id" required class="text-titlest  w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-gray-50">
                                <option class="text-titlest" value="">{{ __('messages.select_patient') }}</option>
                                @foreach($patients as $patient)
                                    <option class="text-titlest" value="{{ $patient->id }}" @selected(!empty($prefillPatientId) && (string) $prefillPatientId === (string) $patient->id)>{{ $patient->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-black  block text-sm font-medium text-gray-700 mb-1">{{ __('messages.medicine') }} *</label>
                            <select name="medicine" required class="text-titlest  w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-gray-50">
                                <option class="text-titlest" value="">{{ __('messages.medicine_name') }}</option>
                                @foreach($medicines as $medicine)
                                    <option class="text-titlest" value="{{ $medicine->name }}">{{ $medicine->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-black  block text-sm font-medium text-gray-700 mb-1">{{ __('messages.dosage') }} *</label>
                            <input type="text" name="dosage" required class="text-titlest  w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="{{ __('messages.dosage') }}">
                        </div>
                        <div>
                            <label class="text-black  block text-sm font-medium text-gray-700 mb-1">{{ __('messages.duration') }} *</label>
                            <input type="text" name="duration" required class="text-titlest  w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="{{ __('messages.duration') }}">
                        </div>
                        <div>
                            <label class="text-black  block text-sm font-medium text-gray-700 mb-1">{{ __('messages.notes') }}</label>
                            <textarea name="notes" rows="3" class="text-titlest  w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="{{ __('messages.notes') }}"></textarea>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 mt-6 pt-4 border-t">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg transition">
                            {{ __('messages.send_to_pharmacy') }}
                        </button>
                        <button type="button" onclick="closePrescriptionModal()" class="bg-gray-100 hover:bg-gray-200 text-gray-800 font-semibold py-2 px-6 rounded-lg transition">
                            {{ __('messages.cancel') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Prescription Modal -->
        <div id="editPrescriptionModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 flex items-start justify-center z-50 p-4 overflow-y-auto">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-xl max-h-[80vh] overflow-y-auto mt-16">
                <div class="sticky top-0 bg-white border-b px-6 py-4 flex items-center justify-between">
                    <h3 class="text-2xl font-bold text-gray-900">{{ __('messages.edit_prescription') }}</h3>
                    <button onclick="closeEditPrescriptionModal()" class="text-gray-400 hover:text-gray-600 text-3xl font-bold leading-none">&times;</button>
                </div>
                <form id="editPrescriptionForm" method="POST" action="#" class="p-6">
                    @csrf
                    @method('PUT')
                    <div class="space-y-4">
                        <div>
                            <label class="text-black  block text-sm font-medium text-gray-700 mb-1">{{ __('messages.select_patient') }} *</label>
                            <select id="edit_patient_id" name="patient_id" required class="text-titlest  w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-gray-50">
                                <option class="text-titlest" value="">{{ __('messages.select_patient') }}</option>
                                @foreach($patients as $patient)
                                    <option class="text-titlest" value="{{ $patient->id }}">{{ $patient->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-black  block text-sm font-medium text-gray-700 mb-1">{{ __('messages.medicine') }} *</label>
                            <select id="edit_medicine" name="medicine" required class="text-titlest  w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-gray-50">
                                <option class="text-titlest" value="">{{ __('messages.medicine_name') }}</option>
                                @foreach($medicines as $medicine)
                                    <option class="text-titlest" value="{{ $medicine->name }}">{{ $medicine->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-black  block text-sm font-medium text-gray-700 mb-1">{{ __('messages.dosage') }} *</label>
                            <input id="edit_dosage" type="text" name="dosage" required class="text-titlest  w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-gray-50" placeholder="{{ __('messages.dosage') }}">
                        </div>
                        <div>
                            <label class="text-black  block text-sm font-medium text-gray-700 mb-1">{{ __('messages.duration') }} *</label>
                            <input id="edit_duration" type="text" name="duration" required class="text-titlest w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-gray-50" placeholder="{{ __('messages.duration') }}">
                        </div>
                        <div>
                            <label class="text-black  block text-sm font-medium text-gray-700 mb-1">{{ __('messages.notes') }}</label>
                            <textarea id="edit_notes" name="notes" rows="3" class="text-titlest  w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-gray-50" placeholder="{{ __('messages.notes') }}"></textarea>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 mt-6 pt-4 border-t">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg transition">
                            {{ __('messages.save') }}
                        </button>
                        <button type="button" onclick="closeEditPrescriptionModal()" class="bg-gray-100 hover:bg-gray-200 text-gray-800 font-semibold py-2 px-6 rounded-lg transition">
                            {{ __('messages.cancel') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
            <div>
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-title text-lg font-semibold mb-4">{{ __('messages.previous_prescriptions') }}</h2>
                <!-- Create Prescription Modal Button -->
                <button onclick="openPrescriptionModal()" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg transition mb-6">
                    + {{ __('messages.create_prescription') }}
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white rounded-lg border text-gray-900">
                    <thead>
                        <tr class="border-b bg-gray-100">
                            <th class="px-4 py-3 text-center font-bold text-lg">{{ __('messages.patient') }}</th>
                            <th class="px-4 py-3 text-center font-bold text-lg">{{ __('messages.date') }}</th>
                            <th class="px-4 py-3 text-center font-bold text-lg">{{ __('messages.time') }}</th>
                            <th class="px-4 py-3 text-center font-bold text-lg">{{ __('messages.medicine') }}</th>
                            <th class="px-4 py-3 text-center font-bold text-lg">{{ __('messages.dosage') }}</th>
                            <th class="px-4 py-3 text-center font-bold text-lg">{{ __('messages.duration') }}</th>
                            <th class="px-4 py-3 text-center font-bold text-lg">{{ __('messages.notes') }}</th>
                            <th class="px-4 py-3 text-center font-bold text-lg">{{ __('messages.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($prescriptions as $prescription)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-4 py-3 text-center">{{ $prescription->patient->name ?? '' }}</td>
                                <td class="px-4 py-3 text-center">{{ $prescription->p_date ? $prescription->p_date : ($prescription->created_at ? $prescription->created_at->format('Y-m-d') : '') }}</td>
                                <td class="px-4 py-3 text-center">{{ $prescription->created_at ? $prescription->created_at->format($timeFormat) : '' }}</td>
                                <td class="px-4 py-3 text-center">{{ $prescription->medicine }}</td>
                                <td class="px-4 py-3 text-center">{{ $prescription->dosage }}</td>
                                <td class="px-4 py-3 text-center">{{ $prescription->duration }}</td>
                                <td class="px-4 py-3 text-center">{{ $prescription->notes }}</td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <button
                                            type="button"
                                            onclick="openEditPrescriptionModal(this)"
                                            data-action="{{ route('doctor.prescriptions.update', $prescription) }}"
                                            data-patient-id="{{ $prescription->patient_id }}"
                                            data-medicine="{{ $prescription->medicine }}"
                                            data-dosage="{{ $prescription->dosage }}"
                                            data-duration="{{ $prescription->duration }}"
                                            data-notes="{{ $prescription->notes }}"
                                            class="bg-gray-100 hover:bg-gray-200 text-gray-800 font-semibold py-1.5 px-3 rounded-lg transition"
                                        >
                                            {{ __('messages.edit') }}
                                        </button>

                                        <form method="POST" action="{{ route('doctor.prescriptions.destroy', $prescription) }}" onsubmit="return confirm('{{ __('messages.are_you_sure') }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-semibold py-1.5 px-3 rounded-lg transition">
                                                {{ __('messages.delete') }}
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-6 text-gray-400">{{ __('messages.no_prescriptions_found') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            </div>
        </div>
    </main>
@endsection

@push('scripts')
    <script>
        function openPrescriptionModal() {
            document.getElementById('prescriptionModal').classList.remove('hidden');
        }
        function closePrescriptionModal() {
            document.getElementById('prescriptionModal').classList.add('hidden');
        }

        function openEditPrescriptionModal(button) {
            const form = document.getElementById('editPrescriptionForm');
            form.action = button.dataset.action;

            document.getElementById('edit_patient_id').value = button.dataset.patientId || '';
            const editMedicineSelect = document.getElementById('edit_medicine');
            if (editMedicineSelect) {
                for (let i = 0; i < editMedicineSelect.options.length; i++) {
                    if (editMedicineSelect.options[i].value === button.dataset.medicine) {
                        editMedicineSelect.selectedIndex = i;
                        break;
                    }
                }
            }
            document.getElementById('edit_dosage').value = button.dataset.dosage || '';
            document.getElementById('edit_duration').value = button.dataset.duration || '';
            document.getElementById('edit_notes').value = button.dataset.notes || '';

            document.getElementById('editPrescriptionModal').classList.remove('hidden');
        }

        function closeEditPrescriptionModal() {
            document.getElementById('editPrescriptionModal').classList.add('hidden');
        }

        window.onclick = function(event) {
            const modal = document.getElementById('prescriptionModal');
            const editModal = document.getElementById('editPrescriptionModal');
            if (event.target === modal) {
                closePrescriptionModal();
            }
            if (event.target === editModal) {
                closeEditPrescriptionModal();
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            try {
                const params = new URLSearchParams(window.location.search);
                const open = params.get('open');
                const patientId = params.get('patient_id');
                const appointmentId = params.get('appointment_id');

                if (patientId) {
                    const select = document.getElementById('create_patient_id');
                    if (select) {
                        for (let i = 0; i < select.options.length; i++) {
                            if (select.options[i].value === patientId) {
                                select.selectedIndex = i;
                                break;
                            }
                        }
                    }
                }

                if (appointmentId) {
                    try {
                        const hid = document.querySelector('input[name="appointment_id"]');
                        if (hid) hid.value = appointmentId;
                    } catch (e) {}
                }

                if (open === '1' || patientId || appointmentId) {
                    // open the modal
                    openPrescriptionModal();

                    // remove params to keep URL clean
                    try {
                        const u = new URL(window.location.href);
                        u.searchParams.delete('open');
                        u.searchParams.delete('patient_id');
                        u.searchParams.delete('appointment_id');
                        history.replaceState(null, '', u.toString());
                    } catch (e) { /* ignore */ }
                }
            } catch (e) { /* ignore */ }
        });

        (function() {
            const autoOpen = @json($autoOpenModal ?? false);
            const prefillPatientId = @json($prefillPatientId ?? null);
            if (!autoOpen) return;

            openPrescriptionModal();

            if (prefillPatientId) {
                const select = document.getElementById('create_patient_id');
                if (select) {
                    select.value = String(prefillPatientId);
                }
            }
        })();
    </script>
@endpush
