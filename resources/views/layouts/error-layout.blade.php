@php
    $configData = Helper::appClasses();
@endphp
<!doctype html>

<html
  lang="{{ session()->get('locale') ?? app()->getLocale() }}"
  class="light-style layout-wide"
  dir="ltr"
  data-theme="theme-default"
  data-assets-path="../../assets/"
  data-template="vertical-menu-template"
  data-style="light">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
  
    <title>@yield('title') |
      {{ config('app.name') ? config('app.name') : 'Box-Social' }}</title>
    <meta name="description" content="{{ config('variables.templateDescription') ? config('variables.templateDescription') : '' }}" />
    <meta name="keywords" content="{{ config('variables.templateKeyword') ? config('variables.templateKeyword') : '' }}">
    <!-- laravel CRUD token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Canonical SEO -->
    <link rel="canonical" href="{{ config('variables.productPage') ? config('variables.productPage') : '' }}">
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/Box-media-logo.svg') }}" />
  
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&ampdisplay=swap"
      rel="stylesheet" />
  

    @vite([
        'resources/assets/vendor/fonts/remixicon/remixicon.scss',
        'resources/assets/vendor/fonts/flag-icons.scss',
        'resources/assets/vendor/libs/node-waves/node-waves.scss',
        'resources/assets/vendor/scss/core.scss',
        'resources/assets/vendor/scss/pages/page-misc.scss',
        'resources/assets/css/demo.css',
        'resources/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.scss',
        'resources/assets/vendor/libs/typeahead-js/typeahead.scss'
    ])
    <!-- Page CSS -->

    @yield('page-style')

    <!-- Helpers -->
    
    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
    
    @vite([
        'resources/assets/vendor/js/helpers.js',
        'resources/assets/vendor/js/template-customizer.js',
        'resources/assets/js/config.js',
    ])
  </head>

  <body>
    <!-- Content -->

    {{-- <h4 class="p-6">Blank Page</h4> --}}
    @yield('content')

    <!-- / Content -->

    <!-- Core JS -->
    @vite([
        'resources/assets/vendor/libs/jquery/jquery.js',
        'resources/assets/vendor/libs/popper/popper.js',
        'resources/assets/vendor/js/bootstrap.js',
        'resources/assets/vendor/libs/node-waves/node-waves.js',
        'resources/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js',
        'resources/assets/vendor/libs/hammer/hammer.js',
        'resources/assets/vendor/libs/typeahead-js/typeahead.js',
        'resources/assets/vendor/js/menu.js'
    ])

    <!-- endbuild -->

    <!-- Vendors JS -->

    <!-- Main JS -->
    @vite([
        'resources/assets/js/main.js'
    ])

    <!-- Page JS -->
  </body>
</html>
