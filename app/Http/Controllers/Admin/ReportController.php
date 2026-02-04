<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Exports\ReportsExport;
use App\Models\Settings;
use App\Traits\LogsActivity;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

class ReportController extends Controller
{
    use LogsActivity;
    protected function buildTimeSeries(string $frequency, Carbon $now, ?string $dateFormat = null): array
    {
        $labels = [];
        $appointments = [];
        $patients = [];

        $unitSingular = match ($frequency) {
            'daily' => 'Day',
            'monthly' => 'Month',
            default => 'Week',
        };
        $unitPlural = Str::plural($unitSingular);
        $headerColumn = match ($frequency) {
            'daily' => 'Date',
            'monthly' => 'Month',
            default => 'Week',
        };
        $locale = app()->getLocale();
        // Use locale-aware readable formats regardless of Settings date_format for better labels
        $dailyFmt = 'j M';     // e.g., 16 Dec / ١٦ ديس
        $weeklyFmt = 'j M';    // week start date
        $monthlyFmt = 'M Y';   // e.g., Dec 2025 / ديسمبر 2025

        for ($i = 6; $i >= 0; $i--) {
            if ($frequency === 'daily') {
                $start = $now->copy()->startOfDay()->subDays($i);
                $end = $start->copy()->endOfDay();
                $labels[] = $start->locale($locale)->translatedFormat($dailyFmt);
            } elseif ($frequency === 'monthly') {
                $start = $now->copy()->startOfMonth()->subMonths($i);
                $end = $start->copy()->endOfMonth();
                $labels[] = $start->locale($locale)->translatedFormat($monthlyFmt);
            } else { // weekly
                $start = $now->copy()->startOfWeek()->subWeeks($i);
                $end = $start->copy()->endOfWeek();
                $labels[] = $start->locale($locale)->translatedFormat($weeklyFmt);
            }

            $appointments[] = DB::table('appointments')
                ->whereBetween('scheduled_at', [$start, $end])
                ->count();
            $patients[] = DB::table('patients')
                ->whereBetween('created_at', [$start, $end])
                ->count();
        }

        $timeframeLabel = match ($frequency) {
            'daily' => __('messages.last_7_days'),
            'monthly' => __('messages.last_7_months'),
            default => __('messages.last_7_weeks'),
        };

        return [
            'labels' => $labels,
            'appointments' => $appointments,
            'patients' => $patients,
            'unitSingular' => $unitSingular,
            'unitPlural' => $unitPlural,
            'timeframeLabel' => $timeframeLabel,
            'headerColumn' => $headerColumn,
        ];
    }

