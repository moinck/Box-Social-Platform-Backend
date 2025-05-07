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
                    $title = 'Click To Inactive';
                } else {
                    $title = 'Click To Active';
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
                return     '<div class="d-inline-block">
                                <a href="javascript:;" class="btn btn-sm btn-text-secondary rounded-pill btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="ri-more-2-line"></i></a>
                                <div class="dropdown-menu dropdown-menu-end m-0">
                                    <a href="javascript:;" class="dropdown-item">Details</a>
                                    <a href="javascript:;" class="dropdown-item">Archive</a>
                                    <div class="dropdown-divider"></div>
                                    <a href="javascript:;" class="dropdown-item text-danger delete-record">Delete</a>
                                </div>
                            </div>
                <a href="javascript:;" class="btn btn-sm btn-text-secondary rounded-pill btn-icon item-edit"><i class="ri-edit-box-line"></i></a>';
            })
            ->rawColumns(['name','company_name','email','fca_number','account_status','created_date','action'])
            ->make(true);
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
