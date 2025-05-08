<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class UserManagementController extends Controller
{
    public function userDataTable(Request $request)
    {
        $users = User::where('role','customer')->latest()->get();

        return DataTables::of($users)
            ->addIndexColumn()
            ->addColumn('name', function ($user) {
                $name = $user->first_name . ' ' . $user->last_name;
                return $name;
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
                return $user->created_at->format('d-m-Y H:i A');
            })
            ->addColumn('account_status', function ($user) {
                $status = $user->status == 'active' ? 'checked' : '';
                $title = '';
                if ($user->status == 'active') {
                    $title = 'Click To Disable Account';
                } else {
                    $title = 'Click To Enable Account';
                }

                return '<label class="switch" title="'.$title.'">
                            <input type="checkbox" class="switch-input" '.$status.' data-id="'.$user->id.'" id="user-account-status">
                            <span class="switch-toggle-slider">
                                <span class="switch-on"></span>
                                <span class="switch-off"></span>
                            </span>
                        </label>';
            })
            ->addColumn('action', function ($user) {
                return '
                    <a href="javascript:;" class="btn btn-sm btn-text-secondary rounded-pill btn-icon edit-user-btn" data-user-id="'.$user->id.'"><i class="ri-edit-box-line"></i></a>
                    <a href="javascript:;" class="btn btn-sm btn-text-danger rounded-pill btn-icon delete-user-btn" data-user-id="'.$user->id.'"><i class="ri-delete-bin-line"></i></a>
                ';
            })
            ->rawColumns(['name','company_name','email','fca_number','account_status','created_date','action'])
            ->make(true);
    }

    public function edit($id)
    {
        $user = User::find($id);
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

    public function update(Request $request)
    {

        $userId = $request->edit_user_id;

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

    public function destroy(Request $request)
    {
        $user = User::find($request->user_id);
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

    public function accountStatus(Request $request)
    {
        $user = User::find($request->userId);
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
}