    public function index()
    {
        $now = Carbon::now();
        $settings = Settings::first();
        $frequency = $settings->backup_frequency ?? 'weekly';

        // Time series based on backup frequency
        $series = $this->buildTimeSeries($frequency, $now, $settings->date_format ?? null);

        // Appointments by month (last 12 months)
        $appointmentsByMonth = DB::table('appointments')
            ->selectRaw("DATE_FORMAT(scheduled_at, '%Y-%m') as ym, COUNT(*) as total")
            ->where('scheduled_at', '>=', $now->copy()->subMonths(11)->startOfMonth())
            ->groupBy('ym')
            ->orderBy('ym')
            ->get();

        // Top doctors by appointment count (match timeframe start)
        $topStart = match ($frequency) {
            'daily' => $now->copy()->startOfDay()->subDays(6),
            'monthly' => $now->copy()->startOfMonth()->subMonths(6),
            default => $now->copy()->startOfWeek()->subWeeks(6),
        };
        $topDoctorsRaw = DB::table('appointments as a')
            ->join('users as u', 'u.id', '=', 'a.doctor_id')
            ->select('a.doctor_id', 'u.name', DB::raw('COUNT(*) as total'))
            ->whereNotNull('a.doctor_id')
            ->where('a.scheduled_at', '>=', $topStart)
            ->groupBy('a.doctor_id', 'u.name')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // Calculate doctor percentages (based on max)
        $maxDoctorCount = $topDoctorsRaw->max('total') ?: 1;
        $topDoctors = $topDoctorsRaw->map(function ($doctor) use ($maxDoctorCount) {
            $doctor->percentage = round(($doctor->total / $maxDoctorCount) * 100);
            return $doctor;
        });

        // Status breakdown
        $statusCounts = DB::table('appointments')
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $totals = [
            'appointments' => DB::table('appointments')->count(),
            'patients' => DB::table('patients')->count(),
            'doctors' => DB::table('doctors')->count(),
        ];

        $seriesTitle = match ($frequency) {
            'daily' => 'Daily',
            'monthly' => 'Monthly',
            default => 'Weekly',
        };

        return view('admin.reports.index', [
            'appointmentsByMonth' => $appointmentsByMonth,
            'topDoctors' => $topDoctors,
            'statusCounts' => $statusCounts,
            'totals' => $totals,
            'weeklySeries' => [
                'labels' => $series['labels'],
                'appointments' => $series['appointments'],
                'patients' => $series['patients'],
            ],
            'seriesUnitSingular' => $series['unitSingular'],
            'seriesUnitPlural' => $series['unitPlural'],
            'timeframeLabel' => $series['timeframeLabel'],
            'seriesTitle' => $seriesTitle,
            'headerColumn' => $series['headerColumn'],
        ]);
    }

    public function exportPdf()
    {
        $this->logActivity('exported_report_pdf', ['format' => 'PDF', 'date' => now()->format('Y-m-d H:i')]);
        
        $now = Carbon::now();
        $settings = Settings::first();
        $frequency = $settings->backup_frequency ?? 'weekly';

        // Time series
        $series = $this->buildTimeSeries($frequency, $now, $settings->date_format ?? null);

        // Top doctors
        $topStart = match ($frequency) {
            'daily' => $now->copy()->startOfDay()->subDays(6),
            'monthly' => $now->copy()->startOfMonth()->subMonths(6),
            default => $now->copy()->startOfWeek()->subWeeks(6),
        };
        $topDoctorsRaw = DB::table('appointments as a')
            ->join('users as u', 'u.id', '=', 'a.doctor_id')
            ->select('a.doctor_id', 'u.name', DB::raw('COUNT(*) as total'))
            ->whereNotNull('a.doctor_id')
            ->where('a.scheduled_at', '>=', $topStart)
            ->groupBy('a.doctor_id', 'u.name')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $maxDoctorCount = $topDoctorsRaw->max('total') ?: 1;
        $topDoctors = $topDoctorsRaw->map(function ($doctor) use ($maxDoctorCount) {
            $doctor->percentage = round(($doctor->total / $maxDoctorCount) * 100);
            return $doctor;
        });

        $totals = [
            'appointments' => DB::table('appointments')->count(),
            'patients' => DB::table('patients')->count(),
            'doctors' => DB::table('doctors')->count(),
        ];

        $seriesTitle = match ($frequency) {
            'daily' => 'Daily',
            'monthly' => 'Monthly',
            default => 'Weekly',
        };

        $data = [
            'weeklySeries' => [
                'labels' => $series['labels'],
                'appointments' => $series['appointments'],
                'patients' => $series['patients'],
            ],
            'topDoctors' => $topDoctors,
            'totals' => $totals,
            'generatedAt' => now()->format('Y-m-d H:i:s'),
            'seriesUnitSingular' => $series['unitSingular'],
            'seriesUnitPlural' => $series['unitPlural'],
            'timeframeLabel' => $series['timeframeLabel'],
            'seriesTitle' => $seriesTitle,
            'headerColumn' => $series['headerColumn'],
            'isArabic' => false, // Force English for PDF - DomPDF doesn't support RTL properly
        ];

        // Temporarily switch to English for PDF
        $currentLocale = app()->getLocale();
        app()->setLocale('en');

        $pdf = Pdf::loadView('admin.reports.export-pdf', $data)
            ->setPaper('a4')
            ->setOption('margin-top', 0)
            ->setOption('margin-bottom', 0)
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isFontSubsettingEnabled', true);

        // Restore locale
        app()->setLocale($currentLocale);

        return $pdf->download('hospital-reports-' . now()->format('Y-m-d') . '.pdf');
    }

