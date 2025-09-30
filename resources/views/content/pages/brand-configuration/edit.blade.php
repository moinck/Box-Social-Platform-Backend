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
        <div
            class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-6 row-gap-4">
            <div class="d-flex flex-column justify-content-center">
                <h4 class="mb-1">Edit Brand Kit</h4>
            </div>
            <!-- <div class="d-flex align-content-center flex-wrap gap-4">
                <button class="btn btn-outline-secondary">Discard</button>
                <button type="submit" class="btn btn-primary">Update</button>
            </div> -->
        </div>

        <div class="row">
            <div class="col-12 col-lg-8">
                <div class="card mb-6">
                    <div class="card-header">
                        <h5 class="card-tile mb-0">Brand information</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-floating form-floating-outline mb-5">
                            <input type="text" class="form-control" id="brand_company_name"
                                placeholder="Product title" name="brand_company_name" value="{{ $brandKit->company_name }}" aria-label="Product title" />
                            <label for="brand_company_name">Company Name</label>
                        </div>

                        <div class="row mb-5 gx-5">
                            <div class="col">
                                <div class="form-floating form-floating-outline">
                                    <input type="email" class="form-control" id="brand_email"
                                        placeholder="00000" name="brand_email" value="{{ $brandKit->email }}" aria-label="Product SKU" />
                                    <label for="brand_email">Email</label>
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-floating form-floating-outline">
                                    <input type="number" class="form-control" id="brand_phone"
                                        placeholder="0123-4567" name="brand_phone" value="{{ $brandKit->phone }}" aria-label="Product barcode" />
                                    <label for="brand_phone">Phone</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-floating form-floating-outline mb-5">
                            <textarea class="form-control h-px-75" id="brand_address" rows="3"
                                placeholder="Product title" name="brand_address" aria-label="Product title">{{ $brandKit->address }}</textarea>
                            <label for="brand_address">Address</label>
                        </div>
                        <div class="row mb-5 gx-5">
                            <div class="col">
                                <div class="form-floating form-floating-outline">
                                    <input type="text" class="form-control" id="brand_state"
                                        placeholder="00000" name="brand_state" value="{{ $brandKit->state }}" aria-label="Product SKU" />
                                    <label for="brand_state">State</label>
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-floating form-floating-outline">
                                    <input type="text" class="form-control" id="brand_country"
                                        placeholder="0123-4567" name="brand_country" value="{{ $brandKit->country }}" aria-label="Product barcode" />
                                    <label for="brand_country">Country</label>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-5 gx-5">
                        <div class="col">
                                <div class="form-floating form-floating-outline">
                                    <input type="text" class="form-control" id="brand_country"
                                        placeholder="0123-4567" name="brand_country" value="{{ $brandKit->postal_code }}" aria-label="Product barcode" />
                                    <label for="brand_country">Postalcode</label>
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-floating form-floating-outline">
                                    <input type="text" class="form-control" id="brand_state"
                                        placeholder="00000" name="brand_state" value="{{ $brandKit->website }}" aria-label="Product SKU" />
                                    <label for="brand_state">Website</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
              
                
               
                <!-- /Variants -->
                <!-- Inventory -->
                <!-- /Inventory -->
            </div>
            <!-- /Second column -->

            <!-- Second column -->
            <div class="col-12 col-lg-4">
                <!-- Pricing Card -->
                <div class="card mb-6">
                    <h5 class="card-header">Social Media Icon To Show</h5>

                    <div class="col-lg-12 p-12">
                    
                   
                    @if(in_array('facebook',$socialMedia))
                   
                    
                    <button type="button" class="btn btn-icon btn-facebook waves-effect waves-light"><i class="tf-icons ri-facebook-circle-fill ri-22px"></i></button>
                    @endif

                    @if(in_array('instagram',$socialMedia))
                    
                    <button type="button" class="btn btn-icon btn-instagram waves-effect waves-light"><i class="tf-icons ri-instagram-fill ri-22px"></i></button>
                    @endif

                    @if(in_array('linkedin',$socialMedia))
                    
                    <button type="button" class="btn btn-icon btn-linkedin waves-effect waves-light"><i class="tf-icons ri-linkedin-circle-fill ri-22px"></i></button>
                     @endif

                    @if(in_array('whatsapp',$socialMedia))
                    
                    <button type="button" class="btn btn-icon btn-whatsapp waves-effect waves-light"><i class="tf-icons ri-whatsapp-fill ri-22px"></i></button>
                     @endif

                    @if(in_array('tiktok',$socialMedia))
                    
                    <button type="button" class="btn btn-icon btn-tiktok waves-effect waves-light" style="background-color: #000000; /* or use a gradient */
  color: #fff;"><i class="tf-icons ri-tiktok-fill ri-22px"></i></button>
                     @endif

                    @if(in_array('x',$socialMedia))
                    
                    <button type="button" class="btn btn-icon btn-dark waves-effect waves-light">
  <i class="tf-icons ri-twitter-x-fill ri-22px"></i>
