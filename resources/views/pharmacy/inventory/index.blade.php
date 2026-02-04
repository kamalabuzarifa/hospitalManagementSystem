@extends('layouts.pharmacy')
@section('title', __('messages.thera') . ' - ' . __('messages.pharmacy') . ' - ' . __('messages.inventory'))


@section('content')

<main class="flex-1">
    <div class="container mx-auto px-4" style="padding-bottom: 16px;">
        <h1 class="text-2xl font-bold mb-6">{{ __('messages.pharmacy_inventory') }}</h1>
        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <div class="mb-4">
                <input id="inventorySearch" type="text" placeholder="{{ __('messages.search_medicines_or_prescriptions') }}" class="text-titlest text-black w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-200" />
            </div>
            <h2 class="text-black text-lg font-semibold mb-2">{{ __('messages.stock_list') }}</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border border-gray-200 rounded">
                    <thead>
                        <tr class="bg-gray-50 text-gray-700">
                            <th class="px-6 py-3 text-left font-semibold">{{ __('messages.prescription_id') }}</th>
                            <th class="px-6 py-3 text-left font-semibold">{{ __('messages.medicine_name') }}</th>
                            <th class="px-6 py-3 text-left font-semibold">{{ __('messages.quantity') }}</th>
                            <th class="px-6 py-3 text-left font-semibold">{{ __('messages.expiry_date') }}</th>
                            <th class="px-6 py-3 text-left font-semibold">{{ __('messages.status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                            @forelse($medicines as $medicine)
                        <tr class="border-t">
                            <td class="text-titlest px-6 py-4 text-blue-600 font-medium">{{ $medicine->prescription_id ?? __('messages.not_available') }}</td>
                            <td class="text-titlest px-6 py-4">{{ $medicine->name }}</td>
                            <td class="text-titlest px-6 py-4 text-blue-600 font-medium">{{ optional($medicine->inventory)->quantity ?? 0 }}</td>
                            <td class="text-titlest px-6 py-4">{{ $medicine->expiry_date ? $medicine->expiry_date->format('Y-m-d') : __('messages.not_available') }}</td>
                            <td class="text-titlest px-6 py-4">
                                @php
                                    $statusKey = 'in_stock';
                                    $statusClass = 'bg-gray-200 text-gray-700';
                                    if ($medicine->isExpired()) {
                                        $statusKey = 'expired';
                                        $statusClass = 'bg-red-100 text-red-700';
                                    } elseif ($medicine->inventory && ($medicine->inventory->quantity <= 0)) {
                                        $statusKey = 'out_of_stock';
                                        $statusClass = 'bg-red-100 text-red-700';
                                    } elseif ($medicine->inventory && $medicine->inventory->quantity <= ($settings->low_stock_threshold ?? 5)) {
                                        $statusKey = 'low_stock';
                                        $statusClass = 'bg-yellow-100 text-yellow-700';
                                    }
                                @endphp
                                <span class="text-titlest {{ $statusClass }} px-3 py-1 rounded-full text-sm">{{ __('messages.' . $statusKey) }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-6 text-gray-500">{{ __('messages.no_medicines_found') }}</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-6">
                <button id="addMedicineBtn" type="button" class="bg-blue-500 hover:bg-blue-600 text-white font-medium px-6 py-2 rounded">{{ __('messages.add_medicine') }}</button>
            </div>

            <!-- Add Medicine Modal -->
            <div id="addMedicineModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 z-50 flex items-center justify-center p-4">
                <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto flex flex-col animate-fadeIn">
                    <div class="sticky top-0 bg-white border-b px-6 py-4 flex items-center justify-between">
                        <h3 class="text-2xl font-bold text-gray-900">{{ __('messages.add_medicine') }}</h3>
                        <button id="closeAddMedicineModal" class="text-gray-400 hover:text-gray-600 text-3xl font-bold leading-none">&times;</button>
                    </div>
                    <form method="POST" action="{{ route('pharmacy.inventory.store') }}" class="flex-1 overflow-y-auto overscroll-contain px-6 py-4 pr-4" style="scrollbar-gutter: stable;">
                        @csrf
                        <div class="mb-4">
                            <label class="text-titlest block text-gray-700 font-bold mb-2">{{ __('messages.prescription_id') }}</label>
                            <input list="prescriptionCodesList" type="text" name="prescription_id" id="prescriptionId" class="text-black w-full px-4 py-2 border rounded bg-white font-mono" value="" placeholder="{{ __('messages.prescription_id_placeholder') }}">
                            <datalist id="prescriptionCodesList">
                                @if(!empty($codes))
                                    @foreach($codes as $c)
                                        <option value="{{ $c }}"></option>
                                    @endforeach
                                @endif
                            </datalist>
                        </div>
                        <div class="mb-4">
                            <label class="text-titlest block text-gray-700 font-bold mb-2">{{ __('messages.medicine_name') }} *</label>
                            <div class="flex items-center gap-2">
                                <input type="text" name="name" id="modalName" class="text-black w-full px-4 py-2 border rounded" required>
                                <span id="sourceBadge" class="hidden inline-block text-sm px-2 py-1 rounded bg-gray-100 text-gray-700">Loaded</span>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="text-titlest block text-gray-700 font-bold mb-2">{{ __('messages.quantity') }} *</label>
                            <input type="number" name="quantity" min="1" class="text-black w-full px-4 py-2 border rounded" required>
                        </div>
                        <div class="mb-4">
                            <label class="text-titlest block text-gray-700 font-bold mb-2">{{ __('messages.expiry_date') }} *</label>
                            <input type="date" name="expiry_date" class="text-black w-full px-4 py-2 border rounded" required>
                        </div>
                        <div class="mb-4">
                            <label class="text-titlest block text-gray-700 font-bold mb-2">{{ __('messages.status') }}</label>
                            <input type="text" id="modalStatus" class="text-black w-full px-4 py-2 border rounded bg-gray-100" value="{{ __('messages.in_stock') }}" readonly>
                            <input type="hidden" name="status" value="in_stock">
                            <input type="hidden" name="medicine_id" id="medicineId" value="">
                        </div>
                        <div class="flex items-center gap-4 mt-6 pt-4 border-t">
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded">{{ __('messages.add') }}</button>
                            <button type="button" id="cancelAddMedicine" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-2 px-6 rounded">{{ __('messages.cancel') }}</button>
                        </div>
                    </form>
                </div>
            </div>
            <style>
                @keyframes fadeIn {
                    from { opacity: 0; transform: translateY(40px) scale(0.98); }
                    to { opacity: 1; transform: translateY(0) scale(1); }
                }
                .animate-fadeIn {
                    animation: fadeIn 0.3s cubic-bezier(.4,0,.2,1);
                }
            </style>
            @push('scripts')
                <script>
                    const __TRANSLATIONS = {
                        inStock: {!! json_encode(__('messages.in_stock')) !!},
                        expired: {!! json_encode(__('messages.expired')) !!},
                        lowStock: {!! json_encode(__('messages.low_stock')) !!}
                    };
                    const LOW_STOCK_THRESHOLD = {{ json_encode($settings->low_stock_threshold ?? 10) }};
                    function closeAddMedicineModalFn(e) {
                        if(e) e.preventDefault();
                        document.getElementById('addMedicineModal').classList.add('hidden');
                    }
                    // Close modal when clicking outside
                    window.onclick = function(event) {
                        const modal = document.getElementById('addMedicineModal');
                        if (event.target == modal) {
                            closeAddMedicineModalFn(event);
                        }
                    }
                    document.addEventListener('DOMContentLoaded', function() {
                        // Inventory search: filter table rows by prescription id or medicine name
                        try {
                            const searchInput = document.getElementById('inventorySearch');
                            const tableBody = document.querySelector('table tbody');
                            function filterRows() {
                                const q = (searchInput?.value || '').trim().toLowerCase();
                                if (!tableBody) return;
                                const rows = Array.from(tableBody.querySelectorAll('tr'));
                                if (q === '') {
                                    rows.forEach(r => r.style.display = '');
                                    return;
                                }
                                rows.forEach(r => {
                                    // first td = prescription_id, second td = medicine name
                                    const tds = r.querySelectorAll('td');
                                    if (!tds || tds.length < 2) {
                                        r.style.display = '';
                                        return;
                                    }
                                    const code = (tds[0].textContent || '').trim().toLowerCase();
                                    const name = (tds[1].textContent || '').trim().toLowerCase();
                                    if (code.includes(q) || name.includes(q)) {
                                        r.style.display = '';
                                    } else {
                                        r.style.display = 'none';
                                    }
                                });
                            }
                            const debouncedFilter = (function(fn, wait){ let t; return function(){ clearTimeout(t); t = setTimeout(fn, wait); }})(filterRows, 180);
                            searchInput?.addEventListener('input', debouncedFilter);
                        } catch(e) { /* ignore */ }
                        const addMedicineBtn = document.getElementById('addMedicineBtn');
                        const addMedicineModal = document.getElementById('addMedicineModal');
                        const closeAddMedicineModalBtn = document.getElementById('closeAddMedicineModal');
                        const cancelAddMedicine = document.getElementById('cancelAddMedicine');
                        const quantityInput = document.querySelector('input[name="quantity"]');
                        const expiryInput = document.querySelector('input[name="expiry_date"]');
                        const statusInput = document.getElementById('modalStatus');
                        const prescriptionInput = document.getElementById('prescriptionId');
                        const medicineIdInput = document.getElementById('medicineId');
                        const nameInput = document.querySelector('input[name="name"]');

                        function updateStatus() {
                            const quantity = parseInt(quantityInput.value, 10);
                            const expiry = expiryInput.value;
                            let status = __TRANSLATIONS.inStock;
                            let today = new Date();
                            let expired = false;
                            if (expiry) {
                                const expiryDate = new Date(expiry);
                                if (expiryDate < today.setHours(0,0,0,0)) {
                                    status = __TRANSLATIONS.expired;
                                    expired = true;
                                }
                            }
                            if (!expired && !Number.isNaN(quantity)) {
                                if (quantity <= 0) {
                                    status = {!! json_encode(__('messages.out_of_stock')) !!};
                                } else if (quantity <= LOW_STOCK_THRESHOLD) {
                                    status = __TRANSLATIONS.lowStock;
                                }
                            }
                            statusInput.value = status;
                        }

                        quantityInput?.addEventListener('input', updateStatus);
                        expiryInput?.addEventListener('change', updateStatus);

                        // فتح المودال يدوياً: التركيز على حقل الاسم لادخال دواء جديد
                        function generatePrescriptionCode() {
                            const rand = Math.floor(Math.random() * 900) + 100; // 100-999
                            return 'RX' + Date.now().toString().slice(-6) + rand.toString();
                        }

                        function openModalManual() {
                            addMedicineModal.classList.remove('hidden');
                            // إذا حقل Prescription فارغ، املأ كود تلقائيًا لتسريع العمل
                            try {
                                if (prescriptionInput && (!prescriptionInput.value || prescriptionInput.value.trim() === '')) {
                                    prescriptionInput.value = generatePrescriptionCode();
                                }
                            } catch(e) { /* ignore */ }
                            updateStatus();
                            setTimeout(() => {
                                try {
                                    if (nameInput) {
                                        nameInput.focus();
                                        nameInput.select();
                                    }
                                } catch(e) { /* ignore */ }
                            }, 50);
                        }

                        // فتح المودال نتيجة ملء تلقائي: التركيز على الكمية لتعديلها بسرعة
                        function openModalAutofill() {
                            addMedicineModal.classList.remove('hidden');
                            updateStatus();
                            setTimeout(() => {
                                try {
                                    if (quantityInput) {
                                        quantityInput.focus();
                                        quantityInput.select();
                                    }
                                } catch(e) { /* ignore */ }
                            }, 50);
                        }
                        function closeModal() {
                            addMedicineModal.classList.add('hidden');
                        }
                        addMedicineBtn?.addEventListener('click', openModalManual);
                        closeAddMedicineModalBtn?.addEventListener('click', closeModal);
                        cancelAddMedicine?.addEventListener('click', closeModal);

                        // Debounce helper
                        function debounce(fn, wait) {
                            let t;
                            return function(...args) {
                                clearTimeout(t);
                                t = setTimeout(() => fn.apply(this, args), wait);
                            };
                        }

                        async function lookupByPrescription(code) {
                            if (!code) return;
                            try {
                                const url = '/pharmacy/inventory/find-by-prescription?code=' + encodeURIComponent(code);
                                const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
                                if (!res.ok) {
                                    // not found or error -> clear medicine id but keep user inputs
                                    medicineIdInput.value = '';
                                    return;
                                }
                                const payload = await res.json();
                                if (payload?.success && payload.data) {
                                    const d = payload.data;
                                    // Prefill fields
                                    if (nameInput && (!nameInput.value || nameInput.value.trim() === '')) nameInput.value = d.name || '';
                                    if (quantityInput) quantityInput.value = d.quantity ?? 0;
                                    if (expiryInput) expiryInput.value = d.expiry_date ?? '';
                                    if (medicineIdInput) medicineIdInput.value = d.medicine_id ?? '';
                                    // Show source badge
                                    try {
                                        const badge = document.getElementById('sourceBadge');
                                        if (badge) {
                                            if (d.source === 'medicine') {
                                                badge.textContent = 'من المخزون';
                                                badge.classList.remove('hidden');
                                                badge.classList.remove('bg-yellow-100');
                                                badge.classList.remove('bg-green-100');
                                                badge.classList.add('bg-green-100');
                                            } else if (d.source === 'prescription') {
                                                badge.textContent = 'من الوصفة';
                                                badge.classList.remove('hidden');
                                                badge.classList.remove('bg-green-100');
                                                badge.classList.add('bg-yellow-100');
                                            } else {
                                                badge.classList.add('hidden');
                                            }
                                        }
                                    } catch (e) { /* ignore */ }
                                    // Update status label
                                    if (d.status === 'expired') statusInput.value = __TRANSLATIONS.expired;
                                    else if (d.status === 'low_stock') statusInput.value = __TRANSLATIONS.lowStock;
                                    else statusInput.value = __TRANSLATIONS.inStock;
                                    // open modal if not open
                                    if (addMedicineModal.classList.contains('hidden')) {
                                        openModalAutofill();
                                    } else {
                                        updateStatus();
                                        // إذا المودال مفتوح مسبقاً ضع التركيز على الكمية بعد التحديث
                                        setTimeout(() => {
                                            try {
                                                if (quantityInput) {
                                                    quantityInput.focus();
                                                    quantityInput.select();
                                                }
                                            } catch(e) { /* ignore */ }
                                        }, 20);
                                    }
                                }
                            } catch (e) {
                                console.error('lookup error', e);
                            }
                        }

                        const debouncedLookup = debounce(function() {
                            const code = (prescriptionInput?.value || '').trim();
                            if (code.length > 0) lookupByPrescription(code);
                        }, 350);

                        // Trigger lookup when prescription code changes/selected
                        prescriptionInput?.addEventListener('input', debouncedLookup);
                        prescriptionInput?.addEventListener('change', debouncedLookup);
                        prescriptionInput?.addEventListener('blur', debouncedLookup);

                        // If page was opened with ?medicine={id}, fetch that medicine and open modal for edit
                        try {
                            const params = new URLSearchParams(window.location.search);
                            const medId = params.get('medicine');
                            if (medId) {
                                // fetch by id and open modal autofill
                                (async function() {
                                    try {
                                        const url = '/pharmacy/inventory/find-by-id?id=' + encodeURIComponent(medId);
                                        const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
                                        if (!res.ok) return;
                                        const payload = await res.json();
                                        if (payload?.success && payload.data) {
                                            const d = payload.data;
                                            if (nameInput && (!nameInput.value || nameInput.value.trim() === '')) nameInput.value = d.name || '';
                                            if (quantityInput) quantityInput.value = d.quantity ?? 0;
                                            if (expiryInput) expiryInput.value = d.expiry_date ?? '';
                                            if (medicineIdInput) medicineIdInput.value = d.medicine_id ?? '';
                                            try {
                                                const badge = document.getElementById('sourceBadge');
                                                if (badge) {
                                                    badge.textContent = 'من المخزون';
                                                    badge.classList.remove('hidden');
                                                    badge.classList.remove('bg-yellow-100');
                                                    badge.classList.add('bg-green-100');
                                                }
                                            } catch(e) {}
                                            openModalAutofill();
                                            // بعد فتح المودال، انزع معامل الكود من الـ URL ليبقى الرابط نظيفاً
                                            try {
                                                const u = new URL(window.location.href);
                                                u.searchParams.delete('code');
                                                history.replaceState(null, '', u.toString());
                                            } catch(e) { /* ignore */ }
                                        }
                                    } catch(e) { console.error(e); }
                                })();
                            }

                            // If a prescription code was passed in the URL (e.g. ?code=RX123), prefill and lookup
                            try {
                                const params2 = new URLSearchParams(window.location.search);
                                const codeParam = params2.get('code');
                                if (codeParam && prescriptionInput) {
                                    prescriptionInput.value = codeParam;
                                    // immediately lookup by prescription code and open modal
                                    // remove `code` from URL after lookup to keep URL clean
                                    lookupByPrescription(codeParam).then(() => {
                                        try {
                                            const u = new URL(window.location.href);
                                            u.searchParams.delete('code');
                                            history.replaceState(null, '', u.toString());
                                        } catch(e) { /* ignore */ }
                                    }).catch(() => {
                                        try {
                                            const u = new URL(window.location.href);
                                            u.searchParams.delete('code');
                                            history.replaceState(null, '', u.toString());
                                        } catch(e) { /* ignore */ }
                                    });
                                }
                            } catch(e) { /* ignore */ }
                        } catch(e) { /* ignore */ }

                        // Close modal on ESC
                        document.addEventListener('keydown', function(e) {
                            if (e.key === 'Escape') closeModal();
                        });
                    });
                </script>
            @endpush

             @stack('scripts')
        </div>
    </div>
</main>

@endsection
