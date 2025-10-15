@extends('layouts/layoutMaster')

@section('title', 'Post Content')

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
        'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
        'resources/assets/vendor/libs/select2/select2.scss',
    ])
@endsection

<!-- Vendor Scripts -->
@section('vendor-script')
    @vite([
        'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
        'resources/assets/vendor/libs/@form-validation/popular.js',
        'resources/assets/vendor/libs/@form-validation/bootstrap5.js',
        'resources/assets/vendor/libs/@form-validation/auto-focus.js',
        'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
        'resources/assets/vendor/libs/select2/select2.js',
    ])
@endsection



@section('content')

    <!-- Main Table -->
    <div class="card">
        <div class="card-header d-flex flex-column flex-md-row border-bottom user-table-header">
            <div class="head-label">
                <h5 class="card-title mb-0">Text Post</h5>
            </div>
            <div class="dt-action-buttons text-end pt-3 pt-md-0">
                <div class="dt-buttons btn-group flex-wrap"> 
                    <a href="{{ route('post-content.create') }}" class="btn btn-secondary btn-primary waves-effect waves-light" 
                        data-bs-toggle="tooltip" data-bs-placement="bottom" title="Create Post Content">
                        <span>
                            <i class="ri-add-line ri-16px me-sm-2"></i>
                            <span class="d-none d-sm-inline-block">Create Text Post</span>
                        </span>
                    </a>
                    <button class="btn btn-secondary btn-primary waves-effect waves-light" type="button" id="post-content-import-btn"
                        data-bs-toggle="tooltip" data-bs-placement="bottom"
                        title="Import Post Content Data">
                        <span>
                            <i class="ri-upload-2-line ri-16px me-sm-2"></i>
                            <span class="d-none d-sm-inline-block">Import Data</span>
                        </span>
                    </button> 
                </div>
            </div>
        </div>
        <div class="card-datatable table-responsive">
            <table class="dt-fixedheader table table-bordered" id="post-content-data-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Post Title</th>
                        <th>Post Category</th>
                        <th>Post Sub Category</th>
                        <th>Post Description</th>
                        <th>Created Date</th>
                        <th>Updated Date</th>
                        <th class="table-action-col">action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
    <!--/ Main Table -->

        {{-- data import modal --}}
        <div class="modal fade" id="data-import-modal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="modalCenterTitle">Import Data</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col mb-6 mt-2">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <h4 class="mb-4">Select Import Format</h4>
                                    <div class="float-end">
                                        <a href="{{ asset('sample-post-content-data.xlsx') }}" class="btn btn-icon btn-primary btn-fab demo waves-effect waves-light" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Download Sample File" id="download-sample-file">
                                            <i class="ri-download-line"></i>
                                        </a>
                                    </div>
                                </div>
                                <br>
                                <form id="post-content-import-form" action="{{ route('post-content.import') }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <div class="col-12">
                                        <div class="form-floating form-floating-outline">
                                            <input type="file" id="post_content_file" name="post_content_file" class="form-control"
                                                placeholder="Last Name"  accept=".csv, .xlsx, .xls"/>
                                            <label for="post_content_file">Post Content File</label>
                                        </div>
                                    </div>
                                    <div class="col-12 text-center d-flex flex-wrap justify-content-center gap-4 row-gap-4 mt-3">
                                        <button type="submit" class="btn btn-primary">Import</button>
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
            PostContentDataTable();

            // post content data table function
            function PostContentDataTable() {
                var PostContentTable = $('#post-content-data-table').DataTable({
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
                        url: "{{ route('post-content.data-table') }}",
                        data: function (d) {
                            d.category_id = $('#category_filter').val();
                            d.sub_category_id = $('#sub_category_filter').val();
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
                        var targetDiv = $('#post-content-data-table_wrapper .row:first .col-sm-12.col-md-6:first-child');
                        targetDiv.prop('style','margin-top:1.25rem;margin-bottom:1.25rem');

                        // Create a row to hold the two md-3 divs
                        targetDiv.append('<div class="row"><div class="col-md-6" id="category-filter-container"></div><div class="col-md-6 d-none" id="sub-category-filter-dropdown"></div></div>');

                        // Append category filter
                        $('#category-filter-container').append(`
                            <select class="form-select input-sm" id="category_filter">
                                <option value="">All Categories</option>
                            </select>
                        `);

                        // Append sub category filter
                        $('#sub-category-filter-dropdown').append(`
                            <select class="form-select input-sm" id="sub_category_filter">
                                <option value="">All Sub Categories</option>
                            </select>
                        `);

                        // Parse the categories JSON data
                        var categories = JSON.parse('{!! addslashes($categories) !!}');
                        var subCategories = JSON.parse('{!! addslashes($subCategories) !!}');

                        // Populate the category select with categories
                        $.each(categories, function(index, obj) {
                            $('#category_filter').append('<option data-id="' + obj.id + '" value="' + obj.id + '">' + obj.name + '</option>');
                        });
                        
                        // Populate the sub category select with sub categories
                        $.each(subCategories, function(index, obj) {
                            $('#sub_category_filter').append('<option data-id="' + obj.id + '" value="' + obj.id + '">' + obj.name + '</option>');
                        });

                        // Filter results on category select change
                        $('#category_filter').on('change', function() {
                            // if category change, then make sub category value empty
                            $('#sub_category_filter').val('');
                            PostContentTable.draw();
                        });

                        // Filter results on sub category select change
                        $('#sub_category_filter').on('change', function() {
                            PostContentTable.draw();
                        });

                        // select2
                        var select2_category = $('#category_filter');
                        var select2_sub_category = $('#sub_category_filter');
                        // add select2
                        if (select2_category.length) {
                            select2_category.each(function () {
                                var $this = $(this);
                                select2Focus($this);
                                $this.wrap('<div class="position-relative"></div>').select2({
                                    placeholder: 'Select Category',
                                    dropdownParent: $this.parent()
                                });
                            });
                        }
                        if (select2_sub_category.length) {
                            select2_sub_category.each(function () {
                                var $this = $(this);
                                select2Focus($this);
                                $this.wrap('<div class="position-relative"></div>').select2({
                                    placeholder: 'Select Sub Category',
                                    dropdownParent: $this.parent()
                                });
                            });
                        }
                        var css = `.select2-container--default .select2-results > .select2-results__options {
                            max-height: 11.5rem;
                            overflow-y: auto;
                        }`;
                        var style = document.createElement('style');
                        style.type = 'text/css';
                        style.appendChild(document.createTextNode(css));
                        document.head.appendChild(style);
                    },
                    columns: [
                        { data: 'DT_RowIndex', name: 'DT_RowIndex',orderable: false, searchable: false},
                        { data: 'post_title', name: 'post_title'},
                        { data: 'post_category', name: 'post_category'},
                        { data: 'post_sub_category', name: 'post_sub_category'},
                        { data: 'post_description', name: 'post_description'},
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

            // delete post content
            $(document).on('click', '.delete-post-content-btn', function() {
                var postId = $(this).data('post-id');
                var table = $('#post-content-data-table').DataTable();
                
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
                            url: "{{ route('post-content.delete') }}",
                            type: "POST",
                            data: {
                                _token: '{{ csrf_token() }}',
                                post_id: postId
                            },
                            beforeSend: function () {
                                showBSPLoader();
                            },
                            complete: function () {
                                hideBSPLoader();
                            },
                            success: function(response) {
                                if (response.success == true) {
                                    showSweetAlert('success', 'Deleted!', 'Post Content has been deleted.');
                                    // PostContentDataTable();
                                    reloadDataTablePreservingPage(table); 
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

            $(document).on('click', '#post-content-import-btn', function() {
                $('#data-import-modal').modal('show');
            });

            // sub category filter
            $(document).on('change','#category_filter', function() {
                // PostContentDataTable();
                $.ajax({
                    url: '{{ route('post-content.sub-category.get.data') }}',
                    type: 'GET',
                    data: {
                        category_id: $(this).val()
                    },
                    beforeSend: function() {
                        showBSPLoader();
                    },
                    complete: function() {
                        hideBSPLoader();
                    },
                    success: function(data) {
                        if (data.success) {
                            $('#sub-category-filter-dropdown').removeClass('d-none');
                            var responseData = data.data;
                            var option = '';
                            option += '<option value="0">Sub Categories</option>';
                            responseData.forEach(function(item) {
                                option += '<option value="' + item.id + '">' + item
                                    .name + '</option>';
                            });
                            $('#sub_category_filter').html(option);
                        } else {
                            $('#sub-category-filter-dropdown').addClass('d-none');
                            $('#sub_category_filter').html(
                                '<option value="0">No Sub Categories</option>'
                            );
                        }
                    }
                });
            });
            // -------------------------------------------
        });
    </script>
@endsection
