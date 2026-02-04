<?php

namespace App\Http\Controllers\doctor;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\User;
use App\Models\Appointment;
use App\Models\Patient;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DoctorController extends Controller
{
    use LogsActivity;

    public function __construct()
    {
        // Admin doctors management permissions
        $this->middleware('permission:doctors.view,manage_doctors')->only(['index', 'show']);
        $this->middleware('permission:doctors.create,manage_doctors')->only(['create', 'store']);
        $this->middleware('permission:doctors.edit,manage_doctors')->only(['edit', 'update']);
        $this->middleware('permission:doctors.delete,manage_doctors')->only(['destroy']);
    }

    public function patientsIndex()
    {
        $doctor = Auth::user()->doctor;
        if (!$doctor) abort(403);

        $patients = Patient::whereHas('appointments', function ($q) use ($doctor) {
            $q->where('doctor_profile_id', $doctor->id);
        })->paginate(20);

        return view('doctor.patients.index', compact('patients', 'doctor'));
    }

    public function index()
    {
        $doctors = Doctor::with('user')->latest()->paginate(20);
        return view('admin.doctors.index', compact('doctors'));
    }

    public function create()
    {
        $users = User::whereDoesntHave('doctor')
            ->whereHas('role', fn ($q) => $q->where('name', 'doctor'))
            ->get();

        return view('admin.doctors.create', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id|unique:doctors,user_id',
            'specialization' => 'required|string|max:255',
        ]);

        $doctor = Doctor::create($validated);

        $this->logActivity('created_doctor', [
            'doctor_id' => $doctor->id,
            'user_id' => $doctor->user_id
        ]);

        return redirect()->route('admin.doctors.index');
    }

    public function show(Doctor $doctor)
    {
        $doctor->load('user', 'appointments.patient');

        $upcomingAppointments = $doctor->appointments()
            ->whereDate('scheduled_at', '>=', today())
            ->orderBy('scheduled_at')
            ->limit(10)
            ->get();

        return view('admin.doctors.show', compact('doctor', 'upcomingAppointments'));
    }

    public function dashboard()
    {
        $doctor = Auth::user()->doctor;
        if (!$doctor) abort(403);

        $todayAppointments = Appointment::where('doctor_profile_id', $doctor->id)
            ->whereDate('scheduled_at', today())
            ->count();

        $upcomingAppointments = Appointment::where('doctor_profile_id', $doctor->id)
            ->where('status', 'scheduled')
            ->whereDate('scheduled_at', '>=', today())
            ->count();

        $completedAppointments = Appointment::where('doctor_profile_id', $doctor->id)
            ->where('status', 'completed')
            ->count();

        $pendingPrescriptions = Appointment::where('doctor_profile_id', $doctor->id)
            ->where('status', 'pending')
            ->count();

        $recentAppointments = Appointment::with('patient')
            ->where('doctor_profile_id', $doctor->id)
            ->whereDate('scheduled_at', '>=', today())
            ->orderBy('scheduled_at')
            ->limit(10)
            ->get();

        $todayPatientQueue = Appointment::with('patient')
            ->where('doctor_profile_id', $doctor->id)
            ->whereDate('scheduled_at', today())
            ->get()
            ->map(function ($a) {
                return (object)[
                    'id' => $a->id,
                    'name' => $a->patient->name ?? '',
                    'appointment_time' => optional($a->scheduled_at)->format('H:i') ?? '',
                    'status' => $a->status,
                ];
            });

        return view('doctor.dashboard', compact(
            'doctor',
            'todayAppointments',
            'upcomingAppointments',
            'completedAppointments',
            'pendingPrescriptions',
            'recentAppointments',
            'todayPatientQueue'
        ));
    }

    public function myAppointments(Request $request)
    {
        $doctor = Auth::user()->doctor;
        if (!$doctor) abort(403);

        $query = Appointment::with('patient')
            ->where('doctor_profile_id', $doctor->id);

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->date) {
            $query->whereDate('scheduled_at', $request->date);
        }

        $appointments = $query
            ->orderBy('scheduled_at', 'desc')
            ->paginate(15);

        return view('doctor.appointments.index', compact('appointments', 'doctor'));
    }

    public function viewPatient($appointmentId)
    {
        $doctor = Auth::user()->doctor;
        if (!$doctor) abort(403);

        $appointment = Appointment::with(['patient', 'prescription.medicines'])
            ->where('doctor_profile_id', $doctor->id)
            ->findOrFail($appointmentId);

        $patientHistory = Appointment::with(['doctor.user', 'prescription'])
            ->where('patient_id', $appointment->patient_id)
            ->where('status', 'completed')
            ->orderBy('scheduled_at', 'desc')
            ->get();

        return view('doctor.patients.show', compact('appointment', 'patientHistory'));
    }

    public function settings()
    {
        $doctor = Auth::user()->doctor;
        if (!$doctor) abort(403);

        return view('doctor.Setting.index', compact('doctor'));
    }

    public function settingsUpdate(Request $request)
    {
        $user = Auth::user();
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|digits_between:1,11',
            'specialization' => 'nullable|string|max:255',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after_or_equal:start_time',
            'avatar' => 'nullable|image|max:2048',
        ]);
        dd($validated);

        // $user->update([
        //     'name' => $validated['name'],
        //     'phone' => $validated['phone'] ?? $user->phone,
        // ]);

        $user->name = $user->name; 
        $user->phone = $user->phone;
        $user->save();

        
        $doctor = $user->doctor;

        if ($doctor) {
            if ($request->hasFile('avatar')) {
                $path = $request->file('avatar')->store('avatars', 'public');
                $doctor->avatar_url = '/storage/' . $path;
            }

            $doctor->specialization = $validated['specialization'] ?? $doctor->specialization;
            $doctor->start_time = $validated['start_time'] ?? $doctor->start_time;
            $doctor->end_time = $validated['end_time'] ?? $doctor->end_time;
            $doctor->email_notifications = $request->has('email_notifications');
            $doctor->sms_notifications = $request->has('sms_notifications');

            $doctor->save();
        }
        
        return redirect()->route('doctor.settings');
    }
}
