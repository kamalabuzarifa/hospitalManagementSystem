
@extends('layouts.pharmacy')
@section('title',__('messages.thera') . ' - ' . __('messages.pharmacy') . ' - ' . __('messages.prescriptions'))


@section('content')

   <main class="flex-1">
      <div class="container mx-auto px-4" style="padding-bottom: 10px;">
         <h1 class="text-2xl font-bold mb-6">{{ __('messages.prescriptions') }}</h1>
         <div class="bg-white rounded-lg shadow p-6 mb-8">
               <div class="mb-4">
               <form method="GET" action="{{ route('pharmacy.prescriptions.index') }}">
                  <input
                     id="prescriptionsSearch"
                     type="text"
                     name="search"
                     value="{{ request('search') }}"
                     placeholder="{{ __('messages.search_by_prescription_or_patient') }}"
                     class="text-titlest w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-200"
                  />
               </form>
            </div>
            <div class="overflow-x-auto">
               <table class="min-w-full bg-white border border-gray-200 rounded">
                  <thead>
                        <tr class="bg-gray-50 text-gray-700">
                        <th class="px-6 py-3 text-left font-semibold">{{ __('messages.prescription_id') }}</th>
                        <th class="px-6 py-3 text-left font-semibold">{{ __('messages.patient') }}</th>
                        <th class="px-6 py-3 text-left font-semibold">{{ __('messages.doctor') }}</th>
                        <th class="px-6 py-3 text-left font-semibold">{{ __('messages.date') }}</th>
                        <th class="px-6 py-3 text-left font-semibold">{{ __('messages.status') }}</th>
                        <th class="px-6 py-3 text-left font-semibold">{{ __('messages.actions') }}</th>
                     </tr>
                  </thead>
                  <tbody>
                     @forelse($prescriptions as $prescription)
                        <tr class="border-t">
                           <td class="text-titlest px-6 py-4 text-blue-600 font-medium">
                              @php
                                 $rxCodes = $prescription->medicines->pluck('prescription_id')->filter()->unique();
                              @endphp
                              {{ $rxCodes->isNotEmpty() ? $rxCodes->implode(', ') : ('RX' . $prescription->id) ?? __('messages.not_available') }}
                           </td>
                           <td class="text-titlest px-6 py-4">{{ $prescription->appointment?->patient?->name ?? '' }}</td>
                           <td class="text-titlest px-6 py-4">{{ __('messages.dr') }} {{ $prescription->doctor?->user?->name ?? '' }}</td>
                           <td class="text-titlest px-6 py-4">{{ optional($prescription->created_at)->format('Y-m-d') }}</td>
                           <td class="text-titlest px-6 py-4">
                              @if($prescription->medicines->count() > 0)
                                 <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-sm">{{ __('messages.fulfilled') }}</span>
                              @else
                                 <span class="bg-gray-200 text-gray-700 px-3 py-1 rounded-full text-sm">{{ __('messages.pending') }}</span>
                              @endif
                           </td>
                           <td class="text-titlest px-6 py-4">
                              <a href="{{ route('pharmacy.prescriptions.show', $prescription) }}" class="text-blue-600 hover:underline">{{ __('messages.view') }}</a>
                           </td>
                        </tr>
                     @empty
                        <tr class="border-t">
                           <td class="text-titlest px-6 py-4" colspan="5">{{ __('messages.no_prescriptions_found') }}</td>
                        </tr>
                     @endforelse
                  </tbody>
               </table>
            </div>

            <div class="mt-4">
               <div class="div-all-accessLogs p-4 border-t border-gray-100">{{ $prescriptions->links() }}</div>
            </div>
         </div>
      </div>
   </main>

@endsection

   @push('scripts')
      <script>
         document.addEventListener('DOMContentLoaded', function () {
            try {
               const input = document.getElementById('prescriptionsSearch');
               if (!input) return;
               const form = input.closest('form') || document.querySelector('form[action="{{ route('pharmacy.prescriptions.index') }}"]');
               if (!form) return;
               let t;
               input.addEventListener('input', function () {
                  clearTimeout(t);
                  t = setTimeout(function () {
                     // submit the form (GET)
                     form.submit();
                  }, 380);
               });
               // optional: submit on paste
               input.addEventListener('paste', function () {
                  clearTimeout(t);
                  t = setTimeout(function () { form.submit(); }, 380);
               });
            } catch (e) { /* ignore */ }
         });
      </script>
   @endpush
