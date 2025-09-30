@extends('layouts.email-layout')

@section('title','New Contact Us')

@section('main-content')

    {{-- this email send to admin , to inform that new contact form is submitted --}}
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
        New Contact Us
    </div>

    <!-- user details -->
    <div class="email-description"
        style="text-align: left; font-size: 16px; color: #555; margin-top: 32px; line-height: 1.5;">
        Hello Admin,
    </div>

    <!-- Description -->
    <div class="email-description"
        style="text-align: left; font-size: 16px; color: #555; margin-bottom: 20px; line-height: 1.5;">
        This is to inform you that we received a new contact us request. Please check the details below.
    </div>

    <div class="email-description"
        style="text-align: left; font-size: 16px; color: #555; margin-bottom: 10px; line-height: 1.5;">
        <b>Name:</b> {{ $contactUs->name }}
    </div>

    <div class="email-description"
        style="text-align: left; font-size: 16px; color: #555; margin-bottom: 10px; line-height: 1.5;">
        <b>Email:</b> {{ $contactUs->email }}
    </div>

    <div class="email-description"
        style="text-align: left; font-size: 16px; color: #555; margin-bottom: 10px; line-height: 1.5;">
        <b>Company Name:</b> {{ $contactUs->subject }}
    </div>

    <div class="email-description"
        style="text-align: left; font-size: 16px; color: #555; margin-bottom: 10px; line-height: 1.5;">
        <b>Message:</b> {{ $contactUs->message }}
    </div>
    <hr>

    <!-- CTA Button -->
    {{-- <div style="text-align: center; margin-bottom: 20px;">
        <a href="{{ route('pages-home') }}" class="verify-button"
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
            Box Social
        </a>
    </div>

    <!-- Description -->
    <div class="email-description"
        style="text-align: left; font-size: 16px; color: #555; margin-bottom: 32px; line-height: 1.5;">
        If you find any issue or have any questions, please contact support at <a href="mailto:help@boxsocials.com" style="color: #F4D106;">help@boxsocials.com</a>.
    </div> --}}
@endsection
