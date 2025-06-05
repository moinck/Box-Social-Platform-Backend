<?php

namespace App\Http\Controllers\pages;

use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
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
}
