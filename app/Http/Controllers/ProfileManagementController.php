<?php

namespace App\Http\Controllers;

use App\Helpers\Helpers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileManagementController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $profileImage = $user->profile_image ?? null;
        if ($profileImage) {
            $profileImage = asset($profileImage);
        } else {
            $profileImage = asset('assets/img/avatars/1.png');
        }
        return view('content.pages.profile-management.edit', compact('user', 'profileImage'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'edit_first_name' => 'required',
            'edit_last_name' => 'required',
            'edit_company_name' => 'required',
            'edit_user_fca_number' => 'required|min:6|max:30',
            'edit_user_image' => 'nullable|image|mimes:jpeg,png,jpg|max:1024',
        ]);

        $user = Auth::user();
        $user->first_name = $request->edit_first_name;
        $user->last_name = $request->edit_last_name;
        $user->company_name = $request->edit_company_name;
        $user->fca_number = $request->edit_user_fca_number;
        if ($request->hasFile('edit_user_image')) {
            $oldImage = $user->profile_image;
            $profileImage = Helpers::uploadImage('profile',$request->file('edit_user_image'), 'images/profile');
            $user->profile_image = $profileImage;

            // delete old image
            if ($oldImage) {
                Helpers::deleteImage($oldImage);
            }
        }
        $user->save();

        return redirect()->back()->with('success', 'Profile updated successfully');
    }
}
