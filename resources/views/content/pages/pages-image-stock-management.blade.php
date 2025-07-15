@extends('layouts/layoutMaster')

@section('title', 'Stock Image Management')

<!-- Vendor Styles -->
@section('vendor-style')
    @vite([
            'resources/assets/vendor/libs/@form-validation/form-validation.scss',
            'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
            'resources/assets/vendor/libs/select2/select2.scss',
            'resources/assets/vendor/libs/typeahead-js/typeahead.scss'
        ])
@endsection

<!-- Vendor Scripts -->
@section('vendor-script')
    @vite([
        'resources/assets/vendor/libs/@form-validation/popular.js',
        'resources/assets/vendor/libs/@form-validation/bootstrap5.js',
        'resources/assets/vendor/libs/@form-validation/auto-focus.js',
        'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
        'resources/assets/vendor/libs/select2/select2.js',
        'resources/assets/vendor/libs/typeahead-js/typeahead.js'
    ])
@endsection


@section('content')
    {{-- main content --}}
    <div class="row mt-5 mb-5">
        <div class="card mb-6">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0">Stock Image Management</h5>
                {{-- <small class="text-muted float-end">Default label</small> --}}
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-danger me-4 delete_select_images d-none"
                        style="float: inline-end;">
                        <span class="tf-icons ri-delete-bin-line ri-16px me-2"></span>Delete Select Images
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="card-header p-0">
                    <div class="nav-align-top">
                        <ul class="nav nav-tabs" role="tablist">
                            <li class="nav-item">
                                <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab"
                                    data-bs-target="#navs-image-home-section" aria-controls="navs-image-home-section"
                                    aria-selected="true">
                                    <i class="tf-icons ri-image-add-fill me-2"></i>
                                    Image Search
                                </button>
                            </li>
                            <li class="nav-item">
                                <button type="button" id="saved-img-tab-btn" class="nav-link" role="tab"
                                    data-bs-toggle="tab" data-bs-target="#navs-saved-image-section"
                                    aria-controls="navs-saved-image-section" aria-selected="false">
                                    <i class="tf-icons ri-save-3-line me-2"></i>
                                    Saved Images
                                    <span
                                        class="badge rounded-pill badge-center h-px-20 w-px-20 bg-label-success ms-2 pt-50"
                                        style="width: fit-content !important;"
                                        id="saved-img-count">{{ @$savedImagesCount ?? 0 }}</span>
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="card-body pt-5">
                    <div class="tab-content p-0">
                        <div class="tab-pane fade show active" id="navs-image-home-section" role="tabpanel">
                            <form id="stock_images_management" role="form" enctype="multipart/form-data">
                                <div class="row">
                                    <div class="col-md-6 mb-6">
                                        <div class="form-floating form-floating-outline">
                                            {{-- <select id="select2Icons" class="select2-icons form-select tag-name-select"
                                                name="select2Icons" data-allow-clear="true">
                                                @foreach ($topics as $key => $subtopics)
                                                    <optgroup label="{{ strtoupper($key) }}">
                                                        @foreach ($subtopics as $subkey => $subtopic)
                                                            <option value="{{ $subtopic }}" data-icon="ri-wordpress-fill">
                                                                {{ strtoupper($subtopic) }}
                                                            </option>
                                                        @endforeach
                                                    </optgroup>
                                                @endforeach
                                            </select> --}}
                                            <input id="select2Icons" name="select2Icons" class="form-control typeahead-search" type="text" autocomplete="off" placeholder="Enter search topic" />
                                        </div>
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <div class="form-floating form-floating-outline">
                                            <select id="api_type" class="select2-icons form-select" name="api_type">
                                                <option value="pixabay" data-icon="ri-wordpress-fill" selected>Pixabay
                                                </option>
                                                <option value="pexels" data-icon="ri-wordpress-fill">Pexels</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-1 col-sm-12">
                                        <button
                                            class="clipboard-btn btn btn-primary me-2 waves-effect waves-light search_btn">
                                            Search
                                        </button>
                                    </div>
                                    <div class="col-md-3 col-sm-12">
                                        <button type="button" class="btn btn-primary me-4 d-none save_select_images"
                                            style=" float: inline-end;">
                                            <i class="ri-save-3-line me-sm-1 me-0"></i>
                                            Save Select Images
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

                                        <button id="previousButton" class="btn  btn-prev waves-effect">
                                            <i class="ri-arrow-left-line me-sm-1 me-0"></i>
                                            <span class="align-middle d-sm-inline-block d-none">Previous</span>
                                        </button>
                                        <button id="nextButton" class="btn btn-primary btn-next waves-effect waves-light">
                                            <span class="align-middle d-sm-inline-block d-none me-sm-1">Next</span>
                                            <i class="ri-arrow-right-line"></i>
                                        </button>
                                    </div>

                                    {{-- <div class="col-md-12">
                                        <br><br>
                                        <button type="button" class="btn btn-primary me-4 save_select_images"
                                            style=" float: inline-end;">Save Select Images</button>
                                    </div> --}}
                                </div>
                            </form>
                        </div>
                        <div class="tab-pane fade" id="navs-saved-image-section" role="tabpanel">
                            <div class="row">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-body">
                                            {{-- saved topics dropdown --}}
                                            <div class="col-6">
                                                <div class="form-floating form-floating-outline">
                                                    <select id="saved_topics_list" class="select2-icons form-select tag-name-select" name="saved_topics_list" data-allow-clear="true">
                                                        @forelse ($savedImageTopics as $index => $item)
                                                            @if ($index == 0)
                                                                <option value="0">Select Tags to search</option>
                                                            @endif
                                                            <option value="{{ $item }}">{{ $item }}</option>
                                                        @empty
                                                            <option value="">No saved topics</option>
                                                        @endforelse
                                                    </select>
                                                    <label for="saved_topics_list">Saved Tags</label>
                                                </div>
                                            </div>
                                            <div class="row mt-5" id="saved_images">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

        {{-- data export modal --}}
        <div class="modal fade" id="save-data-modal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="modalCenterTitle">Add New Tag Name</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="addNewTagNameForm" class="row g-5">
                            <div class="col-12">
                                <div class="input-group input-group-merge">
                                    <div class="form-floating form-floating-outline">
                                        <input id="custom_tag_name" name="custom_tag_name" class="form-control " type="text" placeholder="Enter tag name">
                                        <label for="custom_tag_name">Tag Name</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 d-flex flex-wrap justify-content-center gap-4 row-gap-4 mt-5">
                                <button type="button" id="save-image-data-btn" class="btn btn-primary waves-effect waves-light">Save Images</button>
                                <button type="button" class="btn btn-outline-secondary btn-reset waves-effect" data-bs-dismiss="modal" aria-label="Close">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <!--/ Fixed Header -->
    <!--/ Select -->
