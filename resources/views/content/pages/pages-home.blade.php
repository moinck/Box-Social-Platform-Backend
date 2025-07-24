@extends('layouts/layoutMaster')

@section('title', 'User Management')

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
                <h5 class="card-title mb-0">User Management</h5>
            </div>
            <div class="dt-action-buttons text-end pt-3 pt-md-0">
                <div class="dt-buttons btn-group flex-wrap"> 
                    <button class="btn btn-secondary btn-primary waves-effect waves-light" type="button" id="user-export-btn"
                        data-bs-toggle="tooltip" data-bs-placement="bottom"
                        title="Export User Management Data">
                        <span>
                            <i class="ri-upload-2-line ri-16px me-sm-2"></i>
                            <span class="d-none d-sm-inline-block">Export Data</span>
                        </span>
                    </button> 
                </div>
            </div>
        </div>
        <div class="card-datatable table-responsive">
            <table class="dt-fixedheader table table-bordered" id="user-data-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Full Name</th>
                        <th>Company Name</th>
                        <th>Email Address</th>
                        <th>FCA number</th>
                        <th>Created Date</th>
                        <th>Account Status</th>
                        <th class="table-action-col">Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
    <!--/ Main Table -->

    <!-- Edit User Modal -->
    <div class="modal fade" id="edit-user-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-simple">
            <div class="modal-content">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="modal-body p-0">
                    <div class="text-center mb-6">
                        <h4 class="mb-2">Edit User Information</h4>
                    </div>
                    <form id="edit-user-form" class="row g-5" method="POST">
                        @csrf
                        <div class="col-12 col-md-6">
                            <div class="form-floating form-floating-outline">
                                <input type="text" id="edit_first_name" name="edit_first_name" class="form-control"
                                    placeholder="First Name" required />
                                <label for="edit_first_name">First Name</label>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-floating form-floating-outline">
                                <input type="text" id="edit_last_name" name="edit_last_name" class="form-control"
                                    placeholder="Last Name" />
                                <label for="edit_last_name">Last Name</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-floating form-floating-outline">
                                <input type="text" id="edit_company_name" name="edit_company_name" class="form-control"
                                    placeholder="Company Name" />
                                <label for="edit_company_name">Company Name</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-floating form-floating-outline">
                                <input type="email" id="edit_user_email" name="edit_user_email" class="form-control"
                                    placeholder="User Email" />
                                <label for="edit_user_email">Email</label>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" value="user_has_brandkit" id="user_has_brandkit" checked="" onclick="return false;">
                                <label class="form-check-label" for="user_has_brandkit">
                                    <span>User has Brandkit</span>
                                </label>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" value="user_is_verified" id="user_is_verified" checked="" onclick="return false;">
                                <label class="form-check-label" for="user_is_verified">
                                    <span>User is Verified</span>
                                </label>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-floating form-floating-outline">
                                <input type="text" id="user_fca_number" name="user_fca_number" class="form-control"
                                    placeholder="123456789" />
                                <label for="user_fca_number">FCA No.</label>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-floating form-floating-outline">
                                <select id="user_account_status" name="user_account_status" class="form-select"
                                    aria-label="Default select example">
                                    <option value="">Select Status</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                                <label for="user_account_status">Account Status</label>
                            </div>
                        </div>
                        <input type="hidden" name="edit_user_id" id="edit_user_id">
                        <div class="col-12 text-center d-flex flex-wrap justify-content-center gap-4 row-gap-4">
                            <button type="submit" class="btn btn-primary">Submit</button>
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
    <!--/ Edit User Modal -->

    {{-- data export modal --}}
    <div class="modal fade" id="data-export-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modalCenterTitle">Export Data</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col mb-6 mt-2 text-center">
                            <h4 class="mb-4">Select Export Format</h4>
                            <div class="d-flex justify-content-center gap-4">
                                <button type="button" id="csv-export-btn" title="Export users in CSV format" class="btn btn-primary">CSV</button>
                                <button type="button" id="excel-export-btn" title="Export users in Excel format" class="btn btn-primary">Excel</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!--/ Select -->
@endsection

