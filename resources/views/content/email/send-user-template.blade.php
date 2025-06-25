@extends('layouts.email-layout')

@section('title','New Template Created')

@section('main-content')
    <!-- Logo Section -->
    <div style="text-align: center; margin-bottom: 24px;">
        <img src="http://178.128.45.173:9162/assets/img/box-logo-horizontal.png" alt="Box Social Logo" class="logo-img"
            style="width: 200px; height: auto; display: block; margin: 0 auto;">
    </div>

    <hr>
    <!-- Title -->
    <div class="email-title"
        style="text-align: center; font-size: 26px; color: #222; font-weight: 600; margin-bottom: 10px; line-height: 1.3;">
        New Template Created
    </div>

    <!-- Greeting -->
    <div class="email-description"
        style="text-align: left; font-size: 16px; color: #555; margin-top: 32px; line-height: 1.5;">
        Hello {{ $data['user']->first_name ?? "User" }} {{ $data['user']->last_name ?? "" }},
    </div>

    <!-- Description -->
    <div class="email-description"
        style="text-align: left; font-size: 16px; color: #555; margin-bottom: 20px; line-height: 1.5;">
        Thank you for creating a new template with us. Below are the details of your template:
    </div>

    <!-- Template Details -->
    <div class="email-description"
        style="text-align: left; font-size: 16px; color: #555; margin-bottom: 10px; line-height: 1.5;">
        <b>Template Name:</b> {{ $data['template']->template_name ?? "Template" }}
    </div>

    <div class="email-description"
        style="text-align: left; font-size: 16px; color: #555; margin-bottom: 10px; line-height: 1.5;">
        <b>Created At:</b> {{ $data['template']->created_at->format('F j, Y \a\t g:i a') ?? now()->format('F j, Y \a\t g:i a') }}
    </div>

    <div class="email-description"
        style="text-align: left; font-size: 16px; color: #555; margin-bottom: 20px; line-height: 1.5;">
        We've attached a preview image of your template to this email for your reference and You can access and manage your templates anytime in your account dashboard.
    </div>

    <!-- CTA Button -->
    <div style="text-align: center; margin: 30px 0;">
        <a href="http://178.128.45.173:9163/dashboard" target="_blank" class="dashboard-button"
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
            Go to Dashboard
        </a>
    </div>
    <hr>
@endsection