<?php

namespace App\Http\Controllers;

use App\Models\CookiePolicy;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CookiePolicyController extends Controller
{
    /** Cookie Policy */
    public function cookiePolicy()
    {
        
        $cookiePolicy = CookiePolicy::latest()->first();

        return view('content.pages.cookie-policy.edit', compact('cookiePolicy'));

    }

    /** Save Cookie Policy */
    public function saveCookiePolicy(Request $request)
    {
        try {

            $request->validate([
                'title' => 'required',
                'cookie_policy_description' => 'required',
            ]);

            CookiePolicy::updateorCreate([
                'id' => $request->cookie_policy_id
            ],[
                'title' => $request->title,
                'description' => $request->cookie_policy_description
            ]);

            $message = "Cookie policy created successfully.";
            if ($request->cookie_policy_id) {
                $message = "Cookie policy updated successfully.";
            }

            return redirect()->route('cookie-policy')->with('success', $message);

        } catch (Exception $e) {
            Log::error($e);
            return redirect()->back()->with('error','Something went wrong.');
        }
    }
}
