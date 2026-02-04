<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Http\Requests\StorePatientRequest;
use App\Http\Requests\UpdatePatientRequest;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    use LogsActivity;

    public function __construct()
    {
        // Admin patient management permissions
        $this->middleware('permission:patients.view,manage_patients')->only(['index', 'show']);
        $this->middleware('permission:patients.create,manage_patients')->only(['create', 'store']);
        $this->middleware('permission:patients.edit,manage_patients')->only(['edit', 'update']);
        $this->middleware('permission:patients.delete,manage_patients')->only(['destroy']);
    }
    public function index(Request $request)
    {
        $query = Patient::latest();

        if ($request->search) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%');
        }

        $patients = $query->paginate(20);

        return view('admin.patients.index', compact('patients'));
    }

    public function create()
    {
        return view('admin.patients.create');
    }

    public function store(StorePatientRequest $request)
    {
        $patient = Patient::create($request->validated());
        $this->logActivity('created_patient', ['patient_id' => $patient->id, 'name' => $patient->name]);

        return redirect()
            ->route('admin.patients.index')
            ->with('success', __('messages.patient_added_success'));
    }

    public function show(Patient $patient)
    {
        $patient->load('appointments.doctor');
        return view('admin.patients.show', compact('patient'));
    }

    public function edit(Patient $patient)
    {
        return view('admin.patients.edit', compact('patient'));
    }

    public function update(UpdatePatientRequest $request, Patient $patient)
    {
        $patient->update($request->validated());
        $this->logActivity('updated_patient', ['patient_id' => $patient->id, 'name' => $patient->name]);

        return redirect()
            ->route('patients.show', $patient)
            ->with('success', __('messages.patient_updated_success'));
    }

    public function destroy(Patient $patient)
    {
        $name = $patient->name;
        $patient->delete();
        $this->logActivity('deleted_patient', ['name' => $name]);

        return redirect()
            ->route('admin.patients.index')
            ->with('success', __('messages.patient_deleted_success'));
    }

    // Patient Portal Methods
    public function myDashboard()
    {
        $patient = Patient::where('phone', auth()->user()->phone ?? '')->first();
        
        if (!$patient) {
            return view('patient.dashboard')->with('patient', null);
        }

        $upcomingAppointments = $patient->appointments()
            ->where('appointment_date', '>=', today())
            ->where('status', 'scheduled')
            ->count();

        $completedAppointments = $patient->appointments()
            ->where('status', 'completed')
            ->count();

        $recentAppointments = $patient->appointments()
            ->with(['doctor.user', 'prescription'])
            ->where('appointment_date', '>=', today())
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->limit(5)
            ->get();

        return view('patient.dashboard', compact('patient', 'upcomingAppointments', 'completedAppointments', 'recentAppointments'));
    }

    public function myAppointments()
    {
        $patient = Patient::where('phone', auth()->user()->phone ?? '')->first();
        
        if (!$patient) {
            return view('patient.appointments.index')->with('appointments', collect());
        }

        $appointments = $patient->appointments()
            ->with(['doctor.user', 'prescription'])
            ->orderBy('appointment_date', 'desc')
            ->orderBy('appointment_time', 'desc')
            ->paginate(15);

        return view('patient.appointments.index', compact('appointments', 'patient'));
    }

    public function myPrescriptions()
    {
        $patient = Patient::where('phone', auth()->user()->phone ?? '')->first();
        
        if (!$patient) {
            return view('patient.prescriptions.index')->with('prescriptions', collect());
        }

        $prescriptions = $patient->appointments()
            ->with(['prescription.doctor.user', 'doctor.user'])
            ->whereHas('prescription')
            ->orderBy('appointment_date', 'desc')
            ->paginate(15);

        return view('patient.prescriptions.index', compact('prescriptions', 'patient'));
    }

    public function showPrescription($appointmentId)
    {
        $patient = Patient::where('phone', auth()->user()->phone ?? '')->first();
        
        if (!$patient) {
            abort(403);
        }

        $appointment = $patient->appointments()
            ->with(['prescription.doctor.user', 'doctor.user'])
            ->findOrFail($appointmentId);

        return view('patient.prescriptions.show', compact('appointment'));
    }
}
