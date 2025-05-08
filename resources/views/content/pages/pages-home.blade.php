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
        {{-- <h5 class="card-header text-center text-md-start pb-md-0">User Management</h5> --}}
        {{-- <div class="card-header border-bottom">
      
    </div> --}}
        <div class="card-header flex-column flex-md-row border-bottom">
            <div class="head-label">
                <h5 class="card-title mb-0">User Management</h5>
            </div>
            {{-- <div class="dt-action-buttons text-end pt-3 pt-md-0">
            <div class="dt-buttons btn-group flex-wrap"> 
                <button class="btn btn-secondary create-new btn-primary waves-effect waves-light" tabindex="0" aria-controls="DataTables_Table_0" type="button"><span><i class="ri-add-line ri-16px me-sm-2"></i> <span class="d-none d-sm-inline-block">Add New Record</span></span></button> 
            </div>
        </div> --}}
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
                        <th>Action</th>
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
                    info: false,
                    paging: true,
                    pageLength: 10,
                    ajax: "{{ route('user.data-table') }}",
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
                    success: function(response) {
                        if (response.success == true) {
                            showSweetAlert('success', 'Updated !',
                                'User has been updated successfully.');
                            $('#edit-user-modal').modal('hide');
                            UserDataTable();
                        }
                    },
                    error: function(xhr) {
                        console.log(xhr.responseText);
                        showSweetAlert('error', 'Error !', 'Something went wrong.');
                    }
                });
            });
            // ----------------------------------------------------------

            // delete user 
            $(document).on('click', '.delete-user-btn', function() {
                var userId = $(this).data('user-id');
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
                            url: "{{ route('user.delete') }}",
                            type: "POST",
                            data: {
                                _token: '{{ csrf_token() }}',
                                user_id: userId
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
                                console.log(xhr.responseText);
                                showSweetAlert('error', 'Error!', 'Something went wrong.');
                            }
                        });
                    }
                });
            });
            // ----------------------------------------------------------
        });
    </script>
@endsection
