@extends('layouts.email-layout')

@section('title','')

@section('main-content')
    <!-- Logo Section -->
    <div style="text-align: center; margin-bottom: 24px;">
        <img src="https://admin.boxsocials.com/assets/img/box-logo-horizontal.png" 
            alt="Box Socials Logo"
            style="display:block; margin:0 auto; width:300px; max-width:100%; height:auto;">
    </div>

    <hr>

    <div style="text-align: left; margin-bottom: 24px;">{!! $content !!}</div>

@endsection
