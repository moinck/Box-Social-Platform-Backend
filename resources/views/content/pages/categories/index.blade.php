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
                    <button class="btn btn-secondary btn-primary waves-effect waves-light" type="button" id="category-add-btn"
                        data-bs-toggle="tooltip" data-bs-placement="bottom" title="Add Category">
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

    {{-- add category modal --}}
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
                        <div class="col-12">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <h5 class="mb-0">Subcategories</h5>
                                <button type="button" class="btn btn-sm btn-primary" id="add-subcategory-btn">
                                    <i class="fas fa-plus me-1"></i> Add Subcategory
                                </button>
                            </div>
                            <div id="subcategories-container">
                                <!-- Subcategory fields will be added here dynamically -->
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

    {{-- edit category modal --}}
    <div class="modal fade" id="edit-category-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-simple">
            <div class="modal-content">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="modal-body p-0">
                    <div class="text-center mb-6">
                        <h4 class="mb-2">Edit Category</h4>
                    </div>
                    <form id="edit-category-form" class="row g-5" method="POST">
                        @csrf
                        <input type="hidden" name="edit_category_id" id="edit_category_id">
                        <div class="col-12">
                            <div class="form-floating form-floating-outline">
                                <input type="text" id="edit_category_name" name="edit_category_name" class="form-control"
                                    placeholder="Category Name" required />
                                <label for="edit_category_name">Category Name</label>
                            </div>
                        </div>
                        <div class="col-8">
                            <div class="form-floating form-floating-outline mt-6">
                                <input type="file" id="edit_category_image" name="edit_category_image" class="form-control"
                                    placeholder="Image"  accept="image/*"/>
                                <label for="edit_category_image">Image</label>
                            </div>
                            <small class="text-dark">only upload image if you want to change image</small>
                        </div>
                        <div class="col-4">
                            <div class="text-center">
                                <img src="" alt="edit category image" class="img-fluid br-1" id="edit_category_image_preview" height="200" width="200">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-floating form-floating-outline">
                                <textarea id="edit_category_description" rows="3" name="edit_category_description" class="form-control h-px-75"
                                    placeholder="Description"></textarea>
                                <label for="edit_category_description">Description</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-floating form-floating-outline">
                                <select id="edit_category_status" name="edit_category_status" class="form-select"
                                    aria-label="Default select example">
                                    <option value="">Select Status</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                                <label for="edit_category_status">Status</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <h5 class="mb-0">Subcategories</h5>
                                <button type="button" class="btn btn-sm btn-primary" id="edit-add-subcategory-btn">
                                    <i class="fas fa-plus me-1"></i> Add Subcategory
                                </button>
                            </div>
                            <div id="edit-subcategories-container">
                                <!-- Subcategory fields will be added here dynamically -->
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
    </div>
@endsection

