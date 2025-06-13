<?php

namespace App\Http\Controllers;

use App\Helpers\Helpers;
use App\Mail\ContactUsMail;
use App\Models\ContactUs;
use App\Models\User;
use App\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class ContactUsController extends Controller
{
    use ResponseTrait;

    public function index() 
    {
        return view('content.pages.admin.contact-us.index');
    }

    // data table function
    public function contactUsDataTable(Request $request) 
    {
        $contactUs = ContactUs::latest()->get();

        return DataTables::of($contactUs)
            ->addIndexColumn()
            ->addColumn('name', function ($contactUs) {
                return $contactUs->name;
            })
            ->addColumn('email', function ($contactUs) {
                return $contactUs->email;
            })
            ->addColumn('company_name', function ($contactUs) {
                return $contactUs->subject;
            })
            ->addColumn('message', function ($contactUs) {
                return $contactUs->message;
            })
            ->addColumn('created_date', function ($contactUs) {
                return Helpers::dateFormate($contactUs->created_at);
            })
            ->make(true);
    }

    // store contact us data From API
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'required',
            'email' => 'required|email:rfc,dns',
            'company_name' => 'required',
            'message' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->validationError('Validation errors', $validator->errors(), 422);
        }

        $ipAddress = $request->getClientIp();
        $userAgent = $request->header('User-Agent');

        // store data in database
        $contactUs = ContactUs::create([
            'name' => $request->full_name,
            'email' => $request->email,
            'subject' => $request->company_name,
            'message' => $request->message,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
        ]);

        // send notification to admin
        Helpers::sendNotification($contactUs, 'new-contact-us');

        // send information mail to admin
        $adminMail = User::where('role', 'admin')->first()->email;
        Mail::to($adminMail)->send(new ContactUsMail($contactUs));

        return $this->success([],'Contact Us submitted successfully');
    }

    public function mailPreview()
    {
        $contactUs = ContactUs::latest()->first();
        return view('content.email.contact-us-email', compact('contactUs'));
    }
}
