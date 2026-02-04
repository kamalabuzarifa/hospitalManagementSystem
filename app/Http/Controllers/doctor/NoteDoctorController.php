<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Note;
use Illuminate\Support\Facades\Auth;

class NoteDoctorController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'note' => 'required|string',
        ]);

        $note = Note::create([
            'doctor_id' => Auth::user()->doctor->id,
            'patient_id' => $request->patient_id,
            'note' => $request->note,
        ]);

        return redirect()->route('doctor.dashboard')
            ->with('success', __('messages.note_sent_successfully'));
    }
}