<!-- Page Scripts -->
@section('page-script')
    {{-- @vite(['resources/assets/js/tables-datatables-extensions.js']) --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script>
        $(document).ready(function() {
            CategoriesDataTable();
            let editSubcategoryCount = 0;

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
                        { data: 'action', name: 'action', orderable: false, searchable: false},
                    ],
                    language: {
                        paginate: {
                            next: '<i class="ri-arrow-right-s-line"></i>',
                            previous: '<i class="ri-arrow-left-s-line"></i>'
                        }
                    },
                    drawCallback: function(settings) {
                        $('[data-bs-toggle="tooltip"]').tooltip();
                    }
                });
            }
            // -------------------------------------------

            // add category function
            $(document).on('click','#category-add-btn', function () {
                // reset the form
                $('#add-category-form')[0].reset();
                $('.subcategory-item').remove();
                subcategoryCount = 0;
                // reset the form validation
                addCategoryFV.resetForm();
                $('#add-category-modal').modal('show');
            });
            // -------------------------------------------
            
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

            // change status function
            $(document).on('click', '#category-status', function (e) {
                e.preventDefault();
                var id = $(this).data('id');

                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't to change status!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, change it!',
                    customClass: {
                        confirmButton: 'btn btn-primary me-3',
                        cancelButton: 'btn btn-outline-secondary'
                    },
                    buttonsStyling: false
                }).then(function(result) {
                    if (result.value) {
                        $.ajax({
                            url: "{{ route('categories.change-status') }}",
                            type: "POST",
                            data: {
                                id: id,
                                _token: "{{ csrf_token() }}"
                            },
                            beforeSend: function () {
                                showBSPLoader();
                            },
                            complete: function () {
                                hideBSPLoader();
                            },
                            success: function (response) {
                                if (response.success == true) {
                                    showSweetAlert('success', 'Updated !','Category status has been updated successfully.');
                                    CategoriesDataTable();
                                }
                            },
                            error: function (xhr) {
                                hideBSPLoader();
                                console.log(xhr.responseText);
                                showSweetAlert('error', 'Error !', 'Something went wrong.');
                            }
                        });
                    } else {
                        $(this).prop('checked', !$(this).prop('checked'));
                    }
                });
            });
            // ----------------------------------------------------------

            // Edit User modal
            $(document).on('click', '.edit-category-btn', function() {
                var categoryId = $(this).data('category-id');
                var editUrl = "{{ url('/categories/edit/') }}/" + categoryId;
                $.ajax({
                    url: editUrl,
                    type: "GET",
                    success: function(response) {
                        if (response.success == true) {
                            $('#edit_category_name').val(response.data.name);
                            $('#edit_category_id').val(categoryId);
                            $('#edit_category_description').val(response.data.description);
                            $('#edit_category_status').val(response.data.status);
                            var accountStatus = response.data.status;
                            if (accountStatus == true) {
                                $('#edit_category_status').val('active');
                            } else {
                                $('#edit_category_status').val('inactive');
                            }

                            var ImageUrl = "{{ asset('') }}" + response.data.image;
                            $('#edit_category_image_preview').attr('src', ImageUrl);

                            var subcategories = response.data.children;
                            var subcategoriesEditHtml = '';
                            editSubcategoryCount = 0;
                            if (subcategories.length > 0) {
                                subcategories.forEach(function(subcategory) {
                                    editSubcategoryCount++;
                                    subcategoriesEditHtml += `
                                        <div class="col-12 mt-2 edit-subcategory-item">
                                            <div class="input-group input-group-merge">
                                                <div class="form-floating form-floating-outline">
                                                    <input
                                                        type="text"
                                                        class="form-control edit-subcategory-name"
                                                        id="edit_subcategory_name_${editSubcategoryCount}"
                                                        name="edit_subcategory_name[${editSubcategoryCount}]"
                                                        data-subcategory-id="${subcategory.id}"
                                                        placeholder="Subcategory ${editSubcategoryCount} Name"
                                                        value="${subcategory.name}"
                                                        aria-describedby="edit_subcategory_name_${editSubcategoryCount}" />
                                                    <label for="edit_subcategory_name_${editSubcategoryCount}">Subcategory ${editSubcategoryCount} Name</label>
                                                </div>
                                                <span class="input-group-text text-danger cursor-pointer remove-edit-subcategory-btn" data-subcategory-id="${subcategory.id}"><i class="ri-delete-bin-line"></i></span>
                                            </div>
                                        </div>
                                    `;

                                    // also add validation for subcategory name
                                    categoryEditFV.revalidateField('edit_subcategory_name');
                                    categoryEditFV.addField(`edit_subcategory_name[${editSubcategoryCount}]`, {
                                        validators: {
                                            notEmpty: {
                                                message: 'Subcategory ' + editSubcategoryCount + ' name is required'
                                            }
                                        }
                                    });
                                });

                                editSubcategoryCount = subcategories.length;
                            }
                            $('#edit-subcategories-container').html(subcategoriesEditHtml);
                        } else {
                            // toastr.error(response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log(xhr.responseText);
                    }
                });
                // clear edit user form validation
                // categoryEditFV.resetForm();
                $('#edit-category-modal').modal('show');
            });
            // -------------------------------------------

            // edit form validation & submission
            var editCategoryForm = document.getElementById('edit-category-form');
            var categoryEditFV = FormValidation.formValidation( editCategoryForm,
                {
                    fields: {
                        edit_category_name: {
                            validators: {
                                notEmpty: {
                                    message: 'Please enter category name'
                                },
                                stringLength: {
                                    min: 2,
                                    max: 50,
                                    message: 'Category name must be between 2 and 50 characters'
                                }
                            }
                        },
                        edit_category_image: {
                            validators: {
                                file: {
                                    extension: 'png,jpg,jpeg,gif',
                                    type: 'image/jpeg,image/png,image/jpg,image/gif',
                                    maxSize: 2 * 1024 * 1024,
                                    message: 'Please upload a valid image file'
                                }
                            }
                        },
                        edit_category_description: {
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
                        edit_category_status: {
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
                                if (['edit_category_name', 'edit_category_description','edit_category_status'].includes(field)) {
                                    return '.col-12';
                                }
                                if (['edit_category_image'].includes(field)) {
                                    return '.col-8';
                                }
                                return '.col-12';
                            }
                        }),
                        submitButton: new FormValidation.plugins.SubmitButton(),
                        // defaultSubmit: new FormValidation.plugins.DefaultSubmit(),
                        // autoFocus: new FormValidation.plugins.AutoFocus()
                    }
                }
            ).on('core.form.valid', function() {
                // Form is valid, proceed with form submission
                var form = $('#edit-category-form');
                var formData = new FormData(form[0]); // Creates FormData object

                // get subcategory ids with name in array
                var subcategoryIds = [];
                $('.edit-subcategory-name').each(function() {
                    subcategoryIds.push({
                        id: $(this).data('subcategory-id') ?? 0,
                        name: $(this).val()
                    });
                });
                formData.append('edit_subcategory_ids', JSON.stringify(subcategoryIds));

                $.ajax({
                    url: "{{ route('categories.update') }}",
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
                            showSweetAlert('success', 'Updated !','Category has been updated successfully.');
                            $('#edit-category-modal').modal('hide');
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

            // delete category 
            $(document).on('click', '.delete-category-btn', function() {
                var categoryId = $(this).data('category-id');
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it!',
                    customClass: {
                        confirmButton: 'btn btn-primary me-3',
                        cancelButton: 'btn btn-outline-secondary'
                    },
                    buttonsStyling: false
                }).then(function(result) {
                    if (result.value) {
                        $.ajax({
                            url: "{{ route('categories.delete') }}",
                            type: "POST",
                            data: {
                                _token: '{{ csrf_token() }}',
                                category_id: categoryId
                            },
                            beforeSend: function () {
                                showBSPLoader();
                            },
                            complete: function () {
                                hideBSPLoader();
                            },
                            success: function(response) {
                                if (response.success == true) {
                                    showSweetAlert('success', 'Deleted!', 'Category has been deleted.');
                                    CategoriesDataTable();
                                } else {
                                    showSweetAlert('error', 'Error!', 'Something went wrong.');
                                }
                            },
                            error: function(xhr, status, error) {
                                hideBSPLoader();
                                console.log(xhr.responseText);
                                showSweetAlert('error', 'Error!', 'Something went wrong.');
                            }
                        });
                    }
                });
            });
            // ----------------------------------------------------------


            // add subcategory
            let subcategoryCount = 0;
            $(document).on('click', '#add-subcategory-btn', function() {
                subcategoryCount++;
                var subcategoriesContainer = $('#subcategories-container');
                var subcategoryHtml = `
                    <div class="col-12 mt-2 subcategory-item">
                        <div class="input-group input-group-merge">
                            <div class="form-floating form-floating-outline">
                                <input
                                    type="text"
                                    class="form-control subcategory-name"
                                    id="subcategory_name_${subcategoryCount}"
                                    name="subcategory_name[${subcategoryCount}]"
                                    placeholder="Subcategory Name"
                                    aria-describedby="subcategory_name_${subcategoryCount}" />
                                <label for="subcategory_name_${subcategoryCount}">Subcategory Name ${subcategoryCount}</label>
                            </div>
                            <span class="input-group-text text-danger cursor-pointer remove-subcategory-btn"><i class="ri-delete-bin-line"></i></span>
                        </div>
                    </div>
                `;
                subcategoriesContainer.append(subcategoryHtml);

                // also add validation for subcategory name
                addCategoryFV.revalidateField('subcategory_name');
                addCategoryFV.addField(`subcategory_name[${subcategoryCount}]`, {
                    validators: {
                        notEmpty: {
                            message: 'Subcategory ' + subcategoryCount + ' name is required'
                        }
                    }
                });
            });
            // ----------------------------------------------------------

            // remove subcategory
            $(document).on('click', '.remove-subcategory-btn', function() {
                $(this).parent().parent().remove();
                subcategoryCount--;
                addCategoryFV.removeField(`subcategory_name[${subcategoryCount}]`);
            });
            // ----------------------------------------------------------

            // add edit subcategory
            // new category have subcategory id = 0
            $(document).on('click', '#edit-add-subcategory-btn', function() {
                editSubcategoryCount++;

                var editSubcategoriesContainer = $('#edit-subcategories-container');
                var subcategoryHtml = `
                    <div class="col-12 mt-2 edit-subcategory-item">
                        <div class="input-group input-group-merge">
                            <div class="form-floating form-floating-outline">
                                <input
                                    type="text"
                                    class="form-control edit-subcategory-name"
                                    id="edit_subcategory_name_${editSubcategoryCount}"
                                    name="edit_subcategory_name[${editSubcategoryCount}]"
                                    data-subcategory-id="0"
                                    placeholder="Subcategory Name"
                                    aria-describedby="edit_subcategory_name_${editSubcategoryCount}" />
                                <label for="edit_subcategory_name_${editSubcategoryCount}">Subcategory Name ${editSubcategoryCount}</label>
                            </div>
                            <span class="input-group-text text-danger cursor-pointer remove-edit-subcategory-btn"><i class="ri-delete-bin-line"></i></span>
                        </div>
                    </div>
                `;
                editSubcategoriesContainer.append(subcategoryHtml);

                // also add validation for subcategory name
                categoryEditFV.revalidateField('edit_subcategory_name');
                categoryEditFV.addField(`edit_subcategory_name[${editSubcategoryCount}]`, {
                    validators: {
                        notEmpty: {
                            message: 'Subcategory ' + editSubcategoryCount + ' name is required'
                        }
                    }
                });
            });
            // ----------------------------------------------------------
            
            // remove edit subcategory
            $(document).on('click', '.remove-edit-subcategory-btn', function() {
                $(this).parent().parent().remove();
                editSubcategoryCount--;
                categoryEditFV.removeField(`edit_subcategory_name[${editSubcategoryCount}]`);
            });
            // ----------------------------------------------------------
        });
    </script>
@endsection