</button>
                    
                    @endif
                   
                    </div>





                </div>
                
                <div class="card mb-6">
                    <h5 class="card-header">Color</h5>

                    <div class="col-lg-12 p-12">
                    

                    @if(!empty($brandKit->color))
                        @foreach ( json_decode($brandKit->color) as $color )
                        <button type="button" class="btn btn-icon btn-tiktok waves-effect waves-light" style="background-color: {{ $color }};"></i></button>                     
                        @endforeach
                    @endif
                    </div>





                </div>
                <!-- /Pricing Card -->
                <!-- Organize Card -->
                <div class="card mb-6">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Organize</h5>
                    </div>
                    <div class="card-body">
                        <!-- Vendor -->
                        <div class="mb-5 col ecommerce-select2-dropdown">
                            <div class="form-floating form-floating-outline">
                                <select id="vendor" class="select2 form-select" data-placeholder="Select Vendor">
                                    <option value="">Select Vendor</option>
                                    <option value="men-clothing">Men's Clothing</option>
                                    <option value="women-clothing">Women's-clothing</option>
                                    <option value="kid-clothing">Kid's-clothing</option>
                                </select>
                                <label for="vendor">Vendor</label>
                            </div>
                        </div>
                        <!-- Category -->
                        <div class="mb-5 col ecommerce-select2-dropdown d-flex justify-content-between align-items-center">
                            <div class="form-floating form-floating-outline w-100 me-4">
                                <select id="category-org" class="select2 form-select" data-placeholder="Select Category">
                                    <option value="">Select Category</option>
                                    <option value="Household">Household</option>
                                    <option value="Management">Management</option>
                                    <option value="Electronics">Electronics</option>
                                    <option value="Office">Office</option>
                                    <option value="Automotive">Automotive</option>
                                </select>
                                <label for="category-org">Category</label>
                            </div>
                            <div>
                                <button class="btn btn-outline-primary btn-icon btn-lg"><i
                                        class="ri-add-line"></i></button>
                            </div>
                        </div>
                        <!-- Collection -->
                        <div class="mb-5 col ecommerce-select2-dropdown">
                            <div class="form-floating form-floating-outline">
                                <select id="collection" class="select2 form-select" data-placeholder="Collection">
                                    <option value="">Collection</option>
                                    <option value="men-clothing">Men's Clothing</option>
                                    <option value="women-clothing">Women's-clothing</option>
                                    <option value="kid-clothing">Kid's-clothing</option>
                                </select>
                                <label for="collection">Collection</label>
                            </div>
                        </div>
                        <!-- Status -->
                        <div class="mb-5 col ecommerce-select2-dropdown">
                            <div class="form-floating form-floating-outline">
                                <select id="status-org" class="select2 form-select" data-placeholder="Select Status">
                                    <option value="">Select Status</option>
                                    <option value="Published">Published</option>
                                    <option value="Scheduled">Scheduled</option>
                                    <option value="Inactive">Inactive</option>
                                </select>
                                <label for="status-org">Status</label>
                            </div>
                        </div>
                        <!-- Tags -->
                        <div>
                            <div class="form-floating form-floating-outline">
                                <input id="ecommerce-product-tags" class="form-control h-auto"
                                    name="ecommerce-product-tags" value="Normal,Standard,Premium"
                                    aria-label="Product Tags" />
                                <label for="ecommerce-product-tags">Tags</label>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /Organize Card -->
            </div>
            <!-- /Second column -->
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
