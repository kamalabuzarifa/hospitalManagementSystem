<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ActivityLog;
class SettingDoctorController extends Controller
{
    public function index()
    {
        $doctor = Auth::user()->doctor;
        if (!$doctor) abort(403);

        // Language is session-based (middleware sets app locale from session)
        $language = app()->getLocale() ?: 'en';
        return view('doctor.setting.index', compact('doctor', 'language'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'phone' => 'nullable|digits_between:1,11',
            'specialization' => 'nullable|string|max:255',
            'avatar' => 'nullable|image|max:2048',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after_or_equal:start_time',
            'language' => 'required|in:en,ar',
        ]);

        $changes = [];
        // تحديث بيانات المستخدم (name, phone)
        if ($user->name !== $validated['name']) {
            $changes['name'] = ['old' => $user->name, 'new' => $validated['name']];
            $user->name = $validated['name'];
        }
        if (array_key_exists('phone', $validated) && $user->phone !== $validated['phone']) {
            $changes['phone'] = ['old' => $user->phone, 'new' => $validated['phone']];
            $user->phone = $validated['phone'];
        }
        $user->save();

        $doctor = $user->doctor;
        if ($doctor) {
            // Language is session-based (do not persist on doctor/settings tables)
            $currentLanguage = session('locale', app()->getLocale() ?: 'en');
            if ($currentLanguage !== $validated['language']) {
                $changes['language'] = ['old' => $currentLanguage, 'new' => $validated['language']];
            }
            session(['locale' => $validated['language']]);
            app()->setLocale($validated['language']);

            if ($request->hasFile('avatar')) {
                $path = $request->file('avatar')->store('avatars', 'public');
                $changes['avatar'] = ['old' => $doctor->avatar_url, 'new' => '/storage/' . $path];
                $doctor->avatar_url = '/storage/' . $path;
            }

            if (array_key_exists('specialization', $validated) && $doctor->specialization !== $validated['specialization']) {
                $changes['specialization'] = ['old' => $doctor->specialization, 'new' => $validated['specialization']];
                $doctor->specialization = $validated['specialization'];
            }
            if (array_key_exists('start_time', $validated) && $doctor->start_time !== $validated['start_time']) {
                $changes['start_time'] = ['old' => $doctor->start_time, 'new' => $validated['start_time']];
                $doctor->start_time = $validated['start_time'];
            }
            if (array_key_exists('end_time', $validated) && $doctor->end_time !== $validated['end_time']) {
                $changes['end_time'] = ['old' => $doctor->end_time, 'new' => $validated['end_time']];
                $doctor->end_time = $validated['end_time'];
            }

            $newDashboardNotif = $request->has('dashboard_notifications');
            if ((bool) ($doctor->dashboard_notifications ?? true) !== (bool) $newDashboardNotif) {
                $changes['dashboard_notifications'] = ['old' => (bool) ($doctor->dashboard_notifications ?? true), 'new' => (bool) $newDashboardNotif];
                $doctor->dashboard_notifications = (bool) $newDashboardNotif;
            }
            $doctor->save();
        }

        // سجل التغيير في جدول activity_logs إذا كان هناك تغييرات
        if (!empty($changes)) {
            ActivityLog::create([
                'user_id' => $user->id,
                'action' => 'updated_doctor_settings', // يجب أن يكون مطابقاً لما يعرضه الواجهة
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'meta' => ['changes' => $changes], // وضع التغييرات تحت مفتاح changes
            ]);
        }

        return redirect()->route('doctor.settings')->with('success', __('messages.settings_updated'));
    }
}
