@extends('layouts/layoutMaster')

@section('title', 'Profile Management')

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
        <div class="row">
            {{-- simple header --}}
            <div class="col-lg-12 col-md-12 col-sm-12">
                <div class="card b-6 mb-6">
                    <div class="user-profile-header d-flex flex-column flex-sm-row text-sm-start text-center">
                        <div class="flex-shrink-0 m-3 mx-sm-0 mx-auto" style="margin-top:1.25rem;margin-bottom:1.25rem;padding: 0rem 0.5rem 0 0.5rem;">
                            <img src="{{ $profileImage }}" alt="user image" id="account-file-input" height="200" width="200"
                                class="d-block h-auto ms-0 rounded-4 user-profile-img" />
                        </div>
                        <div class="flex-grow-1 mt-4 mt-sm-12">
                            <div
                                class="d-flex align-items-md-end align-items-sm-start align-items-center justify-content-md-between justify-content-start mx-5 flex-md-row flex-column gap-6">
                                <div class="user-profile-info">
                                    <h4 class="mb-2">{{ $user->first_name }} {{ $user->last_name }}</h4>
                                    <ul
                                        class="list-inline mb-0 d-flex align-items-center flex-wrap justify-content-sm-start justify-content-center gap-4">
                                        <li class="list-inline-item">
                                            <i class="ri-user-line me-2 ri-24px"></i>
                                            <span class="fw-medium">{{ $user->role }}</span>
                                        </li>
                                        <li class="list-inline-item">
                                            <i class="ri-calendar-line me-2 ri-24px"></i>
                                            <span class="fw-medium">Joined | {{ $user->created_at->format('d F Y') }}</span>
                                        </li>
                                    </ul>

                                    {{-- add button to upload image --}}
                                    {{-- <div class="button-wrapper mt-3">
                                        <label for="upload" class="btn btn-primary me-3 mb-4 waves-effect waves-light" tabindex="0">
                                            <span class="d-none d-sm-block">Upload new photo</span>
                                            <i class="ri-upload-2-line d-block d-sm-none"></i>
                                            <input type="file" id="upload" class="account-file-input" hidden="" accept="image/png, image/jpeg">
                                        </label>
                                        <p>Allowed JPG, GIF or PNG. Max size of 800K</p>
                                    </div> --}}
                                </div>
                                {{-- <a href="javascript:void(0)" class="btn btn-primary">
                                    <i class="ri-user-follow-line ri-16px me-2"></i>Connected
                                </a> --}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- profile information --}}
            <div class="col-lg-12 col-md-12 col-sm-12">
                <div class="card mb-6">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Profile Information</h4>
                    </div>
                    <div class="card-body mt-2">
                        <form id="edit-profile-form" action="{{ route('profile-management.update') }}" class="row g-5" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="col-12 col-md-6">
                                <div class="form-floating form-floating-outline">
                                    <input type="text" id="edit_first_name" name="edit_first_name" class="form-control"
                                        placeholder="First Name" value="{{ $user->first_name }}" />
                                    <label for="edit_first_name">First Name</label>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-floating form-floating-outline">
                                    <input type="text" id="edit_last_name" name="edit_last_name" class="form-control"
                                        placeholder="Last Name" value="{{ $user->last_name }}" />
                                    <label for="edit_last_name">Last Name</label>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-floating form-floating-outline">
                                    <input type="email" id="edit_user_email" name="edit_user_email" class="form-control"
                                        placeholder="User Email" value="{{ $user->email }}"  />
                                    <label for="edit_user_email">Email</label>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-floating form-floating-outline">
                                    <input type="file" id="edit_user_image" name="edit_user_image" class="form-control"
                                        placeholder="User Image" accept="image/*"/>
                                    <label for="edit_user_image">Image</label>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-floating form-floating-outline">
                                    <input type="text" id="edit_company_name" name="edit_company_name" class="form-control"
                                        placeholder="Company Name" value="{{ $user->company_name }}" />
                                    <label for="edit_company_name">Company Name</label>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-floating form-floating-outline">
                                    <input type="number" id="edit_user_fca_number" name="edit_user_fca_number" class="form-control"
                                        placeholder="123456789" value="{{ $user->fca_number }}" />
                                    <label for="edit_user_fca_number">FCA No.</label>
                                </div>
                            </div>
                            <div class="col-12 text-center d-flex flex-wrap justify-content-center gap-4 row-gap-4">
                                <button type="submit" class="btn btn-primary">Update</button>
                                <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="modal"
                                    aria-label="Close">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- image with subscription plan detail --}}
            {{-- <div class="col-lg-4 col-md-12 col-sm-12">
                <div class="card mb-6">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <span class="badge bg-label-primary rounded-pill">Standard</span>
                            <div class="d-flex justify-content-center">
                                <sup class="h5 pricing-currency mt-5 mb-0 me-1 text-primary">$</sup>
                                <h1 class="mb-0 text-primary">99</h1>
                                <sub class="h6 pricing-duration mt-auto mb-3 fw-normal">month</sub>
                            </div>
                        </div>
                        <ul class="list-unstyled g-2 my-6">
                            <li class="mb-2 d-flex align-items-center">
                                <i class="ri-circle-fill text-body ri-10px me-2"></i><span>10 Users</span>
                            </li>
                            <li class="mb-2 d-flex align-items-center">
                                <i class="ri-circle-fill text-body ri-10px me-2"></i><span>Up to 10 GB storage</span>
                            </li>
                            <li class="mb-2 d-flex align-items-center">
                                <i class="ri-circle-fill text-body ri-10px me-2"></i><span>Basic Support</span>
                            </li>
                        </ul>
                        <div class="d-flex justify-content-between align-items-center mb-1 fw-medium text-heading">
                            <span>Days</span>
                            <span>26 of 30 Days</span>
                        </div>
                        <div class="progress mb-1 rounded">
                            <div class="progress-bar rounded" role="progressbar" style="width: 75%" aria-valuenow="75"
                                aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <small>4 days remaining</small>
                        <div class="d-grid w-100 mt-6">
                            <button class="btn btn-primary waves-effect waves-light" data-bs-target="#upgradePlanModal"
                                data-bs-toggle="modal">
                                Upgrade Plan
                            </button>
                        </div>
                    </div>
                </div>
            </div> --}}
        </div>

        {{-- brand kit information --}}
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12">
                <div class="card mb-6">
                    <div class="card-header">
                        <h4 class="card-tile mb-0">Brand Contact Details</h4>
                    </div>
                    <div class="card-body">
                        @if ($method == 'create')
                            <form action="{{ route('brand-configuration.store') }}" method="POST">
                        @else
                            <form action="{{ route('brand-configuration.update') }}" method="POST">
                            <input type="hidden" name="id" value="{{ $brandKitData->id }}">
                        @endif
                            @csrf

                            {{-- all input fields --}}
                            <input type="hidden" name="user_id" value="{{ auth()->user()->id }}">
                            <div class="form-floating form-floating-outline mb-5">
                                <input type="text" class="form-control" id="brand_company_name" placeholder="Product title"
                                    name="brand_company_name" value="{{ $brandKitData->company_name ?? auth()->user()->company_name }}"
                                    aria-label="Product title"  />
                                <label for="brand_company_name">Company Name</label>
                            </div>
        
                            <div class="row mb-5 gx-5">
                                <div class="col">
                                    <div class="form-floating form-floating-outline">
                                        <input type="email" class="form-control" id="brand_email" placeholder="00000"
                                            name="brand_email" value="{{ $brandKitData->email ?? auth()->user()->email }}" aria-label="Product SKU"  />
                                        <label for="brand_email">Email</label>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="form-floating form-floating-outline">
                                        <input type="number" class="form-control" id="brand_phone" placeholder="0123-4567"
                                            name="brand_phone" value="{{ $brandKitData->phone ?? "123456789" }}" aria-label="Product barcode"  />
                                        <label for="brand_phone">Phone</label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-floating form-floating-outline mb-5">
                                <textarea class="form-control h-px-75" id="brand_address" rows="3" placeholder="Product title"
                                    name="brand_address" aria-label="Product title" >{{ $brandKitData->address ?? "Address" }}</textarea>
                                <label for="brand_address">Address</label>
                            </div>
                            <div class="row mb-5 gx-5">
                                <div class="col">
                                    <div class="form-floating form-floating-outline">
                                        <input type="text" class="form-control" id="brand_state" placeholder="00000"
                                            name="brand_state" value="{{ $brandKitData->state ?? "State" }}" aria-label="Product SKU"  />
                                        <label for="brand_state">State</label>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="form-floating form-floating-outline">
                                        <input type="text" class="form-control" id="brand_country" placeholder="0123-4567"
                                            name="brand_country" value="{{ $brandKitData->country ?? "Country" }}" 
                                            aria-label="Product barcode" />
                                        <label for="brand_country">Country</label>
                                    </div>
                                </div>
                            </div>
        
                            <div class="row mb-5 gx-5">
                                <div class="col">
                                    <div class="form-floating form-floating-outline">
                                        <input type="text" class="form-control" id="brand_postal_code" placeholder="0123-4567"
                                            name="brand_postal_code" value="{{ $brandKitData->postal_code ?? "Postalcode" }}" 
                                            aria-label="Product barcode" />
                                        <label for="brand_postal_code">Postalcode</label>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="form-floating form-floating-outline">
                                        <input type="text" class="form-control" id="brand_website" placeholder="00000"
                                            name="brand_website" value="{{ $brandKitData->website ?? "Website" }}" aria-label="Product SKU" />
                                        <label for="brand_website">Website</label>
                                    </div>
                                </div>
                            </div>

                            {{-- all checkbox --}}
                            <div class="row mb-5 gx-5">
                                <div class="col">
                                    <div class="form-check">
                                        <input class="form-check-input" name="show_email_on_post" type="checkbox" id="show_email"
                                            {{ ($brandKitData->show_email_on_post ?? 0) == 1 ? 'checked' : '' }} >
                                        <label class="form-check-label" for="show_email">
                                            <span>Show email on post</span>
                                        </label>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="form-check">
                                        <input class="form-check-input" name="show_phone_number_on_post" type="checkbox" id="show_phone"
                                            {{ ($brandKitData->show_phone_number_on_post ?? 0) == 1 ? 'checked' : '' }} >
                                        <label class="form-check-label" for="show_phone">
                                            <span>Show phone number on post</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-5 gx-5">
                                <div class="col">
                                    <div class="form-check">
                                        <input class="form-check-input" name="show_website_on_post" type="checkbox" id="show_website"
                                            {{ ($brandKitData->show_website_on_post ?? 0) == 1 ? 'checked' : '' }} >
                                        <label class="form-check-label" for="show_website">
                                            <span>Show website on post</span>
                                        </label>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="form-check">
                                        <input class="form-check-input" name="show_address_on_post" type="checkbox" id="show_address"
                                            {{ ($brandKitData->show_address_on_post ?? 0) == 1 ? 'checked' : '' }} >
                                        <label class="form-check-label" for="show_address">
                                            <span>Show address on post</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
    
                            <div class="col-12 text-center d-flex flex-wrap justify-content-center gap-4 row-gap-4">
                                @if ($method == 'edit')
                                    <button type="submit" class="btn btn-primary">Update</button>
                                @else
                                    <button type="submit" class="btn btn-primary">Create</button>
                                @endif
                                <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="modal"
                                    aria-label="Close">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page-script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script>
        // Update/reset user image of account page
        let accountUserImage = document.getElementById('account-file-input');
        const fileInput = document.querySelector('#edit_user_image'),
            resetFileInput = document.querySelector('.account-image-reset');

        if (accountUserImage) {
            const resetImage = accountUserImage.src;
            fileInput.onchange = () => {
                // check file size
                if (fileInput.files[0].size > 1 * 1024 * 1024) {
                    return;
                }
                // check file type
                if (!fileInput.files[0].type.includes('image')) {
                    return;
                }
                if (fileInput.files[0]) {
                    accountUserImage.src = window.URL.createObjectURL(fileInput.files[0]);
                }
            };
            if (resetFileInput) {
                resetFileInput.onclick = () => {
                    fileInput.value = '';
                    accountUserImage.src = resetImage;
                };
            }
        }
        $(document).ready(function() {

            // profile form validation
            const formValidationExamples = document.getElementById('edit-profile-form');
            const validator = FormValidation.formValidation(formValidationExamples, {
                fields: {
                    edit_first_name: {
                        validators: {
                            notEmpty: {
                                message: 'Please enter first name'
                            }
                        }
                    },
                    edit_last_name: {
                        validators: {
                            notEmpty: {
                                message: 'Please enter last name'
                            }
                        }
                    },
                    edit_company_name: {
                        validators: {
                            notEmpty: {
                                message: 'Please enter company name'
                            }
                        }
                    },
                    edit_user_image: {
                        validators: {
                            file: {
                                extension: 'png,jpg,jpeg',
                                type: 'image/jpeg,image/png',
                                maxSize: 1 * 1024 * 1024,
                                message: 'Please upload a valid image file (max 1MB)'
                            }
                        }
                    },
                    edit_user_fca_number: {
                        validators: {
                            notEmpty: {
                                message: 'Please enter FCA number'
                            },
                            regexp: {
                                regexp: /^[0-9]*$/,
                                message: 'FCA Number can only contain digits'
                            },
                            stringLength: {
                                min: 6,
                                max: 6,
                                message: 'FCA Number must be 6 digits'
                            }
                        }
                    }
                },
                plugins: {
                    trigger: new FormValidation.plugins.Trigger(),
                    bootstrap5: new FormValidation.plugins.Bootstrap5({
                        eleValidClass: '',
                        rowSelector: function(field, ele) {
                            if (['edit_first_name', 'edit_last_name', 'edit_company_name','edit_user_fca_number'
                                ].includes(field)) {
                                return '.col-md-6';
                            }
                            return '.col-12';
                        }
                    }),
                    submitButton: new FormValidation.plugins.SubmitButton(),
                    autoFocus: new FormValidation.plugins.AutoFocus()
                }
            }).on('core.form.valid', function() {
                // disable submit button
                $('#edit-profile-form button[type="submit"]').prop('disabled', true);
                $('#edit-profile-form').submit();
            });
            // -----------------------------------------------------
        });
    </script>
@endsection
