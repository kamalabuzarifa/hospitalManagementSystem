<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\Patient;
use Illuminate\Support\Facades\Auth;
class AppointmentsController extends Controller
{

    public function index(Request $request)
    {
        $doctor = Auth::user()->doctor;
        if (!$doctor) abort(403);

        $query = Appointment::with('patient')
            ->where('doctor_profile_id', $doctor->id);

        $patientId = $request->integer('patient_id');
        $patient = null;
        if ($patientId > 0) {
            $query->where('patient_id', $patientId);

            $patient = Patient::query()
                ->whereKey($patientId)
                ->whereHas('appointments', function ($q) use ($doctor) {
                    $q->where('doctor_profile_id', $doctor->id);
                })
                ->first();
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->date) {
            $query->whereDate('scheduled_at', $request->date);
        }

        $appointments = $query
            ->orderBy('scheduled_at', 'desc')
            ->paginate(15);

        return view('doctor.appointments.index', compact('appointments', 'doctor', 'patient', 'patientId'));
    }
}
