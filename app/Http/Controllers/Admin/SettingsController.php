<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Settings;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class SettingsController extends Controller
{
    use LogsActivity;

    /**
     * Run manual backup and return output
     */
    public function backupNow(Request $request)
    {
        $settings = \App\Models\Settings::first();
        $backupPath = $settings && $settings->backup_destination ? $settings->backup_destination : 'D:\\Backups';
        $output = [];
        $result = null;
        // Pass the backup path as an argument to the batch file if needed
        $cmd = '"D:\\mapp\\htdocs\\hospitalManagementSystem\\backup_db_to_d_backups.bat"';
        if ($backupPath) {
            $cmd .= ' "' . $backupPath . '"';
        }
        exec($cmd, $output, $result);
        $outputText = implode("\n", $output);
        return redirect()->route('admin.settings.index')->with('backup_output', $outputText);
    }
    /**
     * Update role permission (for Admin Roles & Permissions table)
     */
    public function updateRolePermission(Request $request)
    {
        $validated = $request->validate([
            'role' => 'required|in:administrator,staff',
            'permission' => 'required|in:full_access,limited_access',
        ]);

        // تحديث صلاحية الدور في جدول roles
        $roleMap = [
            'administrator' => 'admin',
            'staff' => 'staff',
        ];
        $roleName = $roleMap[$validated['role']] ?? $validated['role'];
        $role = \App\Models\Role::where('name', $roleName)->first();
        $oldPermission = $role ? $role->permission_level : null;
        if ($role) {
            $role->permission_level = $validated['permission'];
            $role->save();
        }

        // Log activity
        $this->logActivity('changed_role_permission', [
            'role' => $validated['role'],
            'old_permission' => $oldPermission,
            'new_permission' => $validated['permission'],
            'full_text_en' => 'Changed permission for ' . $validated['role'] . ' from ' . $oldPermission . ' to ' . $validated['permission'],
            'full_text_ar' => 'تم تغيير صلاحية ' . $validated['role'] . ' من ' . $oldPermission . ' إلى ' . $validated['permission'],
        ]);

        return redirect()->route('admin.settings.index')->with('success', __('messages.settings_updated'));
    }

    public function index()
    {
        // Get or create settings
        $settings = Settings::first() ?? Settings::create([
            'language' => 'en',
            'time_format' => '24',
            'date_format' => 'DD/MM/YYYY',
            'backup_frequency' => 'weekly',
            'backup_destination' => null,
            'notification_new_appointment' => true,
            'notification_new_patient' => true,
            'reception_dashboard_notifications' => true,
            'pharmacy_dashboard_notifications' => true,
            'dashboard_recent_appointments' => true,
            'dashboard_recent_patient' => true,
            'dark_mode' => false,
        ]);

        // Admin Roles & Permissions (DB-driven)
        $roleKeyToDbName = [
            'administrator' => 'admin',
            'staff' => 'staff',
        ];

        $rolesByName = Role::query()
            ->whereIn('name', array_values($roleKeyToDbName))
            ->get()
            ->keyBy('name');

        $permissionLabel = function (?string $level): string {
            // Legacy cleanup: we no longer support read_only in UI.
            if ($level === 'read_only') {
                $level = 'limited_access';
            }

            return match ($level) {
                'full_access' => __('messages.full_access'),
                'limited_access' => __('messages.limited_access'),
                default => $level ?? (__('messages.read_only') ?? 'Read-Only'),
            };
        };

        $roles = [
            [
                'key' => 'administrator',
                'label' => __('messages.administrator'),
                'db_name' => 'admin',
                'permission_level' => ($rolesByName['admin']->permission_level ?? 'limited_access') === 'read_only'
                    ? 'limited_access'
                    : ($rolesByName['admin']->permission_level ?? 'limited_access'),
            ],
            [
                'key' => 'staff',
                'label' => __('messages.staff'),
                'db_name' => 'staff',
                'permission_level' => ($rolesByName['staff']->permission_level ?? 'limited_access') === 'read_only'
                    ? 'limited_access'
                    : ($rolesByName['staff']->permission_level ?? 'limited_access'),
            ],
        ];

        foreach ($roles as $index => $role) {
            $roles[$index]['permission_label'] = $permissionLabel($role['permission_level']);
        }

        return view('admin.settings.index', compact('settings', 'roles'));
    }

    /**
     * Update settings in storage
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'language' => 'required|string|in:en,ar',
            'time_format' => 'required|string|in:12,24',
            'date_format' => 'required|string',
            'backup_frequency' => 'required|string|in:daily,weekly,monthly',
            'backup_destination' => 'nullable|string',
            'dashboard_notifications' => 'nullable|boolean',
            'notification_new_appointment' => 'boolean',
            'notification_new_patient' => 'boolean',
            'dark_mode' => 'nullable|boolean',
            'dashboard_recent_appointments' => 'boolean',
            'dashboard_recent_patient' => 'boolean',
        ]);

        // Language is session-based (do not persist on settings table)
        $previousLocale = $request->session()->get('locale', app()->getLocale() ?: 'en');
        $selectedLanguage = $validated['language'];
        unset($validated['language']);

        // Handle checkbox values (unchecked checkboxes don't send values)
        $dashboardNotifications = $request->has('dashboard_notifications');
        $validated['reception_dashboard_notifications'] = $dashboardNotifications;
        $validated['pharmacy_dashboard_notifications'] = $dashboardNotifications;
        $validated['notification_new_appointment'] = isset($validated['notification_new_appointment']) && $validated['notification_new_appointment'];
        $validated['notification_new_patient'] = isset($validated['notification_new_patient']) && $validated['notification_new_patient'];

        // Persist dark_mode (checkbox not present if unchecked)
        $validated['dark_mode'] = $request->has('dark_mode');
        if (!Schema::hasColumn('settings', 'dark_mode')) {
            unset($validated['dark_mode']);
        }

        // Avoid 500s if migrations weren't run yet
        if (Schema::hasColumn('settings', 'dashboard_recent_appointments')) {
            $validated['dashboard_recent_appointments'] = isset($validated['dashboard_recent_appointments']) && $validated['dashboard_recent_appointments'];
        } else {
            unset($validated['dashboard_recent_appointments']);
        }

        if (Schema::hasColumn('settings', 'dashboard_recent_patient')) {
            $validated['dashboard_recent_patient'] = isset($validated['dashboard_recent_patient']) && $validated['dashboard_recent_patient'];
        } else {
            unset($validated['dashboard_recent_patient']);
        }

        // Get first settings record (or create if doesn't exist)
        $settings = Settings::first();
        $oldSettings = $settings ? $settings->toArray() : [];

        if (!$settings) {
            $settings = Settings::create(array_merge(['language' => 'en'], $validated));
        } else {
            $settings->update($validated);
        }

        // Ensure immediate language swap in UI by setting session locale
        // and updating the app locale for this request.
        $request->session()->put('locale', $selectedLanguage);
        app()->setLocale($selectedLanguage);

        // Log individual changes (also when first-time save)
        $changed = fn(string $key) => !isset($oldSettings[$key]) || $oldSettings[$key] !== $validated[$key];

        if ($previousLocale !== $selectedLanguage) {
            // Store full display text in both languages for searchability
            $languageData = [
                'ar' => ['name_ar' => 'العربية', 'name_en' => 'Arabic', 'display_ar' => 'تم تغيير اللغة إلى العربية', 'display_en' => 'Changed language to Arabic'],
                'en' => ['name_ar' => 'الإنجليزية', 'name_en' => 'English', 'display_ar' => 'تم تغيير اللغة إلى الإنجليزية', 'display_en' => 'Changed language to English']
            ];
            $data = $languageData[$selectedLanguage] ?? [];
            $this->logActivity('changed_language', [
                'to_language' => $selectedLanguage,
                'language_name_ar' => $data['name_ar'] ?? $selectedLanguage,
                'language_name_en' => $data['name_en'] ?? $selectedLanguage,
                'full_text_ar' => $data['display_ar'] ?? '',
                'full_text_en' => $data['display_en'] ?? ''
            ]);
        }

        if ($changed('time_format')) {
            $timeValue = $validated['time_format'] . ' hours';
            $this->logActivity('changed_time_format', [
                'time_format' => $timeValue,
                'full_text_ar' => 'تم تغيير صيغة الوقت إلى ' . $timeValue,
                'full_text_en' => 'Changed time format to ' . $timeValue
            ]);
        }

        if ($changed('date_format')) {
            $this->logActivity('changed_date_format', [
                'date_format' => $validated['date_format'],
                'full_text_ar' => 'تم تغيير صيغة التاريخ إلى ' . $validated['date_format'],
                'full_text_en' => 'Changed date format to ' . $validated['date_format']
            ]);
        }

        if ($changed('backup_frequency')) {
            $freqLabel = match($validated['backup_frequency']) {
                'daily' => __('messages.daily'),
                'weekly' => __('messages.weekly'),
                'monthly' => __('messages.monthly'),
            };
            $freqEn = match($validated['backup_frequency']) {
                'daily' => 'Daily',
                'weekly' => 'Weekly',
                'monthly' => 'Monthly',
            };
            $freqAr = match($validated['backup_frequency']) {
                'daily' => 'يومي',
                'weekly' => 'أسبوعي',
                'monthly' => 'شهري',
            };
            $this->logActivity('changed_backup_frequency', [
                'backup_frequency' => $freqLabel,
                'full_text_ar' => 'تم تغيير تكرار النسخ الاحتياطي إلى ' . $freqAr,
                'full_text_en' => 'Changed backup frequency to ' . $freqEn
            ]);
        }

        if ($changed('backup_destination')) {
            $dest = $validated['backup_destination'] ?? '';
            $oldDest = $oldSettings['backup_destination'] ?? '';
            // Only log if there's an actual change (not just null vs empty string)
            if ($dest !== $oldDest) {
                $this->logActivity('changed_backup_destination', [
                    'backup_destination' => $dest,
                    'full_text_ar' => $dest ? ('تم تغيير مسار النسخ الاحتياطي إلى ' . $dest) : 'تم مسح مسار النسخ الاحتياطي',
                    'full_text_en' => $dest ? ('Changed backup destination to ' . $dest) : 'Cleared backup destination'
                ]);
            }
        }

        // Log notification changes
        if ($changed('reception_dashboard_notifications') || $changed('pharmacy_dashboard_notifications')) {
            $statusEn = $dashboardNotifications ? 'Enabled' : 'Disabled';
            $statusAr = $dashboardNotifications ? 'مفعّل' : 'معطّل';
            $this->logActivity('toggled_notification', [
                'notification' => 'Dashboard notifications',
                'status' => $statusEn,
                'full_text_ar' => $statusAr . ' إشعارات لوحات الاستقبال والصيدلية',
                'full_text_en' => $statusEn . ' Reception & Pharmacy dashboard notifications'
            ]);
        }

        if ($changed('notification_new_appointment')) {
            $statusEn = $validated['notification_new_appointment'] ? 'Enabled' : 'Disabled';
            $statusAr = $validated['notification_new_appointment'] ? 'مفعّل' : 'معطّل';
            $this->logActivity('toggled_notification', [
                'notification' => 'New appointment notification',
                'status' => $statusEn,
                'full_text_ar' => $statusAr . ' إشعار المواعيد الجديدة',
                'full_text_en' => $statusEn . ' New appointment notification'
            ]);
        }

        if ($changed('notification_new_patient')) {
            $statusEn = $validated['notification_new_patient'] ? 'Enabled' : 'Disabled';
            $statusAr = $validated['notification_new_patient'] ? 'مفعّل' : 'معطّل';
            $this->logActivity('toggled_notification', [
                'notification' => 'New patient notification',
                'status' => $statusEn,
                'full_text_ar' => $statusAr . ' إشعار المرضى الجدد',
                'full_text_en' => $statusEn . ' New patient notification'
            ]);
        }

        if ($changed('dashboard_recent_appointments')) {
            $statusEn = $validated['dashboard_recent_appointments'] ? 'Enabled' : 'Disabled';
            $statusAr = $validated['dashboard_recent_appointments'] ? 'مفعّل' : 'معطّل';
            $this->logActivity('toggled_notification', [
                'notification' => 'Recent appointments dashboard box',
                'status' => $statusEn,
                'full_text_ar' => $statusAr . ' صندوق الحجوزات الأخيرة',
                'full_text_en' => $statusEn . ' Recent appointments box'
            ]);
        }

        if ($changed('dashboard_recent_patient')) {
            $statusEn = $validated['dashboard_recent_patient'] ? 'Enabled' : 'Disabled';
            $statusAr = $validated['dashboard_recent_patient'] ? 'مفعّل' : 'معطّل';
            $this->logActivity('toggled_notification', [
                'notification' => 'Recent patient dashboard box',
                'status' => $statusEn,
                'full_text_ar' => $statusAr . ' صندوق المريض الأخير',
                'full_text_en' => $statusEn . ' Recent patient box'
            ]);
        }

        // Run backup after saving settings
        $settings = Settings::first();
        $backupPath = $settings && $settings->backup_destination ? $settings->backup_destination : 'D:\\Backups';
        $output = [];
        $result = null;
        $cmd = '"D:\\mapp\\htdocs\\hospitalManagementSystem\\backup_db_to_d_backups.bat"';
        if ($backupPath) {
            $cmd .= ' "' . $backupPath . '"';
        }
        exec($cmd, $output, $result);
        $outputText = implode("\n", $output);
        return redirect()->route('admin.settings.index')->with(['success' => __('messages.settings_updated'), 'backup_output' => $outputText]);
    }

    /**
     * Toggle notification setting via AJAX
     */
    public function toggleNotification(Request $request)
    {
        $validated = $request->validate([
            'field' => 'required|string|in:notification_new_appointment,notification_new_patient',
            'value' => 'required|boolean'
        ]);

        $settings = Settings::first();
        if (!$settings) {
            $settings = Settings::create([
                'language' => 'en',
                'time_format' => '24',
                'date_format' => 'DD/MM/YYYY',
                'backup_frequency' => 'weekly',
                'backup_destination' => null,
                'notification_new_appointment' => true,
                'notification_new_patient' => true,
            ]);
        }

        $settings->update([
            $validated['field'] => (bool) $validated['value']
        ]);

        $fieldLabel = $validated['field'] === 'notification_new_appointment'
            ? __('messages.new_appointment')
            : __('messages.new_patient');

        $fieldLabelEn = $validated['field'] === 'notification_new_appointment'
            ? 'New appointment notification'
            : 'New patient notification';

        $fieldLabelAr = $validated['field'] === 'notification_new_appointment'
            ? 'إشعار المواعيد الجديدة'
            : 'إشعار المرضى الجدد';

        $statusEn = $validated['value'] ? 'Enabled' : 'Disabled';
        $statusAr = $validated['value'] ? 'مفعّل' : 'معطّل';

        $this->logActivity('toggled_notification', [
            'notification' => $fieldLabel,
            'status' => $statusEn,
            'full_text_ar' => $statusAr . ' ' . $fieldLabelAr,
            'full_text_en' => $statusEn . ' ' . $fieldLabelEn
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Setting saved successfully'
        ]);
    }

    /**
     * Toggle dark mode preference via AJAX
     */
    public function toggleTheme(Request $request)
    {
        $validated = $request->validate([
            'value' => 'required|boolean'
        ]);

        $settings = Settings::first();
        if (!$settings) {
            $settings = Settings::create([
                'language' => 'en',
                'time_format' => '24',
                'date_format' => 'DD/MM/YYYY',
                'backup_frequency' => 'weekly',
                'backup_destination' => null,
                'notification_new_appointment' => true,
                'notification_new_patient' => true,
                'reception_dashboard_notifications' => true,
                'pharmacy_dashboard_notifications' => true,
                'dashboard_recent_appointments' => true,
                'dashboard_recent_patient' => true,
                'dark_mode' => (bool) $validated['value'],
            ]);
        } else {
            if (Schema::hasColumn('settings', 'dark_mode')) {
                $old = $settings->dark_mode ?? null;
                $settings->update(['dark_mode' => (bool) $validated['value']]);

                $this->logActivity('toggled_dark_mode', [
                    'old' => $old,
                    'new' => (bool) $validated['value'],
                    'full_text_en' => 'Toggled dark mode to ' . (($validated['value']) ? 'Enabled' : 'Disabled'),
                    'full_text_ar' => ($validated['value']) ? 'تم تفعيل الوضع الداكن' : 'تم تعطيل الوضع الداكن'
                ]);
            }
        }

        return response()->json(['success' => true]);
    }
}
