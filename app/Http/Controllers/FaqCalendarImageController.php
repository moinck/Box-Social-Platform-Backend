<?php

namespace App\Http\Controllers;

use App\Helpers\Helpers;
use App\Models\FaqCalendar;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class FaqCalendarImageController extends Controller
{
    /** List of FAQ Calendar List */
    public function index(Request $request)
    {
        try {

            if ($request->ajax()) {

                $faqCalendar = FaqCalendar::get();

                return DataTables::of($faqCalendar)
                    ->addIndexColumn()
                    ->addColumn('year', function ($row) {
                        return $row->year;
                    })
                    ->addColumn('month', function ($row) {
                        return Helpers::getMonth()[$row->month] ?? '-';
                    })
                    ->addColumn('image_url', function ($row) {
                        return '<a href="' . $row->image_url . '" target="_blank">
                            <img src="' . $row->image_url . '" alt="Image" width="100">
                        </a>';
                    })
                    ->addColumn('action', function ($row) {
                        $monthId = Helpers::encrypt($row->id);
                        return '
                            <a href="javascript:;" title="edit month records" class="btn btn-sm btn-text-secondary rounded-pill btn-icon edit-month-btn"
                                data-bs-toggle="tooltip" data-bs-placement="bottom" data-month-id="' . $monthId . '"><i class="ri-edit-box-line"></i></a>
                            <a href="javascript:;" title="delete month records" class="btn btn-sm btn-text-danger rounded-pill btn-icon delete-month-btn"
                                data-bs-toggle="tooltip" data-bs-placement="bottom" data-month-id="' . $monthId . '"><i class="ri-delete-bin-line"></i></a>
                        ';
                    })
                    ->rawColumns(['image_url', 'action'])
                    ->make(true);

            }

            $months = Helpers::getMonth();

            return view("content.pages.faq-calendar.index",compact("months"));

        } catch (Exception $e) {
            Log::error($e);
            return redirect()->back()->with('error','Something went wrong.');
        }
    }

    /** Save FAQ Calendar Records */
    public function saveFaqCalendar(Request $request)
    {
        DB::beginTransaction();
        try {

            $validator = Validator::make($request->all(), [
                'year'  => 'required|digits:4',
                'month' => 'required|integer|min:1|max:12',
                'image' => 'required_if:is_image_exists,0|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors(),
                ], 422);
            }

            $dataExists = FaqCalendar::where('year',$request->year)->where('month',$request->month);

            $calenderData = "";
            $id = "";
            if ($request->faq_calendar_id) {
                $id = Helpers::decrypt($request->faq_calendar_id);
                $calenderData = FaqCalendar::find($id);

                $dataExists = $dataExists->where('id','!=',$id);
            }

            $dataExists = $dataExists->exists();

            if ($dataExists) {
                return response()->json([
                    'success' => false,
                    'message' => 'This month/year aleady exists.'
                ]);
            }

            $imageUrl = $calenderData ? $calenderData->image_url : null;
            if (isset($request->image) && $request->file('image') ) {
                $prefix = 'calendar_'.$request->month.'_'.$request->year.'_';
                $imageUrl = Helpers::uploadImage($prefix,$request->file('image'),'images/calendar');

                if($calenderData) {
                    if($calenderData->image_url) {
                        Helpers::deleteImage($calenderData->image_url);
                    }
                }
            }

            FaqCalendar::updateorCreate([
                'id' => $id
            ], [
                'year' => $request->year,
                'month' => $request->month,
                'image_url' => $imageUrl,
            ]);
            
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Calendar record saved successfully.'
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e);
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.'
            ]);
        }
    }

    /** Edit FAQ Calendar Record */
    public function editFaqCalendar(Request $request,$id)
    {
        try {
            $calendarRecId = Helpers::decrypt($id);
            $calendarRec = FaqCalendar::find($calendarRecId);

            if ($calendarRec) {
                return response()->json([
                    'success' => true,
                    'message' => 'Record fetched successfully.',
                    'data' => $calendarRec
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Calendar record not found.'
            ], 404);

        } catch (Exception $e) {
            Log::error($e);
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.'
            ]);
        }
    }

    /** Delete FAQ Calender Record */
    public function deleteFaqCalendar(Request $request)
    {
        DB::beginTransaction();
        try {
            $calendarRecId = Helpers::decrypt($request->faq_calendar_id);
            $calendarRec = FaqCalendar::find($calendarRecId);

            if ($calendarRec) {

                if($calendarRec->image_url) {
                    Helpers::deleteImage($calendarRec->image_url);
                }
                $calendarRec->delete();

                DB::commit();
                return response()->json([
                    'success' => true,
                    'message' => 'Record deleted successfully.'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Record not found.'
                ]);
            }

        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e);
            return response()->json([
                'success' => false,
                'message' => 'Somehting went wrong.'
            ]);
        }
    }
}
