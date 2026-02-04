<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\Patient;
use Illuminate\Support\Facades\Auth;

class PatientsController extends Controller

{
    public function index(Request $request)
    {
        $doctor = Auth::user()->doctor;
        if (!$doctor) abort(403);

        $search = trim((string) $request->input('search'));

        $patients = Patient::query()
            ->whereHas('appointments', function ($q) use ($doctor) {
                $q->where('doctor_profile_id', $doctor->id);
            })
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('name', 'like', '%' . $search . '%')
                        ->orWhere('phone', 'like', '%' . $search . '%')
                        ->orWhere('patient_code', 'like', '%' . $search . '%');

                    if (is_numeric($search)) {
                        $qq->orWhere('id', (int) $search);
                    }
                });
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('doctor.patients.index', compact('patients', 'doctor', 'search'));
    }

    public function show(Request $request, $patientId)
    {
        $doctor = Auth::user()->doctor;
        if (!$doctor) abort(403);

        $patient = Patient::query()
            ->whereHas('appointments', function ($q) use ($doctor) {
                $q->where('doctor_profile_id', $doctor->id);
            })
            ->findOrFail($patientId);

        $todaysAppointment = null;

        $requestedAppointmentId = $request->query('appointment_id');
        if (!empty($requestedAppointmentId)) {
            $todaysAppointment = Appointment::with(['prescription.medicines'])
                ->where('doctor_profile_id', $doctor->id)
                ->where('patient_id', $patient->id)
                ->whereKey($requestedAppointmentId)
                ->first();
        }

        if (!$todaysAppointment) {
            $todaysAppointment = Appointment::with(['prescription.medicines'])
                ->where('doctor_profile_id', $doctor->id)
                ->where('patient_id', $patient->id)
                ->where(function ($q) {
                    $q->whereDate('scheduled_at', today())
                        ->orWhereDate('date', today());
                })
                ->orderByRaw('scheduled_at is null')
                ->orderBy('scheduled_at')
                ->orderBy('time')
                ->first();
        }

        $appointmentsHistory = Appointment::with(['doctor.user', 'prescription'])
            ->where('doctor_profile_id', $doctor->id)
            ->where('patient_id', $patient->id)
            ->orderByDesc('id')
            ->orderByDesc('scheduled_at')
            ->limit(25)
            ->get();

        return view('doctor.patients.show', compact('patient', 'todaysAppointment', 'appointmentsHistory'));
    }
    public function records()
    {
        $doctor = Auth::user()->doctor;
        if (!$doctor) abort(403);

        $search = trim((string) request('search'));

        $latestAppointmentForDoctor = Appointment::query()
            ->whereColumn('patient_id', 'patients.id')
            ->where('doctor_profile_id', $doctor->id)
            ->orderByDesc('scheduled_at')
            ->orderByDesc('id');

        $patients = Patient::query()
            ->whereHas('appointments', function ($q) use ($doctor) {
                $q->where('doctor_profile_id', $doctor->id);
            })
            ->addSelect([
                'latest_appointment_id' => (clone $latestAppointmentForDoctor)->select('id')->limit(1),
                'latest_appointment_status' => (clone $latestAppointmentForDoctor)->select('status')->limit(1),
            ])
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('name', 'like', '%' . $search . '%')
                        ->orWhere('phone', 'like', '%' . $search . '%')
                        ->orWhere('patient_code', 'like', '%' . $search . '%');

                    if (is_numeric($search)) {
                        $qq->orWhere('id', (int) $search);
                    }
                });
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('doctor.patients-records.index', compact('patients', 'search'));
    }

    public function recordShow(Appointment $appointment)
    {
        $doctor = Auth::user()->doctor;
        if (!$doctor) abort(403);

        abort_unless((int) $appointment->doctor_profile_id === (int) $doctor->id, 404);

        $appointment->load([
            'patient',
            'doctor.user',
            'prescription.medicines',
        ]);

        $patientHistory = Appointment::with(['doctor.user', 'prescription'])
            ->where('doctor_profile_id', $doctor->id)
            ->where('patient_id', $appointment->patient_id)
            ->orderByDesc('id')
            ->orderByDesc('scheduled_at')
            ->limit(25)
            ->get();

        return view('doctor.patients-records.show', compact('appointment', 'patientHistory'));
    }
}
