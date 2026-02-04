<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Appointment;
use App\Http\Requests\StorePatientRequest;
use App\Http\Requests\UpdatePatientRequest;
use App\Http\Requests\StoreAppointmentRequest;
use App\Http\Requests\UpdateAppointmentRequest;
use App\Traits\LogsActivity;
use App\Models\Settings;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;

class ReceptionController extends Controller
{
    use LogsActivity;

    private function getGlobalSettings(): Settings
    {
        return Settings::query()->firstOrCreate([], [
            'language' => 'en',
            'time_format' => '24',
            'date_format' => 'DD/MM/YYYY',
            'backup_frequency' => 'weekly',
            'backup_destination' => null,
            'notification_new_appointment' => true,
            'notification_new_patient' => true,
            'reception_dashboard_notifications' => true,
            'pharmacy_dashboard_notifications' => true,
            'allow_edit_appointments' => true,
            'allow_edit_patients' => true,
            'default_appointment_duration' => 20,
            'auto_assign_doctor' => false,
            'dark_mode' => false,
        ]);
    }
    // public function __construct()
    // {
    //     $this->middleware('auth');
    // }

    // ============ PATIENTS ============

    public function patientIndex(Request $request)
    {
        $query = Patient::latest();

        if ($request->search) {
            $search = trim((string) $request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%')
                    ->orWhere('patient_code', 'like', '%' . $search . '%');

                if (is_numeric($search)) {
                    $q->orWhere('id', (int) $search);
                }
            });
        }

        $patients = $query->paginate(15);

        return view('reception.patients.index', compact('patients'));
    }

    public function patientCreate()
    {
        return view('reception.patients.create');
    }

    public function patientStore(StorePatientRequest $request)
    {
        $settings = $this->getGlobalSettings();
        if (!$settings->allow_edit_patients) {
            return back()->withErrors(['settings' => __('messages.patient_changes_disabled')]);
        }

        Patient::create($request->validated());

        return redirect()
            ->route('reception.patients.index')
            ->with('success', __('messages.patient_added_success'));
    }

    public function patientShow(Patient $patient)
    {
        return view('reception.patients.show', compact('patient'));
    }

    public function patientEdit(Patient $patient)
    {
        $settings = $this->getGlobalSettings();
        if (!$settings->allow_edit_patients) {
            abort(403, __('messages.patient_changes_disabled'));
        }

        return view('reception.patients.edit', compact('patient'));
    }

    // ============ DASHBOARD ============
    public function dashboard()
    {
        $settings = $this->getGlobalSettings();

        $today = now()->toDateString();
        $urgentWindowMinutes = 30;
        $urgentCutoff = now()->copy()->addMinutes($urgentWindowMinutes)->toDateTimeString();

        $todaysAppointments = Appointment::with(['patient', 'doctor.user'])
            ->whereDate('scheduled_at', $today)
            ->orderBy('scheduled_at')
            ->get();

        $urgentAppointments = Appointment::with(['patient', 'doctor.user'])
            ->whereDate('scheduled_at', $today)
            ->whereIn('status', ['scheduled', 'pending'])
            ->where('scheduled_at', '<=', $urgentCutoff)
            ->orderBy('scheduled_at')
            ->limit(10)
            ->get();

        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        $unreadNotifications = $user
            ? $user->unreadNotifications()->latest()->limit(5)->get()
            : collect();

        return view('reception.dashboard', compact('settings', 'today', 'todaysAppointments', 'urgentAppointments', 'urgentWindowMinutes', 'unreadNotifications'));
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

    public function settings()
    {
        $settings = $this->getGlobalSettings();

        return view('reception.settings.index', compact('settings'));
    }

    public function updateSettings(Request $request)
    {
        $settings = $this->getGlobalSettings();

        $validated = $request->validate([
            'language' => ['required', 'in:ar,en'],
            'notification_new_appointment' => ['nullable', 'boolean'],
            'notification_new_patient' => ['nullable', 'boolean'],
            'reception_dashboard_notifications' => ['nullable', 'boolean'],
            'allow_edit_appointments' => ['nullable', 'boolean'],
            'allow_edit_patients' => ['nullable', 'boolean'],
            'default_appointment_duration' => ['required', 'integer', 'min:5', 'max:240'],
            'auto_assign_doctor' => ['nullable', 'boolean'],
            'dark_mode' => ['nullable', 'boolean'],
        ]);

        $settings->notification_new_appointment = (bool) ($validated['notification_new_appointment'] ?? false);
        $settings->notification_new_patient = (bool) ($validated['notification_new_patient'] ?? false);
        $settings->reception_dashboard_notifications = (bool) ($validated['reception_dashboard_notifications'] ?? false);
        $settings->allow_edit_appointments = (bool) ($validated['allow_edit_appointments'] ?? false);
        $settings->allow_edit_patients = (bool) ($validated['allow_edit_patients'] ?? false);
        $settings->default_appointment_duration = (int) $validated['default_appointment_duration'];
        $settings->auto_assign_doctor = (bool) ($validated['auto_assign_doctor'] ?? false);
        $settings->dark_mode = (bool) ($validated['dark_mode'] ?? false);
        $settings->save();

        // Language is session-based (do not persist on settings table)
        session(['locale' => $validated['language']]);
        app()->setLocale($validated['language']);

        return redirect()
            ->route('reception.settings')
            ->with('success', __('messages.settings_saved_success'));
    }

    public function patientUpdate(UpdatePatientRequest $request, Patient $patient)
    {
        $settings = $this->getGlobalSettings();
        if (!$settings->allow_edit_patients) {
            abort(403, __('messages.patient_changes_disabled'));
        }

        $patient->update($request->validated());

        return redirect()
            ->route('reception.patients.show', $patient)
            ->with('success', __('messages.patient_updated_success'));
    }

    public function patientDestroy(Patient $patient)
    {
        $settings = $this->getGlobalSettings();
        if (!$settings->allow_edit_patients) {
            abort(403, __('messages.patient_changes_disabled'));
        }

        $patient->delete();

        return redirect()
            ->route('reception.patients.index')
            ->with('success', __('messages.patient_deleted_success'));
    }

    // ============ APPOINTMENTS ============

    public function appointmentIndex(Request $request)
    {
        $query = Appointment::with(['patient', 'doctor.user'])
            ->orderByDesc('scheduled_at');

        if ($request->date) {
            $query->whereDate('scheduled_at', $request->date);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->search) {
            $search = trim((string) $request->search);

            $query->where(function ($q) use ($search) {
                $q->whereHas('patient', function ($patientQuery) use ($search) {
                    $patientQuery
                        ->where('name', 'like', '%' . $search . '%')
                        ->orWhere('phone', 'like', '%' . $search . '%');
                })
                ->orWhereHas('doctor', function ($doctorQuery) use ($search) {
                    $doctorQuery->whereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', '%' . $search . '%');
                    });
                });
            });
        }
        $appointments = $query->paginate(20);
        $patients = Patient::orderBy('name')->get();
        $doctors = Doctor::query()
            ->with('user')
            ->join('users', 'users.id', '=', 'doctors.user_id')
            ->orderBy('users.name')
            ->select('doctors.*')
            ->get();
        return view('reception.appointments.index', compact('appointments', 'patients', 'doctors'));
    }

    public function appointmentCreate()
    {
        $patients = Patient::orderBy('name')->get();
        $doctors = Doctor::query()
            ->with('user')
            ->join('users', 'users.id', '=', 'doctors.user_id')
            ->orderBy('users.name')
            ->select('doctors.*')
            ->get();

        return view('reception.appointments.create', compact('patients', 'doctors'));
    }

    public function appointmentStore(StoreAppointmentRequest $request)
    {
        $settings = $this->getGlobalSettings();
        if (!$settings->allow_edit_appointments) {
            return back()->withErrors(['settings' => __('messages.appointment_changes_disabled')]);
        }

        $data = $request->validated();

        if (!$settings->auto_assign_doctor && empty($data['doctor_id'])) {
            return back()
                ->withInput()
                    ->withErrors(['doctor_id' => __('messages.doctor_required_when_auto_assign_disabled')]);
        }

        $doctorProfileId = $data['doctor_id'] ?? null;

        // Auto assign doctor: prefer Department (specialization) = general
        if (!$doctorProfileId && $settings->auto_assign_doctor) {
            $generalDoctor = Doctor::query()
                ->join('users', 'users.id', '=', 'doctors.user_id')
                ->whereNotNull('doctors.specialization')
                ->whereRaw('LOWER(TRIM(doctors.specialization)) LIKE ?', ['%general%'])
                ->orderBy('users.name')
                ->select('doctors.*')
                ->first();

            if ($generalDoctor) {
                $doctorProfileId = $generalDoctor->id;
            } elseif (!empty($data['patient_id'])) {
                // Fallback: last doctor used for this patient
                $lastAppointment = Appointment::query()
                    ->where('patient_id', $data['patient_id'])
                    ->whereNotNull('doctor_profile_id')
                    ->orderByDesc('scheduled_at')
                    ->orderByDesc('id')
                    ->first();

                if ($lastAppointment?->doctor_profile_id) {
                    $doctorProfileId = $lastAppointment->doctor_profile_id;
                }
            }
        }

        if ($doctorProfileId) {
            $doctor = Doctor::query()->whereKey($doctorProfileId)->firstOrFail();
            $data['doctor_profile_id'] = $doctor->id;
            $data['doctor_id'] = $doctor->user_id; // keep legacy column in sync
        } else {
            $data['doctor_profile_id'] = null;
            $data['doctor_id'] = null;
        }

        $scheduledAt = Carbon::createFromFormat('Y-m-d H:i', $data['date'] . ' ' . $data['time']);
        $data['scheduled_at'] = $scheduledAt->toDateTimeString();

        // Prevent overlap for doctor appointments based on default duration
        if (!empty($data['doctor_profile_id'])) {
            $durationMinutes = (int) ($settings->default_appointment_duration ?? 20);
            $windowStart = $scheduledAt->copy()->subMinutes(max(1, $durationMinutes) - 1);
            $windowEnd = $scheduledAt->copy()->addMinutes(max(1, $durationMinutes) - 1);

            $conflict = Appointment::query()
                ->where('doctor_profile_id', $data['doctor_profile_id'])
                ->whereNotNull('scheduled_at')
                ->where('status', '!=', 'cancelled')
                ->whereBetween('scheduled_at', [$windowStart->toDateTimeString(), $windowEnd->toDateTimeString()])
                ->exists();

            if ($conflict) {
                return back()
                    ->withInput()
                    ->withErrors(['time' => __('messages.doctor_appointment_conflict_time')]);
            }
        }

        Appointment::create($data);

        return redirect()
            ->route('reception.appointments.index')
            ->with('success', __('messages.appointment_scheduled_success'));
    }

    public function appointmentShow(Appointment $appointment)
    {
        return view('reception.appointments.show', compact('appointment'));
    }

    public function appointmentEdit(Appointment $appointment)
    {
        $settings = $this->getGlobalSettings();
        if (!$settings->allow_edit_appointments) {
            return redirect()
                ->route('reception.appointments.show', $appointment)
                ->withErrors(['settings' => __('messages.appointment_changes_disabled')]);
        }

        $patients = Patient::orderBy('name')->get();
        $doctors = Doctor::query()
            ->with('user')
            ->join('users', 'users.id', '=', 'doctors.user_id')
            ->orderBy('users.name')
            ->select('doctors.*')
            ->get();

        return view('reception.appointments.edit', compact('appointment', 'patients', 'doctors'));
    }

    public function appointmentUpdate(UpdateAppointmentRequest $request, Appointment $appointment)
    {
        $settings = $this->getGlobalSettings();
        if (!$settings->allow_edit_appointments) {
            return back()->withErrors(['settings' => __('messages.appointment_changes_disabled')]);
        }

        $data = $request->validated();

        if (!$settings->auto_assign_doctor && empty($data['doctor_id'])) {
            return back()
                ->withInput()
                ->withErrors(['doctor_id' => __('messages.doctor_required_when_auto_assign_disabled')]);
        }

        $doctorProfileId = $data['doctor_id'] ?? null;

        // Auto assign doctor on update if cleared: prefer Department (specialization) = general
        if (!$doctorProfileId && $settings->auto_assign_doctor) {
            $generalDoctor = Doctor::query()
                ->join('users', 'users.id', '=', 'doctors.user_id')
                ->whereNotNull('doctors.specialization')
                ->whereRaw('LOWER(TRIM(doctors.specialization)) LIKE ?', ['%general%'])
                ->orderBy('users.name')
                ->select('doctors.*')
                ->first();

            if ($generalDoctor) {
                $doctorProfileId = $generalDoctor->id;
            } elseif (!empty($data['patient_id'])) {
                $lastAppointment = Appointment::query()
                    ->where('patient_id', $data['patient_id'])
                    ->whereNotNull('doctor_profile_id')
                    ->whereKeyNot($appointment->id)
                    ->orderByDesc('scheduled_at')
                    ->orderByDesc('id')
                    ->first();

                if ($lastAppointment?->doctor_profile_id) {
                    $doctorProfileId = $lastAppointment->doctor_profile_id;
                }
            }
        }

        if ($doctorProfileId) {
            $doctor = Doctor::query()->whereKey($doctorProfileId)->firstOrFail();
            $data['doctor_profile_id'] = $doctor->id;
            $data['doctor_id'] = $doctor->user_id; // keep legacy column in sync
        } else {
            $data['doctor_profile_id'] = null;
            $data['doctor_id'] = null;
        }

        $scheduledAt = Carbon::createFromFormat('Y-m-d H:i', $data['date'] . ' ' . $data['time']);
        $data['scheduled_at'] = $scheduledAt->toDateTimeString();

        if (!empty($data['doctor_profile_id'])) {
            $durationMinutes = (int) ($settings->default_appointment_duration ?? 20);
            $windowStart = $scheduledAt->copy()->subMinutes(max(1, $durationMinutes) - 1);
            $windowEnd = $scheduledAt->copy()->addMinutes(max(1, $durationMinutes) - 1);

            $conflict = Appointment::query()
                ->where('doctor_profile_id', $data['doctor_profile_id'])
                ->whereNotNull('scheduled_at')
                ->where('status', '!=', 'cancelled')
                ->whereKeyNot($appointment->id)
                ->whereBetween('scheduled_at', [$windowStart->toDateTimeString(), $windowEnd->toDateTimeString()])
                ->exists();

            if ($conflict) {
                return back()
                    ->withInput()
                    ->withErrors(['time' => __('messages.doctor_appointment_conflict_time')]);
            }
        }

        $appointment->update($data);

        return redirect()
            ->route('reception.appointments.show', $appointment)
            ->with('success', __('messages.appointment_updated_success'));
    }

    public function appointmentDestroy(Appointment $appointment)
    {
        $settings = $this->getGlobalSettings();
        if (!$settings->allow_edit_appointments) {
            return back()->withErrors(['settings' => __('messages.appointment_changes_disabled')]);
        }

        $appointment->delete();

        return redirect()
            ->route('reception.appointments.index')
            ->with('success', __('messages.appointment_deleted_success'));
    }

    // ============ APPOINTMENT ACTIONS ============

    public function appointmentCheckin(Appointment $appointment)
    {
        $settings = $this->getGlobalSettings();
        if (!$settings->allow_edit_appointments) {
            return back()->withErrors(['settings' => __('messages.appointment_changes_disabled')]);
        }

        $appointment->update(['status' => 'checked_in']);

        return back()->with('success', __('messages.patient_checked_in_success'));
    }

    public function appointmentCancel(Appointment $appointment)
    {
        $settings = $this->getGlobalSettings();
        if (!$settings->allow_edit_appointments) {
            return back()->withErrors(['settings' => __('messages.appointment_changes_disabled')]);
        }

        $appointment->update(['status' => 'cancelled']);
        $this->logActivity('cancelled_appointment', ['appointment_id' => $appointment->id]);

        return back()->with('success', __('messages.appointment_cancelled_success'));
    }

    public function appointmentComplete(Appointment $appointment)
    {
        $settings = $this->getGlobalSettings();
        if (!$settings->allow_edit_appointments) {
            return back()->withErrors(['settings' => __('messages.appointment_changes_disabled')]);
        }

        $appointment->update(['status' => 'completed']);
        $this->logActivity('discharged_patient', ['appointment_id' => $appointment->id, 'patient_id' => $appointment->patient_id]);
        $this->logActivity('completed_appointment', ['appointment_id' => $appointment->id]);

        return back()->with('success', __('messages.appointment_completed_success'));
    }

    public function assistSurgery(Appointment $appointment)
    {
        $this->logActivity('assisted_surgery', ['appointment_id' => $appointment->id, 'patient_id' => $appointment->patient_id]);

        return back()->with('success', __('messages.surgery_assistance_recorded_success'));
    }
}
