@extends('layouts/layoutMaster')

@section('title', 'Email Content')

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
        <div class="card-header d-flex flex-column flex-md-row border-bottom email-setings-header" style="justify-content: space-between;">
            <div class="head-label">
                <h5 class="card-title mb-0">Email Settings</h5>
            </div>
            <div class="dt-action-buttons text-end pt-3 pt-md-0">
                <div class="dt-buttons btn-group flex-wrap">
                    <a href="{{ route('email-settings.create') }}" class="btn btn-secondary btn-primary waves-effect waves-light">
                        <span>
                            <i class="ri-mail-add-line ri-16px me-sm-2"></i>
                            <span class="d-none d-sm-inline-block">Create Email</span>
                        </span>
                    </a>
                </div>
            </div>
        </div>
        <div class="card-datatable table-responsive">
            <table class="dt-fixedheader table table-bordered" id="email-settings-data-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Title</th>
                        <th>Subject</th>
                        <th>Action</th>
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
            EmailContentDataTable();

            // user subscription data table function
            function EmailContentDataTable() {
                var PaymentTable = $('#email-settings-data-table').DataTable({
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
                        url: "{{ route('email-settings') }}",
                        type: "POST",
                        beforeSend: function () {
                            showBSPLoader();
                        },
                        complete: function () {
                            hideBSPLoader();
                        },
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    },
                    columns: [
                        { data: 'DT_RowIndex', name: 'DT_RowIndex'},
                        { data: 'title', name: 'title'},
                        { data: 'subject', name: 'subject'},
                        { data: 'action', name: 'action', searchable: false, orderable: false},
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


            // Delete Email
            $(document).on('click', '.delete-content-btn', function() {
                var emailId = $(this).data('content-id');
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
                            url: "{{ route('email-settings.delete') }}",
                            type: "POST",
                            data: {
                                _token: '{{ csrf_token() }}',
                                email_id: emailId
                            },
                            beforeSend: function () {
                                showBSPLoader();
                            },
                            complete: function () {
                                hideBSPLoader();
                            },
                            success: function(response) {
                                if (response.success == true) {
                                    showSweetAlert('success', 'Deleted!', 'Email has been deleted.');
                                    EmailContentDataTable();
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
            //------------------------------------------------------------
        });
    </script>
@endsection
