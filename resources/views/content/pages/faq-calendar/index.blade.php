@extends('layouts/layoutMaster')

@section('title', 'FAQ Calendar Records')

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
                <h5 class="card-title mb-0">FAQ Calendar Records</h5>
            </div>
            <div class="dt-action-buttons text-end pt-3 pt-md-0">
                <div class="dt-buttons btn-group flex-wrap"> 
                    <button class="btn btn-secondary btn-primary waves-effect waves-light" type="button" id="faq-calendar-add-btn"
                        data-bs-toggle="tooltip" data-bs-placement="bottom" title="Add Calendar Record">
                        <span>
                            <i class="ri-add-line ri-16px me-sm-2"></i>
                            <span class="d-none d-sm-inline-block">Add Calendar Record</span>
                        </span>
                    </button> 
                </div>
            </div>
        </div>
        <div class="card-datatable table-responsive">
            <table class="dt-fixedheader table table-bordered" id="faq-calendar-data-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Year</th>
                        <th>Month</th>
                        <th>Image</th>
                        <th class="table-action-col">Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
    <!--/ Main Table -->

    {{-- add - edit video link modal --}}
    <div class="modal fade" id="add-faq-calendar-modal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-simple">
            <div class="modal-content">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="modal-body p-0">
                    <div class="text-center mb-6">
                        <h4 class="mb-2">Video Link</h4>
                    </div>
                    <form id="add-faq-calendar-form" class="row g-5" method="POST">
                        @csrf
                        <input type="hidden" name="faq_calendar_id" id="faq_calendar_id">
                        <div class="col-12">
                            <div class="form-floating form-floating-outline">
                                <input type="text" id="year" name="year" value="{{ \Carbon\Carbon::now()->format('Y') }}" class="form-control"
                                    placeholder="Year" required />
                                <label for="year">Year</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-floating form-floating-outline">
                                <select id="month" name="month" class="form-select"
                                    aria-label="Default select example">
                                    <option value="">Select Month</option>
                                    @foreach ($months as $key => $month)
                                        <option value="{{ $key }}">{{ $month }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-floating form-floating-outline">
                                <input type="file" id="image" name="image" class="form-control" placeholder="User Image" accept="image/*">
                                <label for="image">Image</label>
                            </div>
                        </div>
                        <input type="hidden" name="is_image_exists" id="is_image_exists" value="0">
                        <div class="col-12 text-center d-flex flex-wrap justify-content-center gap-4 row-gap-4">
                            <button type="submit" class="btn btn-primary">Save</button>
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
            FaqCalendarDataTable();

            // contact data table function
            function FaqCalendarDataTable() {
                var ContactTable = $('#faq-calendar-data-table').DataTable({
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
                        url: "{{ route('faq-calendar') }}",
                        beforeSend: function () {
                            showBSPLoader();
                        },
                        complete: function () {
                            hideBSPLoader();
                        }
                    },
                    columns: [
                        { data: 'DT_RowIndex', name: 'DT_RowIndex'},
                        { data: 'year', name: 'year'},
                        { data: 'month', name: 'month'},
                        { data: 'image_url', name: 'image_url'},
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
            
            // validate the submit form
            const addFaqCalendarForm = document.getElementById('add-faq-calendar-form');
            const addFaqCalendarFV = FormValidation.formValidation(addFaqCalendarForm, {
                fields: {
                    year: {
                        validators: {
                            notEmpty: {
                                message: 'Please enter year'
                            },
                            stringLength: {
                                min: 4,
                                max: 4,
                                message: 'Year must be 4 digits'
                            },
                            regexp: {
                                regexp: /^[0-9]{4}$/,
                                message: 'Year must contain only digits'
                            }
                        }
                    },
                    month: {
                        validators: {
                            notEmpty: {
                                message: 'Please select month'
                            },
                            regexp: {
                                regexp: /^(0?[1-9]|1[0-2])$/,
                                message: 'Please enter a valid month (1–12)'
                            }
                        }
                    },
                    image: {
                        validators: {
                            notEmpty: {
                                message: 'Please upload an image'
                            },
                            file: {
                                extension: 'jpg,jpeg,png,gif',
                                type: 'image/jpeg,image/png,image/gif',
                                maxSize: 2 * 1024 * 1024, // 2 MB
                                message: 'Please choose a valid image file (jpg, jpeg, png, gif) under 2MB'
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
                            if (['year', 'month', 'image'].includes(field)) {
                                return '.col-12';
                            }
                            return '.col-12';
                        }
                    }),
                    submitButton: new FormValidation.plugins.SubmitButton(),
                }
            }).on('core.form.valid', function() {
                // Form is valid, proceed with form submission
                var form = $('#add-faq-calendar-form');
                var formData = new FormData(form[0]); // Creates FormData object

                $.ajax({
                    url: "{{ route('faq-calendar.save') }}",
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
                            showSweetAlert('success', 'Saved !',response.message);
                            $('#add-faq-calendar-modal').modal('hide');
                            FaqCalendarDataTable();
                        } else {
                            showSweetAlert('error', 'Error !',response.message);
                            $('#add-faq-calendar-modal').modal('hide');
                            FaqCalendarDataTable();
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

            // add video link function
            $(document).on('click','#faq-calendar-add-btn', function () {
                $('#add-faq-calendar-form')[0].reset();
                $("#faq_calendar_id").val('');
                $("#is_image_exists").val(0);
                addFaqCalendarFV.resetForm();
                $('#add-faq-calendar-modal').modal('show');
            });
            // -------------------------------------------

            // Edit User modal
            $(document).on('click', '.edit-month-btn', function() {
                var calendarId = $(this).data('month-id');
                var editUrl = "{{ route('faq-calendar.edit', ':id') }}".replace(':id', calendarId);

                $.ajax({
                    url: editUrl,
                    type: "GET",
                    success: function(response) {
                        if (response.success === true) {
                            $('#year').val(response.data.year);
                            $('#month').val(response.data.month);
                            $('#faq_calendar_id').val(calendarId);
                            // If image exists, don’t require it
                            if (response.data.image_url) {
                                // Remove "required" validator for edit
                                $("#is_image_exists").val(1);                                
                                addFaqCalendarFV.updateValidatorOption('image', 'notEmpty', 'enabled', false);
                            } else {
                                // No image in DB → still required
                                addFaqCalendarFV.updateValidatorOption('image', 'notEmpty', 'enabled', true);
                            }

                            // Show modal for editing
                            $('#add-faq-calendar-modal').modal('show');
                        } else {
                            showSweetAlert('error', 'Error!', response.message);
                        }
                    },
                    error: function(xhr) {
                        console.log(xhr.responseText);
                        showSweetAlert('error', 'Error!', 'Something went wrong.');
                    }
                });
            });
            // -------------------------------------------

            // delete video link
            $(document).on('click', '.delete-month-btn', function() {
                var calendarId = $(this).data('month-id');
                var table = $('#faq-calendar-data-table').DataTable();

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
                            url: "{{ route('faq-calendar.delete') }}",
                            type: "POST",
                            data: {
                                _token: '{{ csrf_token() }}',
                                faq_calendar_id: calendarId
                            },
                            beforeSend: function () {
                                showBSPLoader();
                            },
                            complete: function () {
                                hideBSPLoader();
                            },
                            success: function(response) {
                                if (response.success == true) {
                                    showSweetAlert('success', 'Deleted!', 'Calendar record has been deleted.');
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
