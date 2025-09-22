@extends('layouts/layoutMaster')

@section('title', 'Dummy FCA Number')

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
                <h5 class="card-title mb-0">Dummy FCA Number</h5>
            </div>
            <div class="dt-action-buttons text-end pt-3 pt-md-0">
                <div class="dt-buttons btn-group flex-wrap"> 
                    <button class="btn btn-secondary btn-primary waves-effect waves-light" type="button" id="dummy-fca-add-btn"
                        data-bs-toggle="tooltip" data-bs-placement="bottom" title="Add Dummy FCA Number">
                        <span>
                            <i class="ri-add-line ri-16px me-sm-2"></i>
                            <span class="d-none d-sm-inline-block">Add Dummy FCA Number</span>
                        </span>
                    </button> 
                </div>
            </div>
        </div>
        <div class="card-datatable table-responsive">
            <table class="dt-fixedheader table table-bordered" id="dummy-fca-data-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>FCA Number</th>
                        <th>Company Name</th>
                        <th>User</th>
                        <th class="table-action-col">Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
    <!--/ Main Table -->

    {{-- add fca number modal --}}
    <div class="modal fade" id="add-dummy-fca-modal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-simple">
            <div class="modal-content">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="modal-body p-0">
                    <div class="text-center mb-6">
                        <h4 class="mb-2">Add Dummy FCA Number</h4>
                    </div>
                    <form id="add-dummy-fca-form" class="row g-5" method="POST">
                        @csrf
                        <div class="col-12">
                            <div class="form-floating form-floating-outline">
                                <input type="text" id="fca_number" name="fca_number" class="form-control"
                                    placeholder="FCA Number" required />
                                <label for="fca_number">FCA Number</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-floating form-floating-outline">
                                <input type="text" id="company_name" name="company_name" class="form-control"
                                    placeholder="Company Name" required />
                                <label for="company_name">Company Name</label>
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

    {{-- edit fca number modal --}}
    <div class="modal fade" id="edit-dummy-fca-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-simple">
            <div class="modal-content">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="modal-body p-0">
                    <div class="text-center mb-6">
                        <h4 class="mb-2">Edit Dummy FCA Number</h4>
                    </div>
                    <form id="edit-dummy-fca-form" class="row g-5" method="POST">
                        @csrf
                        <input type="hidden" name="edit_fca_number_id" id="edit_fca_number_id">
                        <div class="col-12">
                            <div class="form-floating form-floating-outline">
                                <input type="text" id="edit_fca_number" name="edit_fca_number" class="form-control"
                                    placeholder="FCA Number" required />
                                <label for="edit_fca_number">FCA Number</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-floating form-floating-outline">
                                <input type="text" id="edit_company_name" name="edit_company_name" class="form-control"
                                    placeholder="Company Name" required />
                                <label for="edit_company_name">Company Name</label>
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
            DummyFCANumberDataTable();

            // contact data table function
            function DummyFCANumberDataTable() {
                var ContactTable = $('#dummy-fca-data-table').DataTable({
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
                        url: "{{ route('dummy-fca-number') }}",
                        beforeSend: function () {
                            showBSPLoader();
                        },
                        complete: function () {
                            hideBSPLoader();
                        }
                    },
                    columns: [
                        { data: 'DT_RowIndex', name: 'DT_RowIndex'},
                        { data: 'fca_number', name: 'fca_number'},
                        { data: 'company_name', name: 'company_name'},
                        { data: 'user', name: 'user'},
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

            // add fca number function
            $(document).on('click','#dummy-fca-add-btn', function () {
                // reset the form
                $('#add-dummy-fca-form')[0].reset();
                // reset the form validation
                addDummyFCANumberFV.resetForm();

                $("#custom_label_div").addClass('d-none');

                $('#add-dummy-fca-modal').modal('show');
            });
            // -------------------------------------------
            
            // validate the submit form
            const addDummyFcaNumberForm = document.getElementById('add-dummy-fca-form');
            const addDummyFCANumberFV = FormValidation.formValidation(addDummyFcaNumberForm, {
                fields: {
                    fca_number: {
                        validators: {
                            notEmpty: {
                                message: 'Please enter your fca number'
                            },
                            stringLength: {
                                min: 6,
                                max: 7,
                                message: 'FCA number must be between 6 and 7 characters'
                            }
                        }
                    },
                    company_name: {
                        validators: {
                            notEmpty: {
                                message: 'Please enter company name'
                            },
                            stringLength: {
                                max: 150,
                                message: 'Company name must be less than 150 characters'
                            }
                        }
                    },
                },
                plugins: {
                    trigger: new FormValidation.plugins.Trigger(),
                    bootstrap5: new FormValidation.plugins.Bootstrap5({
                        eleValidClass: '',
                        rowSelector: function(field, ele) {
                            // Customize row selector based on your form layout
                            if (['fca_number', 'company_name'].includes(field)) {
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
                var form = $('#add-dummy-fca-form');
                var formData = new FormData(form[0]); // Creates FormData object

                $.ajax({
                    url: "{{ route('dummy-fca-number.store') }}",
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
                            showSweetAlert('success', 'Created !','Dummy FCA has been created successfully.');
                            $('#add-dummy-fca-modal').modal('hide');
                            DummyFCANumberDataTable();
                        } else {
                            showSweetAlert('error', 'Warning !', response.message);
                            $('#add-dummy-fca-modal').modal('hide');
                            DummyFCANumberDataTable();
                        }
                    },
                    error: function(xhr) {
                        hideBSPLoader();
                        if (xhr.status === 422) {
                            // Validation failed
                            var errors = xhr.responseJSON.errors;
                            var messages = "";

                            $.each(errors, function(key, value) {
                                messages += value[0]; // take the first message of each field
                            });

                            showSweetAlert('error', 'Validation Error', messages);
                        } else {
                            // Other error
                            console.log(xhr.responseText);
                            showSweetAlert('error', 'Error !', 'Something went wrong.');
                        }
                    }
                });
            });
            // ----------------------------------------------------------

            // Edit User modal
            $(document).on('click', '.edit-fca-number-btn', function() {
                var fcaNumberId = $(this).data('fca-number-id');
                var editUrl = "{{ url('/dummy-fca-number/edit/') }}/" + fcaNumberId;
                $.ajax({
                    url: editUrl,
                    type: "GET",
                    success: function(response) {
                        if (response.success == true) {
                            $('#edit_fca_number').val(response.data.fca_number);
                            $('#edit_fca_number_id').val(fcaNumberId);
                            $('#edit_company_name').val(response.data.company_name);
                        } else {
                            // toastr.error(response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log(xhr.responseText);
                    }
                });
                // clear edit user form validation
            
                $('#edit-dummy-fca-modal').modal('show');
            });
            // -------------------------------------------

            // edit form validation & submission
            var editDummyFcaNumberForm = document.getElementById('edit-dummy-fca-form');
            var dummyFcaEditFV = FormValidation.formValidation( editDummyFcaNumberForm,
                {
                    fields: {
                        edit_fca_number: {
                            validators: {
                                notEmpty: {
                                    message: 'Please enter your fca number'
                                },
                                stringLength: {
                                    min: 6,
                                    max: 7,
                                    message: 'FCA number must be between 6 and 7 characters'
                                }
                            }
                        },
                        edit_company_name: {
                            validators: {
                                notEmpty: {
                                    message: 'Please enter company name'
                                },
                                stringLength: {
                                    max: 150,
                                    message: 'Company name must be less than 150 characters'
                                }
                            }
                        },
                    },
                    plugins: {
                        trigger: new FormValidation.plugins.Trigger(),
                        bootstrap5: new FormValidation.plugins.Bootstrap5({
                            eleValidClass: '',
                            rowSelector: function(field, ele) {
                                // Customize row selector based on your form layout
                                if (['edit_fca_number', 'edit_company_name'].includes(field)) {
                                    return '.col-12';
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
                var form = $('#edit-dummy-fca-form');
                var formData = new FormData(form[0]); // Creates FormData object

                var table = $('#dummy-fca-data-table').DataTable();

                $.ajax({
                    url: "{{ route('dummy-fca-number.update') }}",
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
                            DummyFCANumberDataTable();
                            showSweetAlert('success', 'Updated !','Dummy FCA number has been updated successfully.');
                            $('#edit-dummy-fca-modal').modal('hide');
                        } else {
                            showSweetAlert('error', 'Warning !', response.message);
                            $('#edit-dummy-fca-modal').modal('hide');
                            DummyFCANumberDataTable();
                        }
                    },
                    error: function(xhr) {
                        hideBSPLoader();
                        if (xhr.status === 422) {
                            // Validation failed
                            var errors = xhr.responseJSON.errors;
                            var messages = "";

                            $.each(errors, function(key, value) {
                                messages += value[0]; // take the first message of each field
                            });

                            showSweetAlert('error', 'Validation Error', messages);
                        } else {
                            // Other error
                            console.log(xhr.responseText);
                            showSweetAlert('error', 'Error !', 'Something went wrong.');
                        }
                    }
                });
            });
            // ----------------------------------------------------------

            // delete fca number
            $(document).on('click', '.delete-fca-number-btn', function() {
                var fcaNumberId = $(this).data('fca-number-id');
                var table = $('#dummy-fca-data-table').DataTable();

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
                            url: "{{ route('dummy-fca-number.delete') }}",
                            type: "POST",
                            data: {
                                _token: '{{ csrf_token() }}',
                                fca_number_id: fcaNumberId
                            },
                            beforeSend: function () {
                                showBSPLoader();
                            },
                            complete: function () {
                                hideBSPLoader();
                            },
                            success: function(response) {
                                if (response.success == true) {
                                    DummyFCANumberDataTable();
                                    showSweetAlert('success', 'Deleted!', 'FCA number has been deleted.');
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

        });
    </script>
@endsection
