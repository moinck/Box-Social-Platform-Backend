<?php

namespace App\Http\Controllers;

use App\Helpers\Helpers;
use App\Mail\ContactUsMail;
use App\Mail\DynamicContentMail;
use App\Models\ContactUs;
use App\Models\EmailContent;
use App\Models\FaqCalendar;
use App\Models\User;
use App\Models\YoutubeVideoLink;
use App\ResponseTrait;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
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
        $contactUs = ContactUs::latest();

        return DataTables::of($contactUs)
            ->addIndexColumn()
            ->addColumn('checkbox', function ($contactUs) {
                return '<input type="checkbox" class="form-check-input contact-us-checkbox" name="contact_us_id[]" value="' . $contactUs->id . '">';
            })
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
                return '<span data-order="' . $contactUs->created_date . '">' . Helpers::dateFormate($contactUs->created_date) . '</span>';
            })
            ->rawColumns(['checkbox','created_date'])
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

        return $this->success([], 'Contact Us submitted successfully');
    }

    public function mailPreview()
    {
        $contactUs = ContactUs::latest()->first();
        return view('content.email.contact-us-email', compact('contactUs'));
    }

    public function destroy(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'contact_us_ids' => 'required|array',
            'contact_us_ids.*' => 'exists:contact_us,id',
        ]);

        if ($validator->fails()) {
            return $this->validationError('Validation errors', $validator->errors(), 422);
        }

        $contactUs = ContactUs::whereIn('id', $request->contact_us_ids)->delete();

        return $this->success([], 'Feedback deleted successfully');
    }

    public function sendMail(Request $request)
    {

        $email_content = EmailContent::where('slug','welcome_beta_trial')->first();
            
        if ($email_content) {
            Mail::send([], [], function ($message) use ($request, $email_content) {

                $content = $email_content->content;
                $html = view('content.email.dynamic-email-content',compact('content'))->render();

                $message->to($request->email)
                    ->subject($email_content->subject)
                    ->html($html);

                // custom headers for Brevo
                $message->getHeaders()->addTextHeader('X-Email-ID', Helpers::encrypt('2712'));
            });
        }

    }

    public function brevoWebhook(Request $request)
    {

        $webhookUrl = "https://webhook.site/7d81e40a-4862-4a24-bd52-f2f87a458673";

        $response = Http::post($webhookUrl, $request);

        exit();
    }
    /** List of youtube video */
    public function youtubeVideoLinks(Request $request)
    {
        $videoLinks = YoutubeVideoLink::where('is_active',1)->get();

        $response = $videoLinks->map(function ($videoLink) {
            return [
                'title' => $videoLink->title,
                'link'  => $videoLink->link,
                'image_url' => $videoLink->image_url,
            ];
        })->toArray();

        return $this->success($response, 'Active video links fetched successfully.');
    }

    /** List of calendar image */
    public function calendarImage(Request $request)
    {
        $calendarImages = FaqCalendar::where('year', Carbon::now()->format('Y'))
            ->where('month', '>=', Carbon::now()->format('m'))
            ->get();

        $response = $calendarImages->map(function ($calendarImage) {
            return [
                'year' => $calendarImage->year,
                'link'  => Helpers::getMonth()[$calendarImage->month],
                'image_url' => $calendarImage->image_url,
            ];
        })->toArray();

        return $this->success($response, 'Current year calendar fetched successfully.');

    }
}
