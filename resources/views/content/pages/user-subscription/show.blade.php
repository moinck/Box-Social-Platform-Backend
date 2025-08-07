@extends('layouts/layoutMaster')

@section('title', 'User Subscription')

<!-- Vendor Styles -->
@section('vendor-style')
    @vite(['resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-select-bs5/select.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-fixedcolumns-bs5/fixedcolumns.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-fixedheader-bs5/fixedheader.bootstrap5.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

<!-- Vendor Scripts -->
@section('vendor-script')
    @vite(['resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('content')
    <div class="app-ecommerce">

        {{-- first DIV --}}
        <div class="card b-6 mb-6">
            <div class="user-profile-header d-flex flex-column flex-sm-row text-sm-start text-center m-5">
                <div class="flex-grow-1">
                    <div
                        class="d-flex align-items-md-end align-items-sm-start align-items-center justify-content-md-between justify-content-start mx-5 flex-md-row flex-column gap-6">
                        <div class="user-profile-info">
                            <h4 class="mb-2">{{ $subscriptionData->user->first_name ?? 'User' }}
                                {{ $subscriptionData->user->last_name ?? 'Name' }}</h4>
                            <ul
                                class="list-inline mb-0 d-flex align-items-center flex-wrap justify-content-sm-start justify-content-center gap-4">
                                <li class="list-inline-item">
                                    <i class="ri-mail-check-fill me-2 ri-24px"></i>
                                    <span class="fw-medium">{{ $subscriptionData->user->email }}</span>
                                </li>
                                <li class="list-inline-item">
                                    <i class="ri-calendar-line me-2 ri-24px"></i>
                                    <span class="fw-medium">Joined |
                                        {{ $subscriptionData->user->created_at->format('d F Y') }}</span>
                                </li>
                            </ul>

                        </div>
                        <a href="{{ route('subscription-management') }}" class="btn btn-primary">
                            <i class="ri-arrow-left-line ri-16px me-2"></i>
                            Go back
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Second div --}}
        <div class="row">

            {{-- Subscription Details Card --}}
            <div class="col-12">
                <div class="card mb-6">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Subscription Details</h5>
                        <span
                            class="badge bg-{{ $subscriptionData->status === 'active' ? 'success' : ($subscriptionData->status === 'cancelled' ? 'danger' : 'warning') }}">
                            {{ ucfirst($subscriptionData->status) }}
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 col-sm-6 mb-4">
                                <div class="d-flex align-items-center">
                                    <div class="avatar flex-shrink-0 me-3">
                                        <span class="avatar-initial rounded bg-label-primary">
                                            <i class="ri-money-dollar-circle-line ri-24px"></i>
                                        </span>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">Amount Paid</small>
                                        <div class="d-flex align-items-center">
                                            <h6 class="mb-0 me-1">{{ strtoupper($subscriptionData->currency) }}
                                                {{ number_format($subscriptionData->amount_paid, 2) }}</h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 mb-4">
                                <div class="d-flex align-items-center">
                                    <div class="avatar flex-shrink-0 me-3">
                                        <span class="avatar-initial rounded bg-label-info">
                                            <i class="ri-calendar-check-line ri-24px"></i>
                                        </span>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">Current Period</small>
                                        <div class="d-flex align-items-center">
                                            <h6 class="mb-0 me-1">
                                                {{ \Carbon\Carbon::parse($subscriptionData->current_period_start)->format('d M Y') }}
                                            </h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 mb-4">
                                <div class="d-flex align-items-center">
                                    <div class="avatar flex-shrink-0 me-3">
                                        <span class="avatar-initial rounded bg-label-warning">
                                            <i class="ri-calendar-close-line ri-24px"></i>
                                        </span>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">End Date</small>
                                        <div class="d-flex align-items-center">
                                            <h6 class="mb-0 me-1">
                                                {{ \Carbon\Carbon::parse($subscriptionData->current_period_end)->format('d M Y') }}
                                            </h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 mb-4">
                                <div class="d-flex align-items-center">
                                    <div class="avatar flex-shrink-0 me-3">
                                        <span class="avatar-initial rounded bg-label-success">
                                            <i class="ri-download-line ri-24px"></i>
                                        </span>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">Yearly Downloads</small>
                                        <div class="d-flex align-items-center">
                                            <h6 class="mb-0 me-1">
                                                {{ $userDownloads->total_downloads_used }}/{{ $userDownloads->total_limit }}
                                            </h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Additional subscription info --}}
                        <div class="row mt-3">
                            <div class="col-md-6 mb-3">
                                <div class="form-floating form-floating-outline">
                                    <input type="text" class="form-control"
                                        value="{{ $subscriptionData->stripe_subscription_id ?? "--" }}" readonly />
                                    <label>Stripe Subscription ID</label>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-floating form-floating-outline">
                                    <input type="text" class="form-control"
                                        value="{{ $subscriptionData->stripe_customer_id ?? "--" }}" readonly />
                                    <label>Stripe Customer ID</label>
                                </div>
                            </div>
                        </div>

                        @if ($subscriptionData->cancel_at_period_end)
                            <div class="alert alert-warning d-flex align-items-center" role="alert">
                                <i class="ri-alert-line ri-22px me-2"></i>
                                <div>
                                    This subscription will be cancelled at the end of the current period
                                    ({{ \Carbon\Carbon::parse($subscriptionData->current_period_end)->format('d M Y') }}).
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Plan Information --}}
            <div class="col-12">
                <div class="card mb-6">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Plan Details</h5>
                    </div>
                    <div class="card-body">
                        {{-- Additional subscription info --}}
                        <div class="row mt-3">
                            <div class="col-md-4 mb-3">
                                <div class="form-floating form-floating-outline">
                                    <input type="text" class="form-control"
                                        value="{{ $subscriptionData->plan->name ?? 'Free-Plan' }}" readonly />
                                    <label>Plan Name</label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="form-floating form-floating-outline">
                                    <input type="text" class="form-control"
                                        value="{{ $subscriptionData->plan->price ?? '0' }}" readonly />
                                    <label>Price</label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="form-floating form-floating-outline">
                                    <input type="text" class="form-control"
                                        value="{{ $subscriptionData->plan->currency ?? 'GBP' }}" readonly />
                                    <label>Currency</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <ul class="list-group ps-6 my-5 pt-4">
                                    @foreach (json_decode($subscriptionData->plan->features) as $feature)
                                        <li class="mb-4">{{ $feature }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page-script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script>
        $(document).ready(function() {

        });
    </script>
@endsection
