@extends('layouts.admin')

@section('title',__('messages.thera') . ' - ' . __('messages.administration') . ' - ' . __('messages.manage_staff') )

@section('content')
    <main>
        <div class="py-8 px-8">
            <div class="max-w-7xl mx-auto">
                <!-- Header -->
                <div class="mb-8 staff-header relative">
                    <div class="flex flex-col">
                        <h2 class="text-title text-3xl font-bold text-gray-900">{{ __('messages.manage_staff') }}</h2>
                        <p class="text-titlesticky text-gray-600 staff-subtitle-final" style="width:fit-content;min-width:200px;height:10px;font-size:14px;margin-bottom:20px;">{{ __('messages.manage_staff_subtitle') }}</p>
                    </div>
                    <!-- Button for md+ screens (position switches for RTL/LTR) -->
                    @php $isRtl = app()->getLocale() === 'ar'; @endphp
                    <button onclick="openModal()" class="hidden md:inline-flex items-center {{ $isRtl ? 'flex-row-reverse left-0 ml-2' : 'flex-row right-0 mr-2' }} bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 text-base shadow-md rounded-lg transition absolute top-0 mt-2" style="width: auto; min-width: 140px;">
                        <span class="{{ $isRtl ? 'order-2 mr-0 ml-2' : 'order-1 mr-2' }}">+</span>
                        <span class="align-middle">{{ __('messages.add_staff_member') }}</span>
                    </button>
                </div>

                <!-- Messages -->
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

                @if (session('success'))
                    <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4 text-green-800">
                        {{ session('success') }}
                    </div>
                @endif

                <!-- Staff Table -->
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full table-auto" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-6 py-3 text-xs font-medium text-gray-700 uppercase tracking-wider {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">{{ __('messages.name') }}</th>
                                    <th class="px-6 py-3 text-xs font-medium text-gray-700 uppercase tracking-wider {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">{{ __('messages.email') }}</th>
                                    <th class="px-6 py-3 text-xs font-medium text-gray-700 uppercase tracking-wider {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">{{ __('messages.position') }}</th>
                                    <th class="px-6 py-3 text-xs font-medium text-gray-700 uppercase tracking-wider {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">{{ __('messages.department') }}</th>
                                    <th class="px-6 py-3 text-xs font-medium text-gray-700 uppercase tracking-wider {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">{{ __('messages.status') }}</th>
                                    <th class="px-6 py-3 text-xs font-medium text-gray-700 uppercase tracking-wider {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">{{ __('messages.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse($staff as $member)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">
                                            <div class="text-sm font-medium text-gray-900">{{ $member->user->name }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">{{ $member->user->email }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">
                                            <span class="text-sm text-blue-600 font-medium">{{ ucfirst($member->user->role->name ?? __('messages.not_available')) }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">{{ $member->department ?? __('messages.not_available') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">
                                            @if($member->status)
                                                <span class="px-4 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 shadow-inner">{{ __('messages.active') }}</span>
                                            @else
                                                <span class="px-4 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-700 shadow-inner">{{ __('messages.inactive') }}</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">
                                            <div class="flex justify-around gap-2 {{ app()->getLocale() === 'ar' ? 'flex-row-reverse' : '' }}">
                                                <a href="{{ route('admin.staff.show', $member) }}" class="text-blue-600 hover:text-blue-900 font-semibold">{{ __('messages.view') }}</a>
                                                <a href="{{ route('admin.staff.edit', $member) }}" class="text-blue-600 hover:text-blue-900 font-semibold">{{ __('messages.edit') }}</a>
                                                <form action="{{ route('admin.staff.destroy', $member) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('messages.are_you_sure') }}')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900 font-semibold">{{ __('messages.delete') }}</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                            {{ __('messages.no_staff') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination -->
                <!-- Button for small screens (below table) -->
                <div class="block md:hidden mt-6 flex justify-center">
                    <button onclick="openModal()" class="block md:hidden bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 text-base shadow-md rounded-lg transition mx-auto" style="width: auto; min-width: 140px;">
                        <span class="align-middle">+ {{ __('messages.add_staff_member') }}</span>
                    </button>
                </div>
                @if($staff->hasPages())
                    <div class="mt-6">
                        <div class="div-all-accessLogs p-4 border-t border-gray-100">{{ $staff->links() }}</div>
                    </div>
                @endif
            </div>

            <!-- Add Staff Modal -->
            <style>
                @media (max-width: 640px) {
                    #staffModal .modal-content {
                        height: 500px;
                        max-height: 500px;
                        overflow-y: auto;
                    }
                }
            </style>
            <div id="staffModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50 p-4">
                <div class="bg-white rounded-lg shadow-xl w-full max-w-4xl max-h-[95vh] overflow-y-auto modal-content">
                    <div class="sticky top-0 bg-white border-b px-6 py-4 flex items-center justify-between">
                        <h3 class="text-2xl font-bold text-gray-900">{{ __('messages.add_new_staff') }}</h3>
                        <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 text-3xl font-bold leading-none">&times;</button>
                    </div>

                    <form action="{{ route('admin.staff.store') }}" method="POST" class="p-6">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Left Column -->
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.full_name') }} *</label>
                                    <input type="text" name="name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Ex: Ahmed Ali">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.email') }} *</label>
                                    <input type="email" name="email" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Ex: ahmed@hospital.com">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.phone_number') }}</label>
                                    <input type="text" name="phone" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Ex: 0599123456">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.address') }}</label>
                                    <textarea name="address" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Ex: Gaza, Palestine"></textarea>
                                </div>
                            </div>

                            <!-- Right Column -->
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.role_position') }}</label>
                                    <select name="role_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-gray-50">
                                        <option value="">{{ __('messages.select_role') }}</option>
                                        @foreach($roles as $role)
                                            <option value="{{ $role->id }}">{{ __('messages.role_' . strtolower($role->name)) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.department') }}</label>
                                    <select name="department" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-gray-50">
                                        <option value="">{{ __('messages.select_department') }}</option>
                                        <option value="Emergency">{{ __('messages.dept_emergency') }}</option>
                                        <option value="Surgery">{{ __('messages.dept_surgery') }}</option>
                                        <option value="Cardiology">{{ __('messages.dept_cardiology') }}</option>
                                        <option value="Pediatrics">{{ __('messages.dept_pediatrics') }}</option>
                                        <option value="Radiology">{{ __('messages.dept_radiology') }}</option>
                                        <option value="Laboratory">{{ __('messages.dept_laboratory') }}</option>
                                        <option value="Pharmacy">{{ __('messages.dept_pharmacy') }}</option>
                                        <option value="Reception">{{ __('messages.dept_reception') }}</option>
                                        <option value="Administration">{{ __('messages.dept_administration') }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.hire_date') }}</label>
                                    <input type="date" name="hire_date" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.password') }} *</label>
                                    <input type="password" name="password" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Ex: Pass@123">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.confirm_password') }} *</label>
                                    <input type="password" name="password_confirmation" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Confirm password">
                                </div>
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="flex items-center gap-4 mt-6 pt-4 border-t">
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg transition">
                                {{ __('messages.create_staff') }}
                            </button>
                            <button type="button" onclick="closeModal()" class="bg-gray-100 hover:bg-gray-200 text-gray-800 font-semibold py-2 px-6 rounded-lg transition">
                                {{ __('messages.cancel') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </main>
@endsection

@push('scripts')
    <script>
        function openModal() {
            document.getElementById('staffModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('staffModal').classList.add('hidden');
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('staffModal');
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>
@endpush
