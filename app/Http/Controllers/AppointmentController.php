<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Http\Requests\StoreAppointmentRequest;
use App\Http\Requests\UpdateAppointmentRequest;
use App\Traits\LogsActivity;
use App\Models\Settings;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    use LogsActivity;

    public function __construct()
    {
        $this->middleware('permission:appointments.view,manage_appointments')->only(['index', 'show']);
        $this->middleware('permission:appointments.create,manage_appointments')->only(['create', 'store']);
        $this->middleware('permission:appointments.edit,manage_appointments')->only(['edit', 'update']);
        $this->middleware('permission:appointments.delete,manage_appointments')->only(['destroy']);
    }

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
            'allow_edit_appointments' => true,
            'default_appointment_duration' => 20,
            'auto_assign_doctor' => false,
            'dark_mode' => false,
        ]);
    }

    public function index(Request $request)
    {
        $query = Appointment::with(['patient', 'doctor.user'])->latest('scheduled_at');

        if ($request->date) {
            $query->whereDate('scheduled_at', $request->date);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->doctor_id) {
            $query->where('doctor_profile_id', $request->doctor_id);
        }

        $appointments = $query->paginate(25);
        $doctors = Doctor::with('user')->get();

        return view('admin.appointments.index', compact('appointments', 'doctors'));
    }

    public function create()
    {
        $settings = $this->getGlobalSettings();
        if (!$settings->allow_edit_appointments) {
            return redirect()
                ->route('admin.appointments.index')
                ->withErrors(['settings' => __('messages.appointment_changes_disabled')]);
        }

        $patients = Patient::orderBy('name')->get();
        $doctors = Doctor::with('user')->get();

        return view('admin.appointments.create', compact('patients', 'doctors'));
    }

    public function store(StoreAppointmentRequest $request)
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

        if ($settings->auto_assign_doctor && empty($data['doctor_id'])) {
            $generalDoctor = Doctor::query()
                ->join('users', 'users.id', '=', 'doctors.user_id')
                ->whereNotNull('doctors.specialization')
                ->whereRaw('LOWER(TRIM(doctors.specialization)) LIKE ?', ['%general%'])
                ->orderBy('users.name')
                ->select('doctors.*')
                ->first();

            if ($generalDoctor) {
                $data['doctor_id'] = $generalDoctor->id;
            } else {
                $lastDoctorProfileId = Appointment::query()
                    ->where('patient_id', $data['patient_id'] ?? null)
                    ->whereNotNull('doctor_profile_id')
                    ->latest('scheduled_at')
                    ->value('doctor_profile_id');

                if ($lastDoctorProfileId) {
                    $data['doctor_id'] = $lastDoctorProfileId;
                }
            }
        }

        $doctorProfileId = $data['doctor_id'] ?? null;
        if ($doctorProfileId) {
            $doctor = Doctor::query()->whereKey($doctorProfileId)->firstOrFail();
            $data['doctor_profile_id'] = $doctor->id;
            $data['doctor_id'] = $doctor->user_id;
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

        $appointment = Appointment::create($data);
        $this->logActivity('scheduled_appointment', ['appointment_id' => $appointment->id, 'patient_id' => $appointment->patient_id]);

        return redirect()
            ->route('admin.appointments.index')
            ->with('success', __('messages.appointment_scheduled_success'));
    }

    public function show(Appointment $appointment)
    {
        $appointment->load(['patient', 'doctor.user']);
        return view('admin.appointments.show', compact('appointment'));
    }

    public function edit(Appointment $appointment)
    {
        $settings = $this->getGlobalSettings();
        if (!$settings->allow_edit_appointments) {
            return redirect()
                ->route('admin.appointments.show', $appointment)
                ->withErrors(['settings' => __('messages.appointment_changes_disabled')]);
        }

        $patients = Patient::orderBy('name')->get();
        $doctors = Doctor::with('user')->get();

        return view('admin.appointments.edit', compact('appointment', 'patients', 'doctors'));
    }

    public function update(UpdateAppointmentRequest $request, Appointment $appointment)
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

        if ($settings->auto_assign_doctor && empty($data['doctor_id'])) {
            $generalDoctor = Doctor::query()
                ->join('users', 'users.id', '=', 'doctors.user_id')
                ->whereNotNull('doctors.specialization')
                ->whereRaw('LOWER(TRIM(doctors.specialization)) LIKE ?', ['%general%'])
                ->orderBy('users.name')
                ->select('doctors.*')
                ->first();

            if ($generalDoctor) {
                $data['doctor_id'] = $generalDoctor->id;
            } else {
                $patientId = $data['patient_id'] ?? $appointment->patient_id;
                $lastDoctorProfileId = Appointment::query()
                    ->where('patient_id', $patientId)
                    ->whereNotNull('doctor_profile_id')
                    ->latest('scheduled_at')
                    ->value('doctor_profile_id');

                if ($lastDoctorProfileId) {
                    $data['doctor_id'] = $lastDoctorProfileId;
                }
            }
        }

        $doctorProfileId = $data['doctor_id'] ?? null;
        if ($doctorProfileId) {
            $doctor = Doctor::query()->whereKey($doctorProfileId)->firstOrFail();
            $data['doctor_profile_id'] = $doctor->id;
            $data['doctor_id'] = $doctor->user_id;
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
            ->route('admin.appointments.show', $appointment)
            ->with('success', __('messages.appointment_updated_success'));
    }

    public function destroy(Appointment $appointment)
    {
        $settings = $this->getGlobalSettings();
        if (!$settings->allow_edit_appointments) {
            return back()->withErrors(['settings' => __('messages.appointment_changes_disabled')]);
        }

        $appointment->delete();

        return redirect()
            ->route('admin.appointments.index')
            ->with('success', __('messages.appointment_deleted_success'));
    }
}
