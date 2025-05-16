@extends('layouts/layoutMaster')

@section('title', 'Categories')

<!-- Vendor Styles -->
@section('vendor-style')
    @vite([
        'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
        'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
        'resources/assets/vendor/libs/datatables-select-bs5/select.bootstrap5.scss',
        'resources/assets/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.scss',
        'resources/assets/vendor/libs/datatables-fixedcolumns-bs5/fixedcolumns.bootstrap5.scss',
        'resources/assets/vendor/libs/datatables-fixedheader-bs5/fixedheader.bootstrap5.scss',
        'resources/assets/vendor/libs/@form-validation/form-validation.scss',
        'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'
    ])
@endsection

<!-- Vendor Scripts -->
@section('vendor-script')
    @vite([
        'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
        'resources/assets/vendor/libs/@form-validation/popular.js',
        'resources/assets/vendor/libs/@form-validation/bootstrap5.js',
        'resources/assets/vendor/libs/@form-validation/auto-focus.js',
        'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'
    ])
@endsection



@section('content')

    <!-- Main Table -->
    <div class="card">
        <div class="card-header d-flex flex-column flex-md-row border-bottom user-table-header">
            <div class="head-label">
                <h5 class="card-title mb-0">Categories</h5>
            </div>
            <div class="dt-action-buttons text-end pt-3 pt-md-0">
                <div class="dt-buttons btn-group flex-wrap"> 
                    <button class="btn btn-secondary btn-primary waves-effect waves-light" type="button" id="category-add-btn" title="Add Category">
                        <span>
                            <i class="ri-add-line ri-16px me-sm-2"></i>
                            <span class="d-none d-sm-inline-block">Add Category</span>
                        </span>
                    </button> 
                </div>
            </div>
        </div>
        <div class="card-datatable table-responsive">
            <table class="dt-fixedheader table table-bordered" id="categories-data-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Name</th>
                        <th>image</th>
                        <th>description</th>
                        <th>status</th>
                        <th>created Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
    <!--/ Main Table -->

    <div class="modal fade" id="add-category-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-simple">
            <div class="modal-content">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="modal-body p-0">
                    <div class="text-center mb-6">
                        <h4 class="mb-2">Add Category</h4>
                    </div>
                    <form id="add-category-form" class="row g-5" method="POST">
                        @csrf
                        <div class="col-12">
                            <div class="form-floating form-floating-outline">
                                <input type="text" id="category_name" name="category_name" class="form-control"
                                    placeholder="Category Name" required />
                                <label for="category_name">Category Name</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-floating form-floating-outline">
                                <input type="file" id="category_image" name="category_image" class="form-control"
                                    placeholder="Last Name"  accept="image/*"/>
                                <label for="category_image">Image</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-floating form-floating-outline">
                                <textarea id="category_description" rows="3" name="category_description" class="form-control h-px-75"
                                    placeholder="Description"></textarea>
                                <label for="category_description">Description</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-floating form-floating-outline">
                                <select id="category_status" name="category_status" class="form-select"
                                    aria-label="Default select example">
                                    <option value="">Select Status</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                                <label for="category_status">Status</label>
                            </div>
                        </div>
                        <div class="col-12 text-center d-flex flex-wrap justify-content-center gap-4 row-gap-4">
                            <button type="submit" class="btn btn-primary">Create</button>
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
@endsection

<!-- Page Scripts -->
@section('page-script')
    {{-- @vite(['resources/assets/js/tables-datatables-extensions.js']) --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script>
        $(document).ready(function() {
            CategoriesDataTable();

            // contact data table function
            function CategoriesDataTable() {
                var ContactTable = $('#categories-data-table').DataTable({
                    bLengthChange: false,
                    searchable: true,
                    serverSide: true,
                    orderable: true,
                    searching: true,
                    destroy: true,
                    info: true,
                    paging: true,
                    pageLength: 10,
                    ajax: {
                        url: "{{ route('categories.data-table') }}",
                        beforeSend: function () {
                            showBSPLoader();
                        },
                        complete: function () {
                            hideBSPLoader();
                        }
                    },
                    columns: [
                        { data: 'DT_RowIndex', name: 'DT_RowIndex'},
                        { data: 'name', name: 'name'},
                        { data: 'image', name: 'image'},
                        { data: 'description', name: 'description'},
                        { data: 'status', name: 'status'},
                        { data: 'created_at', name: 'created_at'},
                        { data: 'action', name: 'action'},
                    ],
                    language: {
                        paginate: {
                            next: '<i class="ri-arrow-right-s-line"></i>',
                            previous: '<i class="ri-arrow-left-s-line"></i>'
                        }
                    }
                });
            }
            // -------------------------------------------

            // add category function
            $(document).on('click','#category-add-btn', function () {
                // reset the form
                $('#add-category-form')[0].reset();
                // reset the form validation
                addCategoryFV.resetForm();
                $('#add-category-modal').modal('show');
            });
            
            // validate the submit form
            const addCategoryForm = document.getElementById('add-category-form');
            const addCategoryFV = FormValidation.formValidation(addCategoryForm, {
                fields: {
                    category_name: {
                        validators: {
                            notEmpty: {
                                message: 'Please enter your category name'
                            },
                            stringLength: {
                                min: 2,
                                max: 50,
                                message: 'Category name must be between 2 and 50 characters'
                            }
                        }
                    },
                    category_image: {
                        validators: {
                            notEmpty: {
                                message: 'Please upload category image'
                            },
                            file: {
                                extension: 'png,jpg,jpeg,gif',
                                type: 'image/jpeg,image/png,image/jpg,image/gif',
                                maxSize: 2 * 1024 * 1024,
                                message: 'Please upload a valid image file'
                            }
                        }
                    },
                    category_description: {
                        validators: {
                            notEmpty: {
                                message: 'Please enter your category description'
                            },
                            stringLength: {
                                max: 100,
                                message: 'Category description must be less than 100 characters'
                            }
                        }
                    },
                    category_status: {
                        validators: {
                            notEmpty: {
                                message: 'Please select account status'
                            }
                        }
                    }
                },
                plugins: {
                    trigger: new FormValidation.plugins.Trigger(),
                    bootstrap5: new FormValidation.plugins.Bootstrap5({
                        eleValidClass: '',
                        rowSelector: function(field, ele) {
                            // Customize row selector based on your form layout
                            if (['category_name', 'category_image', 'category_description',
                                    'category_status'
                                ].includes(field)) {
                                return '.col-12';
                            }
                            return '.col-12';
                        }
                    }),
                    submitButton: new FormValidation.plugins.SubmitButton(),
                    // defaultSubmit: new FormValidation.plugins.DefaultSubmit(),
                    // autoFocus: new FormValidation.plugins.AutoFocus()
                }
            }).on('core.form.valid', function() {
                // Form is valid, proceed with form submission
                var form = $('#add-category-form');
                var formData = new FormData(form[0]); // Creates FormData object

                $.ajax({
                    url: "{{ route('categories.store') }}",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    beforeSend: function () {
                        showBSPLoader();
                    },
                    complete: function () {
                        hideBSPLoader();
                    },
                    success: function(response) {
                        if (response.success == true) {
                            showSweetAlert('success', 'Created !','Category has been created successfully.');
                            $('#add-category-modal').modal('hide');
                            CategoriesDataTable();
                        }
                    },
                    error: function(xhr) {
                        hideBSPLoader();
                        console.log(xhr.responseText);
                        showSweetAlert('error', 'Error !', 'Something went wrong.');
                    }
                });
            });
            // ----------------------------------------------------------
        });
    </script>
@endsection
