<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use App\Models\Inventory;
use Illuminate\Http\Request;

class PharmacyController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:pharmacy.view,manage_pharmacy')->only(['index', 'show']);
        $this->middleware('permission:pharmacy.create,manage_pharmacy')->only(['create', 'store']);
        $this->middleware('permission:pharmacy.edit,manage_pharmacy')->only(['edit', 'update']);
        $this->middleware('permission:pharmacy.delete,manage_pharmacy')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $query = Medicine::with('inventory');

        if ($request->search) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('sku', 'like', '%' . $request->search . '%');
        }

        if ($request->low_stock) {
            $query->whereHas('inventory', function($q) {
                $q->whereRaw('quantity <= minimum_threshold');
            });
        }

        $medicines = $query->latest()->paginate(20);

        return view('admin.pharmacy.index', compact('medicines'));
    }

    public function create()
    {
        return view('admin.pharmacy.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:medicines,name',
            'sku' => 'required|string|max:255|unique:medicines,sku',
            'unit_price' => 'required|numeric|min:0',
            'expiry_date' => 'nullable|date|after:today',
            'quantity' => 'required|integer|min:0',
            'minimum_threshold' => 'required|integer|min:0',
        ]);

        $medicine = Medicine::create([
            'name' => $validated['name'],
            'sku' => $validated['sku'],
            'unit_price' => $validated['unit_price'],
            'expiry_date' => $validated['expiry_date'] ?? null,
        ]);

        Inventory::create([
            'medicine_id' => $medicine->id,
            'quantity' => $validated['quantity'],
            'minimum_threshold' => $validated['minimum_threshold'],
        ]);

        return redirect()
            ->route('admin.pharmacy.index')
            ->with('success', __('messages.medicine_added_success'));
    }

    public function show(Medicine $pharmacy)
    {
        $pharmacy->load('inventory');
        return view('admin.pharmacy.show', compact('pharmacy'));
    }

    public function edit(Medicine $pharmacy)
    {
        $pharmacy->load('inventory');
        return view('admin.pharmacy.edit', compact('pharmacy'));
    }

    public function update(Request $request, Medicine $pharmacy)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:medicines,name,' . $pharmacy->id,
            'sku' => 'required|string|max:255|unique:medicines,sku,' . $pharmacy->id,
            'unit_price' => 'required|numeric|min:0',
            'expiry_date' => 'nullable|date|after:today',
            'quantity' => 'required|integer|min:0',
            'minimum_threshold' => 'required|integer|min:0',
        ]);

        $pharmacy->update([
            'name' => $validated['name'],
            'sku' => $validated['sku'],
            'unit_price' => $validated['unit_price'],
            'expiry_date' => $validated['expiry_date'] ?? null,
        ]);

        $pharmacy->inventory()->update([
            'quantity' => $validated['quantity'],
            'minimum_threshold' => $validated['minimum_threshold'],
        ]);

        return redirect()
            ->route('admin.pharmacy.show', $pharmacy)
            ->with('success', __('messages.medicine_updated_success'));
    }

    public function destroy(Medicine $pharmacy)
    {
        $pharmacy->delete();

        return redirect()
            ->route('admin.pharmacy.index')
            ->with('success', __('messages.medicine_deleted_success'));
    }
}
