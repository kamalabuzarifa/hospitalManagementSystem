<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Appointment;
use App\Models\Settings;
use App\Models\Staff;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DashboardAdminController extends Controller
{
    public function index()
    {
        $totalPatients = Patient::query()->count();
        $totalDoctors = Doctor::query()->count();
        $totalAppointments = Appointment::query()->count();
        $totalStaff = Staff::query()->count();

        // Get settings to determine which notifications to show
        $settings = Settings::first();

        // Basic revenue estimate if appointments have a 'fee' column; fallback to 0
        $totalRevenue = 0;
        if (Schema::hasColumn('appointments', 'fee')) {
            $totalRevenue = (float) Appointment::query()->sum('fee');
        }

        // Recent notifications: latest appointments and patient registrations
        $recentAppointments = Appointment::query()
            ->with(['doctor.user', 'patient'])
            ->latest()
            ->limit(3)
            ->get();

        $recentPatients = Patient::query()
            ->latest()
            ->limit(3)
            ->get();

        // Appointment status counts for dashboard card
        $statusStats = Appointment::query()
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        // Build notifications array based on settings
        $notifications = [];

        if ($settings && $settings->notification_new_appointment) {
            foreach ($recentAppointments as $appt) {
                $notifications[] = [
                    'type' => 'appointment',
                    'title' => __('messages.new_appointment'),
                    'doctor' => optional(optional($appt->doctor)->user)->name ?? '—',
                    'patient' => optional($appt->patient)->name ?? '—',
                    'time' => $appt->created_at,
                ];
            }
        }

        if ($settings && $settings->notification_new_patient) {
            foreach ($recentPatients as $patient) {
                $notifications[] = [
                    'type' => 'patient',
                    'title' => __('messages.new_patient'),
                    'patient' => $patient->name,
                    'time' => $patient->created_at,
                ];
            }
        }

        // Sort notifications by time (newest first)
        usort($notifications, function($a, $b) {
            return strtotime($b['time']) - strtotime($a['time']);
        });

        // Limit to 5 most recent
        $notifications = array_slice($notifications, 0, 5);

        return view('admin.dashboard.index', [
            'totalPatients' => $totalPatients,
            'totalDoctors' => $totalDoctors,
            'totalAppointments' => $totalAppointments,
            'totalStaff' => $totalStaff,
            'totalRevenue' => $totalRevenue,
            'recentAppointments' => $recentAppointments,
            'recentPatients' => $recentPatients,
            'statusStats' => $statusStats,
            'notifications' => $notifications,
            'settings' => $settings,
        ]);
    }

    public function exportPatientsCsv(): StreamedResponse
    {
        $fileName = 'patients_export_'.now()->format('Ymd_His').'.csv';
        $columns = ['id','name','email','phone','created_at'];

        $callback = function () use ($columns) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $columns);
            Patient::query()->orderBy('id')->chunk(500, function ($chunk) use ($handle) {
                foreach ($chunk as $p) {
                    fputcsv($handle, [
                        $p->id, $p->name, $p->email, $p->phone, optional($p->created_at)->toDateTimeString(),
                    ]);
                }
            });
            fclose($handle);
        };

        return response()->streamDownload($callback, $fileName, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function exportAppointmentsCsv(): StreamedResponse
    {
        $fileName = 'appointments_export_'.now()->format('Ymd_His').'.csv';
        $columns = ['id','patient_id','doctor_id','status','scheduled_at','created_at'];

        $callback = function () use ($columns) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $columns);
            Appointment::query()->orderBy('id')->chunk(500, function ($chunk) use ($handle) {
                foreach ($chunk as $a) {
                    fputcsv($handle, [
                        $a->id, $a->patient_id, $a->doctor_id, $a->status, optional($a->scheduled_at)->toDateTimeString(), optional($a->created_at)->toDateTimeString(),
                    ]);
                }
            });
            fclose($handle);
        };

        return response()->streamDownload($callback, $fileName, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function importPatientsCsv(Request $request)
    {
        $request->validate([
            'file' => ['required','file','mimes:csv,txt'],
        ]);

        $path = $request->file('file')->getRealPath();
        $handle = fopen($path, 'r');
        $header = fgetcsv($handle);
        $count = 0;
        while (($row = fgetcsv($handle)) !== false) {
            $data = array_combine($header, $row);
            if (!$data) { continue; }
            Patient::updateOrCreate(
                ['id' => $data['id'] ?? null],
                [
                    'name' => $data['name'] ?? '',
                    'email' => $data['email'] ?? null,
                    'phone' => $data['phone'] ?? null,
                ]
            );
            $count++;
        }
        fclose($handle);

        return back()->with('status', "Imported {$count} patients");
    }

    public function importAppointmentsCsv(Request $request)
    {
        $request->validate([
            'file' => ['required','file','mimes:csv,txt'],
        ]);

        $path = $request->file('file')->getRealPath();
        $handle = fopen($path, 'r');
        $header = fgetcsv($handle);
        $count = 0;
        while (($row = fgetcsv($handle)) !== false) {
            $data = array_combine($header, $row);
            if (!$data) { continue; }

            $doctorProfileId = $data['doctor_profile_id'] ?? null;
            $doctorUserId = $data['doctor_id'] ?? null;
            if (!$doctorProfileId && $doctorUserId) {
                $doctorProfileId = Doctor::query()->where('user_id', $doctorUserId)->value('id');
            }

            Appointment::updateOrCreate(
                ['id' => $data['id'] ?? null],
                [
                    'patient_id' => $data['patient_id'] ?? null,
                    'doctor_id' => $data['doctor_id'] ?? null,
                    'doctor_profile_id' => $doctorProfileId,
                    'status' => $data['status'] ?? 'scheduled',
                    'scheduled_at' => $data['scheduled_at'] ?? null,
                ]
            );
            $count++;
        }
        fclose($handle);

        return back()->with('status', "Imported {$count} appointments");
    }

    public function counts(): \Illuminate\Http\JsonResponse
    {
        $totals = [
            'patients' => (int) Patient::query()->count(),
            'doctors' => (int) Doctor::query()->count(),
            'appointments' => (int) Appointment::query()->count(),
            'staff' => (int) Staff::query()->count(),
        ];
        $statusStats = Appointment::query()
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        return response()->json([
            'totals' => $totals,
            'status' => $statusStats,
        ]);
    }
}

