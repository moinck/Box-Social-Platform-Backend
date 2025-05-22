<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileManagementController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        return view('content.pages.profile-management.edit', compact('user'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'edit_first_name' => 'required',
            'edit_last_name' => 'required',
            'edit_company_name' => 'required',
            'edit_user_fca_number' => 'required|min:6|max:30',
        ]);

        $user = Auth::user();
        $user->first_name = $request->edit_first_name;
        $user->last_name = $request->edit_last_name;
        $user->company_name = $request->edit_company_name;
        $user->fca_number = $request->edit_user_fca_number;
        $user->save();

        return redirect()->back()->with('success', 'Profile updated successfully');
    }
}
