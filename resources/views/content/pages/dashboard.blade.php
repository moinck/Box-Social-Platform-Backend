@extends('layouts/layoutMaster')

@section('title', 'Dashboard')

<!-- Vendor Styles -->
@section('vendor-style')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-select-bs5/select.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.scss', 'resources/assets/vendor/libs/datatables-fixedcolumns-bs5/fixedcolumns.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-fixedheader-bs5/fixedheader.bootstrap5.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

<!-- Vendor Scripts -->
@section('vendor-script')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection


@section('content')
    <div class="row g-6">
        <div class="col-sm-6 col-lg-3">
            <div class="card card-border-shadow-primary h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="avatar me-4">
                            <span class="avatar-initial rounded-3 bg-label-primary"><i
                                    class="ri-user-line ri-24px"></i></span>
                        </div>
                        <h4 class="mb-0">{{ $pageData['totalUser'] }}</h4>
                    </div>
                    <h6 class="mb-0 fw-normal">Total Users</h6>
                    <p class="mb-0">
                        <span class="me-1 fw-medium">{{ $pageData['activeUser'] }} Active</span>
                        <small class="text-muted">{{ $pageData['inactiveUser'] }} Inactive</small>
                    </p>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-border-shadow-warning h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="avatar me-4">
                            <span class="avatar-initial rounded-3 bg-label-warning"><i
                                    class="tf-icons ri-archive-stack-line ri-24px"></i></span>
                        </div>
                        <h4 class="mb-0">{{ $pageData['categoriesCount'] }}</h4>
                    </div>
                    <h6 class="mb-0 fw-normal">Categories</h6>
                    <p class="mb-0">
                        <span class="me-1 fw-medium">{{ $pageData['subCategoriesCount'] }}</span>
                        <small class="text-muted">Sub Categories</small>
                    </p>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-border-shadow-danger h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="avatar me-4">
                            <span class="avatar-initial rounded-3 bg-label-danger"><i
                                    class="ri-profile-line ri-24px"></i></span>
                        </div>
                        <h4 class="mb-0">{{ $pageData['postContent'] }}</h4>
                    </div>
                    <h6 class="mb-0 fw-normal">Post Content</h6>
                    <p class="mb-0">
                        {{-- <span class="me-1 fw-medium">+4.3%</span>
                        <small class="text-muted">than last week</small> --}}
                    </p>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-border-shadow-info h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="avatar me-4">
                            <span class="avatar-initial rounded-3 bg-label-info"><i
                                    class="ri-layout-2-line ri-24px"></i></span>
                        </div>
                        <h4 class="mb-0">{{ $pageData['postTemplate'] }}</h4>
                    </div>
                    <h6 class="mb-0 fw-normal">Post Template</h6>
                    {{-- <p class="mb-0">
                        <span class="me-1 fw-medium">+4.3%</span>
                        <small class="text-muted">than last week</small>
                    </p> --}}
                </div>
            </div>
        </div>
        
        {{-- notification table --}}
        <div class="col-8">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <div class="card-title mb-0">
                        <h5 class="m-0 me-2">Notification</h5>
                    </div>
                </div>
                <hr class="m-0">
                <div class="card-datatable table-responsive">
                    <div id="notification-data-table_wrapper" class="dataTables_wrapper dt-bootstrap5 no-footer">
                        <div class="table-responsive">
                            <table class="dt-route-vehicles table dataTable no-footer dtr-column" id="notification-data-table" aria-describedby="notification-data-table_info">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Title</th>
                                        <th>Message</th>
                                        <th>Type</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- recent user list --}}
        <div class="col-4 col-xxl-4">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title m-0 me-2">Recent Users</h5>
                    <div class="card-header-elements ms-auto">
                        <span class="badge bg-primary text-dark rounded-pill">{{ date('d M Y | D') }}</span>
                    </div>
                </div>
                <hr class="m-0">
                <div class="card-body">
                    <ul class="p-0 m-0">
                        @foreach ($pageData['recentUsers'] as $user)
                            <li class="d-flex align-items-center @if($loop->last) @else mb-4 pb-2 @endif">
                                <div class="avatar flex-shrink-0 me-4">
                                    <img src="{{ $user->profile_image ? asset($user->profile_image) : asset('assets/img/avatars/5.png') }}"
                                        alt="avatar" class="rounded-3">
                                </div>
                                <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                    <div class="me-2">
                                        <h6 class="mb-0">{{ $user->first_name }} {{ $user->last_name }}</h6>
                                        <small class="d-flex align-items-center">
                                            <i class="ri-calendar-line ri-16px"></i>
                                            <span class="ms-2">{{ $user->created_at->format('d M Y | h:i A') }}</span>
                                        </small>
                                    </div>
                                    @if ($user->is_verified)
                                        <div class="badge bg-label-success rounded-pill">Verified</div>
                                    @else
                                        <div class="badge bg-label-danger rounded-pill">Not Verified</div>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
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
            NotificationDataTable();

            // notification data table function
            function NotificationDataTable() {
                var NotificationTable = $('#notification-data-table').DataTable({
                    bLengthChange: false,
                    searchable: true,
                    serverSide: true,
                    orderable: true,
                    searching: true,
                    destroy: true,
                    info: true,
                    paging: true,
                    pageLength: 5,
                    ajax: {
                        url: "{{ route('notification.data-table') }}",
                        beforeSend: function () {
                            showBSPLoader();
                        },
                        complete: function () {
                            hideBSPLoader();
                        }
                    },
                    columns: [
                        { data: 'DT_RowIndex', name: 'DT_RowIndex'},
                        { data: 'title', name: 'title'},
                        { data: 'message', name: 'message'},
                        { data: 'type', name: 'type'},
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

            // notification mark as read
            $(document).on('click', '.notification-mark-as-read-btn', function() {
                var notificationId = $(this).data('notification-id');
                $.ajax({
                    url: "{{ route('notification.mark-as-read') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        id: notificationId
                    },
                    beforeSend: function () {
                        showBSPLoader();
                    },
                    complete: function () {
                        hideBSPLoader();
                    },
                    success: function(response) {
                        if (response.success == true) {
                            showSweetAlert('success', 'Updated!', 'Notification has been marked as read.');
                            NotificationDataTable();
                        }
                    },
                    error: function(xhr, status, error) {
                        hideBSPLoader();
                        console.log(xhr.responseText);
                        showSweetAlert('error', 'Error!', 'Something went wrong.');
                    }
                });
            });
            // -------------------------------------------
        });
    </script>
@endsection
