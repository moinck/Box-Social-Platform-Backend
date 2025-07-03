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
                        <th>Created Date</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
    <!--/ Main Table -->

    <!--/ Select -->
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
                                ContactUsDataTable();
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
        });
    </script>
@endsection
