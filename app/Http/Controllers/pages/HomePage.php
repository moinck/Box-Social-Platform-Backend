<?php

namespace App\Http\Controllers\pages;

use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Models\Categories;
use App\Models\PostContent;
use App\Models\PostTemplate;
use App\Models\User;
use App\Notifications\CustomVerifyEmail;
use Illuminate\Http\Request;

class HomePage extends Controller
{
	public function index()
	{
		return view('content.pages.pages-home');
	}

	/**
	 * Function to encrypt a value
	 * @param mixed $value
	 * @return string
	 */
	public function encode($value)
	{
		return Helpers::encrypt($value);
	}

	/**
	 * Function to decrypt a value
	 * @param mixed $value
	 * @return string
	 */
	public function decode($value)
	{
		return Helpers::decrypt($value);
	}

	public function sendEmail()
	{
		$user = User::first();
		$user->sendEmailVerificationNotification();
	}

	public function dashboard()
	{
		$pageData = [];

		$users = User::where('role',"customer")
		->selectRaw('
			COUNT(id) as total,
			SUM(CASE WHEN status = "active" THEN 1 ELSE 0 END) as active,
			SUM(CASE WHEN status = "inactive" THEN 1 ELSE 0 END) as inactive
		')->first();

		$categoriesCount = Categories::where('parent_id', null)->count();
		$subCategoriesCount = Categories::where('parent_id', '!=', null)->count();

		// post content
		$postContent = PostContent::count();

		// post template
		$postTemplate = PostTemplate::count();

		$pageData['totalUser'] = $users->total;
		$pageData['activeUser'] = $users->active;
		$pageData['inactiveUser'] = $users->inactive;
		$pageData['categoriesCount'] = $categoriesCount;
		$pageData['subCategoriesCount'] = $subCategoriesCount;
		$pageData['postContent'] = $postContent;
		$pageData['postTemplate'] = $postTemplate;

		$recentUsers = User::where('role', "customer")->orderBy('id', 'desc')->take(8)->get();
		$pageData['recentUsers'] = $recentUsers;

		return view('content.pages.dashboard', compact('pageData'));
	}
}
