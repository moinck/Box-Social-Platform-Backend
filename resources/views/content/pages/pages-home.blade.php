@extends('layouts/layoutMaster')

@section('title', 'DataTables - Tables')

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

<!-- Page Scripts -->
@section('page-script')
@vite(['resources/assets/js/tables-datatables-extensions.js'])
@endsection

@section('content')
<!-- Scrollable -->

<!-- Fixed Header -->
<div class="card">
  <h5 class="card-header text-center text-md-start pb-md-0">User Management</h5>
  <div class="card-header border-bottom">
    
  </div>
  <div class="card-datatable table-responsive">
    <table class="dt-fixedheader table table-bordered">
      <thead>
        <tr>
            <th>Full Name</th>
            <th>Company Name</th>
            <th>Email Address</th>
            <th>Website</th>
            <th>FCA number</th>
            <th>Action</th>
        </tr>
      </thead>
       </table>
  </div>
</div>
<!--/ Fixed Header -->


<!--/ Select -->
@endsection
