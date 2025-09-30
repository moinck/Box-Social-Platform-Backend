<?php

namespace App\Http\Controllers\pages;

use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Models\BrandKit;
use App\Models\Categories;
use App\Models\ContactUs;
use App\Models\IconManagement;
use App\Models\ImageStockManagement;
use App\Models\PostContent;
use App\Models\PostTemplate;
use App\Models\SubscriptionPlans;
use App\Models\User;
use App\Notifications\CustomVerifyEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

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

		// brand-kit count
		$brandConfigurationCount = BrandKit::whereHas('user', function ($query) {
			$query->where('role', 'customer');
		})->count();

		// feedback count
		$feedbackCount = ContactUs::count();

		// image count
		$imageCount = ImageStockManagement::where(function ($query) {
			$query->whereNotNull('user_id')
				->where('user_id', "=",1);
		})->count();

		// icons count
		$iconsCount = IconManagement::whereNotNull('tag_name')->count();

		$pageData['totalUser'] = $users->total;
		$pageData['activeUser'] = $users->active;
		$pageData['inactiveUser'] = $users->inactive;
		$pageData['categoriesCount'] = $categoriesCount;
		$pageData['subCategoriesCount'] = $subCategoriesCount;
		$pageData['postContentCount'] = $postContent;
		$pageData['postTemplateCount'] = $postTemplate;
		$pageData['brandConfigurationCount'] = $brandConfigurationCount;
		$pageData['feedbackCount'] = $feedbackCount;
		$pageData['imageCount'] = $imageCount;
		$pageData['iconsCount'] = $iconsCount;

		$recentUsers = User::where('role', "customer")->latest()->take(7)->get();
		$pageData['recentUsers'] = $recentUsers;

		return view('content.pages.dashboard', compact('pageData'));
	}
}
