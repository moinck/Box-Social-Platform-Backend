@extends('layouts.email-layout')

@section('title','')

@section('main-content')
    <!-- Logo Section -->
    <div style="text-align: center; margin-bottom: 24px;">
        <img src="https://admin.boxsocials.com/assets/img/box-logo-horizontal.png" alt="Box Socials Logo" class="logo-img"
            style="width: 100; height: 100px; display: block; margin: 0 auto;">
    </div>

    <hr>

    <div style="text-align: left; margin-bottom: 24px;">{!! $content !!}</div>

@endsection
