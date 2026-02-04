<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Appointment;
use App\Models\Medicine;
use App\Models\Prescription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Statistics
        $totalPatients = Patient::count();
        $totalDoctors = Doctor::count();
        $totalAppointments = Appointment::count();
        // حساب Revenue بناءً على عدد المواعيد المكتملة (افتراضي: 500 لكل موعد)
        $completedAppointments = Appointment::where('status', 'completed')->count();
        $totalRevenue = $completedAppointments * 500;
        
        $todayAppointments = Appointment::whereDate('scheduled_at', today())->count();
        $pendingAppointments = Appointment::where('status', 'scheduled')->count();
        
        // Low stock medicines - استخدام قيمة افتراضية إذا لم يكن الجدول موجود
        $lowStockCount = 0;
        try {
            $lowStockCount = Medicine::whereHas('inventory', function($query) {
                $query->whereRaw('quantity <= minimum_threshold');
            })->count();
        } catch (\Exception $e) {
            $lowStockCount = 0;
        }
        
        // Expired medicines - استخدام قيمة افتراضية
        $expiredMedicines = 0;
        try {
            $expiredMedicines = Medicine::where('expiry_date', '<', today())->count();
        } catch (\Exception $e) {
            $expiredMedicines = 0;
        }
        
        // Recent appointments (today and upcoming)
        $recentAppointments = Appointment::with(['patient', 'doctor'])
            ->where('scheduled_at', '>=', today())
            ->orderBy('scheduled_at')
            ->limit(10)
            ->get();
        
        // Monthly appointment stats (last 6 months)
        $monthlyStats = Appointment::select(
            DB::raw('DATE_FORMAT(scheduled_at, "%Y-%m") as month'),
            DB::raw('COUNT(*) as count')
        )
            ->where('scheduled_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->get();
        
        // Appointments by status
        $statusStats = Appointment::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status');
        
        return view('admin.dashboard.index', compact(
            'totalPatients',
            'totalDoctors',
            'totalAppointments',
            'totalRevenue',
            'todayAppointments',
            'pendingAppointments',
            'completedAppointments',
            'lowStockCount',
            'expiredMedicines',
            'recentAppointments',
            'monthlyStats',
            'statusStats'
        ));
    }
}
