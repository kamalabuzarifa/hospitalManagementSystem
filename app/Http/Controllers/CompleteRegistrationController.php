<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompleteRegistrationController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        return view('auth.complete-registration', compact('user'));
    }

    public function submit(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'name' => 'required|string|max:255',
            'password' => 'required|string|min:8|confirmed',
        ]);
        $user->name = $request->name;
        $user->password = bcrypt($request->password);
        $user->completed_registration = true;
        $user->email_verified_at = now();
        $user->save();
        return redirect()->route('dashboard')->with('status', __('messages.registration_completed'));
    }
}
