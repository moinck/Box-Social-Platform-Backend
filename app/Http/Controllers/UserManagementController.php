<?php

namespace App\Http\Controllers;

use App\Exports\UsersExport;
use App\Helpers\Helpers;
use App\Models\User;
use Illuminate\Http\Request;
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
        $users = User::where('role','customer')
        ->when($request->is_brandkit, function ($query) use ($request) {
            if ($request->is_brandkit == 1) {
                $query->whereHas('brandKit');
            } else {
                $query->whereDoesntHave('brandKit');
            }
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
                return Helpers::dateFormate($user->created_at);
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
                            <input type="checkbox" class="switch-input" '.$status.' data-id="'.$userId.'" id="user-account-status">
                            <span class="switch-toggle-slider" data-bs-toggle="tooltip" data-bs-placement="bottom" title="'.$title.'">
                                <span class="switch-on"></span>
                                <span class="switch-off"></span>
                            </span>
                        </label>';
            })
            ->addColumn('action', function ($user) {
                $userId = Helpers::encrypt($user->id);
                return '
                    <a href="javascript:;" class="btn btn-sm btn-text-secondary rounded-pill btn-icon edit-user-btn" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Edit User" data-user-id="'.$userId.'"><i class="ri-edit-box-line"></i></a>
                    <a href="javascript:;" class="btn btn-sm btn-text-danger rounded-pill btn-icon delete-user-btn" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Delete User" data-user-id="'.$userId.'"><i class="ri-delete-bin-line"></i></a>
                ';
            })
            ->rawColumns(['name','company_name','email','fca_number','account_status','created_date','action','is_brandkit'])
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
            'edit_user_email' => 'required|email|max:255|unique:users,email,'.$userId,
            'user_fca_number' => 'required|string|max:255',
            'user_account_status' => 'string|in:active,inactive',
        ]);

        $user = User::find($userId);
        if ($user) {
            $user->first_name = $request->edit_first_name;
            $user->last_name = $request->edit_last_name;
            $user->company_name = $request->edit_company_name;
            $user->email = $request->edit_user_email;
            $user->fca_number = $request->user_fca_number;
            $user->status = $request->user_account_status;

            if ($user->save()) {
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
     * delete User
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request)
    {
        $userId = Helpers::decrypt($request->user_id);
        $user = User::find($userId);
        if ($user) {
            $user->delete();
            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully.'
            ]);
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
        $format = $request->format;
        $name = 'users_'.date('Y-m-d_g-s');
        if ($format == 'csv') {
            return Excel::download(new UsersExport, $name.'.csv');
        } else {
            return Excel::download(new UsersExport, $name.'.xlsx');
        }
    }
}
