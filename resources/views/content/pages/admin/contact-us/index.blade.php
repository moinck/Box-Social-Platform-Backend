@extends('layouts/layoutMaster')

@section('title', 'Feedback Management')

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
    <style>
        table.dataTable thead tr th.checkbox-th:before {
            opacity: 0;
        }
    </style>
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
                <h5 class="card-title mb-0">Feedback Management</h5>
            </div>
            <div class="dt-action-buttons text-end pt-3 pt-md-0">
                <div class="dt-buttons btn-group flex-wrap"> 
                    <button class="btn btn-secondary btn-danger waves-effect waves-light d-none" type="button" id="contact-us-delete-btn" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="Delete User Feedback Data">
                        <span>
                            <i class="ri-delete-bin-line ri-16px me-sm-2" style="vertical-align: baseline;"></i>
                            <span class="d-none d-sm-inline-block">Delete</span>
                        </span>
                    </button> 
                </div>
            </div>
        </div>
        <div class="card-datatable table-responsive">
            <table class="dt-fixedheader table table-bordered" id="contact-us-data-table">
                <thead>
                    <tr>
                        <th class="col-1 checkbox-th">
                            <input type="checkbox" class="form-check-input" id="select-all" title="Select All" style="width: 1.1rem;">
                        </th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Company Name</th>
                        <th>Message</th>
                        <th class="table-date-col">Created Date</th>
                        <th class="table-action-col">Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
    <!--/ Main Table -->

    <!-- Start Send Feedback Reply -->
    <div class="modal fade" id="feedback-reply-modal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-simple">
            <div class="modal-content">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="modal-body p-0">
                    <div class="text-center mb-6">
                        <h4 class="mb-2">Feedback Reply</h4>
                    </div>
                    <form id="feedback-reply-form" class="row g-5" method="POST">
                        @csrf
                        <div class="col-12">
                            <div class="form-floating form-floating-outline">
                                <input type="email" class="form-control" id="email" name="email" placeholder="Enter email" required>
                                <label for="email">Email</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-floating form-floating-outline">
                                <input type="text" class="form-control" id="subject" name="subject" placeholder="Enter subject" required>
                                <label for="subject">Email Subject</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-floating form-floating-outline">
                                <textarea id="message" rows="3" name="message" class="form-control h-px-75"
                                    placeholder="Enter your message"></textarea>
                                <label for="message">Message</label>
                            </div>
                        </div>
                        <input type="hidden" id="feedback_id" name="feedback_id" value="">
                        <div class="col-12 text-center d-flex flex-wrap justify-content-center gap-4 row-gap-4 form-btn">
                            <button type="submit" class="btn btn-primary">Send</button>
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
    <!-- End Send Feedback Reply -->


@endsection

