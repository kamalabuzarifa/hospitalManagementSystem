<?php
use App\Http\Controllers\Admin\SettingsController;

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\Doctor\DoctorController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\PharmacyController;
use App\Http\Controllers\PharmacistController;
use App\Http\Controllers\ReceptionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\StaffController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\doctor\NoteDoctorController;
use App\Http\Controllers\Doctor\DashboardDoctorController;
use App\Http\Controllers\Doctor\AppointmentsController;
use App\Http\Controllers\Doctor\PatientsController;
use App\Http\Controllers\Doctor\SettingDoctorController;
use App\Http\Controllers\Admin\DashboardAdminController;
use App\Http\Controllers\doctor\PrescriptionDoctorController;
// ملف المستخدم الشخصي
Route::middleware(['auth', 'verified', 'profile.completed'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    $user = Auth::user();
    $role = $user->role?->name;

    return match($role) {
        'admin' => redirect()->route('admin.dashboard'),
        'doctor' => redirect()->route('doctor.dashboard'),
        'patient' => redirect()->route('patient.dashboard'),
        'pharmacy' => redirect()->route('pharmacy.dashboard'),
        'receptionist' => redirect()->route('reception.dashboard'),
        default => view('dashboard'),
    };
})->middleware(['auth', 'verified', 'profile.completed'])->name('dashboard');

// reception Routes
Route::middleware(['auth', 'verified', 'profile.completed', 'role:receptionist'])->prefix('reception')->name('reception.')->group(function () {
    // Dashboard
    Route::get('dashboard', [ReceptionController::class, 'dashboard'])->middleware('permission:view_dashboard')->name('dashboard');

    // Settings
    Route::get('settings', [ReceptionController::class, 'settings'])->middleware('permission:edit_profile')->name('settings');
    Route::post('settings', [ReceptionController::class, 'updateSettings'])->middleware('permission:edit_profile')->name('settings.update');
    // Patients Management
    Route::get('patients', [ReceptionController::class, 'patientIndex'])->middleware('permission:patients.view,manage_patients')->name('patients.index');
    Route::get('patients/create', [ReceptionController::class, 'patientCreate'])->middleware('permission:patients.create,manage_patients')->name('patients.create');
    Route::post('patients', [ReceptionController::class, 'patientStore'])->middleware('permission:patients.create,manage_patients')->name('patients.store');
    Route::get('patients/{patient}', [ReceptionController::class, 'patientShow'])->middleware('permission:patients.view,manage_patients')->name('patients.show');
    Route::get('patients/{patient}/edit', [ReceptionController::class, 'patientEdit'])->middleware('permission:patients.edit,manage_patients')->name('patients.edit');
    Route::put('patients/{patient}', [ReceptionController::class, 'patientUpdate'])->middleware('permission:patients.edit,manage_patients')->name('patients.update');
    Route::delete('patients/{patient}', [ReceptionController::class, 'patientDestroy'])->middleware('permission:patients.delete,manage_patients')->name('patients.destroy');

    // Appointments Management
    Route::get('appointments', [ReceptionController::class, 'appointmentIndex'])->middleware('permission:appointments.view,manage_appointments')->name('appointments.index');
    Route::get('appointments/create', [ReceptionController::class, 'appointmentCreate'])->middleware('permission:appointments.create,manage_appointments')->name('appointments.create');
    Route::post('appointments', [ReceptionController::class, 'appointmentStore'])->middleware('permission:appointments.create,manage_appointments')->name('appointments.store');
    Route::get('appointments/{appointment}', [ReceptionController::class, 'appointmentShow'])->middleware('permission:appointments.view,manage_appointments')->name('appointments.show');
    Route::get('appointments/{appointment}/edit', [ReceptionController::class, 'appointmentEdit'])->middleware('permission:appointments.edit,manage_appointments')->name('appointments.edit');
    Route::put('appointments/{appointment}', [ReceptionController::class, 'appointmentUpdate'])->middleware('permission:appointments.edit,manage_appointments')->name('appointments.update');
    Route::delete('appointments/{appointment}', [ReceptionController::class, 'appointmentDestroy'])->middleware('permission:appointments.delete,manage_appointments')->name('appointments.destroy');

    // Appointment Actions
    Route::post('appointments/{appointment}/checkin', [ReceptionController::class, 'appointmentCheckin'])->middleware('permission:appointments.actions,manage_appointments')->name('appointments.checkin');
    Route::post('appointments/{appointment}/cancel', [ReceptionController::class, 'appointmentCancel'])->middleware('permission:appointments.actions,manage_appointments')->name('appointments.cancel');
    Route::post('appointments/{appointment}/complete', [ReceptionController::class, 'appointmentComplete'])->middleware('permission:appointments.actions,manage_appointments')->name('appointments.complete');
    Route::post('appointments/{appointment}/assist-surgery', [ReceptionController::class, 'assistSurgery'])->middleware('permission:appointments.actions,manage_appointments')->name('appointments.assist-surgery');

    // Notifications
    Route::post('notifications/{notification}/read', [ReceptionController::class, 'notificationMarkRead'])->middleware('permission:view_dashboard')->name('notifications.read');
});