<!-- Page Scripts -->
@section('page-script')
    {{-- @vite(['resources/assets/js/tables-datatables-extensions.js']) --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script>
        $(document).ready(function() {
            UserDataTable();

            // user data table function
            function UserDataTable() {
                var UserTable = $('#user-data-table').DataTable({
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
                        url: "{{ route('user.data-table') }}",
                        data: function (d) {
                            d.is_brandkit = $('#brandkit_filter').val();
                            d.account_status = $('#account_status_filter').val();
                        },
                        beforeSend: function () {
                            showBSPLoader();
                        },
                        complete: function () {
                            hideBSPLoader();
                        },
                    },
                    initComplete: function(settings, json) {
                        // Target the first col-md-6 div within the DataTable wrapper
                        var targetDiv = $('#user-data-table_wrapper .row:first .col-sm-12.col-md-6:first-child');
                        targetDiv.prop('style','margin-top:1.25rem;margin-bottom:1.25rem');

                        // Create a row to hold the two select filters
                        targetDiv.append(`
                            <div class="row">
                                <div class="col-md-6" id="brandkit-filter-container"></div>
                                <div class="col-md-6" id="account-status-filter-container"></div>
                            </div>`);

                        // Append brandkit filter
                        $('#brandkit-filter-container').append(`
                            <select class="form-select input-sm" id="brandkit_filter">
                                <option value="">User Brand Configuration</option>
                                <option value="1">Configured</option>
                                <option value="2">Not Configured</option>
                            </select>
                        `);

                        // Filter results on brandkit select change
                        $('#brandkit_filter').on('change', function() {
                            UserTable.draw();
                        });

                        // Append account status filter
                        $('#account-status-filter-container').append(`
                            <select class="form-select input-sm" id="account_status_filter">
                                <option value="">Account Status</option>
                                <option value="1">Active</option>
                                <option value="2">Inactive</option>
                            </select>
                        `);

                        // Filter results on account status select change
                        $('#account_status_filter').on('change', function() {
                            UserTable.draw();
                        });
                    },
                    columns: [
                        { data: 'DT_RowIndex', name: 'DT_RowIndex'},
                        { data: 'name', name: 'name'},
                        { data: 'company_name', name: 'company_name'},
                        { data: 'email', name: 'email'},
                        { data: 'fca_number', name: 'fca_number'},
                        { data: 'created_date', name: 'created_date'},
                        { data: 'account_status', name: 'account_status', orderable: false, searchable: false},
                        { data: 'action', name: 'action', orderable: false, searchable: false}
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

            // account status switch change
            $(document).on('change', '#user-account-status', function() {
                var status = $(this).is(':checked') ? 1 : 0;
                var userId = $(this).data('id');
                $.ajax({
                    url: "{{ route('user.account-status') }}",
                    type: "POST",
                    data: {
                        status: status,
                        userId: userId,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success == true) {
                            showSweetAlert('success', 'Updated!', 'User account status has been updated successfully.');
                            UserDataTable();
                        } else {
                            showSweetAlert('error', 'Error!', 'Something went wrong.');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log(xhr.responseText);
                        showSweetAlert('error', 'Error!', 'Something went wrong.');
                    }
                });
            });
            // -------------------------------------------

            // Edit User modal
            $(document).on('click', '.edit-user-btn', function() {
                var userId = $(this).data('user-id');
                var editUrl = "{{ url('/user/edit/') }}/" + userId;
                $.ajax({
                    url: editUrl,
                    type: "GET",
                    success: function(response) {
                        if (response.success == true) {
                            $('#edit_first_name').val(response.data.first_name);
                            $('#edit_last_name').val(response.data.last_name);
                            $('#edit_company_name').val(response.data.company_name);
                            $('#edit_user_email').val(response.data.email);
                            $('#user_fca_number').val(response.data.fca_number);
                            var accountStatus = response.data.status;
                            if (accountStatus == 'active') {
                                $('#user_account_status').val('active');
                            } else {
                                $('#user_account_status').val('inactive');
                            }

                            $('#user_has_brandkit').prop('checked', response.data.has_brandkit);
                            $('#user_is_verified').prop('checked', response.data.is_verified);
                            $('#edit_user_id').val(userId);
                        } else {
                            // toastr.error(response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log(xhr.responseText);
                    }
                });
                // clear edit user form validation
                userEditFV.resetForm();
                $('#edit-user-modal').modal('show');
            });
            // -------------------------------------------

            // var editUserForm = $('#edit-user-form');
            const formValidationExamples = document.getElementById('edit-user-form');

            const userEditFV = FormValidation.formValidation(formValidationExamples, {
                fields: {
                    edit_first_name: {
                        validators: {
                            notEmpty: {
                                message: 'Please enter your first name'
                            },
                            regexp: {
                                regexp: /^[a-zA-Z\s'-]+$/,
                                message: 'First name can only contain letters, spaces, hyphens, and apostrophes'
                            },
                            stringLength: {
                                min: 2,
                                max: 50,
                                message: 'First name must be between 2 and 50 characters'
                            }
                        }
                    },
                    edit_last_name: {
                        validators: {
                            notEmpty: {
                                message: 'Please enter your last name'
                            },
                            regexp: {
                                regexp: /^[a-zA-Z\s'-]*$/,
                                message: 'Last name can only contain letters, spaces, hyphens, and apostrophes'
                            },
                            stringLength: {
                                max: 50,
                                message: 'Last name must be less than 50 characters'
                            }
                        }
                    },
                    edit_company_name: {
                        validators: {
                            notEmpty: {
                                message: 'Please enter your company name'
                            },
                            regexp: {
                                regexp: /^[a-zA-Z0-9\s&.,'-]*$/,
                                message: 'Company name contains invalid characters'
                            },
                            stringLength: {
                                max: 100,
                                message: 'Company name must be less than 100 characters'
                            }
                        }
                    },
                    edit_user_email: {
                        validators: {
                            notEmpty: {
                                message: 'Please enter your email'
                            },
                            emailAddress: {
                                message: 'Please enter a valid email address'
                            },
                            stringLength: {
                                max: 100,
                                message: 'Email must be less than 100 characters'
                            }
                        }
                    },
                    user_fca_number: {
                        validators: {
                            notEmpty: {
                                message: 'Please enter your FCA Number'
                            },
                            regexp: {
                                regexp: /^[0-9]*$/,
                                message: 'FCA Number can only contain digits'
                            },
                            stringLength: {
                                min: 5,
                                max: 30,
                                message: 'FCA Number must be between 5 and 30 digits'
                            }
                        }
                    },
                    user_account_status: {
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
                            if (['edit_first_name', 'edit_last_name', 'user_fca_number',
                                    'user_account_status'
                                ].includes(field)) {
                                return '.col-md-6';
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
                var form = $('#edit-user-form');
                var formData = new FormData(form[0]); // Creates FormData object

                $.ajax({
                    url: "{{ route('user.update') }}",
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
                            showSweetAlert('success', 'Updated !',
                                'User has been updated successfully.');
                            $('#edit-user-modal').modal('hide');
                            UserDataTable();
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

            // delete user 
            $(document).on('click', '.delete-user-btn', function() {
                var userId = $(this).data('user-id');
                var userName = $(this).data('user-name');
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You want to delete " + userName + " account!",
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
                            url: "{{ route('user.delete') }}",
                            type: "POST",
                            data: {
                                _token: '{{ csrf_token() }}',
                                user_id: userId
                            },
                            beforeSend: function () {
                                showBSPLoader();
                            },
                            complete: function () {
                                hideBSPLoader();
                            },
                            success: function(response) {
                                if (response.success == true) {
                                    showSweetAlert('success', 'Deleted!', 'User has been deleted.');
                                    UserDataTable();
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

            // export user 
            $(document).on('click', '#user-export-btn', function() {
                $('#data-export-modal').modal('show');
            });
            $('#csv-export-btn').on('click', function() {
                ExportUserData('csv');
            });
            $('#excel-export-btn').on('click', function() {
                ExportUserData('xlsx');
            });
            // ----------------------------------------------------------

            // export user function
            function ExportUserData(format) {
                var exportFormData = new FormData();
                exportFormData.append('format', format);
                exportFormData.append('_token', '{{ csrf_token() }}');
                exportFormData.append('is_brandkit', $('#brandkit_filter').val());
                exportFormData.append('account_status', $('#account_status_filter').val());
                exportFormData.append('user_table_search', $('input[type="search"]').val());

                var xhr = new XMLHttpRequest();
                xhr.open("POST", "{{ route('user.export') }}", true);
                xhr.responseType = 'blob';

                xhr.onload = function () {
                    hideBSPLoader();
                    if (xhr.status === 200) {
                        var blob = xhr.response;
                        var url = window.URL.createObjectURL(blob);
                        var a = document.createElement('a');
                        a.href = url;
                        a.download = 'users_' + new Date().getTime() + '.' + format;
                        document.body.appendChild(a);
                        a.click();
                        a.remove();
                        window.URL.revokeObjectURL(url);
                        $('#data-export-modal').modal('hide');
                    } else {
                        showSweetAlert('error', 'Error!', 'Something went wrong.');
                    }
                };

                xhr.onerror = function () {
                    hideBSPLoader();
                    showSweetAlert('error', 'Error!', 'Something went wrong.');
                };

                showBSPLoader();
                xhr.send(exportFormData);
            }
            // ----------------------------------------------------------
        });
    </script>
@endsection
