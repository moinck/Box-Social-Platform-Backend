@extends('layouts/layoutMaster')

@section('title', 'Brand Configuration')

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
                <div class="flex-shrink-0 m-3" style="margin-top:1.25rem;margin-bottom:1.25rem;">
                    <img src="{{ $brandKit->logo ? asset($brandKit->logo) : asset('assets/img/image_not_available.jpg') }}" alt="user image" id="account-file-input" height="200" width="200"
                        class="d-block h-auto ms-0 rounded-4 shadow" />
                </div>
                <div class="flex-grow-1 mt-4 mt-sm-12">
                    <div
                        class="d-flex align-items-md-end align-items-sm-start align-items-center justify-content-md-between justify-content-start mx-5 flex-md-row flex-column gap-6">
                        <div class="user-profile-info">
                            <h4 class="mb-2">{{ $brandKit->user->first_name ?? 'User' }} {{ $brandKit->user->last_name ?? 'Name' }}</h4>
                            <ul
                                class="list-inline mb-0 d-flex align-items-center flex-wrap justify-content-sm-start justify-content-center gap-4">
                                <li class="list-inline-item">
                                    <i class="ri-user-line me-2 ri-24px"></i>
                                    <span class="fw-medium">{{ $brandKit->user->role }}</span>
                                </li>
                                <li class="list-inline-item">
                                    <i class="ri-calendar-line me-2 ri-24px"></i>
                                    <span class="fw-medium">Joined | {{ $brandKit->created_at->format('d F Y') }}</span>
                                </li>
                            </ul>

                        </div>
                        <a href="{{ route('brand-configuration') }}" class="btn btn-primary">
                            <i class="ri-arrow-left-line ri-16px me-2"></i>
                            Go back
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Second div --}}
        <div class="row">

            {{-- brand information --}}
            <div class="col-12 col-lg-8">
                <div class="card mb-6">
                    <div class="card-header">
                        <h5 class="card-tile mb-0">Brand information</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-floating form-floating-outline mb-5">
                            <input type="text" class="form-control" id="brand_company_name" placeholder="Company Name"
                                name="brand_company_name" value="{{ $brandKit->user->company_name }}"
                                aria-label="Company Name" readonly />
                            <label for="brand_company_name">Company Name</label>
                        </div>

                        <div class="row mb-5 gx-5">
                            <div class="col">
                                <div class="form-floating form-floating-outline">
                                    <input type="email" class="form-control" id="brand_email" placeholder="Email"
                                        name="brand_email" value="{{ $brandKit->user->email }}" aria-label="Email" readonly />
                                    <label for="brand_email">Email</label>
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-floating form-floating-outline">
                                    <input type="number" class="form-control" id="brand_phone" placeholder="Phone"
                                        name="brand_phone" value="{{ $brandKit->phone }}" aria-label="Phone" readonly />
                                    <label for="brand_phone">Phone</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-floating form-floating-outline mb-5">
                            <textarea class="form-control h-px-75" id="brand_address" rows="3" placeholder="Address"
                                name="brand_address" aria-label="Address" readonly>{{ $brandKit->address }}</textarea>
                            <label for="brand_address">Address</label>
                        </div>
                        <div class="row mb-5 gx-5">
                            <div class="col">
                                <div class="form-floating form-floating-outline">
                                    <input type="text" class="form-control" id="brand_state" placeholder="County"
                                        name="brand_state" value="{{ $brandKit->state }}" aria-label="Product SKU" readonly />
                                    <label for="brand_state">County</label>
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-floating form-floating-outline">
                                    <input type="text" class="form-control" id="brand_country" placeholder="Country"
                                        name="brand_country" value="{{ $brandKit->country }}" readonly
                                        aria-label="Product barcode" />
                                    <label for="brand_country">Country</label>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-5 gx-5">
                            <div class="col">
                                <div class="form-floating form-floating-outline">
                                    <input type="text" class="form-control" id="brand_country" placeholder="Postalcode"
                                        name="brand_country" value="{{ $brandKit->postal_code }}" readonly
                                        aria-label="Product barcode" />
                                    <label for="brand_country">Postalcode</label>
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-floating form-floating-outline">
                                    <input type="text" class="form-control" id="brand_state" placeholder="Website"
                                        name="brand_state" value="{{ $brandKit->website }}" aria-label="Product SKU" readonly/>
                                    <label for="brand_state">Website</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- brand fonts card --}}
                <div class="card mb-6">
                    <h5 class="card-header">Brand Fonts</h5>
                    <div class="col-lg-12 px-5 pb-5">
                        @if (!empty($fontsData))
                            <div class="row">
                                <div class="row mb-5 gx-5">
                                    <div class="col-8">
                                        <div class="form-floating form-floating-outline">
                                            <input type="email" class="form-control" id="font_family" placeholder="00000"
                                                name="font_family" value="{{ $fontsData['family'] ?? '' }}" aria-label="Font Family" readonly />
                                            <label for="font_family">Font Family</label>
                                        </div>
                                    </div>
                                    {{-- <div class="col-2">
                                        <div class="form-floating form-floating-outline">
                                            <input type="number" class="form-control" id="font_size" placeholder="0123-4567"
                                                name="font_size" value="{{ $fontsData['size'] ?? '' }}" aria-label="Font Size" readonly />
                                            <label for="font_size">Font Size</label>
                                        </div>
                                    </div> --}}
                                    <div class="col-4 font-icon-btns">
                                        @if (!empty($fontsData['font_bold']) && $fontsData['font_bold'] == "true")
                                            <button type="button" class="btn btn-icon btn-primary waves-effect waves-light" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Font Bold">
                                                <i class="tf-icons ri-bold ri-22px"></i>
                                            </button>
                                        @else
                                            <button type="button" class="btn btn-icon btn-outline-primary waves-effect waves-light" data-bs-toggle="tooltip" data-bs-placement="bottom" title="No Font Bold">
                                                <i class="tf-icons ri-bold ri-22px"></i>
                                            </button>
                                        @endif
                                        @if (!empty($fontsData['font_italic']) && $fontsData['font_italic'] == "true")
                                            <button type="button" class="btn btn-icon btn-primary waves-effect waves-light" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Font Italic">
                                                <i class="tf-icons ri-italic ri-22px"></i>
                                            </button>
                                        @else
                                            <button type="button" class="btn btn-icon btn-outline-primary waves-effect waves-light" data-bs-toggle="tooltip" data-bs-placement="bottom" title="No Font Italic">
                                                <i class="tf-icons ri-italic ri-22px"></i>
                                            </button>
                                        @endif
                                        @if (!empty($fontsData['font_underline']) && $fontsData['font_underline'] == "true")
                                            <button type="button" class="btn btn-icon btn-primary waves-effect waves-light" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Font Underline">
                                                <i class="tf-icons ri-underline ri-22px"></i>
                                            </button>
                                        @else
                                            <button type="button" class="btn btn-icon btn-outline-primary waves-effect waves-light" data-bs-toggle="tooltip" data-bs-placement="bottom" title="No Font Underline">
                                                <i class="tf-icons ri-underline ri-22px"></i>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- social media icon & Brand color & post information  --}}
            <div class="col-12 col-lg-4">
                {{-- design style --}}
                <div class="card mb-6">
                    <h5 class="card-header">Design Style</h5>
                    <div class="col-lg-12 px-5 pb-5">
                        @if (!empty($brandKit->designStyle))
                            <button type="button" class="btn btn-outline-primary waves-effect" data-bs-toggle="tooltip" data-bs-placement="bottom" title="{{ $brandKit->designStyle->name }}">
                                <span class="tf-icons ri-palette-line ri-16px me-2"></span>
                                {{ $brandKit->designStyle->name }}
                            </button>   
                        @else
                            <button type="button" class="btn btn-outline-primary waves-effect" data-bs-toggle="tooltip" data-bs-placement="bottom" title="No Design Style">
                                <span class="tf-icons ri-palette-line ri-16px me-2"></span>
                                No Design Style
                            </button>
                        @endif
                    </div>
                </div>

                {{-- social media icon --}}
                <div class="card mb-6">
                    <h5 class="card-header">Social Media Icon To Show</h5>
                    <div class="col-lg-12 px-5 pb-5">
                        @if (in_array('facebook', $socialMedia))
                            <button type="button" class="btn btn-icon btn-facebook waves-effect waves-light" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Facebook">
                                <i class="tf-icons ri-facebook-circle-fill ri-22px"></i>
                            </button>
                        @else
                            {{-- <button type="button" class="btn btn-icon btn-outline-primary waves-effect waves-light" data-bs-toggle="tooltip" data-bs-placement="bottom" title="No Facebook">
                                <i class="tf-icons ri-facebook-circle-fill ri-22px"></i>
                            </button> --}}
                        @endif

                        @if (in_array('instagram', $socialMedia))
                            <button type="button" class="btn btn-icon btn-instagram waves-effect waves-light" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Instagram">
                                <i class="tf-icons ri-instagram-fill ri-22px"></i>
                            </button>
                        @else
                            {{-- <button type="button" class="btn btn-icon btn-outline-primary waves-effect waves-light" data-bs-toggle="tooltip" data-bs-placement="bottom" title="No Instagram">
                                <i class="tf-icons ri-instagram-fill ri-22px"></i>
                            </button> --}}
                        @endif

                        @if (in_array('linkedin', $socialMedia))
                            <button type="button" class="btn btn-icon btn-linkedin waves-effect waves-light" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Linkedin">
                                <i class="tf-icons tf-icons ri-linkedin-box-fill ri-22px"></i>
                            </button>
                        @else
                            {{-- <button type="button" class="btn btn-icon btn-outline-primary waves-effect" data-bs-toggle="tooltip" data-bs-placement="bottom" title="No Linkedin">
                                <i class="tf-icons tf-icons ri-linkedin-box-fill ri-22px"></i>
                            </button> --}}
                        @endif

                        @if (in_array('whatsapp', $socialMedia))
                            <button type="button" class="btn btn-icon btn-whatsapp waves-effect waves-light" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Whatsapp">
                                <i class="tf-icons ri-whatsapp-fill ri-22px"></i>
                            </button>
                        @else
                            {{-- <button type="button" class="btn btn-icon btn-outline-primary waves-effect waves-light" data-bs-toggle="tooltip" data-bs-placement="bottom" title="No Whatsapp">
                                <i class="tf-icons ri-whatsapp-fill ri-22px"></i>
                            </button> --}}
                        @endif

                        @if (in_array('tiktok', $socialMedia))
                            <button type="button" class="btn btn-icon btn-tiktok waves-effect waves-light" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Tiktok"
                                style="background-color: #000000;color: #fff;">
                                <i class="tf-icons ri-tiktok-fill ri-22px"></i>
                            </button>
                        @else
                            {{-- <button type="button" class="btn btn-icon btn-outline-primary waves-effect waves-light" data-bs-toggle="tooltip" data-bs-placement="bottom" title="No Tiktok">
                                <i class="tf-icons ri-tiktok-fill ri-22px"></i>
                            </button> --}}
                        @endif

                        @if (in_array('x', $socialMedia))
                            <button type="button" class="btn btn-icon btn-dark waves-effect waves-light" data-bs-toggle="tooltip" data-bs-placement="bottom" title="X">
                                <i class="tf-icons ri-twitter-x-fill ri-22px"></i>
                            </button>
                        @else
                            {{-- <button type="button" class="btn btn-icon btn-outline-primary waves-effect waves-light" data-bs-toggle="tooltip" data-bs-placement="bottom" title="No X">
                                <i class="tf-icons ri-twitter-x-fill ri-22px"></i>
                            </button> --}}
                        @endif
                    </div>
                </div>

                {{-- brand color --}}
                <div class="card mb-6">
                    <h5 class="card-header">Brand Color</h5>
                    <div class="col-lg-12 px-5 pb-5">
                        @if (!empty($brandKit->color))
                            @foreach (json_decode($brandKit->color) as $color)
                                <button type="button" class="btn btn-icon btn-tiktok waves-effect waves-light"
                                    style="background-color: {{ $color }};" data-bs-toggle="tooltip" data-bs-placement="bottom" title="{{ $color }}"></button>
                            @endforeach
                        @endif
                    </div>
                </div>

                {{-- post information --}}
                <div class="card mb-6">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="ri-box-line ri-24px"></i> Post information</h5>
                    </div>
                    <div class="card-body">
                        <!-- Vendor -->
                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" value="show_email" id="show_email"
                                {{ $brandKit->show_email_on_post == 1 ? 'checked' : '' }} onclick="return false;">
                            <label class="form-check-label" for="show_email">
                                <span>Show email on post</span>
                            </label>
                        </div>
                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" value="show_phone" id="show_phone"
                                {{ $brandKit->show_phone_number_on_post == 1 ? 'checked' : '' }} onclick="return false;">
                            <label class="form-check-label" for="show_phone">
                                <span>Show phone number on post</span>
                            </label>
                        </div>
                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" value="show_website" id="show_website"
                                {{ $brandKit->show_website_on_post == 1 ? 'checked' : '' }} onclick="return false;">
                            <label class="form-check-label" for="show_website">
                                <span>Show website on post</span>
                            </label>
                        </div>
                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" value="show_address" id="show_address"
                                {{ $brandKit->show_address_on_post == 1 ? 'checked' : '' }} onclick="return false;">
                            <label class="form-check-label" for="show_address">
                                <span>Show address on post</span>
                            </label>
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
