@extends('layouts.admin')

@section('title', __('messages.thera') . ' - ' . __('messages.administration') . ' - ' . __('messages.manage_staff_View') . ' (' . ($staff->user->name ?? 'userstaff') . ') ' )

@section('content')
    <main>
        <style>
            @media (max-width: 900px) {
                .text-3xl { font-size: 1.5rem !important; }
                .text-lg { font-size: 1.1rem !important; }
                .p-6 { padding: 1.2rem !important; }
                .p-8 { padding: 1.2rem !important; }
                .mb-8 { margin-bottom: 1.5rem !important; }
                .gap-8 { gap: 1.5rem !important; }
            }
            @media (max-width: 600px) {
                .py-8 { padding-top: 1.2rem !important; padding-bottom: 1.2rem !important; }
                .px-8 { padding-left: 0.5rem !important; padding-right: 0.5rem !important; }
                .max-w-4xl { max-width: 100vw !important; }
                .p-6, .p-8 { padding: 0.7rem !important; }
                .mb-8 { margin-bottom: 1rem !important; }
                .gap-8 { gap: 1rem !important; }
                .grid-cols-2, .lg\:grid-cols-2 { grid-template-columns: 1fr !important; }
                .flex.items-center.gap-3 { flex-direction: column !important; gap: 0.7rem !important; align-items: stretch !important; }
                .flex.items-center.justify-between { flex-direction: column !important; gap: 0.7rem !important; align-items: stretch !important; }
                button, a { width: 100%; min-width: 0; }
            }
        </style>
        <div class="py-8 px-8">
            <div class="max-w-4xl mx-auto">
                <!-- Header -->
                <div class="mb-8 flex items-center justify-between">
                    @php
                        $locale = app()->getLocale();
                        $dateFormat = $locale === 'ar' ? 'd M Y' : 'M d, Y';
                        $dateTimeFormat = $locale === 'ar' ? 'd M Y h:i A' : 'M d, Y h:i A';
                        $departmentKey = $staff->department;
                        $department = $departmentKey
                            ? __($deptLabel = 'messages.dept_' . strtolower(str_replace(' ', '_', $departmentKey))) !== $deptLabel
                                ? __($deptLabel)
                                : $departmentKey
                            : __('messages.not_specified');
                        $roleName = $staff->user->role->name ?? null;
                        $translatedRole = $roleName ? __('messages.role_' . strtolower($roleName)) : __('messages.not_specified');
                    @endphp
                    <div>
                        <h2 class="text-title text-3xl font-bold text-gray-900">{{ $staff->user->name }}</h2>
                        <p class="text-titlesticky text-gray-600 mt-2">{{ $staff->position }} - {{ $department }}</p>
                    </div>
                    <div class="flex gap-3">
                        <a href="{{ route('admin.staff.edit', $staff) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg transition">
                            {{ __('messages.edit') }}
                        </a>
                        <form action="{{ route('admin.staff.destroy', $staff) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('messages.are_you_sure_delete_staff') }}');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-6 rounded-lg transition">
                                {{ __('messages.delete') }}
                            </button>
                        </form>
                        <a href="{{ route('admin.staff.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-800 font-semibold py-2 px-6 rounded-lg transition">
                            {{ __('messages.back') }}
                        </a>
                    </div>
                </div>

                <!-- Status Badge -->
                <div class="mb-8">
                    @if($staff->status)
                        <span class="px-3 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-800">{{ __('messages.active') }}</span>
                    @else
                        <span class="px-3 py-1 text-sm font-semibold rounded-full bg-gray-100 text-gray-800">{{ __('messages.inactive') }}</span>
                    @endif
                </div>

                <!-- Staff Details -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Personal Information -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('messages.personal_information') }}</h3>
                        <div class="space-y-3">
                            <div>
                                <p class="text-sm text-gray-600">{{ __('messages.email') }}</p>
                                <p class="text-gray-900 font-medium">{{ $staff->user->email }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">{{ __('messages.phone_number') }}</p>
                                <p class="text-gray-900 font-medium">{{ $staff->user->phone ?? __('messages.not_specified') }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">{{ __('messages.hire_date') }}</p>
                                <p class="text-gray-900 font-medium">{{ $staff->hire_date?->locale($locale)->translatedFormat($dateFormat) ?? __('messages.not_specified') }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Position & Department -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('messages.position_and_department') }}</h3>
                        <div class="space-y-3">
                            <div>
                                <p class="text-sm text-gray-600">{{ __('messages.department') }}</p>
                                <p class="text-gray-900 font-medium">{{ $staff->department ?? __('messages.not_specified') }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">{{ __('messages.role_position') }}</p>
                                <p class="text-gray-900 font-medium">{{ $translatedRole }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Address Information -->
                @if($staff->address)
                    <div class="bg-white rounded-lg shadow p-6 mt-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('messages.address_information') }}</h3>
                        <div class="space-y-3">
                            <div>
                                <p class="text-sm text-gray-600">{{ __('messages.address') }}</p>
                                <p class="text-gray-900 font-medium">{{ $staff->address }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Account Information -->
                <div class="bg-white rounded-lg shadow p-6 mt-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('messages.account_information') }}</h3>
                    <div class="space-y-3">
                        <div>
                            <p class="text-sm text-gray-600">{{ __('messages.user_id') }}</p>
                            <p class="text-gray-900 font-medium">#{{ $staff->user->id }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">{{ __('messages.created') }}</p>
                            <p class="text-gray-900 font-medium">{{ $staff->created_at->locale($locale)->translatedFormat($dateTimeFormat) }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">{{ __('messages.last_updated') }}</p>
                            <p class="text-gray-900 font-medium">{{ $staff->updated_at->locale($locale)->translatedFormat($dateTimeFormat) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection
