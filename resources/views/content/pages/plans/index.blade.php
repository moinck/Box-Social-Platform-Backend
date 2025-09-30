@extends('layouts/layoutMaster')

@section('title', 'Subscription Plans')

<!-- Vendor Styles -->
@section('vendor-style')
    @vite(['resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-select-bs5/select.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-fixedcolumns-bs5/fixedcolumns.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-fixedheader-bs5/fixedheader.bootstrap5.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

<!-- Vendor Scripts -->
@section('vendor-script')
    @vite(['resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('content')
    <div class="col-12">
        <div class="card">
            <!-- Pricing Plans -->
            <div class="pb-sm-12 pb-2 rounded-top">
                <div class="container py-6">
                    <h4 class="text-center mb-2 mt-0 mt-md-4">Pricing Plans</h4>
                    <p class="text-center mb-2">
                        This are the Basic Plans provided by <span>{{ config('app.name') }}</span>
                    </p>
                    {{-- <div class="d-flex align-items-center justify-content-center flex-wrap gap-2 pt-7 mb-6">
                        <label class="switch switch-sm ms-sm-5 ps-sm-5 me-0">
                            <span class="switch-label fw-medium text-body pe-1 fs-6">Monthly</span>
                            <input type="checkbox" class="switch-input price-duration-toggler" checked />
                            <span class="switch-toggle-slider">
                                <span class="switch-on"></span>
                                <span class="switch-off"></span>
                            </span>
                            <span class="switch-label fw-medium text-body ps-9 fs-6">Annual</span>
                        </label>
                        <div class="mt-n5 ms-n5 ml-2 mb-8 d-none d-sm-flex align-items-center gap-1">
                            <i class="ri-corner-left-down-fill ri-24px text-muted scaleX-n1-rtl"></i>
                            <span class="badge badge-sm bg-label-primary rounded-pill mb-2">Save up to 10%</span>
                        </div>
                    </div> --}}

                    <div class="pricing-plans row mx-4 gy-3 px-lg-12">
                        @foreach ($plans as $plan)
                            <div class="col-lg mb-lg-0 mb-3">
                                <div class="card border shadow-none">
                                    <div class="card-body pt-12">
                                        <div class="mt-3 mb-5 text-center">
                                            @if ($plan->is_popular)
                                                <div class="position-absolute end-0 me-6 top-0 mt-6">
                                                    <span class="badge bg-label-primary rounded-pill">Popular</span>
                                                </div>
                                            @endif
                                            <img src="{{ asset('assets/img/illustrations/pricing-standard.png') }}"
                                                alt="Basic Image" height="100" />
                                        </div>
                                        <h4 class="card-title text-center text-capitalize mb-2">{{ $plan->name }}</h4>
                                        <p class="text-center mb-5">{{ $plan->description }}</p>
                                        <div class="text-center">
                                            <div class="d-flex justify-content-center">
                                                <sup class="h6 pricing-currency mt-2 mb-0 me-1 text-body">$</sup>
                                                <h1 class="mb-0 text-primary">{{ $plan->price }}</h1>
                                                <sub class="h6 text-body pricing-duration mt-auto mb-1 ms-1">/{{ $plan->interval }}</sub>
                                            </div>
                                        </div>

                                        <ul class="list-group ps-6 my-5 pt-4">
                                            <li class="mb-4">Plan Currency: {{ $plan->currency }}</li>
                                            @foreach (json_decode($plan->features) as $feature)
                                                <li class="mb-4">{{ $feature }}</li>
                                            @endforeach
                                        </ul>

                                        {{-- <a href="javascript::void(0)" class="btn btn-outline-success d-grid w-100">Select Plan</a> --}}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <!--/ Pricing Plans -->
        </div>
    </div>
@endsection

@section('page-script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script></script>
@endsection
