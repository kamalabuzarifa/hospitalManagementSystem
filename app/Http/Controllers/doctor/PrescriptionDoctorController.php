<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Prescription;
use App\Models\Appointment;
use App\Models\Medicine;
use Illuminate\Support\Facades\Auth;
use App\Models\Settings;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class PrescriptionDoctorController extends Controller
{
    public function index(Request $request)
    {
        $doctor = Auth::user()->doctor;
        $patients = \App\Models\Patient::all();
        $medicines = Medicine::orderBy('name')->get();
        $settings = Settings::first() ?? Settings::create([
            'language' => 'en',
            'time_format' => '24',
            'date_format' => 'DD/MM/YYYY',
            'backup_frequency' => 'weekly',
            'backup_destination' => null,
            'notification_new_appointment' => true,
            'notification_new_patient' => true,
        ]);
        $prescriptions = \App\Models\Prescription::with(['patient'])
            ->where('doctor_id', $doctor->id)
            ->orderByDesc('created_at')
            ->get();

        $prefillPatientId = $request->query('patient_id');
        $prefillAppointmentId = $request->query('appointment_id');
        $autoOpenModal = (bool) $request->query('open');

        return view('doctor.prescriptions.index', compact(
            'prescriptions',
            'patients',
            'settings',
            'prefillPatientId',
            'prefillAppointmentId',
            'autoOpenModal',
            'medicines'
        ));
    }
        public function store(Request $request)
        {
            $doctor = Auth::user()->doctor;
            $validated = $request->validate([
                'patient_id' => 'required|exists:patients,id',
                'appointment_id' => 'nullable|exists:appointments,id',
                'medicine' => 'required|string',
                'dosage' => 'required|string',
                'duration' => 'required|string',
                'notes' => 'nullable|string',
            ]);

            $appointment = null;

            if (!empty($validated['appointment_id'])) {
                $appointment = Appointment::with('prescription')
                    ->where('id', $validated['appointment_id'])
                    ->where('patient_id', $validated['patient_id'])
                    ->where('doctor_profile_id', $doctor->id)
                    ->first();
            }

            if (!$appointment) {
                // Fallback: find an appointment for this patient+doctor that is happening now.
                $candidateAppointments = Appointment::with('prescription')
                    ->where('patient_id', $validated['patient_id'])
                    ->where('doctor_profile_id', $doctor->id)
                    ->whereIn('status', ['scheduled', 'checked_in', 'pending'])
                    ->orderByDesc('scheduled_at')
                    ->orderByDesc('id')
                    ->limit(10)
                    ->get();

                foreach ($candidateAppointments as $candidate) {
                    $appointmentAt = $candidate->scheduled_at;

                    if (empty($appointmentAt) && !empty($candidate->date) && !empty($candidate->time)) {
                        try {
                            $appointmentAt = Carbon::parse($candidate->date . ' ' . $candidate->time);
                        } catch (\Throwable $e) {
                            $appointmentAt = null;
                        }
                    }

                    if (!$appointmentAt) {
                        continue;
                    }

                    $inTimeWindow = now()->between($appointmentAt->copy()->subMinutes(15), $appointmentAt->copy()->addMinutes(40));
                    if ($inTimeWindow) {
                        $appointment = $candidate;
                        break;
                    }
                }
            }

            if (!$appointment) {
                throw ValidationException::withMessages([
                    'patient_id' => __('messages.cannot_create_prescription_outside_appointment_time'),
                ]);
            }

            if (!empty($appointment->prescription)) {
                throw ValidationException::withMessages([
                    'patient_id' => __('messages.prescription_already_exists_for_appointment'),
                ]);
            }

            Prescription::create([
                'doctor_id' => $doctor->id,
                'patient_id' => $validated['patient_id'],
                'appointment_id' => $appointment->id,
                'medicine' => $validated['medicine'],
                'dosage' => $validated['dosage'],
                'duration' => $validated['duration'],
                'notes' => $validated['notes'] ?? null,
                'p_date' => now()->toDateString(),
            ]);

            return redirect()->route('doctor.prescriptions.index')->with('success', __('messages.prescription_created_success'));
        }

        public function update(Request $request, Prescription $prescription)
        {
            $doctor = Auth::user()->doctor;

            if ((int) $prescription->doctor_id !== (int) $doctor->id) {
                abort(403);
            }

            $validated = $request->validate([
                'patient_id' => 'required|exists:patients,id',
                'medicine' => 'required|string',
                'dosage' => 'required|string',
                'duration' => 'required|string',
                'notes' => 'nullable|string',
            ]);

            $updates = [
                'patient_id' => $validated['patient_id'],
                'medicine' => $validated['medicine'],
                'dosage' => $validated['dosage'],
                'duration' => $validated['duration'],
                'notes' => $validated['notes'] ?? null,
            ];

            if ((int) $validated['patient_id'] !== (int) $prescription->patient_id) {
                $updates['appointment_id'] = Appointment::where('patient_id', $validated['patient_id'])
                    ->where('doctor_profile_id', $doctor->id)
                    ->orderByDesc('scheduled_at')
                    ->value('id');
            }

            $prescription->update($updates);

            return redirect()->route('doctor.prescriptions.index')->with('success', __('messages.prescription_updated_success'));
        }

        public function destroy(Prescription $prescription)
        {
            $doctor = Auth::user()->doctor;

            if ((int) $prescription->doctor_id !== (int) $doctor->id) {
                abort(403);
            }

            $prescription->delete();

            return redirect()->route('doctor.prescriptions.index')->with('success', __('messages.prescription_deleted_success'));
        }
}
