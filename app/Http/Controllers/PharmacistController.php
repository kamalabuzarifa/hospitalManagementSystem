<?php
namespace App\Http\Controllers;

use App\Models\Medicine;
use App\Models\Inventory;
use App\Models\Prescription;
use App\Models\Settings;
use App\Models\User;
use App\Notifications\PrescriptionDispensed;
use App\Notifications\MedicineStockAlert;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PharmacistController extends Controller


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
    public function dashboard()
    {
        $settings = $this->getGlobalSettings();

        $globalSettings = $settings;
        $globalThreshold = $globalSettings->low_stock_threshold ?? 5;

        $allWithInventory = Medicine::with('inventory')->get();
        $lowStockMedicinesCollection = $allWithInventory->filter(function($m) use ($globalThreshold) {
            $inv = $m->inventory;
            if (!$inv) return false;
            // Use global threshold to determine low stock (global setting overrides per-item minimum)
            $threshold = $globalThreshold;
            return (int)$inv->quantity <= (int)$threshold;
        });

        $lowStockCount = $lowStockMedicinesCollection->count();
        
        $expiredMedicines = Medicine::where('expiry_date', '<', today())->count();
        $expiredMedicinesList = Medicine::where('expiry_date', '<', today())->limit(10)->get();
        
        $totalMedicines = Medicine::count();
        
        $pendingPrescriptions = Prescription::whereDoesntHave('medicines')->count();

        $lowStockMedicines = $lowStockMedicinesCollection->take(10)->values();

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $unreadNotifications = $user->unreadNotifications()->latest()->limit(5)->get();

        return view('pharmacy.dashboard', compact(
            'settings',
            'lowStockCount',
            'expiredMedicines',
            'totalMedicines',
            'pendingPrescriptions',
            'lowStockMedicines',
            'expiredMedicinesList',
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
    public function markMedicineStockNotificationsRead(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user) {
            abort(403);
        }

        $user->unreadNotifications()->where('data->type', 'medicine_stock')->get()->each(function ($n) {
            $n->markAsRead();
        });

        return back()->with('success', __('messages.notifications_marked_read'));
    }
    public function markMedicineNotificationsRead(Request $request, $medicineId)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user) {
            abort(403);
        }

        $user->unreadNotifications()
            ->where('data->type', 'medicine_stock')
            ->where('data->medicine_id', (int) $medicineId)
            ->get()
            ->each(fn($n) => $n->markAsRead());

        return back()->with('success', __('messages.notifications_marked_read'));
    }
    public function inventory()
    {
        $medicines = \App\Models\Medicine::with('inventory')->get();

        // Gather unique prescription codes for autocomplete (from prescriptions and medicines)
        $prescriptionCodes = \App\Models\Prescription::query()->pluck('prescription_code')->filter();
        $medicineCodes = \App\Models\Medicine::query()->pluck('prescription_id')->filter();
        $codes = $prescriptionCodes->merge($medicineCodes)->unique()->values();

        $settings = $this->getGlobalSettings();

        return view('pharmacy.inventory.index', compact('medicines', 'codes', 'settings'));
    }

    /**
     * AJAX: find medicine by prescription code and return JSON
     */
    public function findByPrescription(Request $request)
    {
        $code = (string) $request->get('code');
        if ($code === '') {
            return response()->json(['success' => false, 'message' => 'empty_code'], 400);
        }

        // Try to find medicine directly by prescription_id
        $medicine = Medicine::with('inventory')->where('prescription_id', $code)->first();

        // If not found, try to find a prescription and return its first medicine (if any)
        if (!$medicine) {
            $prescription = Prescription::with(['medicines.inventory'])->where('prescription_code', $code)->first();
            if ($prescription) {
                if ($prescription->medicines->isNotEmpty()) {
                    $medicine = $prescription->medicines->first()->load('inventory');
                } else {
                    // No linked Medicine model — return the raw medicine text from prescription if available
                    $name = $prescription->medicine ?? null;
                    return response()->json([
                        'success' => true,
                        'data' => [
                            'medicine_id' => null,
                            'name' => $name,
                            'quantity' => 0,
                            'expiry_date' => null,
                            'status' => 'in_stock',
                            'source' => 'prescription',
                        ],
                    ]);
                }
            }
        }

        if (!$medicine) {
            return response()->json(['success' => false, 'message' => 'not_found'], 404);
        }

        $inventory = $medicine->inventory;
        $settings = $this->getGlobalSettings();
        $globalThreshold = $settings->low_stock_threshold ?? 5;
        // Use global threshold (override per-inventory minimum)
        $threshold = $globalThreshold;

        return response()->json([
            'success' => true,
            'data' => [
                'medicine_id' => $medicine->id,
                'name' => $medicine->name,
                'quantity' => $inventory ? (int) $inventory->quantity : 0,
                'expiry_date' => $medicine->expiry_date ? $medicine->expiry_date->format('Y-m-d') : null,
                'status' => $inventory && $medicine->isExpired() ? 'expired' : (($inventory && $inventory->quantity <= $threshold) ? 'low_stock' : 'in_stock'),
                'source' => 'medicine',
            ],
        ]);
    }

    /**
     * AJAX: find medicine by id and return JSON (used when navigating from dashboard alerts)
     */
    public function findById(Request $request)
    {
        $id = (int) $request->get('id');
        if ($id <= 0) {
            return response()->json(['success' => false, 'message' => 'invalid_id'], 400);
        }

        $medicine = Medicine::with('inventory')->find($id);
        if (!$medicine) {
            return response()->json(['success' => false, 'message' => 'not_found'], 404);
        }

        $inventory = $medicine->inventory;
        $settings = $this->getGlobalSettings();
        $globalThreshold = $settings->low_stock_threshold ?? 5;
        // Use global threshold (override per-inventory minimum)
        $threshold = $globalThreshold;

        return response()->json([
            'success' => true,
            'data' => [
                'medicine_id' => $medicine->id,
                'name' => $medicine->name,
                'quantity' => $inventory ? (int) $inventory->quantity : 0,
                'expiry_date' => $medicine->expiry_date ? $medicine->expiry_date->format('Y-m-d') : null,
                'status' => $inventory && $medicine->isExpired() ? 'expired' : (($inventory && $inventory->quantity <= $threshold) ? 'low_stock' : 'in_stock'),
                'source' => 'medicine',
            ],
        ]);
    }
    public function prescriptions(Request $request)
    {
        $query = Prescription::with(['appointment.patient', 'doctor.user', 'medicines'])
            ->whereNotNull('doctor_id');

        if ($request->status === 'pending') {
            $query->whereDoesntHave('medicines');
        } elseif ($request->status === 'fulfilled') {
            $query->whereHas('medicines');
        }

        if ($request->search) {
            $search = trim((string) $request->search);
            $query->where(function($q) use ($search) {
                // match prescription_code
                $q->where('prescription_code', 'like', "%{$search}%")
                    // or patient name
                    ->orWhereHas('appointment.patient', function($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%");
                    })
                    // or any linked medicine's prescription_id (qualify table to avoid ambiguous column)
                    ->orWhereHas('medicines', function($q3) use ($search) {
                        $q3->where('medicines.prescription_id', 'like', "%{$search}%");
                    });

                // if search is numeric, also match by prescription id
                if (is_numeric($search)) {
                    $q->orWhere('id', (int) $search);
                }
            });
        }

        $prescriptions = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('pharmacy.prescriptions.index', compact('prescriptions'));
    }

    public function showPrescription(Prescription $prescription)
    {
        $prescription->load(['appointment.patient', 'doctor.user', 'medicines']);

        $this->logActivity('administered_medication', ['prescription_id' => $prescription->id, 'patient_id' => $prescription->appointment->patient_id]);

        return view('pharmacy.prescriptions.show', compact('prescription'));
    }

    public function dispensePrescription(Request $request, Prescription $prescription)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user) {
            abort(403);
        }

        $prescription->load(['doctor.user', 'appointment.patient', 'patient', 'medicines']);

        if ($prescription->medicines()->exists() || $prescription->dispensed_at) {
            return back()->with('error', __('messages.prescription_already_dispensed'));
        }

        $rawMedicine = (string) ($prescription->medicine ?? '');
        $tokens = preg_split('/[\n,;|،]+/u', $rawMedicine) ?: [];
        $medicineNames = collect($tokens)
            ->map(fn ($value) => trim((string) $value))
            ->filter(fn ($value) => $value !== '')
            ->values();

        if ($medicineNames->isEmpty()) {
            return back()->with('error', __('messages.no_medicine_in_prescription'));
        }

        $attachedMedicineIds = [];

        try {
            DB::transaction(function () use ($medicineNames, $prescription, $user, &$attachedMedicineIds) {
                foreach ($medicineNames as $nameOrSku) {
                    $medicine = Medicine::query()
                        ->whereRaw('LOWER(sku) = LOWER(?)', [$nameOrSku])
                        ->orWhereRaw('LOWER(name) = LOWER(?)', [$nameOrSku])
                        ->first();

                    if (!$medicine) {
                        // fallback: partial name match
                        $medicine = Medicine::query()
                            ->where('name', 'like', '%' . $nameOrSku . '%')
                            ->first();
                    }

                    if (!$medicine) {
                        throw new \RuntimeException('الدواء غير موجود بالمخزون: ' . $nameOrSku);
                    }

                    if ($medicine->expiry_date && $medicine->expiry_date->isPast()) {
                        throw new \RuntimeException('الدواء منتهي الصلاحية: ' . $medicine->name);
                    }

                    $inventory = Inventory::query()
                        ->where('medicine_id', $medicine->id)
                        ->lockForUpdate()
                        ->first();

                    if (!$inventory) {
                        throw new \RuntimeException('لا يوجد سجل مخزون للدواء: ' . $medicine->name);
                    }

                    if ((int) $inventory->quantity < 1) {
                        throw new \RuntimeException('الدواء غير متوفر حالياً: ' . $medicine->name);
                    }

                    $quantity = 1;

                    if ((int) $inventory->quantity < $quantity) {
                        throw new \RuntimeException('الكمية غير كافية للدواء: ' . $medicine->name);
                    }

                    $inventory->decrement('quantity', $quantity);

                    // اربط prescription_id الخاص بالدواء برقم الوصفة إذا لم يكن موجوداً
                    if (empty($medicine->prescription_id)) {
                        $medicine->prescription_id = $prescription->prescription_code ?? ('RX' . $prescription->id);
                        $medicine->save();
                    }

                        // After decrement, check stock levels to possibly notify pharmacy staff
                        $inventory->refresh();
                        if ($inventory->quantity <= 0) {
                            $alert = new MedicineStockAlert('out', $medicine->id, $medicine->name, 0);
                            $recipients = User::query()
                                ->whereHas('role', fn($q) => $q->where('name', 'pharmacy'))
                                ->get();
                            foreach ($recipients as $r) {
                                $r->notify($alert);
                            }
                        } elseif ($inventory->quantity <= ($inventory->minimum_threshold ?? 5)) {
                            $alert = new MedicineStockAlert('low', $medicine->id, $medicine->name, $inventory->quantity);
                            $recipients = User::query()
                                ->whereHas('role', fn($q) => $q->where('name', 'pharmacy'))
                                ->get();
                            foreach ($recipients as $r) {
                                $r->notify($alert);
                            }
                        }

                    $prescription->medicines()->syncWithoutDetaching([
                        $medicine->id => ['quantity' => $quantity],
                    ]);

                    $attachedMedicineIds[] = $medicine->id;
                }

                $prescription->forceFill([
                    'dispensed_at' => now(),
                    'dispensed_by_user_id' => $user->id,
                ])->save();
            });
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        $patientName = $prescription->appointment?->patient?->name
            ?? $prescription->patient?->name
            ?? '';
        $doctorName = $prescription->doctor?->user?->name ?? '';

        $notification = new PrescriptionDispensed(
            prescriptionId: (int) $prescription->id,
            appointmentId: $prescription->appointment_id ? (int) $prescription->appointment_id : null,
            patientId: $prescription->patient_id ? (int) $prescription->patient_id : null,
            patientName: $patientName,
            doctorName: $doctorName,
        );

        $notifiedUserIds = [];

        $doctorUser = $prescription->doctor?->user;
        if ($doctorUser && (int) $doctorUser->id !== (int) $user->id) {
            if (!$prescription->doctor || $prescription->doctor->dashboard_notifications) {
                $doctorUser->notify($notification);
                $notifiedUserIds[] = (int) $doctorUser->id;
            }
        }

        $settings = $this->getGlobalSettings();
        if ($settings->reception_dashboard_notifications) {
            $receptionUsers = User::query()
                ->whereHas('role', fn ($q) => $q->where('name', 'receptionist'))
                ->where('id', '!=', $user->id)
                ->get();

            foreach ($receptionUsers as $receptionUser) {
                if (in_array((int) $receptionUser->id, $notifiedUserIds, true)) {
                    continue;
                }
                $receptionUser->notify($notification);
            }
        }

        $this->logActivity('dispensed_prescription', [
            'prescription_id' => $prescription->id,
            'medicine_ids' => $attachedMedicineIds,
            'dispensed_by_user_id' => $user->id,
        ]);

        return back()->with('success', __('messages.prescription_dispensed_success'));
    }
        public function settings()
    {
        $settings = $this->getGlobalSettings();

        return view('pharmacy.settings.index', [
            'settings' => $settings,
                'language' => app()->getLocale() ?: 'en',
        ]);
    }

    public function updateSettings(Request $request)
    {
        $settings = $this->getGlobalSettings();

        $validated = $request->validate([
            'language' => ['required', 'in:ar,en'],
            'pharmacy_dashboard_notifications' => ['nullable', 'boolean'],
            'notify_expired_medicines' => ['nullable', 'boolean'],
            'notify_low_stock_medicines' => ['nullable', 'boolean'],
            'notify_pending_prescriptions' => ['nullable', 'boolean'],
            'notify_dispensed_prescriptions' => ['nullable', 'boolean'],
            'notify_inventory_status' => ['nullable', 'boolean'],
            'low_stock_threshold' => ['required', 'integer', 'min:1'],
        ]);

        $settings->pharmacy_dashboard_notifications = (bool) ($validated['pharmacy_dashboard_notifications'] ?? false);
        $settings->notify_expired_medicines = (bool) ($validated['notify_expired_medicines'] ?? false);
        $settings->notify_low_stock_medicines = (bool) ($validated['notify_low_stock_medicines'] ?? false);
        $settings->notify_pending_prescriptions = (bool) ($validated['notify_pending_prescriptions'] ?? false);
        $settings->notify_dispensed_prescriptions = (bool) ($validated['notify_dispensed_prescriptions'] ?? false);
        $settings->notify_inventory_status = (bool) ($validated['notify_inventory_status'] ?? false);
        $settings->low_stock_threshold = (int) ($validated['low_stock_threshold'] ?? 10);
        $settings->save();

        // Language is session-based (do not persist on settings table)
        session(['locale' => $validated['language']]);
        app()->setLocale($validated['language']);

        return redirect()->route('pharmacy.settings.index')->with('success', __('messages.settings_updated'));
    }
    public function addInventory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'expiry_date' => 'required|date',
            'status' => 'required|in:in_stock,low_stock,expired',
            'prescription_id' => 'nullable|string|max:255',
        ]);

        // Prevent assigning the same prescription code to multiple different medicines
        $providedCode = $validated['prescription_id'] ?? null;
        $existingByName = \App\Models\Medicine::where('name', $validated['name'])->first();
        if ($providedCode) {
            $conflict = \App\Models\Medicine::where('prescription_id', $providedCode)
                ->when($existingByName, fn($q) => $q->where('id', '!=', $existingByName->id))
                ->exists();

            if ($conflict) {
                return back()->with('error', __('messages.prescription_code_taken', ['code' => $providedCode]));
            }
        }

        // ابحث عن الدواء أو أنشئه
        $medicine = Medicine::firstOrCreate(
            ['name' => $validated['name']],
            [
                'expiry_date' => $validated['expiry_date'],
                'sku' => uniqid('med_'),
                'prescription_id' => $validated['prescription_id'] ?? null,
            ]
        );

        // إذا كان الدواء موجوداً بالفعل، حدّث prescription_id إذا تغيّر
        if ($medicine->prescription_id !== ($validated['prescription_id'] ?? null)) {
            $medicine->prescription_id = $validated['prescription_id'] ?? null;
            $medicine->save();
        }

        // حدّث أو أنشئ الجرد
        $inventory = Inventory::updateOrCreate(
            ['medicine_id' => $medicine->id],
            [
                'quantity' => $validated['quantity'],
                'minimum_threshold' => 5, // قيمة افتراضية، يمكن تعديلها لاحقاً
            ]
        );

        // حدّث تاريخ الانتهاء في الدواء إذا تغيّر
        if ($medicine->expiry_date != $validated['expiry_date']) {
            $medicine->expiry_date = $validated['expiry_date'];
            $medicine->save();
        }

        return redirect()->route('pharmacy.inventory.index')->with('success', __('Medicine added/updated successfully.'));
    }
}
