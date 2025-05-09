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
@vite(['resources/assets/js/pages-images-stock'])
@endsection

@section('content')
<!-- Scrollable -->

<!-- Fixed Header -->
<div class="card">
  <h5 class="card-header text-center text-md-start pb-md-0">Stock Image Management</h5>
  <div class="card-header border-bottom">
  <form id="stock_images_management" role="form" enctype="multipart/form-data">
  <div class="row">
          <div class="col-md-6 mb-6">
            <div class="form-floating form-floating-outline">
              <select id="select2Icons" class="select2-icons form-select" name="select2Icons">

              @foreach( $topics as $key => $subtopics)
              <optgroup label="{{ strtoupper($key) }}">
                @foreach ( $subtopics as $subkey => $subtopic)
                  <option value="{{ $subtopic }}" data-icon="ri-wordpress-fill" selected>{{ strtoupper($subtopic) }}</option>
                  @endforeach
                </optgroup>
              @endforeach
              </select>
            </div>
          </div>
          <div class="col-md-2 mb-2">
            <div class="form-floating form-floating-outline">
              <select id="api_type" class="select2-icons form-select" name="api_type">

              <option value="pixabay" data-icon="ri-wordpress-fill" selected>Pixabay</option>
              <option value="pexels" data-icon="ri-wordpress-fill">Pexels</option>
              </select>
            </div>
          </div>
          <div class="col-md-4 col-sm-12">
            <button class="clipboard-btn btn btn-primary me-2 waves-effect waves-light search_btn">
            Search
            </button>
</div>
        </div>


    <br>
    <div class="row mb-6" id="sortable-cards">
    
    <div class="col-lg-3 col-md-6 col-sm-12">
      <div class="card drag-item cursor-move mb-lg-0 mb-6">
        
    </div>
  </div>
    </div>
  
    <div class="row">
    <br>
    <hr>
    <div class="col-12 d-flex justify-content-between">

      <input type="hidden" name="total_page" id="total_page" value="1">

        <button id="previousButton" class="btn  btn-prev waves-effect"> <i class="ri-arrow-left-line me-sm-1 me-0"></i>
          <span class="align-middle d-sm-inline-block d-none" >Previous</span>
        </button>
        <button id="nextButton" class="btn btn-primary btn-next waves-effect waves-light"> 
    <span class="align-middle d-sm-inline-block d-none me-sm-1">Next</span> 
    <i class="ri-arrow-right-line"></i>
</button>
      </div>
              
    <div class="col-md-12">
    <br><br>
      <button type="button" class="btn btn-primary me-4 save_select_images" style="
      float: inline-end;
  ">Save Select Images</button>
    </div>
  </div>
</form>
</div>


<!--/ Fixed Header -->
<!--/ Select -->
@endsection
