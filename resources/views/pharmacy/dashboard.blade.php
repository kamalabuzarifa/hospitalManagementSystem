
@extends('layouts.pharmacy')
@section('title',__('messages.thera') . ' - ' . __('messages.pharmacy') . ' - ' . __('messages.dashboard'))


@section('content')
    <main class="flex-1">
        <div class="container mx-auto px-4" style="padding-bottom: 10px;">
            <div class="max-w-7xl mx-auto">
                <h2 class="text-title text-2xl font-bold mb-8">{{ __('messages.dashboard') }}</h2>
                {{-- Unfilled / Pending prescriptions --}}
                @if($settings->notify_pending_prescriptions ?? true)
                <section class="mb-8">
                    <h3 class="text-titlesticky text-lg font-semibold mb-4">{{ __('messages.pending_prescriptions') }}</h3>
                    <div class="bg-white rounded-lg shadow overflow-hidden mb-4">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">{{ __('messages.prescription_id') }}</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">{{ __('messages.patient_name') }}</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">{{ __('messages.date') }}</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">{{ __('messages.status') }}</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">{{ __('messages.action') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @php
                                        $pendingPrescriptions = \App\Models\Prescription::with(['appointment.patient'])
                                            ->whereNull('dispensed_at')
                                            ->orderByDesc('created_at')
                                            ->limit(10)
                                            ->get();
                                    @endphp
                                    @forelse($pendingPrescriptions as $prescription)
                                            <td class="px-6 py-4 text-gray-800">
                                                @php
                                                    $rxCodes = $prescription->medicines->pluck('prescription_id')->filter()->unique();
                                                @endphp
                                                {{ $rxCodes->isNotEmpty() ? $rxCodes->implode(', ') : ('RX' . $prescription->id) ?? __('messages.not_available') }}
                                            </td>
                                            <td class="px-6 py-4 text-gray-700">{{ $prescription->appointment?->patient?->name ?? '-' }}</td>
                                            <td class="px-6 py-4 text-gray-600">{{ $prescription->created_at?->format('Y-m-d') }}</td>
                                            <td class="px-6 py-4"><span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-xs">{{ __('messages.pending') }}</span></td>
                                            <td class="px-6 py-4"><a href="{{ route('pharmacy.prescriptions.show', $prescription) }}" class="text-blue-600 hover:underline">{{ __('messages.view') }}</a></td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-6 py-4 text-center text-gray-400">{{ __('messages.no_urgent_requests_found') }}</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>
                @endif

                {{-- Dispensed prescriptions --}}
                @if($settings->notify_dispensed_prescriptions ?? true)
                <section class="mb-8">
                    <h3 class="text-titlesticky text-lg font-semibold mb-4">{{ __('messages.dispensed_prescriptions') }}</h3>
                    <div class="bg-white rounded-lg shadow overflow-hidden mb-4">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">{{ __('messages.prescription_id') }}</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">{{ __('messages.patient_name') }}</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">{{ __('messages.date') }}</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">{{ __('messages.status') }}</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">{{ __('messages.action') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @php
                                        $dispensedPrescriptions = \App\Models\Prescription::with(['appointment.patient', 'medicines'])
                                            ->whereNotNull('dispensed_at')
                                            ->orderByDesc('dispensed_at')
                                            ->limit(10)
                                            ->get();
                                    @endphp
                                    @forelse($dispensedPrescriptions as $prescription)
                                        <tr>
                                            <td class="px-6 py-4 text-gray-800">
                                                @php
                                                    $rxCodes = $prescription->medicines->pluck('prescription_id')->filter()->unique();
                                                @endphp
                                                {{ $rxCodes->isNotEmpty() ? $rxCodes->implode(', ') : ('RX' . $prescription->id) ?? __('messages.not_available') }}
                                            </td>
                                            <td class="px-6 py-4 text-gray-700">{{ $prescription->appointment?->patient?->name ?? '-' }}</td>
                                            <td class="px-6 py-4 text-gray-600">{{ $prescription->dispensed_at?->format('Y-m-d') ?? $prescription->created_at?->format('Y-m-d') }}</td>
                                            <td class="px-6 py-4"><span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-xs">{{ __('messages.dispensed') }}</span></td>
                                            <td class="px-6 py-4"><a href="{{ route('pharmacy.prescriptions.show', $prescription) }}" class="text-blue-600 hover:underline">{{ __('messages.view') }}</a></td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-6 py-4 text-center text-gray-400">{{ __('messages.no_dispensed_prescriptions_found') }}</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>
                @endif

                {{-- Inventory Status --}}
                @if($settings->notify_inventory_status ?? true)
                <section class="mb-8">
                    <h3 class="text-titlesticky text-lg font-semibold mb-4">{{ __('messages.inventory_status') }}</h3>
                    <div class="bg-white rounded-lg shadow overflow-hidden mb-4">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">{{ __('messages.medicine_name') }}</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">{{ __('messages.quantity') }}</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">{{ __('messages.status') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach(\App\Models\Medicine::with('inventory')->get() as $medicine)
                                        @php
                                            $quantity = $medicine->inventory?->quantity ?? 0;
                                            $statusKey = 'in_stock';
                                            $statusClass = 'bg-green-100 text-green-800';
                                            if ($medicine->isExpired()) {
                                                $statusKey = 'expired';
                                                $statusClass = 'bg-red-100 text-red-800';
                                            } elseif ($quantity <= ($settings->low_stock_threshold ?? 5) && $quantity > 0) {
                                                $statusKey = 'low_stock';
                                                $statusClass = 'bg-yellow-100 text-yellow-800';
                                            } elseif ($quantity < 1) {
                                                $statusKey = 'out_of_stock';
                                                $statusClass = 'bg-red-100 text-red-800';
                                            }
                                        @endphp
                                        <tr>
                                            <td class="px-6 py-4 text-gray-700">{{ $medicine->name }}</td>
                                            <td class="px-6 py-4 text-blue-600 font-medium">{{ $quantity }}</td>
                                            <td class="px-6 py-4"><span class="{{ $statusClass }} px-3 py-1 rounded-full text-xs">{{ __('messages.' . $statusKey) }}</span></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>
                @endif

                {{-- Notifications  --}}
                @if(($settings->pharmacy_dashboard_notifications ?? true))
                    <section class="mb-8">
                        <a id="notifications"></a>
                        <h3 class="text-titlesticky text-lg font-semibold mb-4">{{ __('messages.notifications') }}</h3>

                        @php
                            $items = $unreadNotifications ?? collect();
                            $isArabic = app()->getLocale() === 'ar';
                        @endphp

                        @if($items->isEmpty())
                            <div class="py-2 text-gray-400">{{ __('messages.no_notifications') }}</div>
                        @else
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                @foreach($items as $notification)
                                    @php
                                        $data = (array) ($notification->data ?? []);
                                        $message = $isArabic ? ($data['message_ar'] ?? null) : ($data['message_en'] ?? null);
                                        $prescriptionId = $data['prescription_id'] ?? null;
                                    @endphp

                                    <div class="bg-white rounded-lg shadow h-full border px-4 py-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4">
                                        <div class="min-w-0 w-full">
                                            <div class="font-semibold text-gray-900 text-sm sm:text-base">{{ $message ?? __('messages.new_notification') }}</div>
                                            <div class="text-gray-500 text-xs mt-1">{{ $notification->created_at?->diffForHumans() ?? '' }}</div>
                                        </div>

                                        <div class="flex items-center gap-2 flex-none mt-3 sm:mt-0">
                                            @if(isset($data['medicine_id']) && $data['medicine_id'])
                                                <a href="{{ route('pharmacy.inventory.index') }}?medicine={{ $data['medicine_id'] }}" class="w-full sm:w-28 h-10 inline-flex items-center justify-center bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium text-sm">{{ __('messages.view') }}</a>
                                            @elseif($prescriptionId)
                                                <a href="{{ route('pharmacy.prescriptions.show', $prescriptionId) }}" class="w-full sm:w-28 h-10 inline-flex items-center justify-center bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium text-sm">{{ __('messages.view') }}</a>
                                            @endif

                                            <form method="POST" action="{{ route('pharmacy.notifications.read', $notification->id) }}" class="w-full sm:w-auto">
                                                @csrf
                                                <button type="submit" class="w-full sm:w-28 h-10 inline-flex items-center justify-center bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium text-sm">{{ __('messages.mark_read') }}</button>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </section>
                @endif

                <!-- Stock Alerts Widget -->
                @if(($settings->notify_low_stock_medicines ?? true) || ($settings->notify_expired_medicines ?? true))
                <section class="mb-8">
                    <h3 class="text-titlesticky text-lg font-semibold mb-4">{{ __('messages.stock_alerts') ?? 'Stock Alerts' }}</h3>
                    <div class="bg-white rounded-lg shadow p-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @if($settings->notify_low_stock_medicines ?? true)
                            <div>
                                <h4 class="text-black font-semibold mb-2">{{ __('messages.low_stock') }}</h4>
                                @if(isset($lowStockMedicines) && $lowStockMedicines->isNotEmpty())
                                    <ul class="space-y-2">
                                        @foreach($lowStockMedicines as $med)
                                            <li class="flex items-center justify-between p-2 border rounded">
                                                <div>
                                                    <div class="text-titlest font-medium">{{ $med->name }}</div>
                                                    <div class="text-sm text-gray-500">{{ optional($med->inventory)->quantity ?? 0 }} remaining</div>
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <a href="{{ route('pharmacy.inventory.index') }}?medicine={{ $med->id }}{{ $med->prescription_id ? '&code=' . urlencode($med->prescription_id) : '' }}" class="text-sm inline-flex items-center justify-center px-3 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded">{{ __('messages.view') }}</a>
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <div class="text-gray-500">{{ __('messages.no_low_stock_medicines') ?? 'No low-stock medicines' }}</div>
                                @endif
                            </div>
                            @endif

                            @if($settings->notify_expired_medicines ?? true)
                            <div>
                                <h4 class="text-black font-semibold mb-2">{{ __('messages.expired') }}</h4>
                                @if(isset($expiredMedicinesList) && $expiredMedicinesList->isNotEmpty())
                                    <ul class="space-y-2">
                                        @foreach($expiredMedicinesList as $med)
                                            <li class="flex items-center justify-between p-2 border rounded">
                                                <div>
                                                    <div class="text-titlest font-medium">{{ $med->name }}</div>
                                                    <div class="text-sm text-gray-500">{{ $med->expiry_date?->format('Y-m-d') }}</div>
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <a href="{{ route('pharmacy.inventory.index') }}?medicine={{ $med->id }}{{ $med->prescription_id ? '&code=' . urlencode($med->prescription_id) : '' }}" class="text-sm inline-flex items-center justify-center px-3 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded">{{ __('messages.view') }}</a>
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <div class="text-gray-500">{{ __('messages.no_expired_medicines') ?? 'No expired medicines' }}</div>
                                @endif
                            </div>
                            @endif
                        </div>
                    </div>
                </section>
                @endif

            </div>
        </div>
    </main>

@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.open-notifications').forEach(function (el) {
                el.addEventListener('click', function (e) {
                    // ensure navigation takes place even if other handlers prevent default
                    window.location.href = el.href;
                });
            });
        });
    </script>
@endpush
