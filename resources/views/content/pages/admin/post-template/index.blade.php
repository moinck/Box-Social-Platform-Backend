@extends('layouts/layoutMaster')

@section('title', 'Post Template')

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
                <h5 class="card-title mb-0">Post Template</h5>
            </div>
            <div class="dt-action-buttons text-end pt-3 pt-md-0">
                <div class="dt-buttons btn-group flex-wrap"> 
                    <a href="http://178.128.45.173:9163/admin/create-templates?token={{ $currentAdminToken ?? '' }}" target="_blank" class="btn btn-secondary btn-primary waves-effect waves-light" 
                        data-bs-toggle="tooltip" data-bs-placement="bottom" title="Create Post Template">
                        <span>
                            <i class="ri-add-line ri-16px me-sm-2"></i>
                            <span class="d-none d-sm-inline-block">Create Post Template</span>
                        </span>
                    </a>
                </div>
            </div>
        </div>
        <div class="card-datatable table-responsive">
            <table class="dt-fixedheader table table-bordered" id="post-template-data-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Template</th>
                        <th>Post Content</th>
                        <th>Category</th>
                        <th>Sub Category</th>
                        <th>Status</th>
                        <th>Created Date</th>
                        <th class="" style="width: 130px;">Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
    <!--/ Main Table -->

    <!--/ Select -->

    {{-- need a modal to show image --}}
    <div class="modal fade" id="template-image-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Image</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <img src="" alt="template-image" class="template-modal-image">
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
            PostTemplateDataTable();

            // post template data table function
            function PostTemplateDataTable() {
                var PostTemplateTable = $('#post-template-data-table').DataTable({
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
                        url: "{{ route('post-template.data-table') }}",
                        data: function (d) {
                            d.category = $('#category_filter').val();
                            d.status = $('#status_filter').val();
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
                        var targetDiv = $('#post-template-data-table_wrapper .row:first .col-sm-12.col-md-6:first-child');
                        targetDiv.prop('style','margin-top:1.25rem;margin-bottom:1.25rem');

                        // Create a row to hold the two md-3 divs
                        targetDiv.append('<div class="row"><div class="col-md-6" id="category-filter-container"></div><div class="col-md-6" id="status-filter-container"></div></div>');

                        // Append category filter
                        $('#category-filter-container').append('<select class="form-select input-sm" id="category_filter"><option value="">Categories</option></select>');

                        // Append status filter
                        $('#status-filter-container').append(`<select class="form-select input-sm" id="status_filter">
                            <option value="0">Status</option>
                            <option value="1">Active</option>
                            <option value="2">Inactive</option>
                        </select>`);

                        // Parse the categories JSON data
                        var categories = JSON.parse('{!! addslashes($categories) !!}');

                        // Populate the category select with categories
                        $.each(categories, function(index, obj) {
                            $('#category_filter').append('<option value="' + obj.id + '">' + obj.name + '</option>');
                        });

                        // Filter results on category select change
                        $('#category_filter').on('change', function() {
                            // PostTemplateTable.columns(3).search(this.value).draw();
                            PostTemplateTable.draw();
                        });

                        // Filter results on status select change
                        $('#status_filter').on('change', function() {
                            // PostTemplateTable.columns('raw_status').search(this.value).draw();
                            PostTemplateTable.draw();
                        });
                    },
                    columns: [
                        { data: 'DT_RowIndex', name: 'DT_RowIndex'},
                        { data: 'template_image', name: 'template_image'},
                        { data: 'post_content', name: 'post_content'},
                        { data: 'category', name: 'category'},
                        { data: 'sub_category', name: 'sub_category'},
                        { data: 'status', name: 'status'},
                        { data: 'created_at', name: 'created_at'},
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
                    },
                });
            }
            // -------------------------------------------

            // change status function
            $(document).on('click', '#post-template-status', function (e) {
                e.preventDefault();
                var id = $(this).data('id');

                Swal.fire({
                    title: 'Are you sure?',
                    text: "You want to change status!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, change it!',
                    customClass: {
                        confirmButton: 'btn btn-primary me-3',
                        cancelButton: 'btn btn-outline-secondary'
                    },
                    buttonsStyling: false
                }).then(function(result) {
                    if (result.value) {
                        $.ajax({
                            url: "{{ route('post-template.change-status') }}",
                            type: "POST",
                            data: {
                                id: id,
                                _token: "{{ csrf_token() }}"
                            },
                            beforeSend: function () {
                                showBSPLoader();
                            },
                            complete: function () {
                                hideBSPLoader();
                            },
                            success: function (response) {
                                if (response.success == true) {
                                    showSweetAlert('success', 'Updated !','Post Template status has been updated successfully.');
                                    PostTemplateDataTable();
                                }
                            },
                            error: function (xhr) {
                                hideBSPLoader();
                                console.log(xhr.responseText);
                                showSweetAlert('error', 'Error !', 'Something went wrong.');
                            }
                        });
                    } else {
                        $(this).prop('checked', !$(this).prop('checked'));
                    }
                });
            });
            // ----------------------------------------------------------

            // delete post template
            $(document).on('click', '.delete-post-template-btn', function() {
                var postTemplateId = $(this).data('post-template-id');
                var selectedCategory = $('#category_filter').val();
                var selectedStatus = $('#status_filter').val();
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
                            url: "{{ route('post-template.delete') }}",
                            type: "POST",
                            data: {
                                _token: '{{ csrf_token() }}',
                                post_template_id: postTemplateId
                            },
                            beforeSend: function () {
                                showBSPLoader();
                            },
                            complete: function () {
                                hideBSPLoader();
                            },
                            success: function(response) {
                                if (response.success == true) {
                                    // PostTemplateDataTable();
                                    showSweetAlert('success', 'Deleted!', 'Post Template has been deleted.');

                                    setTimeout(function() {
                                        $('#category_filter').val(selectedCategory);
                                        $('#status_filter').val(selectedStatus);
                                        // trigger change in category dropdown
                                        $('#category_filter').trigger('change');
                                        // trigger change in status dropdown
                                        $('#status_filter').trigger('change');
                                    }, 500);
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

            // show image modal
            $(document).on('click','.template-image', function () {
                var image = $(this).attr('src');
                var category = $(this).data('category');
                $('#template-image-modal .modal-body img').attr('src', image);
                $('#template-image-modal .modal-header .modal-title').text(category);
                $('#template-image-modal').modal('show');
            });
            // ----------------------------------------------------------

            // delete post template
            $(document).on('click', '.duplicate-post-template-btn', function() {
                var postTemplateId = $(this).data('post-template-id');
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You want to create duplicate of this post template!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, duplicate it!',
                    customClass: {
                        confirmButton: 'btn btn-primary me-3',
                        cancelButton: 'btn btn-outline-secondary'
                    },
                    buttonsStyling: false
                }).then(function(result) {
                    if (result.value) {
                        $.ajax({
                            url: "{{ route('post-template.create-duplicate') }}",
                            type: "POST",
                            data: {
                                _token: '{{ csrf_token() }}',
                                post_template_id: postTemplateId
                            },
                            beforeSend: function () {
                                showBSPLoader();
                            },
                            complete: function () {
                                hideBSPLoader();
                            },
                            success: function(response) {
                                if (response.success == true) {
                                    PostTemplateDataTable();
                                    showSweetAlert('success', 'Created!', 'Duplicate Post Template has been created.');
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
