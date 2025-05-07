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
  'resources/assets/vendor/libs/datatables-fixedheader-bs5/fixedheader.bootstrap5.scss'
])
@endsection

<!-- Vendor Scripts -->
@section('vendor-script')
@vite(['resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js'])
@endsection



@section('content')
<!-- Scrollable -->

<!-- Fixed Header -->
<div class="card">
    {{-- <h5 class="card-header text-center text-md-start pb-md-0">User Management</h5> --}}
    {{-- <div class="card-header border-bottom">
      
    </div> --}}
    <div class="card-header flex-column flex-md-row border-bottom">
        <div class="head-label">
            <h5 class="card-title mb-0">User Management</h5>
        </div>
        <div class="dt-action-buttons text-end pt-3 pt-md-0">
            <div class="dt-buttons btn-group flex-wrap"> 
                <button class="btn btn-secondary create-new btn-primary waves-effect waves-light" tabindex="0" aria-controls="DataTables_Table_0" type="button"><span><i class="ri-add-line ri-16px me-sm-2"></i> <span class="d-none d-sm-inline-block">Add New Record</span></span></button> 
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
                    <th>Action</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
<!--/ Fixed Header -->


<!--/ Select -->
@endsection

<!-- Page Scripts -->
@section('page-script')
    {{-- @vite(['resources/assets/js/tables-datatables-extensions.js']) --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script>
        $(document).ready(function() {
            UserDataTable();

            function UserDataTable(){
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
                        {data: 'DT_RowIndex', name: 'DT_RowIndex'},
                        {data: 'name', name: 'name'},
                        {data: 'company_name', name: 'company_name'},
                        {data: 'email', name: 'email'},
                        {data: 'fca_number', name: 'fca_number'},
                        {data: 'created_date', name: 'created_date'},
                        {data: 'account_status', name: 'account_status'},
                        {data: 'action', name: 'action'}
                    ],
                    language: {
                        paginate: {
                            next: '<i class="ri-arrow-right-s-line"></i>',
                            previous: '<i class="ri-arrow-left-s-line"></i>'
                        }
                    }
                });
            }

            // account status switch change
            $(document).on('change','#user-account-status', function () {
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
                    success: function (response) {
                        if (response.success == true) {
                            UserDataTable();
                        } else {
                            // toastr.error(response.message);
                        }
                    },
                    error: function (xhr, status, error) {
                        console.log(xhr.responseText);
                    }
                });
            });
        });
    </script>
@endsection