<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use App\Models\User;
use App\Models\Role;
use App\Models\Doctor;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;

class StaffController extends Controller
{
    use LogsActivity;

    public function __construct()
    {
        $this->middleware('permission:staff.view,manage_users')->only(['index', 'show']);
        $this->middleware('permission:staff.create,manage_users')->only(['create', 'store']);
        $this->middleware('permission:staff.edit,manage_users')->only(['edit', 'update', 'toggleStatus']);
        $this->middleware('permission:staff.delete,manage_users')->only(['destroy']);
    }
    public function index()
    {
        $staff = Staff::with('user')->paginate(10);
        $roles = Role::whereIn('name', ['doctor', 'receptionist', 'pharmacy'])->get();
        return view('admin.staff.index', compact('staff', 'roles'));
    }

    public function create()
    {
        $roles = Role::all();
        return view('admin.staff.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|digits_between:1,10',
            'department' => 'nullable|string|max:255',
            'role_id' => 'required|exists:roles,id',
            'password' => 'required|string|min:8|confirmed',
            'hire_date' => 'nullable|date',
            'address' => 'nullable|string',
        ]);

        // Create user account
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => bcrypt($validated['password']),
            'role_id' => $validated['role_id'],
            'completed_registration' => false,
        ]);

        // Get role and set default department if not provided
        $role = Role::find($validated['role_id']);
        $department = $validated['department'];
        if (empty($department) && $role) {
            $department = match($role->name) {
                'doctor' => 'General Medicine',
                'pharmacy' => 'Pharmacy',
                'receptionist' => 'Reception',
                'admin' => 'Administration',
                default => 'General',
            };
        }

        // Create staff record
        Staff::create([
            'phone' => $validated['phone'],
            'department' => $department,
            'user_id' => $user->id,
            'hire_date' => $validated['hire_date'],
            'address' => $validated['address'],
        ]);

        // If role is 'doctor', also create a Doctor record
        if ($role && $role->name === 'doctor') {
            Doctor::create([
                'user_id' => $user->id,
                'specialization' => $department ?? null,
                'start_time' => null,
                'end_time' => null,
            ]);
        }

        $this->logActivity('created_staff', [
            'staff_name' => $validated['name'],
            'department' => $department ?? '',
            'full_text_ar' => 'تم إنشاء موظف ' . $validated['name'],
            'full_text_en' => 'Created staff member ' . $validated['name'],
        ]);

        return redirect()->route('admin.staff.index')->with('success', __('messages.staff_member_created_success'));
    }

    public function show(Staff $staff)
    {
        $staff->load('user');
        $name = $staff->user->name;
        $this->logActivity('viewed_staff', [
            'staff_name' => $name,
            'full_text_ar' => 'عرض بيانات الموظف ' . $name,
            'full_text_en' => 'Viewed staff member ' . $name,
        ]);
        return view('admin.staff.show', compact('staff'));
    }

    public function edit(Staff $staff)
    {
        $staff->load('user');
        $roles = Role::all();
        $name = $staff->user->name;
        $this->logActivity('opened_staff_edit', [
            'staff_name' => $name,
            'full_text_ar' => 'فتح صفحة تعديل الموظف ' . $name,
            'full_text_en' => 'Opened staff edit for ' . $name,
        ]);
        return view('admin.staff.edit', compact('staff', 'roles'));
    }

    public function update(Request $request, Staff $staff)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $staff->user_id,
            'phone' => 'required|digits_between:1,10',
            'department' => 'nullable|string|max:255',
            'role_id' => 'required|exists:roles,id',
            'hire_date' => 'nullable|date',
            'address' => 'nullable|string',
        ]);

        // Get new role and set default department if not provided
        $newRole = Role::find($validated['role_id']);
        $department = $validated['department'];
        if (empty($department) && $newRole) {
            $department = match($newRole->name) {
                'doctor' => 'General Medicine',
                'pharmacy' => 'Pharmacy',
                'receptionist' => 'Reception',
                'admin' => 'Administration',
                default => 'General',
            };
        }

        // Update staff record
        $staff->update([
            // 'phone' => $validated['phone'],
            'department' => $department,
            'hire_date' => $validated['hire_date'],
            'address' => $validated['address'],
        ]);

        // Update user record
        $oldRole = $staff->user->role;
        $staff->user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'role_id' => $validated['role_id'],
        ]);

        // Handle Doctor record transitions
        if ($oldRole && $oldRole->name === 'doctor' && (!$newRole || $newRole->name !== 'doctor')) {
            // Role changed from doctor to something else: delete Doctor record
            Doctor::where('user_id', $staff->user->id)->delete();
        } elseif ((!$oldRole || $oldRole->name !== 'doctor') && $newRole && $newRole->name === 'doctor') {
            // Role changed to doctor: create Doctor record
            Doctor::firstOrCreate(
                ['user_id' => $staff->user->id],
                [
                    'specialization' => $department ?? null,
                    'working_hours' => null,
                    'bio' => null,
                ]
            );
        } elseif ($newRole && $newRole->name === 'doctor') {
            // Remains a doctor: update specialization
            Doctor::where('user_id', $staff->user->id)->update([
                'specialization' => $department ?? null,
            ]);
        }

        $this->logActivity('updated_staff_information', [
            'staff_name' => $validated['name'],
            'full_text_ar' => 'تم تحديث معلومات الموظف ' . $validated['name'],
            'full_text_en' => 'Updated staff information ' . $validated['name'],
        ]);

        return redirect()->route('admin.staff.index')->with('success', __('messages.staff_member_updated_success'));
    }

    public function destroy(Staff $staff)
    {
        $name = $staff->user->name;
        $user = $staff->user;
        $role = $user->role;

        // If user was a doctor, also delete the Doctor record
        if ($role && $role->name === 'doctor') {
            Doctor::where('user_id', $user->id)->delete();
        }

        $staff->delete();
        $user->delete();

        $this->logActivity('deleted_staff', [
            'staff_name' => $name,
            'full_text_ar' => 'تم حذف الموظف ' . $name,
            'full_text_en' => 'Deleted staff member ' . $name,
        ]);

        return redirect()->route('admin.staff.index')->with('success', __('messages.staff_member_deleted_success'));
    }

    public function toggleStatus(Staff $staff)
    {
        $staff->status = !$staff->status;
        $staff->save();

        $name = $staff->user->name ?? '';
        $enabled = (bool) $staff->status;
        $statusEn = $enabled ? 'Enabled' : 'Disabled';
        $statusAr = $enabled ? 'مفعّل' : 'معطّل';
        $this->logActivity('toggled_staff_status', [
            'staff_name' => $name,
            'status' => $statusEn,
            'full_text_ar' => 'تم تغيير حالة الموظف ' . $name . ' إلى ' . $statusAr,
            'full_text_en' => 'Toggled staff status for ' . $name . ' to ' . $statusEn,
        ]);

        return back()->with('success', __('messages.staff_status_updated_success'));
    }
}
