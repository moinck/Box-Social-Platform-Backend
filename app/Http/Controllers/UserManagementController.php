<?php

namespace App\Http\Controllers;

use App\Exports\UsersExport;
use App\Helpers\Helpers;
use App\Models\EmailContent;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class UserManagementController extends Controller
{
    public function index()
    {
        return view('content.pages.pages-home');
    }

    /**
     * User Management DataTable
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function userDataTable(Request $request)
    {
        $users = User::where('role', 'customer')
            ->when($request->filled('is_brandkit'), function ($query) use ($request) {
                if ($request->is_brandkit == 1) {
                    $query->whereHas('brandKit');
                } else if($request->is_brandkit == 2) {
                    $query->whereDoesntHave('brandKit');
                }
            })
            ->when($request->filled('account_status'), function ($query) use ($request) {
                if ($request->account_status == 1) {
                    $query->where('status', '=', 'active');
                } elseif ($request->account_status == 2) {
                    $query->where('status', '=', 'inactive');
                }
            })
            ->when($request->filled('is_admin_verified'), function ($query) use ($request) {
                $query->where('is_admin_verified','=', $request->is_admin_verified);
            })
            ->latest()->get();

        return DataTables::of($users)
            ->addIndexColumn()
            ->addColumn('name', function ($user) {
                $name = $user->first_name . ' ' . $user->last_name;
                return $name;
            })
            ->addColumn('is_brandkit', function ($user) {
                return $user->hasBrandKit() ? 1 : 0;
            })
            ->addColumn('company_name', function ($user) {
                return $user->company_name ?? '-';
            })
            ->addColumn('email', function ($user) {
                return $user->email ?? '-';
            })
            ->addColumn('fca_number', function ($user) {
                return $user->fca_number ?? '-';
            })
            ->addColumn('created_date', function ($user) {
                return '<span data-order="' . $user->created_at . '">' . Helpers::dateFormate($user->created_at) . '</span>';
            })
            ->addColumn('account_status', function ($user) {
                $status = $user->status == 'active' ? 'checked' : '';
                $title = '';
                if ($user->status == 'active') {
                    $title = 'Click To Disable Account';
                } else {
                    $title = 'Click To Enable Account';
                }
                $userId = Helpers::encrypt($user->id);

                return '<label class="switch" >
                            <input type="checkbox" class="switch-input" ' . $status . ' data-id="' . $userId . '" id="user-account-status">
                            <span class="switch-toggle-slider" data-bs-toggle="tooltip" data-bs-placement="bottom" title="' . $title . '">
                                <span class="switch-on"></span>
                                <span class="switch-off"></span>
                            </span>
                        </label>';
            })
            ->addColumn('authorisation_type', function ($user) {
                return $user->authorisation_type == 1 ? "Directly Authorised" : ($user->authorisation_type == 2 ? "Appointed Representative" : '-');
            })
            ->addColumn('appointed_network', function ($user) {
                return $user->appointed_network ? $user->appointed_network : "-";
            })
            ->addColumn('company_type', function ($user) {
                return $user->company_type == 1 ? "Sole Trader" : ($user->company_type == 2 ? "Limited Company" : '-');
            })
            ->addColumn('action', function ($user) {
                $userId = Helpers::encrypt($user->id);
                $name = $user->first_name . ' ' . $user->last_name;

                $button = "";
                if ($user->is_admin_verified == false) {
                    $button = '<a href="javascript:;" class="btn btn-sm btn-text-secondary rounded-pill btn-icon admin-verify-btn" data-bs-toggle="tooltip" title="Need to Verify" data-user-name="' . $name . '" data-user-id="' . $userId . '"><i class="ri-verified-badge-fill"></i></a>';
                }

                return $button.'
                    <a href="javascript:;" class="btn btn-sm btn-text-secondary rounded-pill btn-icon edit-user-btn" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Edit User" data-user-id="' . $userId . '"><i class="ri-edit-box-line"></i></a>
                    <a href="javascript:;" class="btn btn-sm btn-text-danger rounded-pill btn-icon delete-user-btn" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Delete User" data-user-name="' . $name . '" data-user-id="' . $userId . '"><i class="ri-delete-bin-line"></i></a>
                ';
            })
            ->rawColumns(['name', 'company_name', 'email', 'fca_number', 'account_status', 'created_date', 'action', 'is_brandkit'])
            ->make(true);
    }

    /**
     * Get User Edit Data
     * @param mixed $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function edit($id)
    {
        $id = Helpers::decrypt($id);
        $user = User::find($id);
        $user->has_brandkit = $user->hasBrandKit() ? 1 : 0;

        if ($user) {
            return response()->json([
                'success' => true,
                'data' => $user
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'User not found.'
            ]);
        }
    }

    /**
     * Update User Data
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        $userId = Helpers::decrypt($request->edit_user_id);

        $request->validate([
            'edit_first_name' => 'required|string|max:255',
            'edit_last_name' => 'required|string|max:255',
            'edit_company_name' => 'required|string|max:255',
            'edit_user_email' => 'required|email|max:255|unique:users,email,' . $userId,
            'user_fca_number' => 'required|string|max:255',
            'user_account_status' => 'string|in:active,inactive',
            'company_type' => 'required',
            'appointed_network' => 'required_if:appointed_representative,1|required_if:appointed_representative,2',
            'appointed_representative' => 'required_without:direct_authorised',
            'direct_authorised' => 'required_without:appointed_representative',
        ]);

        $user = User::find($userId);
        if ($user) {

            $old_email = $user->email;
            $authorisation_type = isset($request->direct_authorised) ? $request->direct_authorised : (isset($request->appointed_representative) ? $request->appointed_representative : 0);

            $user->first_name = $request->edit_first_name;
            $user->last_name = $request->edit_last_name;
            $user->company_name = $request->edit_company_name;
            $user->email = $request->edit_user_email;
            $user->fca_number = $request->user_fca_number;
            $user->status = $request->user_account_status;
            $user->authorisation_type = $authorisation_type;
            $user->appointed_network = isset($request->appointed_network) ? $request->appointed_network : null;
            $user->company_type = $request->company_type;

            if ($user->save()) {

                if ($user->email != $old_email) {
                    $email_setting = EmailContent::where('slug','user_email_update')->first();
                    if ($email_setting) {
        
                        $format_content = $email_setting->content;
                        $format_content = str_replace('|user_name|', "<b>".$user->first_name."</b>", $format_content);
                        $format_content = str_replace('|new_email|', "<b>".$user->email."</b>", $format_content);
                        $format_content = str_replace('|old_email|', "<b>".$old_email."</b>", $format_content);
        
                        $data = [
                            'email' => $user->email,
                            'subject' => $email_setting->subject,
                            'content' => $format_content
                        ];
                        Helpers::sendDynamicContentEmail($data);
                    }
                }

                return response()->json([
                    'success' => true,
                    'message' => 'User updated successfully.'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update user.'
                ]);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'User not found.'
            ]);
        }
    }

    /**
     * change User account status
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function accountStatus(Request $request)
    {
        $userId = Helpers::decrypt($request->userId);
        $user = User::find($userId);
        if ($user) {
            $user->status = $user->status == 'active' ? 'inactive' : 'active';
            $user->save();
            return response()->json([
                'success' => true,
                'message' => 'Account status updated successfully.'
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'User not found.'
            ]);
        }
    }

    /**
     * export users data
     * @param \Illuminate\Http\Request $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function export(Request $request)
    {
        $request->validate([
            'format' => 'required|in:csv,xlsx',
            'is_brandkit' => 'nullable|in:1,2',
            'account_status' => 'nullable|in:1,2',
            'user_table_search' => 'nullable|string',
        ]);

        $users = User::query()
            ->when($request->is_brandkit, function ($query) use ($request) {
                if ($request->is_brandkit == 1) {
                    $query->whereHas('brandKit');
                } else if($request->is_brandkit == 2) {
                    $query->whereDoesntHave('brandKit');
                }
            })
            ->when($request->account_status && $request->account_status != null, function ($query) use ($request) {
                if ($request->account_status == 1) {
                    $query->where('status', '=', 'active');
                } elseif ($request->account_status == 2) {
                    $query->where('status', '=', 'inactive');
                }
            })
            ->when($request->user_table_search && $request->user_table_search != null, function ($query) use ($request) {
                $query->where(function ($query) use ($request) {
                    $query->where('first_name', 'like', '%' . $request->user_table_search . '%')
                        ->orWhere('last_name', 'like', '%' . $request->user_table_search . '%')
                        ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ['%' . $request->user_table_search . '%'])
                        ->orWhere('company_name', 'like', '%' . $request->user_table_search . '%')
                        ->orWhere('email', 'like', '%' . $request->user_table_search . '%')
                        ->orWhere('created_at', 'like', '%' . $request->user_table_search . '%')
                        ->orWhere('fca_number', 'like', '%' . $request->user_table_search . '%');
                });
            })
            ->where('role', 'customer')
            ->latest()
            ->get();

        $format = $request->format;
        $name = 'users_' . date('Y-m-d_g-s');
        if ($format == 'csv') {
            return Excel::download(new UsersExport($users), $name . '.csv');
        } else {
            return Excel::download(new UsersExport($users), $name . '.xlsx');
        }
    }

    /**
     * delete User
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request)
    {
        $userId = Helpers::decrypt($request->user_id);
        $user = User::find($userId);
        if ($user) {
            $deleteUser = Helpers::deleteUserData($userId);
            if ($deleteUser === true) {
                return response()->json([
                    'success' => true,
                    'message' => 'User deleted successfully.'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'User not deleted.'
                ]);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'User not found.'
            ]);
        }
    }

    /** User Account Verify By Admin */
    public function userAccountVerify(Request $request)
    {
        DB::beginTransaction();
        try {

            $userId = $request->user_id;

            $user = User::find(Helpers::decrypt($userId));
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data not found.'
                ]);
            }

            $user->is_admin_verified = true;
            $user->save();

            if ($user->is_admin_verified == true) {
                $email_setting = EmailContent::where('slug','user_register_acount_reviewed')->first();
                if ($email_setting) {
                    $websiteLink = "<b><a href='https://www.boxsocials.com'>www.boxsocials.com</a></b>";
                    $youtubeLink = "<b><a href='https://www.youtube.com/@BoxSocialsUK'><i>@‌BoxSocialsUK</i></a></b>";
                    $plateFormLink = "<b><a href='https://boxsocials.com/faqs'><i>Box Socials platform</i></a></b>";
    
                    $format_content = $email_setting->content;
                    $format_content = str_replace('|first_name|', "<b>".$user->first_name."</b>", $format_content);
                    $format_content = str_replace('|website_link|', $websiteLink, $format_content);
                    $format_content = str_replace('|youtube_link|', $youtubeLink, $format_content);
                    $format_content = str_replace('|platform_link|', $plateFormLink, $format_content);
    
                    $data = [
                        'email' => $user->email,
                        'subject' => $email_setting->subject,
                        'content' => $format_content
                    ];
                    Helpers::sendDynamicContentEmail($data);
                }
            }
            
            $token = Helpers::generateVarificationToken($user, $request, 'email-verification');
            Helpers::sendVerificationMail($user, $token);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Verified successfully.'
            ]);

        } catch (Exception $e){
            DB::rollBack();
            Log::error($e);
            return response()->json([
                'success' => false,
                'message' => 'Somthing went wrong.'
            ]);
        }
    }
}
