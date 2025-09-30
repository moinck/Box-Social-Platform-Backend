<?php

namespace App\Http\Controllers;

use App\Helpers\Helpers;
use App\Models\EmailContent;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Mailer\Test\Constraint\EmailCount;
use Yajra\DataTables\Facades\DataTables;

class EmailsContentController extends Controller
{
    /** List of email content */
    public function index(Request $request)
    {
        try {

            if ($request->ajax()) {
                $emailContents = EmailContent::get();

                return DataTables::of($emailContents)
                    ->addIndexColumn()
                    ->editColumn('title', function ($emailContents) {
                        return $emailContents->title;
                    })
                    ->editColumn('subject', function ($emailContents) {
                        return $emailContents->subject;
                    })
                    ->editColumn('slug', function ($emailContents) {
                        return $emailContents->slug ? Helpers::emailType()[$emailContents->slug] : '-';
                    })
                    ->addColumn('action', function ($emailContents) {
                        $emailContentId = Helpers::encrypt($emailContents->id);
                        return '
                            <a href="'.route('email-settings.create',['id' => $emailContentId]).'" title="Edit Email" class="btn btn-sm btn-text-secondary rounded-pill btn-icon edit-content-btn"
                                data-bs-toggle="tooltip" data-bs-placement="bottom" data-content-id="' . $emailContentId . '"><i class="ri-edit-box-line"></i></a>
                            <a href="javascript:;" title="Delete Email" class="btn btn-sm btn-text-danger rounded-pill btn-icon delete-content-btn"
                                data-bs-toggle="tooltip" data-bs-placement="bottom" data-content-id="' . $emailContentId . '"><i class="ri-delete-bin-line"></i></a>
                        ';
                    })
                    ->rawColumns(['action'])
                    ->make(true);
            }

            return view('content.pages.email-content.index');

        } catch (Exception $e) {
            Log::error($e);
            return redirect()->back()->with('error','Somehting went wrong.');
        }
    }

    /** Create Email */
    public function createOrEdit(Request $request, $id=null)
    {
        try {

            $emailId = Helpers::decrypt($id);
            $emailType = Helpers::emailType();

            $emailContent = EmailContent::where('id',$emailId)->first();

            return view('content.pages.email-content.edit',compact('emailContent','id','emailType'));

        } catch (Exception $e) {
            Log::error($e);
            return redirect()->back()->with('error','Something went wrong.');
        }
    }

    /** Save Email Content */
    public function saveEmailContent(Request $request)
    {
        DB::beginTransaction();
        try {

            $request->validate([
                'title' => 'required',
                'subject' => 'required',
                'slug' => 'required',
                'content' => 'required'
            ]);

            $id = $request->id;

            EmailContent::updateOrCreate([
                'id' => $id ? Helpers::decrypt($id) : ''
            ],[
                'title' => $request->title,
                'subject' => $request->subject,
                'slug' => $request->slug,
                'content' => $request->content  
            ]);

            DB::commit();

            return redirect()->route('email-settings')->with('success','Email saved successfully.');

        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e);
            return redirect()->back()->with('error','Somehting went wrong.');
        }
    }

    /** Delete Email content */
    public function deleteEmailContent(Request $request)
    {
        try {

            $id = $request->email_id;

            $email = EmailContent::where('id',Helpers::decrypt($id))->first();

            if (!$email) {
                return response()->json(['success' => false, 'message' => 'Data not found.']);
            }

            $email->delete();

            return response()->json(['success' => true, 'message' => 'Email deleted successfully.']);

        } catch (Exception $e) {
            Log::error($e);
            return response()->json(['success' => false, 'message' => 'Something went wrong.']);
        }
    }
}