// Admin Routes
// Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('dashboard', [DashboardAdminController::class, 'index'])->middleware('permission:view_dashboard')->name('dashboard');
    Route::get('dashboard/counts', [DashboardAdminController::class, 'counts'])->middleware('permission:view_dashboard')->name('dashboard.counts');
    // Dashboard CSV Export/Import
    Route::get('dashboard/export/patients', [DashboardAdminController::class, 'exportPatientsCsv'])->middleware('permission:import_export.manage,manage_import_export')->name('dashboard.export.patients');
    Route::get('dashboard/export/appointments', [DashboardAdminController::class, 'exportAppointmentsCsv'])->middleware('permission:import_export.manage,manage_import_export')->name('dashboard.export.appointments');
    Route::post('dashboard/import/patients', [DashboardAdminController::class, 'importPatientsCsv'])->middleware('permission:import_export.manage,manage_import_export')->name('dashboard.import.patients');
    Route::post('dashboard/import/appointments', [DashboardAdminController::class, 'importAppointmentsCsv'])->middleware('permission:import_export.manage,manage_import_export')->name('dashboard.import.appointments');

    // Staff Management
    Route::resource('staff', StaffController::class);
    Route::patch('staff/{staff}/toggle-status', [StaffController::class, 'toggleStatus'])->name('staff.toggle-status');

    // Patients Management
    Route::resource('patients', PatientController::class);

    // Doctors Management
    Route::resource('doctors', DoctorController::class);

    // Appointments Management
    Route::resource('appointments', AppointmentController::class);

    // Access Logs
    Route::get('access-logs', [\App\Http\Controllers\Admin\AccessLogController::class, 'index'])->middleware('permission:access_logs.view,view_access_logs')->name('access-logs.index');
    Route::get('access-logs/download', [\App\Http\Controllers\Admin\AccessLogController::class, 'download'])->middleware('permission:access_logs.view,view_access_logs')->name('access-logs.download');

    // Pharmacy Management
    Route::resource('pharmacy', PharmacyController::class);

    // Reports
    Route::get('reports', [\App\Http\Controllers\Admin\ReportController::class, 'index'])->middleware('permission:reports.view,view_reports')->name('reports.index');
    Route::get('reports/export-pdf', [\App\Http\Controllers\Admin\ReportController::class, 'exportPdf'])->middleware('permission:reports.view,view_reports')->name('reports.export-pdf');
    Route::get('reports/export-excel', [\App\Http\Controllers\Admin\ReportController::class, 'exportExcel'])->middleware('permission:reports.view,view_reports')->name('reports.export-excel');

    // Settings
    Route::get('settings', [\App\Http\Controllers\Admin\SettingsController::class, 'index'])->middleware('permission:settings.manage,manage_settings')->name('settings.index');
    Route::post('settings', [\App\Http\Controllers\Admin\SettingsController::class, 'update'])->middleware('permission:settings.manage,manage_settings')->name('settings.update');
    Route::post('settings/toggle-notification', [\App\Http\Controllers\Admin\SettingsController::class, 'toggleNotification'])->middleware('permission:settings.manage,manage_settings')->name('settings.toggle-notification');
    Route::post('settings/toggle-theme', [\App\Http\Controllers\Admin\SettingsController::class, 'toggleTheme'])->middleware('permission:settings.manage,manage_settings')->name('settings.toggle-theme');
    Route::post('settings/update-role-permission', [\App\Http\Controllers\Admin\SettingsController::class, 'updateRolePermission'])->middleware('permission:settings.manage,manage_settings')->name('settings.updateRolePermission');
});

// Doctor Routes
Route::middleware(['auth', 'verified', 'profile.completed', 'role:doctor'])->prefix('doctor')->name('doctor.')->group(function () {

    // Dashboard
    Route::get('dashboard', [DashboardDoctorController::class, 'index'])->middleware('permission:view_dashboard')->name('dashboard');

    // Patient Queue (custom view)
    Route::get('patient-queue', [DashboardDoctorController::class, 'patientQueue'])->middleware('permission:view_dashboard')->name('patient-queue');

    // Patient Records (Patients index)
    Route::get('patients-records', [PatientsController::class, 'records'])->middleware('permission:patients.view,manage_patients')->name('patients-records');

    // Appointments
    Route::get('appointments', [AppointmentsController::class, 'index'])->middleware('permission:appointments.view,manage_appointments')->name('appointments.index');
    Route::get('appointments/create-note', [AppointmentsController::class, 'createNote'])->middleware('permission:notes.create,manage_notes')->name('appointments.createNote');
    
    // prescriptions index route
    Route::post('prescriptions/store', [PrescriptionDoctorController::class, 'store'])->middleware('permission:prescriptions.create,manage_prescriptions')->name('prescriptions.store');
    Route::get('prescriptions', [PrescriptionDoctorController::class, 'index'])->middleware('permission:prescriptions.view,manage_prescriptions')->name('prescriptions.index');
    Route::put('prescriptions/{prescription}', [PrescriptionDoctorController::class, 'update'])->middleware('permission:prescriptions.edit,manage_prescriptions')->name('prescriptions.update');
    Route::delete('prescriptions/{prescription}', [PrescriptionDoctorController::class, 'destroy'])->middleware('permission:prescriptions.delete,manage_prescriptions')->name('prescriptions.destroy');
    
    // Notes
    Route::post('notes', [NoteDoctorController::class, 'store'])->middleware('permission:notes.create,manage_notes')->name('notes.store');

    // Patient view
    Route::get('patients/{patient}', [PatientsController::class, 'show'])->middleware('permission:patients.view,manage_patients')->name('patients.show');

    // Patient record details (by appointment)
    Route::get('patients-records/{appointment}', [PatientsController::class, 'recordShow'])->middleware('permission:patients.view,manage_patients')->name('patients-records.show');

    // Patients index (list)
    Route::get('patients', [PatientsController::class, 'index'])->middleware('permission:patients.view,manage_patients')->name('patients.index');

    // Doctor settings
    Route::get('settings', [SettingDoctorController::class, 'index'])->middleware('permission:edit_profile')->name('settings');
    Route::post('settings/update', [SettingDoctorController::class, 'update'])->middleware('permission:edit_profile')->name('settings.update');

    // Notifications
    Route::post('notifications/{notification}/read', [DashboardDoctorController::class, 'notificationMarkRead'])->middleware('permission:view_dashboard')->name('notifications.read');

});