@endsection

<!-- Page Scripts -->
@section('page-script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    @vite(['resources/assets/js/tables-datatables-extensions.js'])
    @vite(['resources/assets/js/pages-images-stock.js'])

    <script>
        $(document).ready(function () {
            // var searchTopics = @json(config('image_topics'));

            var select2 = $('.tag-name-select');
            // add select2
            if (select2.length) {
                select2.each(function () {
                    var $this = $(this);
                    select2Focus($this);
                    $this.wrap('<div class="position-relative"></div>').select2({
                        placeholder: 'Select Search Topic',
                        dropdownParent: $this.parent()
                    });
                });
            }

            $(document).on('click', '#saved-img-tab-btn', function (e) {
                e.preventDefault();
            });

            // load image when user comes to saved images tab
            $(document).on('shown.bs.tab', 'button[data-bs-target="#navs-saved-image-section"]', function (e) {
                loadSavedImages();
            });

            // on change of tab hide delete button
            $(document).on('shown.bs.tab', 'button[data-bs-target="#navs-image-home-section"]', function (e) {
                $('.delete_select_images').addClass('d-none');
            });

            // only show save button if any image is selected
            $(document).on('change', '.search-image-checkbox', function () {
                if ($('.search-image-checkbox:checked').length > 0) {
                    $('.save_select_images').removeClass('d-none');
                } else {
                    $('.save_select_images').addClass('d-none');
                }
            });
            // --------------------------------------------------

            $(document).on('click', '.save_select_images', function () {
                $('#save-data-modal').modal('show');
            })

            // on change of saved topics list
            $(document).on('change', '#saved_topics_list', function () {
                var selectedTopic = $(this).val();
                if (selectedTopic != 0) {
                    loadSavedImages(selectedTopic);
                } else {
                    loadSavedImages();
                }
            })

            // Function to load saved images
            function loadSavedImages(selectedTopic = null) {
                // hide delete button
                $('.delete_select_images').addClass('d-none');
                var url = "{{ route('image-management.get.saved-images') }}";
                $.ajax({
                    type: 'get',
                    url: url,
                    data: {
                        selectedTopic: selectedTopic
                    },
                    beforeSend: function () {
                        showBSPLoader();
                    },
                    complete: function () {
                        hideBSPLoader();
                    },
                    success: function (data) {
                        var image = "";
                        var newImage = "";
                        var getData = data.data;

                        $.each(getData, function (i, settings) {
                            var image_url = settings.image_url;
                            var imageId = settings.id;
                            var imageExists = settings.image_exists;

                            // if image does not exist then show not available image
                            if (imageExists != true) {
                                image_url = "{{ asset('assets/img/image_not_available.jpg') }}";
                            }

                            // in1 row show only 4 images
                            if (i % 4 === 0) {
                                newImage += `
                                        <div class="row">
                                    `;
                            }
                            newImage += `
                                    <div class="col-md mb-md-0 mb-5">
                                        <div class="form-check custom-option custom-option-image custom-option-image-check">
                                            <input class="form-check-input saved-image-checkbox" type="checkbox" data-image-id="${imageId}" value="${image_url}" id="saved-image-${imageId}"/>
                                            <label class="form-check-label custom-option-content" for="saved-image-${imageId}">
                                            <span class="custom-option-body">
                                                <img src="${image_url}" alt="saved-image"/>
                                            </span>
                                            </label>
                                        </div>
                                    </div>
                                `;
                            if (i % 4 === 3) {
                                newImage += `
                                        </div>
                                    `;
                            }
                        });

                        $("#saved_images").html(newImage);
                    },
                    error: function (error) {
                        hideBSPLoader();
                        console.log(error);
                    }
                });
            }
            // --------------------------------------------------

            let deleteImageIds = [];
            // check if any image is selected or not
            $(document).on('click', '.saved-image-checkbox', function () {
                // also check the current tab
                // if current tab is saved images then only show btn
                var currentTab = $('#navs-saved-image-section').attr('id');
                if (currentTab == 'navs-saved-image-section') {
                    if ($('.saved-image-checkbox:checked').length > 0) {
                        $('.delete_select_images').removeClass('d-none');
                    } else {
                        $('.delete_select_images').addClass('d-none');
                    }
                }
            });
            // --------------------------------------------------

            // delete selected images
            $(document).on('click', '.delete_select_images', function () {
                deleteImageIds = [];
                $('.saved-image-checkbox:checked').each(function () {
                    deleteImageIds.push($(this).data('image-id'));
                });
                if (deleteImageIds.length > 0) {
                    deleteSavedImages();
                } else {
                    showSweetAlert('error', 'Info!', 'Please select 1 image.');
                }
            });
            // --------------------------------------------------

            // delete saved images
            function deleteSavedImages() {
                var url = "{{ route('image-management.delete.saved-images') }}";
                $.ajax({
                    type: 'post',
                    url: url,
                    data: {
                        _token: '{{ csrf_token() }}',
                        image_ids: deleteImageIds
                    },
                    beforeSend: function () {
                        showBSPLoader();
                    },
                    complete: function () {
                        hideBSPLoader();
                    },
                    success: function (data) {
                        if (data.success) {
                            loadSavedImages();
                            $('#saved-img-count').text(data.savedImagesCount);
                            showSweetAlert('success', 'Delete!', 'Image deleted successfully.');
                        } else {
                            showSweetAlert('error', 'Info!', 'Something went wrong.');
                        }
                    },
                    error: function (error) {
                        hideBSPLoader();
                        console.log(error);
                    }
                });
            };
            // --------------------------------------------------
        });
    </script>
@endsection