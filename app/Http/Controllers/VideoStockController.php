<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class VideoStockController extends Controller
{
    public function index()
    {
        return view('content.pages.video-stocks.index');
    }
}