// Patient Routes
Route::middleware(['auth', 'verified', 'profile.completed', 'role:patient'])->prefix('patient')->name('patient.')->group(function () {
    // Dashboard
    Route::get('dashboard', [PatientController::class, 'myDashboard'])->name('dashboard');

    // Appointments
    Route::get('appointments', [PatientController::class, 'myAppointments'])->name('appointments.index');

    // Prescriptions
    Route::get('prescriptions', [PatientController::class, 'myPrescriptions'])->name('prescriptions.index');
    Route::get('prescriptions/{appointment}', [PatientController::class, 'showPrescription'])->name('prescriptions.show');
});

// Pharmacy Routes
Route::middleware(['auth', 'verified', 'profile.completed', 'role:pharmacy'])->prefix('pharmacy')->name('pharmacy.')->group(function () {
    // Settings
    Route::get('settings', [PharmacistController::class, 'settings'])->middleware('permission:edit_profile')->name('settings.index');
    Route::post('settings', [PharmacistController::class, 'updateSettings'])->middleware('permission:edit_profile')->name('settings.update');
    // Dashboard
    Route::get('dashboard', [PharmacistController::class, 'dashboard'])->middleware('permission:view_dashboard')->name('dashboard');

    // Inventory
    Route::get('inventory', [PharmacistController::class, 'inventory'])->middleware('permission:inventory.view,manage_inventory')->name('inventory.index');
    Route::get('inventory/find-by-prescription', [PharmacistController::class, 'findByPrescription'])->middleware('permission:inventory.view,manage_inventory')->name('inventory.find_by_prescription');
    Route::get('inventory/find-by-id', [PharmacistController::class, 'findById'])->middleware('permission:inventory.view,manage_inventory')->name('inventory.find_by_id');
    Route::post('inventory', [PharmacistController::class, 'addInventory'])->middleware('permission:manage_inventory')->name('inventory.store');

    // Prescriptions
    Route::get('prescriptions', [PharmacistController::class, 'prescriptions'])->middleware('permission:prescriptions.view,manage_prescriptions')->name('prescriptions.index');
    Route::get('prescriptions/{prescription}', [PharmacistController::class, 'showPrescription'])->middleware('permission:prescriptions.view,manage_prescriptions')->name('prescriptions.show');
    Route::post('prescriptions/{prescription}/dispense', [PharmacistController::class, 'dispensePrescription'])->middleware('permission:prescriptions.dispense,manage_prescriptions')->name('prescriptions.dispense');

    // Notifications
    Route::post('notifications/{notification}/read', [PharmacistController::class, 'notificationMarkRead'])->middleware('permission:view_dashboard')->name('notifications.read');
    Route::post('notifications/mark-medicine-stock-read', [PharmacistController::class, 'markMedicineStockNotificationsRead'])->middleware('permission:view_dashboard')->name('notifications.mark_medicine_stock_read');
    Route::post('notifications/mark-medicine-read/{medicine}', [PharmacistController::class, 'markMedicineNotificationsRead'])->middleware('permission:view_dashboard')->name('notifications.mark_medicine_read');
});

require __DIR__.'/auth.php';

// Complete registration routes
use App\Http\Controllers\CompleteRegistrationController;
Route::middleware(['auth'])->group(function () {
    Route::get('/complete-registration', [CompleteRegistrationController::class, 'show'])->name('complete-registration');
    Route::post('/complete-registration', [CompleteRegistrationController::class, 'submit'])->name('complete-registration.submit');
});

// Simple locale switch route for immediate testing
Route::get('/lang/{locale}', function (string $locale) {
    $locale = in_array($locale, ['ar', 'en']) ? $locale : 'en';
    session(['locale' => $locale]);
    app()->setLocale($locale);
    return back();
})->name('lang.switch');





