<?php

namespace App\Http\Controllers;

use App\Models\ContactUs;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ContactUsController extends Controller
{
    public function index() 
    {
        return view('content.pages.admin.contact-us.index');
    }

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
            ->addColumn('subject', function ($contactUs) {
                return $contactUs->subject;
            })
            ->addColumn('message', function ($contactUs) {
                return $contactUs->message;
            })
            ->addColumn('created_date', function ($contactUs) {
                return $contactUs->created_at->format('d-m-Y h:i A');
            })
            ->make(true);
    }
}
