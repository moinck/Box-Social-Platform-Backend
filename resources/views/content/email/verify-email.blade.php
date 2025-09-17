@extends('layouts.email-layout')

@section('title','Verify Email')

@section('main-content')
    <!-- Logo Section -->
    <div style="text-align: center; margin-bottom: 24px;">
        <img src="https://admin.boxsocials.com/assets/img/box-logo-horizontal.png" 
            alt="Box Socials Logo"
            style="display:block; margin:0 auto; width:300px; max-width:100%; height:auto;">
    </div>

    <hr>
    <!-- Title -->
    <div class="email-title"
        style="text-align: center; font-size: 26px; color: #222; font-weight: 600; margin-bottom: 10px; line-height: 1.3;">
        Almost There! ✨
    </div>

    <!-- Description -->
    <div class="email-description"
        style="text-align: center; font-size: 16px; color: #555; margin-bottom: 32px; line-height: 1.5;">
        Just one more step…Please confirm your email address by tapping the button below
    </div>

    <!-- CTA Button -->
    <div style="text-align: center; margin-bottom: 20px;">
        <a href="{{ $verification_link }}" class="verify-button"
            style="background-color: #F4D106; 
            color: #000; 
            padding: 14px 32px; 
            border-radius: 30px; 
            text-decoration: none; 
            font-weight: 600; 
            font-size: 16px; 
            display: inline-block; 
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.1); 
            transition: background-color 0.3s ease;
            border: none;
            cursor: pointer;">
            Verify My Email
        </a>
    </div>

    <!-- Alternative Link (for clients that don't support buttons) -->
    {{-- <div style="text-align: center; margin-bottom: 32px;">
        <p style="font-size: 13px; color: #888; margin: 0;">
            Or copy and paste this link in your browser:<br>
            <a href="{{ $verification_link }}" style="color: #F4D106; word-break: break-all; font-size: 12px;">{{ $verification_link }}</a>
        </p>
    </div> --}}

    <!-- Description -->
    {{-- <div class="email-description"
        style="text-align: left; font-size: 16px; color: #555; margin-bottom: 32px; line-height: 1.5;">
        If you find any issue or have any questions, please contact support at <a href="mailto:help@boxsocials.com" style="color: #F4D106;">help@boxsocials.com</a>.
    </div> --}}
@endsection
