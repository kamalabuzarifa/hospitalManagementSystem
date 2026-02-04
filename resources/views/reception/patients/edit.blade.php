@extends('layouts.reception')
@section('title', __('messages.thera') . ' - ' . __('messages.reception') . ' - ' . __('messages.edit_p_patient', ['name' => $patient->name]))

@section('content')
<main>
    <div class="container mx-auto px-4 " style="padding-bottom: 16px;">
        <div class="flex justify-between items-center">
            <h1 class="text-3xl font-bold">{{ __('messages.edit_patient') }}</h1>
            <a href="{{ route('reception.patients.show', $patient) }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-2 px-4 rounded">{{ __('messages.back') }}</a>
        </div>

        @if ($errors->any())
            <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form id="patientUpdateForm" action="{{ route('reception.patients.update', $patient) }}" method="POST" class="bg-white p-6 rounded shadow">
                @csrf
                @method('PUT')

                <h2 class="text-black text-lg font-semibold mb-4 border-b pb-2">{{ __('messages.basic_information') }}</h2>

                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">{{ __('messages.name') }} *</label>
                    <input type="text" name="name" value="{{ old('name', $patient->name) }}" required
                        class="text-titlest  w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @enderror">
                    @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">{{ __('messages.patient_code') }}</label>
                    <input type="text" value="{{ $patient->patient_code ?? __('messages.auto_generated') }}" readonly
                        class="text-titlest w-full px-4 py-2 border rounded bg-gray-100 text-gray-500">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2">{{ __('messages.date_of_birth') }}</label>
                            <input type="date" name="dob" value="{{ old('dob', $patient->dob ? \Carbon\Carbon::parse($patient->dob)->format('Y-m-d') : '') }}" max="{{ date('Y-m-d') }}"
                                   class="text-titlest w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('dob') border-red-500 @enderror">
                        @error('dob') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2">{{ __('messages.gender') }}</label>
                        <select name="gender" class="text-titlest w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('gender') border-red-500 @enderror">
                            <option value="">{{ __('messages.select') }}</option>
                            <option value="male" {{ old('gender', $patient->gender) === 'male' ? 'selected' : '' }}>{{ __('messages.male') }}</option>
                            <option value="female" {{ old('gender', $patient->gender) === 'female' ? 'selected' : '' }}>{{ __('messages.female') }}</option>
                            <option value="other" {{ old('gender', $patient->gender) === 'other' ? 'selected' : '' }}>{{ __('messages.other') }}</option>
                        </select>
                        @error('gender') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>

                <h2 class="text-black text-lg font-semibold mb-4 mt-6 border-b pb-2">{{ __('messages.contact_information') }}</h2>

                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">{{ __('messages.phone') }}</label>
                    <input type="tel" name="phone" value="{{ old('phone', $patient->phone) }}"
                        class="text-titlest w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('phone') border-red-500 @enderror">
                    @error('phone') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">Address</label>
                    <textarea name="address" rows="3"
                            class="text-titlest w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('address') border-red-500 @enderror">{{ old('address', $patient->address) }}</textarea>
                    @error('address') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <h2 class="text-black  text-lg font-semibold mb-4 mt-6 border-b pb-2">{{ __('messages.allergies') }}</h2>
                <div class="mb-3 flex flex-wrap gap-6 items-center">
                    <label class="text-titlest flex items-center gap-2">
                        <input type="radio" name="has_allergies" value="1" {{ (string) old('has_allergies', (string) (int) $patient->has_allergies) === '1' ? 'checked' : '' }}>
                        {{ __('messages.yes') }}
                    </label>
                    <label class="text-titlest flex items-center gap-2">
                        <input type="radio" name="has_allergies" value="0" {{ (string) old('has_allergies', (string) (int) $patient->has_allergies) === '0' ? 'checked' : '' }}>
                        {{ __('messages.no') }}
                    </label>
                </div>
                <div class="mb-4">
                    <label class=" block text-gray-700 font-bold mb-2">{{ __('messages.allergy_details') }}</label>
                    <textarea name="allergy_details" rows="3" placeholder="{{ __('messages.allergy_details_placeholder') }}"
                        class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('allergy_details') border-red-500 @enderror">{{ old('allergy_details', $patient->allergy_details) }}</textarea>
                    @error('allergy_details') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <h2 class="text-black text-lg font-semibold mb-4 mt-6 border-b pb-2">{{ __('messages.chronic_diseases') }}</h2>
                <div class="mb-3 flex flex-wrap gap-6 items-center">
                    <label class="text-titlest flex items-center gap-2">
                        <input type="radio" name="has_chronic" value="1" {{ (string) old('has_chronic', (string) (int) $patient->has_chronic) === '1' ? 'checked' : '' }}>
                        {{ __('messages.yes') }}
                    </label>
                    <label class="text-titlest flex items-center gap-2">
                        <input type="radio" name="has_chronic" value="0" {{ (string) old('has_chronic', (string) (int) $patient->has_chronic) === '0' ? 'checked' : '' }}>
                        {{ __('messages.no') }}
                    </label>
                </div>
                <div class="mb-6">
                    <label class="block text-gray-700 font-bold mb-2">{{ __('messages.chronic_details') }}</label>
                    <textarea name="chronic_details" rows="3" placeholder="{{ __('messages.chronic_details_placeholder') }}"
                        class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('chronic_details') border-red-500 @enderror">{{ old('chronic_details', $patient->chronic_details) }}</textarea>
                    @error('chronic_details') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
            </form>

        <div class="flex gap-2 mt-4">
            <button type="submit" form="patientUpdateForm" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded">
                {{ __('messages.save_changes') }}
            </button>
        </div>
    </div>
</main>
@endsection