    public function exportExcel()
    {
        $this->logActivity('exported_report_excel', ['format' => 'Excel', 'date' => now()->format('Y-m-d H:i')]);
        
        $now = Carbon::now();
        $settings = Settings::first();
        $frequency = $settings->backup_frequency ?? 'weekly';

        // Time series
        $series = $this->buildTimeSeries($frequency, $now, $settings->date_format ?? null);

        // Top doctors
        $topStart = match ($frequency) {
            'daily' => $now->copy()->startOfDay()->subDays(6),
            'monthly' => $now->copy()->startOfMonth()->subMonths(6),
            default => $now->copy()->startOfWeek()->subWeeks(6),
        };
        $topDoctorsRaw = DB::table('appointments as a')
            ->join('users as u', 'u.id', '=', 'a.doctor_id')
            ->select('a.doctor_id', 'u.name', DB::raw('COUNT(*) as total'))
            ->whereNotNull('a.doctor_id')
            ->where('a.scheduled_at', '>=', $topStart)
            ->groupBy('a.doctor_id', 'u.name')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $maxDoctorCount = $topDoctorsRaw->max('total') ?: 1;
        $topDoctors = $topDoctorsRaw->map(function ($doctor) use ($maxDoctorCount) {
            $doctor->percentage = round(($doctor->total / $maxDoctorCount) * 100);
            return $doctor;
        });

        $totals = [
            'appointments' => DB::table('appointments')->count(),
            'patients' => DB::table('patients')->count(),
            'doctors' => DB::table('doctors')->count(),
        ];

        // Build Excel data with proper structure
        $weeklyData = [];

        // Summary Statistics Section (First)
        $weeklyData[] = [__('messages.summary_statistics'), '', ''];
        $weeklyData[] = [__('messages.total_appointments'), $totals['appointments'], ''];
        $weeklyData[] = [__('messages.total_patients'), $totals['patients'], ''];
        $weeklyData[] = [__('messages.total_doctors'), $totals['doctors'], ''];

        // Weekly Statistics Section
        $weeklyData[] = ['', '', ''];
        $titlePrefix = match ($frequency) {
            'daily' => __('messages.daily_statistics'),
            'monthly' => __('messages.monthly_statistics'),
            default => __('messages.weekly_statistics'),
        } . ' (' . $series['timeframeLabel'] . ')';
        $weeklyData[] = [$titlePrefix, '', ''];
        $headerTranslated = match ($series['headerColumn']) {
            'Date' => __('messages.date'),
            'Month' => __('messages.month'),
            default => __('messages.week'),
        };
        $weeklyData[] = [$headerTranslated, __('messages.appointments'), __('messages.patients')];
        foreach ($series['labels'] as $i => $label) {
            $weeklyData[] = [
                $label,
                $series['appointments'][$i] ?? 0,
                $series['patients'][$i] ?? 0,
            ];
        }

        // Top Doctors Section
        $weeklyData[] = ['', '', ''];
        $weeklyData[] = [__('messages.top_doctors') . ' (' . $series['timeframeLabel'] . ')', '', ''];
        $weeklyData[] = [__('messages.doctor_name'), __('messages.appointments'), __('messages.percentage')];
        foreach ($topDoctors as $doctor) {
            $weeklyData[] = [
                $doctor->name,
                $doctor->total,
                $doctor->percentage . '%',
            ];
        }

        $headings = ['Information', 'Value 1', 'Value 2'];
        
        return (new ReportsExport($weeklyData, $headings))->download('hospital-reports-' . now()->format('Y-m-d') . '.xlsx');
    }
}
