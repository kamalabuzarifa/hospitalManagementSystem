<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccessLogController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $role = $request->input('role');
        $from = $request->input('date_from');
        $to = $request->input('date_to');

        // Browser tz offset in minutes (JS getTimezoneOffset: minutes to add to local to reach UTC)
        $tzOffset = (int) $request->input('tz_offset', 0);

        $query = DB::table('activity_logs as al')
            ->leftJoin('users as u', 'u.id', '=', 'al.user_id')
            ->leftJoin('roles as r', 'r.id', '=', 'u.role_id')
            ->select(
                'al.id',
                'al.action',
                'al.meta',
                'al.ip_address',
                'al.user_agent',
                'al.created_at as time',
                'u.name as user',
                'u.email',
                'r.name as role'
            )
            ->latest('al.created_at');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('u.name', 'like', "%{$search}%")
                    ->orWhere('u.email', 'like', "%{$search}%")
                    ->orWhere('r.name', 'like', "%{$search}%")
                    ->orWhere('al.action', 'like', "%{$search}%")
                    ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(al.meta, '$.full_text_ar')) LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(al.meta, '$.full_text_en')) LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(al.meta, '$.notification')) LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(al.meta, '$.status')) LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("CAST(al.meta AS CHAR) COLLATE utf8mb4_unicode_ci LIKE ?", ["%{$search}%"]) // simple text match with Unicode collation
                    ->orWhereRaw("JSON_SEARCH(al.meta, 'one', CONCAT('%', ?, '%')) IS NOT NULL", [$search]); // search any JSON value

                // Also search in translated role names
                $availableRoles = ['admin', 'staff', 'doctor', 'receptionist', 'pharmacy'];
                foreach ($availableRoles as $roleName) {
                    $translated = __("messages.role_{$roleName}");
                    if ($translated !== "messages.role_{$roleName}" && mb_stripos($search, $translated) !== false) {
                        $q->orWhere('r.name', '=', $roleName);

                // If search mentions export/report keywords (Arabic/English), match export actions
                $exportKeywords = ['تصدير', 'تقرير', 'export', 'report'];
                foreach ($exportKeywords as $kw) {
                    if (mb_stripos($search, $kw) !== false) {
                        $q->orWhereIn('al.action', ['exported_report_pdf', 'exported_report_excel']);
                        break;
                    }
                }

                // Narrow down by specific format if mentioned
                $pdfKeywords = ['pdf', 'PDF', 'بي دي إف'];
                foreach ($pdfKeywords as $kw) {
                    if (mb_stripos($search, $kw) !== false) {
                        $q->orWhere('al.action', '=', 'exported_report_pdf');
                        break;
                    }
                }
                $excelKeywords = ['excel', 'Excel', 'اكسل', 'إكسل'];
                foreach ($excelKeywords as $kw) {
                    if (mb_stripos($search, $kw) !== false) {
                        $q->orWhere('al.action', '=', 'exported_report_excel');
                        break;
                    }
                }
                    }
                }

                // If search mentions notifications (Arabic/English), match the action directly
                $notificationKeywords = ['إشعار', 'الإشعارات', 'notification', 'notifications'];
                foreach ($notificationKeywords as $kw) {
                    if (mb_stripos($search, $kw) !== false) {
                        $q->orWhere('al.action', '=', 'toggled_notification');
                        break;
                    }
                }

                // If search mentions enable/disable keywords, also point to notification action
                $toggleKeywords = ['تفعيل', 'تعطيل', 'مفعل', 'مفعّل', 'معطل', 'معطّل', 'enabled', 'disabled'];
                foreach ($toggleKeywords as $kw) {
                    if (mb_stripos($search, $kw) !== false) {
                        $q->orWhere('al.action', '=', 'toggled_notification');
                        break;
                    }
                }

                    // If search mentions export/report keywords (Arabic/English), match export actions
                    $exportKeywords = ['تصدير', 'تقرير', 'export', 'report'];
                    foreach ($exportKeywords as $kw) {
                        if (mb_stripos($search, $kw) !== false) {
                            $q->orWhereIn('al.action', ['exported_report_pdf', 'exported_report_excel']);
                            break;
                        }
                    }

                    // Narrow down by specific format if mentioned
                    $pdfKeywords = ['pdf', 'PDF', 'بي دي إف'];
                    foreach ($pdfKeywords as $kw) {
                        if (mb_stripos($search, $kw) !== false) {
                            $q->orWhere('al.action', '=', 'exported_report_pdf');
                            break;
                        }
                    }
                    $excelKeywords = ['excel', 'Excel', 'اكسل', 'إكسل'];
                    foreach ($excelKeywords as $kw) {
                        if (mb_stripos($search, $kw) !== false) {
                            $q->orWhere('al.action', '=', 'exported_report_excel');
                            break;
                        }
                    }

                    // Generic action verbs mapping (EN/AR)
                    $createdKeywords = ['created', 'create', 'تم إنشاء', 'إنشاء', 'أنشأ'];
                    foreach ($createdKeywords as $kw) {
                        if (mb_stripos($search, $kw) !== false) {
                            $q->orWhere('al.action', 'like', 'created_%');
                            break;
                        }
                    }

                    $deletedKeywords = ['deleted', 'delete', 'تم حذف', 'حذف', 'محذوف'];
                    foreach ($deletedKeywords as $kw) {
                        if (mb_stripos($search, $kw) !== false) {
                            $q->orWhere('al.action', 'like', 'deleted_%');
                            break;
                        }
                    }

                    $updatedKeywords = ['updated', 'update', 'edit', 'edited', 'تعديل', 'تم تحديث', 'تحديث', 'محدث'];
                    foreach ($updatedKeywords as $kw) {
                        if (mb_stripos($search, $kw) !== false) {
                            $q->orWhere('al.action', 'like', 'updated_%');
                            break;
                        }
                    }

                    $viewedKeywords = ['view', 'viewed', 'عرض', 'تم العرض', 'استعراض'];
                    foreach ($viewedKeywords as $kw) {
                        if (mb_stripos($search, $kw) !== false) {
                            $q->orWhere('al.action', 'like', 'viewed_%');
                            break;
                        }
                    }

                    // Entity hints (EN/AR) to narrow by type
                    $staffKeywords = ['staff', 'member', 'موظف', 'الموظف', 'الموظفين'];
                    foreach ($staffKeywords as $kw) {
                        if (mb_stripos($search, $kw) !== false) {
                            $q->orWhere('al.action', 'like', '%staff%');
                            break;
                        }
                    }

                    // Generic action verbs mapping (EN/AR)
                    $createdKeywords = ['created', 'create', 'تم إنشاء', 'إنشاء', 'أنشأ'];
                    foreach ($createdKeywords as $kw) {
                        if (mb_stripos($search, $kw) !== false) {
                            $q->orWhere('al.action', 'like', 'created_%');
                            break;
                        }
                    }

                    $deletedKeywords = ['deleted', 'delete', 'تم حذف', 'حذف', 'محذوف'];
                    foreach ($deletedKeywords as $kw) {
                        if (mb_stripos($search, $kw) !== false) {
                            $q->orWhere('al.action', 'like', 'deleted_%');
                            break;
                        }
                    }

                    $updatedKeywords = ['updated', 'update', 'edit', 'edited', 'تعديل', 'تم تحديث', 'تحديث', 'محدث'];
                    foreach ($updatedKeywords as $kw) {
                        if (mb_stripos($search, $kw) !== false) {
                            $q->orWhere('al.action', 'like', 'updated_%');
                            break;
                        }
                    }

                    $viewedKeywords = ['view', 'viewed', 'عرض', 'تم العرض', 'استعراض'];
                    foreach ($viewedKeywords as $kw) {
                        if (mb_stripos($search, $kw) !== false) {
                            $q->orWhere('al.action', 'like', 'viewed_%');
                            break;
                        }
                    }

                    // Entity hints (EN/AR) to narrow by type
                    $staffKeywords = ['staff', 'member', 'موظف', 'الموظف', 'الموظفين'];
                    foreach ($staffKeywords as $kw) {
                        if (mb_stripos($search, $kw) !== false) {
                            $q->orWhere('al.action', 'like', '%staff%');
                            break;
                        }
                    }

            });
        }

        if ($role) {
            $query->where('r.name', $role);
        }

        if ($from) {
            $query->whereDate('al.created_at', '>=', $from);
        }

        if ($to) {
            $query->whereDate('al.created_at', '<=', $to);
        }

        $logs = $query->paginate(15)->withQueryString();
        $roles = DB::table('roles')->select('name')->orderBy('name')->get();

        return view('admin.access-logs.index', [
            'logs' => $logs,
            'roles' => $roles,
            'filters' => [
                'search' => $search,
                'role' => $role,
                'date_from' => $from,
                'date_to' => $to,
            ],
            'tzOffset' => $tzOffset,
            'tzLabel' => $this->formatOffset($tzOffset),
            'getActionLabel' => function($action) {
                return $this->getActionLabel($action);
            },
        ]);
    }

    public function download(Request $request)
    {
        // Set locale if passed in request to ensure translations are correct
        $locale = $request->input('locale');
        if ($locale && in_array($locale, ['en', 'ar'])) {
            app()->setLocale($locale);
        }

        $search = $request->input('search');
        $role = $request->input('role');
        $from = $request->input('date_from');
        $to = $request->input('date_to');

        $query = DB::table('activity_logs as al')
            ->leftJoin('users as u', 'u.id', '=', 'al.user_id')
            ->leftJoin('roles as r', 'r.id', '=', 'u.role_id')
            ->select(
                'al.id',
                'al.action',
                'al.meta',
                'al.ip_address',
                'al.user_agent',
                'al.created_at as time',
                'u.name as user',
                'u.email',
                'r.name as role'
            )
            ->latest('al.created_at');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('u.name', 'like', "%{$search}%")
                    ->orWhere('u.email', 'like', "%{$search}%")
                    ->orWhere('r.name', 'like', "%{$search}%")
                    ->orWhere('al.action', 'like', "%{$search}%")
                    ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(al.meta, '$.full_text_ar')) LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(al.meta, '$.full_text_en')) LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(al.meta, '$.notification')) LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(al.meta, '$.status')) LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("CAST(al.meta AS CHAR) COLLATE utf8mb4_unicode_ci LIKE ?", ["%{$search}%"]) // simple text match with Unicode collation
                    ->orWhereRaw("JSON_SEARCH(al.meta, 'one', CONCAT('%', ?, '%')) IS NOT NULL", [$search]); // search any JSON value

                // Also search in translated role names
                $availableRoles = ['admin', 'staff', 'doctor', 'receptionist', 'pharmacy'];
                foreach ($availableRoles as $roleName) {
                    $translated = __("messages.role_{$roleName}");
                    if ($translated !== "messages.role_{$roleName}" && mb_stripos($search, $translated) !== false) {
                        $q->orWhere('r.name', '=', $roleName);
                    }
                }

                // If search mentions notifications (Arabic/English), match the action directly
                $notificationKeywords = ['إشعار', 'الإشعارات', 'notification', 'notifications'];
                foreach ($notificationKeywords as $kw) {
                    if (mb_stripos($search, $kw) !== false) {
                        $q->orWhere('al.action', '=', 'toggled_notification');
                        break;
                    }
                }

                // If search mentions enable/disable keywords, also point to notification action
                $toggleKeywords = ['تفعيل', 'تعطيل', 'مفعل', 'مفعّل', 'معطل', 'معطّل', 'enabled', 'disabled'];
                foreach ($toggleKeywords as $kw) {
                    if (mb_stripos($search, $kw) !== false) {
                        $q->orWhere('al.action', '=', 'toggled_notification');
                        break;
                    }
                }


            });
        }

        if ($role) {
            $query->where('r.name', $role);
        }

        if ($from) {
            $query->whereDate('al.created_at', '>=', $from);
        }

        if ($to) {
            $query->whereDate('al.created_at', '<=', $to);
        }

        $logs = $query->get();

        $tzOffset = (int) $request->input('tz_offset', 0);
        $tzLabel = $this->formatOffset($tzOffset);

        $filename = 'access_logs_' . now()->format('Ymd_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($logs, $tzOffset, $tzLabel) {
            $out = fopen('php://output', 'w');
            // Add UTF-8 BOM to keep Excel/Windows rendering UTF-8 properly
            fwrite($out, "\xEF\xBB\xBF");
            // Header row
            fputcsv($out, [__('messages.user'), __('messages.role'), __('messages.time'), __('messages.action_performed')], ';');
            foreach ($logs as $log) {
                // Guard in case meta was not selected for any reason
                $meta = property_exists($log, 'meta') ? $log->meta : null;
                $actionLabel = $this->getActionLabel($log->action, $meta);
                // local time = UTC time - offsetMinutes (because offsetMinutes = minutes to add to local to get UTC)
                $time = $log->time ? \Carbon\Carbon::parse($log->time)->utc()->subMinutes($tzOffset)->format('Y-m-d H:i') . ' (' . $tzLabel . ')' : '';
                fputcsv($out, [
                    $log->user ?? 'N/A',
                    $log->role ?? 'N/A',
                    $time,
                    $actionLabel,
                ], ';');
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    protected function getActionLabel($action, $meta = null)
    {
        $key = "messages.action_{$action}";
        $label = __($key);

        // If the translation key doesn't exist, it returns the key itself
        // So we check if the label equals the key
        if ($label === $key) {
            return ucfirst(str_replace('_', ' ', $action));
        }

        // If there's metadata, append it to the label
        if ($meta) {
            $metaArray = is_string($meta) ? json_decode($meta, true) : $meta;
            if (is_array($metaArray)) {
                // Build a description based on the action and metadata
                if ($action === 'changed_language') {
                    // Use stored language names instead of translating on the fly
                    $langName = app()->getLocale() === 'ar'
                        ? ($metaArray['language_name_ar'] ?? $metaArray['to_language'] ?? '')
                        : ($metaArray['language_name_en'] ?? $metaArray['to_language'] ?? '');
                    if ($langName) {
                        $label .= ' ' . $langName;
                    }
                } elseif (in_array($action, ['changed_time_format', 'changed_date_format', 'changed_backup_frequency'])) {
                    $value = array_values($metaArray)[0] ?? null;
                    if ($value) {
                        $label .= ' ' . $value;
                    }
                } elseif ($action === 'toggled_notification') {
                    $notification = $metaArray['notification'] ?? null;
                    $status = $metaArray['status'] ?? null;
                    if ($notification && $status) {
                        $locale = app()->getLocale();

                        // Normalize notification name to current locale
                        $notifLower = mb_strtolower($notification);
                        $appointmentVariants = [
                            mb_strtolower('New Appointment'),
                            mb_strtolower('New appointment notification'),
                            mb_strtolower('حجز جديد'),
                            mb_strtolower('إشعار المواعيد الجديدة'),
                        ];
                        $patientVariants = [
                            mb_strtolower('New Patient'),
                            mb_strtolower('New patient notification'),
                            mb_strtolower('مريض جديد'),
                            mb_strtolower('إشعار المرضى الجدد'),
                        ];

                        if (in_array($notifLower, $appointmentVariants, true)) {
                            $notificationText = __('messages.new_appointment');
                        } elseif (in_array($notifLower, $patientVariants, true)) {
                            $notificationText = __('messages.new_patient');
                        } else {
                            $notificationText = $notification; // fallback
                        }

                        // Normalize status to current locale
                        $statusLower = mb_strtolower($status);
                        if ($locale === 'ar') {
                            if ($statusLower === 'enabled') {
                                $statusText = 'مفعّل';
                            } elseif ($statusLower === 'disabled') {
                                $statusText = 'معطّل';
                            } else {
                                // If already Arabic or unknown, keep as-is
                                $statusText = $status;
                            }
                        } else { // en
                            if ($statusLower === mb_strtolower('مفعّل')) {
                                $statusText = 'Enabled';
                            } elseif ($statusLower === mb_strtolower('معطّل')) {
                                $statusText = 'Disabled';
                            } else {
                                $statusText = $status;
                            }
                        }

                        $label .= ' - ' . $notificationText . ' (' . $statusText . ')';
                    }
                } elseif (in_array($action, ['created_staff', 'updated_staff_information', 'deleted_staff', 'viewed_staff', 'opened_staff_edit'])) {
                    $name = $metaArray['staff_name'] ?? null;
                    if ($name) {
                        $label .= ' - ' . $name;
                    }
                } elseif ($action === 'toggled_staff_status') {
                    $name = $metaArray['staff_name'] ?? null;
                    $status = $metaArray['status'] ?? null;
                    if ($name || $status) {
                        $locale = app()->getLocale();
                        $statusLower = $status ? mb_strtolower($status) : null;
                        if ($locale === 'ar') {
                            if ($statusLower === 'enabled') {
                                $statusText = 'مفعّل';
                            } elseif ($statusLower === 'disabled') {
                                $statusText = 'معطّل';
                            } else {
                                $statusText = $status ?? '';
                            }
                        } else {
                            if ($statusLower === mb_strtolower('مفعّل')) {
                                $statusText = 'Enabled';
                            } elseif ($statusLower === mb_strtolower('معطّل')) {
                                $statusText = 'Disabled';
                            } else {
                                $statusText = $status ?? '';
                            }
                        }
                        $append = $name ? $name : '';
                        if ($statusText) {
                            $append .= $append ? ' (' . $statusText . ')' : '(' . $statusText . ')';
                        }
                        if ($append) {
                            $label .= ' - ' . $append;
                        }
                    }
                }
            }
        }

        return $label;
    }

    protected function formatOffset(int $offsetMinutes): string
    {
        // JS getTimezoneOffset: minutes to add to local to reach UTC.
        // Positive => west of UTC (behind), Negative => east of UTC (ahead).
        // Display as UTC±HH:MM where sign is inverted relative to the raw offset.
        $absMinutes = abs($offsetMinutes);
        $hours = floor($absMinutes / 60);
        $minutes = $absMinutes % 60;
        $sign = $offsetMinutes > 0 ? '-' : ($offsetMinutes < 0 ? '+' : '+');
        return sprintf('UTC%s%02d:%02d', $sign, $hours, $minutes);
    }
}

