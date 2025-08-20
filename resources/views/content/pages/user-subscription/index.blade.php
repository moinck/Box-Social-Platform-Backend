@extends('layouts/layoutMaster')

@section('title', 'Subscription Management')

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
        <div class="card-header d-flex flex-column flex-md-row border-bottom subscription-management-header" style="justify-content: space-between;">
            <div class="head-label">
                <h5 class="card-title mb-0">Subscription Management</h5>
            </div>
            <div class="dt-action-buttons text-end pt-3 pt-md-0">
                <div class="dt-buttons btn-group flex-wrap"> 
                    <button class="btn btn-secondary btn-primary waves-effect waves-light" type="button" id="subscription-export-btn"
                        data-bs-toggle="tooltip" data-bs-placement="bottom"
                        title="Export Subscription Management Data">
                        <span>
                            <i class="ri-upload-2-line ri-16px me-sm-2"></i>
                            <span class="d-none d-sm-inline-block">Export Data</span>
                        </span>
                    </button> 
                </div>
            </div>
        </div>
        <div class="card-datatable table-responsive">
            <table class="dt-fixedheader table table-bordered" id="subscription-management-data-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>User</th>
                        <th>Subscription Plan</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Status</th>
                        <th>Created Date</th>
                        <th>Updated Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
    <!--/ Main Table -->

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
            UserSubscriptionDataTable();

            // user subscription data table function
            function UserSubscriptionDataTable() {
                var UserSubscriptionTable = $('#subscription-management-data-table').DataTable({
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
                        url: "{{ route('subscription-management.data-table') }}",
                        data: function (d) {
                            d.subscription_plan = $('#subscription_plan_filter').val();
                        },
                        beforeSend: function () {
                            showBSPLoader();
                        },
                        complete: function () {
                            hideBSPLoader();
                        }
                    },
                    initComplete: function(settings, json) {
                        // Target the first col-md-6 div within the DataTable wrapper
                        var targetDiv = $('#subscription-management-data-table_wrapper .row:first .col-sm-12.col-md-6:first-child');
                        targetDiv.prop('style','margin-top:1.25rem;margin-bottom:1.25rem');

                        // Create a row to hold the two select filters
                        targetDiv.append(`
                            <div class="row">
                                <div class="col-md-6" id="subscription-plan-filter-container"></div>
                            </div>`);

                        // Append account status filter
                        $('#subscription-plan-filter-container').append(`
                            <select class="form-select input-sm" id="subscription_plan_filter">
                                <option value="">Subscription Plan</option>
                                <option value="1">Free Plan</option>
                                <option value="2">Premium Plan</option>
                            </select>
                        `);

                        // Filter results on account status select change
                        $('#subscription_plan_filter').on('change', function() {
                            UserSubscriptionTable.draw();
                        });
                    },
                    columns: [
                        { data: 'DT_RowIndex', name: 'DT_RowIndex'},
                        { data: 'user', name: 'user'},
                        { data: 'plan', name: 'plan'},
                        { data: 'start_date', name: 'start_date'},
                        { data: 'end_date', name: 'end_date'},
                        { data: 'status', name: 'status'},
                        { data: 'created_date', name: 'created_date'},
                        { data: 'updated_date', name: 'updated_date'},
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

            // delete brand configuration
            $(document).on('click', '.delete-user-subscription-btn-1', function() {
                var userSubscriptionId = $(this).data('user-subscription-id');
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
                            url: "{{ route('brand-configuration.delete') }}",
                            type: "POST",
                            data: {
                                _token: '{{ csrf_token() }}',
                                brand_kit_id: brandKitId
                            },
                            beforeSend: function () {
                                showBSPLoader();
                            },
                            complete: function () {
                                hideBSPLoader();
                            },
                            success: function(response) {
                                if (response.success == true) {
                                    showSweetAlert('success', 'Deleted!', 'Brand Configuration has been deleted.');
                                    BrandConfigurationDataTable();
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

            $(document).on('click', '#subscription-export-btn', function() {
                $('#data-export-modal').modal('show');
            });
            $('#csv-export-btn').on('click', function() {
                ExportUserData('csv');
            });
            $('#excel-export-btn').on('click', function() {
                ExportUserData('xlsx');
            });

            // export user function
            function ExportUserData(format) {
                var exportFormData = new FormData();
                exportFormData.append('format', format);
                exportFormData.append('_token', '{{ csrf_token() }}');
                exportFormData.append('plan_id', $('#subscription_plan_filter').val());
                exportFormData.append('subscription_table_search', $('input[type="search"]').val());

                var xhr = new XMLHttpRequest();
                xhr.open("POST", "{{ route('subscription-management.export') }}", true);
                xhr.responseType = 'blob';

                xhr.onload = function () {
                    hideBSPLoader();
                    if (xhr.status === 200) {
                        var blob = xhr.response;
                        var url = window.URL.createObjectURL(blob);
                        var a = document.createElement('a');
                        a.href = url;
                        a.download = 'subscription_' + new Date().getTime() + '.' + format;
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

            $(document).on('click', '#generateInvoice', function(){

                var id = $(this).data('id');
                
                $.ajax({
                    url: "{{ route('subscription-management.generate-invoice') }}",
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        id: id
                    },
                    xhrFields: { responseType: 'blob' }, // always request as Blob
                    beforeSend: function () {
                        showBSPLoader();
                    },
                    complete: function () {
                        hideBSPLoader();
                    },
                    success: function (blob, status, xhr) {
                        // Check content type
                        const contentType = xhr.getResponseHeader('Content-Type');

                        if (contentType && contentType.indexOf("application/json") !== -1) {
                            // JSON error instead of PDF
                            const reader = new FileReader();
                            reader.onload = function () {
                                const json = JSON.parse(reader.result);
                                showSweetAlert('error', 'Error!', json.message || 'Something went wrong.');
                            };
                            reader.readAsText(blob); // Convert Blob -> JSON text
                        } else {
                            // ✅ It's a PDF
                            let filename = "export.pdf";
                            const dispo = xhr.getResponseHeader('Content-Disposition');
                            if (dispo && dispo.indexOf('filename=') !== -1) {
                                filename = dispo.split('filename=')[1].replace(/"/g, '').trim();
                            }

                            const url = window.URL.createObjectURL(blob);
                            const a = document.createElement('a');
                            a.href = url;
                            a.download = filename;
                            document.body.appendChild(a);
                            a.click();
                            a.remove();
                            window.URL.revokeObjectURL(url);
                        }
                    },
                    error: function (xhr) {
                        hideBSPLoader();
                        showSweetAlert('error', 'Error!', 'Something went wrong.');
                    }
                });

                
            });
        });
    </script>
@endsection
