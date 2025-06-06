@extends('layouts.email-layout')

@section('main-content')    
    <div class="container">
        <div class="logo">
            <img src="{{ asset('assets/img/Box-media-logo.svg') }}" alt="Logo">
        </div>
        <div class="title">Almost There! ✨</div>
        <div class="subtitle">
            Just one more step — tap the button below to confirm your email and unlock your full experience.
        </div>
        <div class="cta">
            <a href="{{ $verification_link }}">Verify My Email</a>
        </div>
        <div class="footer">
            Didn't request this email?<br>You can safely ignore it, and no changes will be made.<br><br>
            — The Box Social Team
        </div>
    </div>
@endsection
