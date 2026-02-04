<?php

namespace App\Http\Controllers\Doctor;


use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class DashboardDoctorController extends Controller

{

    public function index()
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

        // اجلب جميع المرضى من جدول المرضى
        $patients = \App\Models\Patient::orderBy('name')->get();

        // جلب أول وصفة للطبيب (بدون شرط status)
        $prescription = \App\Models\Prescription::where('doctor_id', $doctor->id)
            ->with(['patient'])
            ->first();
        $patient = $prescription ? $prescription->patient : null;

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $unreadNotifications = $user->unreadNotifications()->latest()->limit(5)->get();

        return view('doctor.dashboard', compact(
            'doctor',
            'todayAppointments',
            'upcomingAppointments',
            'completedAppointments',
            'pendingPrescriptions',
            'patients',
            'prescription',
            'patient',
            'unreadNotifications'
        ));
    }

    public function notificationMarkRead(DatabaseNotification $notification)
    {
        $user = Auth::user();
        if (!$user) {
            abort(403);
        }

        if ((string) $notification->notifiable_type !== get_class($user) || (int) $notification->notifiable_id !== (int) $user->id) {
            abort(403);
        }

        $notification->markAsRead();

        return back();
    }
    public function patientQueue(Request $request)
    {
        $doctor = Auth::user()->doctor;
        if (!$doctor) abort(403);

        $search = $request->input('search');
        $appointments = \App\Models\Appointment::with('patient')
            ->where('doctor_profile_id', $doctor->id)
            ->whereDate('scheduled_at', today())
            ->when($search, function($query, $search) {
                $query->whereHas('patient', function($q) use ($search) {
                    $q->where('name', 'like', "%$search%")
                      ->orWhere('name', 'like', "%" . ucfirst($search) . "%");
                });
            })
            ->orderBy('scheduled_at')
            ->get();

        $allAppointments = \App\Models\Appointment::with('patient')
            ->where('doctor_profile_id', $doctor->id)
            ->when($search, function($query, $search) {
                $query->whereHas('patient', function($q) use ($search) {
                    $q->where('name', 'like', "%$search%")
                      ->orWhere('name', 'like', "%" . ucfirst($search) . "%");
                });
            })
            ->orderByDesc('scheduled_at')
            ->limit(50)
            ->get();

        return view('doctor.patient-queue.index', compact('appointments', 'allAppointments', 'search'));
    }
    
}
