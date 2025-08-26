@extends('layouts.email-layout')

@section('title','Reset Password')

@section('main-content')
    <!-- Logo Section -->
    <div style="text-align: center; margin-bottom: 24px;">
        <img src="https://admin.boxsocials.com/assets/img/box-logo-horizontal.png" alt="Box Social Logo" class="logo-img"
            style="width: 100; height: 100px; display: block; margin: 0 auto;">
    </div>
    <hr>

    <!-- Title -->
    <div class="email-title"
        style="text-align: center; font-size: 26px; color: #222; font-weight: 600; margin-bottom: 10px; line-height: 1.3;">
        Password Reset
    </div>


    <!-- user details -->
    <div class="email-description"
        style="text-align: left; font-size: 16px; color: #555; margin-top: 32px; line-height: 1.5;">
        Hello {{ $user->first_name ?? "User" }} {{ $user->last_name ?? "" }},
    </div>

    <!-- Description -->
    <div class="email-description"
        style="text-align: left; font-size: 16px; color: #555; margin-bottom: 20px; line-height: 1.5;">
        You are receiving this email because we received a request to reset your password. Please use the button below to reset your password.
    </div>

    <!-- CTA Button -->
    <div style="text-align: center; margin-bottom: 20px;">
        <a href="{{ $reset_password_link }}" class="verify-button"
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
            Reset Password
        </a>
    </div>

    <!-- Description -->
    {{-- <div class="email-description"
        style="text-align: left; font-size: 16px; color: #555; margin-bottom: 32px; line-height: 1.5;">
        If you find any issue or have any questions, please contact support at <a href="mailto:help@boxsocials.com" style="color: #F4D106;">help@boxsocials.com</a>.
    </div> --}}
@endsection
