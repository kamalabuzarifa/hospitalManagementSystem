@extends('layouts.admin')

@section('title', __('messages.thera') . ' - ' . __('messages.administration') . ' - ' . __('messages.manage_staff_Edit') . ' (' . ($staff->user->name ?? 'userstaff') . ') ' )

@section('content')
    <main>
        <style>
            .responsive-btn {
                font-size: 1rem;
                padding-left: 2rem;
                padding-right: 2rem;
                padding-top: 0.5rem;
                padding-bottom: 0.5rem;
            }
            @media (max-width: 600px) {
                .responsive-btn {
                    font-size: 0.97rem;
                    padding-left: 1rem;
                    padding-right: 1rem;
                    padding-top: 0.45rem;
                    padding-bottom: 0.45rem;
                }
            }
        </style>
        <div class="py-8 px-4 sm:px-6 md:px-8">
            <div class="max-w-4xl mx-auto">
                <style>
                    @media (max-width: 900px) {
                        .text-3xl { font-size: 1.5rem !important; }
                        .text-2xl { font-size: 1.1rem !important; }
                        .p-8 { padding: 1rem !important; }
                        .mb-8 { margin-bottom: 1.5rem !important; }
                    }
                    @media (max-width: 600px) {
                        .py-8 { padding-top: 1.2rem !important; padding-bottom: 1.2rem !important; }
                        .px-4, .sm\:px-6, .md\:px-8 { padding-left: 0.5rem !important; padding-right: 0.5rem !important; }
                        .gap-4 { gap: 0.7rem !important; }
                        .space-y-4 > :not([hidden]) ~ :not([hidden]) { margin-top: 1.2rem !important; }
                        .grid-cols-2 { grid-template-columns: 1fr !important; }
                    }
                </style>
                <!-- Header -->
                <div class="mb-8">
                    <h2 class="text-title text-3xl font-bold text-gray-900">{{ __('messages.edit_staff_member') }}</h2>
                    <p class="text-titlesticky text-gray-600 mt-2">{{ __('messages.update_staff_info') }}</p>
                </div>

                <!-- Error Messages -->
                @if ($errors->any())
                    <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                        <h3 class="text-red-800 font-semibold mb-2">{{ __('messages.validation_errors') }}</h3>
                        <ul class="text-red-700 text-sm">
                            @foreach ($errors->all() as $error)
                                <li>• {{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Form -->
                <div class="bg-white rounded-lg shadow p-8">
                    <form action="{{ route('admin.staff.update', $staff) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Personal Information -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('messages.personal_information') }}</h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.full_name') }}</label>
                                    <input type="text" name="name" value="{{ old('name', $staff->user->name) }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Enter full name">
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.email') }}</label>
                                        <input type="email" name="email" value="{{ old('email', $staff->user->email) }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Enter email">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.phone_number') }}</label>
                                        <input type="text" name="phone" value="{{ old('phone', $staff->user->phone ?? '') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Enter phone number">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Department & Role -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('messages.position_and_department') }}</h3>
                            <div class="space-y-4">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.department') }}</label>
                                        <input type="text" name="department" value="{{ old('department', $staff->department) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., Cardiology, General, etc.">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.role_position') }}</label>
                                        <select name="role_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                            <option value="">{{ __('messages.select_role') }}</option>
                                            @foreach($roles as $role)
                                                <option value="{{ $role->id }}" @selected(old('role_id', $staff->user->role_id) == $role->id)>{{ __('messages.role_' . strtolower($role->name)) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.hire_date') }}</label>
                                    <input type="date" name="hire_date" value="{{ old('hire_date', $staff->hire_date?->format('Y-m-d')) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                            </div>
                        </div>

                        <!-- Address Information -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('messages.address_information') }}</h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.address') }}</label>
                                    <textarea name="address" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Full address">{{ old('address', $staff->address) }}</textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="flex items-center gap-4">
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition responsive-btn">
                                {{ __('messages.update_staff_member') }}
                            </button>
                            <a href="{{ route('admin.staff.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-800 font-semibold py-2 px-8 rounded-lg transition">
                                {{ __('messages.cancel') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
@endsection