<!-- Page Scripts -->
@section('page-script')
    {{-- @vite(['resources/assets/js/tables-datatables-extensions.js']) --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script>
        $(document).ready(function() {
            ContactUsDataTable();

            // contact data table function
            function ContactUsDataTable() {
                var ContactTable = $('#contact-us-data-table').DataTable({
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
                        url: "{{ route('feedback-management.data-table') }}",
                        beforeSend: function () {
                            showBSPLoader();
                        },
                        complete: function () {
                            hideBSPLoader();
                        }
                    },
                    columns: [
                        { data: 'checkbox', name: 'checkbox', orderable: false, searchable: false},
                        { data: 'name', name: 'name'},
                        { data: 'email', name: 'email'},
                        { data: 'company_name', name: 'company_name'},
                        { data: 'message', name: 'message'},
                        { data: 'created_date', name: 'created_date'},
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
                        $('#select-all').prop('checked', false);
                        $('#contact-us-delete-btn').addClass('d-none');
                    }
                });
            }
            // -------------------------------------------

            // show hide btn
            $(document).on('change', '.contact-us-checkbox', function () {
                if ($('.contact-us-checkbox:checked').length > 0) {
                    $('#contact-us-delete-btn').removeClass('d-none');
                } else {
                    $('#contact-us-delete-btn').addClass('d-none');
                }
            });

            // select all
            $(document).on('click', '#select-all', function () {
                $('.contact-us-checkbox').prop('checked', this.checked);
                if ($('.contact-us-checkbox:checked').length > 0) {
                    $('#contact-us-delete-btn').removeClass('d-none');
                } else {
                    $('#contact-us-delete-btn').addClass('d-none');
                }
            });
            // -------------------------------------------

            // delete selected feedback
            $(document).on('click', '#contact-us-delete-btn', function () {
                var table = $('#contact-us-data-table').DataTable();

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
                        var selectedIds = $('.contact-us-checkbox:checked').map(function () {
                            return $(this).val();
                        }).get();
        
                        if (selectedIds.length === 0) {
                            toastr.error('Please select at least one feedback');
                            return;
                        }
                        
                        $.ajax({
                            url: "{{ route('feedback-management.delete') }}",
                            type: "POST",
                            data: {
                                contact_us_ids: selectedIds,
                                _token: "{{ csrf_token() }}"
                            },
                            beforeSend: function() {
                                showBSPLoader();
                            },
                            complete: function() {
                                hideBSPLoader();
                            },
                            success: function (response) {
                                showSweetAlert('success', 'Deleted!', response.message);
                                // ContactUsDataTable();
                                reloadDataTablePreservingPage(table);

                            },
                            error: function (xhr, status, error) {
                                toastr.error(error);
                            }
                        });
                    } else {
                        // unchecked all
                        $('#select-all').prop('checked', false);
                        $('.contact-us-checkbox').prop('checked', false);
                        $('#contact-us-delete-btn').addClass('d-none');
                    }
                });
            });
            // -------------------------------------------

            //View Feedback Reply Modal
            $(document).on('click', '.view-reply-btn', function() {
                var feedBackId = $(this).data('feedback-id');
                var editUrl = "{{ url('/feedback-management/view-reply/') }}/" + feedBackId;
                $.ajax({
                    url: editUrl,
                    type: "GET",
                    success: function(response) {
                        if (response.success == true) {
                            $(".form-btn").addClass('d-none');
                            $("#email").val(response.data.email).attr('readonly', true);
                            $("#subject").val(response.data.email_subject).attr('readonly', true);
                            $("#message").val(response.data.feedback_reply).attr('readonly', true);
                            $('#feedback-reply-modal').modal('show');
                        } else {
                            // toastr.error(response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log(xhr.responseText);
                    }
                });
            });
            // -------------------------------------------

            //Send Feedback Reply Modal
            $(document).on('click', '.send-reply-btn', function() {
                var feedBackId = $(this).data('feedback-id');
                var table = $('#contact-us-data-table').DataTable();

                // Reset form first
                $("#feedback-reply-form")[0].reset();
                $(".form-btn").removeClass('d-none');

                // Make inputs editable
                $("#subject").attr('readonly', false);
                $("#message").attr('readonly', false);

                // Get row data
                var rowData = table.row($(this).parents('tr')).data();

                // Set values
                $("#feedback_id").val(feedBackId);
                $("#email").val(rowData.email);

                // Show modal
                $('#feedback-reply-modal').modal('show');
            });
            // -------------------------------------------

            // validate the submit form
            const sendFeedbackForm = document.getElementById('feedback-reply-form');
            const sendFeedbackFV = FormValidation.formValidation(sendFeedbackForm, {
                fields: {
                    email: {
                        validators: {
                            notEmpty: {
                                message: 'Please enter your email'
                            },
                            emailAddress: {
                                message: 'Please enter a valid email address'
                            },
                            stringLength: {
                                max: 50,
                                message: 'Email must be less than 50 characters'
                            }
                        }
                    },
                    subject: {
                        validators: {
                            notEmpty: {
                                message: 'Please enter your email subject'
                            },
                            stringLength: {
                                min: 2,
                                max: 150,
                                message: 'Subject must be between 2 and 150 characters'
                            }
                        }
                    },
                    message: {
                        validators: {
                            notEmpty: {
                                message: 'Please enter your message'
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
                            if (['email', 'subject', 'message'].includes(field)) {
                                return '.col-12';
                            }
                            return '.col-12';
                        }
                    }),
                    submitButton: new FormValidation.plugins.SubmitButton(),
                }
            }).on('core.form.valid', function() {
                // Form is valid, proceed with form submission
                var form = $('#feedback-reply-form');
                var formData = new FormData(form[0]); // Creates FormData object
                var table = $('#contact-us-data-table').DataTable();

                $.ajax({
                    url: "{{ route('feedback-management.send-reply') }}",
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
                            showSweetAlert('success', 'Send !','Feedback reply send successfully.');
                            $('#feedback-reply-modal').modal('hide');
                            reloadDataTablePreservingPage(table);
                        }
                    },
                    error: function(xhr) {
                        hideBSPLoader();
                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;
                            let firstError = xhr.responseJSON.message;

                            // Show first error in SweetAlert
                            showSweetAlert('error', 'Validation Error!', firstError);

                            // Or loop through all errors and display
                            $.each(errors, function(key, value) {
                                console.log(key + ": " + value[0]);
                                // You could also place them near the form field if you want
                            });
                        } else {
                            showSweetAlert('error', 'Error!', 'Something went wrong.');
                        }
                    }
                });
            });
            // ----------------------------------------------------------

        });
    </script>
@endsection
