@extends('layouts/layoutMaster')

@section('title', 'YouTube Video Links')

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
                <h5 class="card-title mb-0">YouTube Video Links</h5>
            </div>
            <div class="dt-action-buttons text-end pt-3 pt-md-0">
                <div class="dt-buttons btn-group flex-wrap"> 
                    <button class="btn btn-secondary btn-primary waves-effect waves-light" type="button" id="video-link-add-btn"
                        data-bs-toggle="tooltip" data-bs-placement="bottom" title="Add Video Link">
                        <span>
                            <i class="ri-add-line ri-16px me-sm-2"></i>
                            <span class="d-none d-sm-inline-block">Add Video Link</span>
                        </span>
                    </button> 
                </div>
            </div>
        </div>
        <div class="card-datatable table-responsive">
            <table class="dt-fixedheader table table-bordered" id="video-link-data-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Title</th>
                        <th>Link</th>
                        <th>Image</th>
                        <th>Status</th>
                        <th class="table-action-col">Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
    <!--/ Main Table -->

    {{-- add - edit video link modal --}}
    <div class="modal fade" id="add-video-link-modal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-simple">
            <div class="modal-content">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="modal-body p-0">
                    <div class="text-center mb-6">
                        <h4 class="mb-2">Video Link</h4>
                    </div>
                    <form id="add-video-link-form" class="row g-5" method="POST">
                        @csrf
                        <input type="hidden" name="video_link_id" id="video_link_id">
                        <div class="col-12">
                            <div class="form-floating form-floating-outline">
                                <input type="text" id="title" name="title" class="form-control"
                                    placeholder="Title" required />
                                <label for="title">Title</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-floating form-floating-outline">
                                <textarea id="link" rows="3" name="link" class="form-control h-px-75"
                                    placeholder="Youtube Video Link"></textarea>
                                <label for="link">Link</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-floating form-floating-outline">
                                <input type="file" id="image" name="image" class="form-control" placeholder="User Image" accept="image/*">
                                <label for="image">Image</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-floating form-floating-outline">
                                <select id="video_link_status" name="video_link_status" class="form-select"
                                    aria-label="Default select example">
                                    <option value="">Select Status</option>
                                    <option value="active" selected>Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                                <label for="video_link_status">Status</label>
                            </div>
                        </div>
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
            VideoLinkDataTable();

            // contact data table function
            function VideoLinkDataTable() {
                var ContactTable = $('#video-link-data-table').DataTable({
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
                        url: "{{ route('youtube-video') }}",
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
                        { data: 'video_link', name: 'video_link'},
                        { data: 'image_url', name: 'image_url'},
                        { data: 'status', name: 'status'},
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
            const addVideoLinkForm = document.getElementById('add-video-link-form');
            const addVideoLinkFV = FormValidation.formValidation(addVideoLinkForm, {
                fields: {
                    title: {
                        validators: {
                            notEmpty: {
                                message: 'Please enter your title'
                            },
                            stringLength: {
                                min: 2,
                                max: 100,
                                message: 'Title must be between 2 and 100 characters'
                            }
                        }
                    },
                    link: {
                        validators: {
                            notEmpty: {
                                message: 'Please enter youtube video link'
                            },
                            regexp: {
                                regexp: /^(?:https?:\/\/)?(?:www\.)?(?:m\.)?(?:youtube\.com|youtu\.be)\/(?:watch\?v=|embed\/|v\/|)([\w-]{11})(?:\S+)?$/,
                                message: 'Please enter a valid YouTube video link'
                            }
                        }
                    },
                    video_link_status: {
                        validators: {
                            notEmpty: {
                                message: 'Please select video link status'
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
                            if (['title', 'link', 'video_link_status','image'].includes(field)) {
                                return '.col-12';
                            }
                            return '.col-12';
                        }
                    }),
                    submitButton: new FormValidation.plugins.SubmitButton(),
                    // defaultSubmit: new FormValidation.plugins.DefaultSubmit(),
                    // autoFocus: new FormValidation.plugins.AutoFocus()
                }
            }).on('core.form.valid', function() {
                // Form is valid, proceed with form submission
                var form = $('#add-video-link-form');
                var formData = new FormData(form[0]); // Creates FormData object

                $.ajax({
                    url: "{{ route('youtube-video.save') }}",
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
                            $('#add-video-link-modal').modal('hide');
                            VideoLinkDataTable();
                        }
                    },
                    error: function(xhr) {
                        hideBSPLoader();
                        console.log(xhr.responseText);
                        showSweetAlert('error', 'Error !', 'Something went wrong.');
                    }
                });
            });
            // ----------------------------------------------------------

            // add video link function
            $(document).on('click','#video-link-add-btn', function () {
                $('#add-video-link-form')[0].reset();
                $("#video_link_id").val('');
                addVideoLinkFV.resetForm();
                $('#add-video-link-modal').modal('show');
            });
            // -------------------------------------------

            // change status function
            $(document).on('click', '#youtube-video-status', function (e) {
                e.preventDefault();
                var id = $(this).data('id');
                var table = $('#video-link-data-table').DataTable();
                
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
                            url: "{{ route('youtube-video.change-status') }}",
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
                                    showSweetAlert('success', 'Updated !','Video link status has been updated successfully.');
                                    reloadDataTablePreservingPage(table); 
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

            // Edit User modal
            $(document).on('click', '.edit-youtube-video-btn', function() {
                var videoLinkId = $(this).data('youtube-video-id');
                var editUrl = "{{ route('youtube-video.edit', ':id') }}".replace(':id', videoLinkId);

                $.ajax({
                    url: editUrl,
                    type: "GET",
                    success: function(response) {
                        if (response.success === true) {
                            $('#title').val(response.data.title);
                            $('#link').val(response.data.link);
                            $('#video_link_id').val(videoLinkId);

                            // Fix: match DB values (1 = active, 0 = inactive)
                            $('#video_link_status').val(response.data.is_active == 1 ? 'active' : 'inactive');

                            // Show modal for editing
                            $('#add-video-link-modal').modal('show');
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
            $(document).on('click', '.delete-youtube-video-btn', function() {
                var videoLinkId = $(this).data('youtube-video-id');
                var table = $('#video-link-data-table').DataTable();

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
                            url: "{{ route('youtube-video.delete') }}",
                            type: "POST",
                            data: {
                                _token: '{{ csrf_token() }}',
                                video_link_id: videoLinkId
                            },
                            beforeSend: function () {
                                showBSPLoader();
                            },
                            complete: function () {
                                hideBSPLoader();
                            },
                            success: function(response) {
                                if (response.success == true) {
                                    showSweetAlert('success', 'Deleted!', 'Video link has been deleted.');
                                    // VideoLinkDataTable();
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

        });
    </script>
@endsection
