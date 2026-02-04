@extends('layouts.reception')
@section('title', __('messages.thera') . ' - ' . __('messages.reception') . ' - ' . __('messages.patients') )

@section('content')
    <main>
        <div class="container mx-auto px-4 " style="padding-bottom: 16px;">
            @php($canEditPatients = ($appSettings?->allow_edit_patients ?? true))
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold">{{ __('messages.patients') }}</h1>
                @if($canEditPatients)
                    <button onclick="openModal()" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        + {{ __('messages.add_patient') }}
                    </button>
                @else
                    <button type="button" disabled title="{{ __('messages.patient_changes_disabled') }}" class="bg-gray-300 text-gray-600 font-bold py-2 px-4 rounded cursor-not-allowed">
                        + {{ __('messages.add_patient') }}
                    </button>
                @endif
            </div>

            @if (session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <div class="mb-6">
                <form id="patientSearchForm" action="{{ route('reception.patients.index') }}" method="GET" class="flex items-center bg-gray-100 rounded px-3 py-2">
                    <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 104.5 4.5a7.5 7.5 0 0012.15 12.15z" /></svg>
                    <input id="patientSearch" type="text" name="search" placeholder="{{ __('messages.search_by_name_or_id') }}" value="{{ request('search') }}" 
                        class="text-titlest flex-1 bg-transparent outline-none px-2 py-1" />
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border rounded-lg">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-4 py-3 text-left text-gray-600 font-semibold">{{ __('messages.patient_code') }}</th>
                            <th class="px-4 py-3 text-left text-gray-600 font-semibold">{{ __('messages.name') }}</th>
                            <th class="px-4 py-3 text-left text-gray-600 font-semibold">{{ __('messages.date_of_birth') }}</th>
                            <th class="px-4 py-3 text-left text-gray-600 font-semibold">{{ __('messages.gender') }}</th>
                            <th class="px-4 py-3 text-left text-gray-600 font-semibold">{{ __('messages.phone') }}</th>
                            <th class="px-4 py-3 text-center text-gray-600 font-semibold">{{ __('messages.allergies') }}</th>
                            <th class="px-4 py-3 text-center text-gray-600 font-semibold">{{ __('messages.chronic') }}</th>
                            <th class="px-4 py-3 text-center text-gray-600 font-semibold">{{ __('messages.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($patients as $patient)
                            <tr class="hover:bg-gray-50 border-b align-top">
                                <td class="text-titlest px-4 py-2">{{ $patient->patient_code ?? '—' }}</td>
                                <td class="text-titlest px-4 py-2">{{ $patient->name }}</td>
                                <td class="text-titlest px-4 py-2">{{ $patient->dob ? \Carbon\Carbon::parse($patient->dob)->format('Y-m-d') : '—' }}</td>
                                <td class="text-titlest px-4 py-2">
                                    <span class="capitalize">{{ $patient->gender ?? '—' }}</span>
                                </td>
                                <td class="text-titlest px-4 py-2">{{ $patient->phone ?? '—' }}</td>
                                <td class="text-titlest px-4 py-2 text-center">
                                    @if($patient->has_allergies)
                                        <span class="inline-block bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-sm font-semibold">{{ __('messages.yes') }}</span>
                                        @if($patient->allergy_details)
                                            <div class="mt-1 text-xs text-gray-600 whitespace-normal break-words">
                                                {{ $patient->allergy_details }}
                                            </div>
                                        @endif
                                    @else
                                        <span class="inline-block bg-gray-100 text-gray-700 px-2 py-1 rounded text-sm font-semibold">{{ __('messages.no') }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 text-center">
                                    @if($patient->has_chronic)
                                        <span class="inline-block bg-red-100 text-red-800 px-2 py-1 rounded text-sm font-semibold">{{ __('messages.yes') }}</span>
                                        @if($patient->chronic_details)
                                            <div class="mt-1 text-xs text-gray-600 whitespace-normal break-words">
                                                {{ $patient->chronic_details }}
                                            </div>
                                        @endif
                                    @else
                                        <span class="inline-block bg-gray-100 text-gray-700 px-2 py-1 rounded text-sm font-semibold">{{ __('messages.no') }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 text-center whitespace-nowrap text-sm">
                                    <div class="flex items-center justify-center gap-3 {{ app()->getLocale() === 'ar' ? 'flex-row-reverse' : '' }}">
                                        <a href="{{ route('reception.patients.show', $patient) }}" class="text-blue-600 hover:underline font-semibold">{{ __('messages.view') }}</a>
                                        @php($canEditPatients = ($appSettings?->allow_edit_patients ?? true))
                                        @if($canEditPatients)
                                            <a href="{{ route('reception.patients.edit', $patient) }}" class="text-blue-600 hover:underline font-semibold">{{ __('messages.edit') }}</a>
                                        @else
                                            <span class="text-gray-400 font-semibold cursor-not-allowed" title="Patient changes are disabled by settings.">{{ __('messages.edit') }}</span>
                                        @endif

                                        <form action="{{ route('reception.patients.destroy', $patient) }}" method="POST" class="inline" @if($canEditPatients) onsubmit="return confirm('{{ __('messages.are_you_sure') }}')" @endif>
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" @if(!$canEditPatients) disabled title="Patient changes are disabled by settings." class="text-gray-400 font-semibold cursor-not-allowed" @else class="text-red-600 hover:underline font-semibold" @endif>
                                                {{ __('messages.delete') }}
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-4 text-center text-gray-400">{{ __('messages.no_patients_found') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                <div class="div-all-accessLogs p-4 border-t border-gray-100">{{ $patients->links() }}</div>
            </div>
        </div>

        <!-- Modal -->
        <div id="patientModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 z-50 overflow-y-auto overscroll-contain flex items-start justify-center p-4 pt-16 md:pt-20 pb-8">
                <div class="relative w-full max-w-2xl max-h-[calc(100vh-6rem)] bg-white rounded-lg shadow-xl overflow-hidden flex flex-col">
                <div class="sticky top-0 bg-white border-b px-6 py-4 flex justify-between items-center z-10">
                    <h3 class="text-black text-2xl font-bold">{{ __('messages.new_guest_patient') }}</h3>
                    <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 text-3xl font-bold leading-none">&times;</button>
                </div>

                @if ($errors->any())
                    <div class="mx-6 mt-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('reception.patients.store') }}" method="POST" class="flex-1 overflow-y-auto overscroll-contain scroll-smooth px-6 py-4 pr-4" style="scrollbar-gutter: stable;">
                    @csrf

                    <h2 class="text-black text-lg font-semibold mb-3 mt-2">{{ __('messages.basic_information') }}</h2>
                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2">{{ __('messages.name') }}</label>
                        <input type="text" name="name" value="{{ old('name') }}" placeholder="{{ __('messages.name_placeholder') }}" required
                            class="text-titlest w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @enderror">
                        @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2">{{ __('messages.user_id') }}</label>
                        <input type="text" name="user_id" value="{{ __('messages.auto_generated') }}" readonly
                            class="text-titlest w-full px-4 py-2 border rounded bg-gray-100 text-gray-500">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="mb-4">
                               <label class="block text-gray-700 font-bold mb-2">{{ __('messages.date_of_birth') }}</label>
                               <input type="date" name="dob" value="{{ old('dob') }}" max="{{ date('Y-m-d') }}"
                                   class="text-titlest w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('dob') border-red-500 @enderror">
                            @error('dob') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 font-bold mb-2">Gender</label>
                            <select name="gender" class="text-titlest w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('gender') border-red-500 @enderror">
                                <option class="text-titlest" value="male" {{ old('gender') === 'male' ? 'selected' : '' }}>Male</option>
                                <option class="text-titlest" value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>Female</option>
                            </select>
                            @error('gender') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <h2 class="text-black text-lg font-semibold mb-2">{{ __('messages.contact_information') }}</h2>
                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2">{{ __('messages.phone') }}</label>
                        <input type="tel" name="phone" value="{{ old('phone') }}" placeholder="{{ __('messages.phone_placeholder') }}"
                            class="text-titlest w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('phone') border-red-500 @enderror">
                        @error('phone') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2">{{ __('messages.address') }}</label>
                        <textarea name="address" rows="3" placeholder="{{ __('messages.address_placeholder') }}"
                                class="text-titlest w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('address') border-red-500 @enderror">{{ old('address') }}</textarea>
                        @error('address') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <h2 class="text-black text-lg font-semibold mb-2">{{ __('messages.allergies') }}</h2>
                    <div class="mb-2 flex gap-6 items-center">
                        <label class="text-titlest flex items-center gap-2">
                            <input type="radio" name="has_allergies" value="1" {{ (string) old('has_allergies') === '1' ? 'checked' : '' }}>
                            {{ __('messages.yes') }}
                        </label>
                        <label class="text-titlest flex items-center gap-2">
                            <input type="radio" name="has_allergies" value="0" {{ (string) old('has_allergies') === '0' ? 'checked' : '' }}>
                            {{ __('messages.no') }}
                        </label>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2">{{ __('messages.allergy_details') }}</label>
                        <textarea name="allergy_details" rows="3" placeholder="{{ __('messages.allergy_details_placeholder') }}"
                                class="text-titlest w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('allergy_details') border-red-500 @enderror">{{ old('allergy_details') }}</textarea>
                        @error('allergy_details') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <h2 class="text-black text-lg font-semibold mb-2">{{ __('messages.chronic_diseases') }}</h2>
                    <div class="mb-2 flex gap-6 items-center">
                        <label class="text-titlest flex items-center gap-2">
                            <input type="radio" name="has_chronic" value="1" {{ (string) old('has_chronic') === '1' ? 'checked' : '' }}>
                            {{ __('messages.yes') }}
                        </label>
                        <label class="text-titlest flex items-center gap-2">
                            <input type="radio" name="has_chronic" value="0" {{ (string) old('has_chronic') === '0' ? 'checked' : '' }}>
                            {{ __('messages.no') }}
                        </label>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2">{{ __('messages.chronic_details') }}</label>
                        <textarea name="chronic_details" rows="3" placeholder="{{ __('messages.chronic_details_placeholder') }}"
                                class="text-titlest w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('chronic_details') border-red-500 @enderror">{{ old('chronic_details') }}</textarea>
                        @error('chronic_details') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div class="sticky bottom-0 bg-white border-t -mx-6 px-6 py-4 flex gap-2 justify-end mt-6">
                        <button type="button" onclick="closeModal()" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-2 px-6 rounded">
                            {{ __('messages.cancel') }}
                        </button>
                        <button type="submit" @if(!$canEditPatients) disabled title="{{ __('messages.patient_changes_disabled') }}" class="bg-gray-300 text-gray-600 font-bold py-2 px-6 rounded cursor-not-allowed" @else class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded" @endif>
                            {{ __('messages.save') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>
    <script>
        function openModal() {
            const modal = document.getElementById('patientModal');
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';

            // Always start from the top when opening (after render)
            const resetScroll = () => {
                modal.scrollTop = 0;
                const modalForm = modal.querySelector('form');
                if (modalForm) {
                    modalForm.scrollTop = 0;
                    if (typeof modalForm.scrollTo === 'function') {
                        modalForm.scrollTo({ top: 0, left: 0, behavior: 'auto' });
                    }
                }
            };

            requestAnimationFrame(resetScroll);
            setTimeout(resetScroll, 0);
        }

        function closeModal() {
            document.getElementById('patientModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        // Close modal when clicking outside
        document.getElementById('patientModal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal();
            }
        });

        // Reopen modal if there are validation errors
        @if ($errors->any())
            openModal();
        @endif

        // Debounced auto-submit for search (searches while typing)
        (function() {
            const form = document.getElementById('patientSearchForm');
            const input = document.getElementById('patientSearch');
            if (!form || !input) return;
            let timer = null;
            const DEBOUNCE_MS = 600;

            input.addEventListener('input', function() {
                if (timer) clearTimeout(timer);
                timer = setTimeout(() => {
                    form.submit();
                }, DEBOUNCE_MS);
            });
        })();
    </script>
@endsection
