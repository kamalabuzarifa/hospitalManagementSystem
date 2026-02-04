<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Settings extends Model
{
    protected $fillable = [
        'language',
        'language_admin',
        'language_doctor',
        'language_receptionist',
        'language_pharmacy',
        'language_patient',
        'language_staff',
        'time_format',
        'date_format',
        'backup_frequency',
        'backup_destination',
        'notification_new_appointment',
        'notification_new_patient',
        'dashboard_recent_appointments',
        'dashboard_recent_patient',
        'reception_dashboard_notifications',
        'pharmacy_dashboard_notifications',
        'notify_pending_prescriptions',
        'notify_dispensed_prescriptions',
        'notify_inventory_status',
        'notify_expired_medicines',
        'notify_low_stock_medicines',
        'low_stock_threshold',
        'allow_edit_appointments',
        'allow_edit_patients',
        'default_appointment_duration',
        'auto_assign_doctor',
        'dark_mode',
    ];

    protected $casts = [
        'notification_new_appointment' => 'boolean',
        'notification_new_patient' => 'boolean',
        'dashboard_recent_appointments' => 'boolean',
        'dashboard_recent_patient' => 'boolean',
        'reception_dashboard_notifications' => 'boolean',
        'pharmacy_dashboard_notifications' => 'boolean',
        'notify_pending_prescriptions' => 'boolean',
        'notify_dispensed_prescriptions' => 'boolean',
        'notify_inventory_status' => 'boolean',
        'notify_expired_medicines' => 'boolean',
        'notify_low_stock_medicines' => 'boolean',
        'low_stock_threshold' => 'integer',
        'allow_edit_appointments' => 'boolean',
        'allow_edit_patients' => 'boolean',
        'auto_assign_doctor' => 'boolean',
        'dark_mode' => 'boolean',
        'default_appointment_duration' => 'integer',
    ];
}
