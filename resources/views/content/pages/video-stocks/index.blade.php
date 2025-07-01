@extends('layouts/layoutMaster')

@section('title', 'Stock Video Management')

<!-- Vendor Styles -->
@section('vendor-style')
    @vite(['resources/assets/vendor/libs/@form-validation/form-validation.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

<!-- Vendor Scripts -->
@section('vendor-script')
    @vite(['resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])

@endsection


@section('content')
    <!-- Scrollable -->
    <div class="row mt-5 mb-5">
        <div class="card mb-6">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0">Stock Video Management</h5>
                {{-- <small class="text-muted float-end">Default label</small> --}}
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-danger me-4 delete_select_images d-none" style="float: inline-end;">
                        <span class="tf-icons ri-delete-bin-line ri-16px me-2"></span>Delete Select Videos
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
                                    Video Search
                                </button>
                            </li>
                            <li class="nav-item">
                                <button type="button" id="saved-video-tab-btn" class="nav-link" role="tab"
                                    data-bs-toggle="tab" data-bs-target="#navs-saved-video-section"
                                    aria-controls="navs-saved-video-section" aria-selected="false">
                                    <i class="tf-icons ri-save-3-line me-2"></i>
                                    Saved Videos
                                    <span
                                        class="badge rounded-pill badge-center h-px-20 w-px-20 bg-label-success ms-2 pt-50"
                                        id="saved-video-count">{{ @$data['savedVideoCount'] ?? 0 }}</span>
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="card-body pt-5">
                    <div class="tab-content p-0">
                        <div class="tab-pane fade show active" id="navs-image-home-section" role="tabpanel">
                            <form id="stock_videos_management" role="form" enctype="multipart/form-data">
                                <div class="row">
                                    <div class="col-md-6 mb-6">
                                        <div class="form-floating form-floating-outline">
                                            <select id="video_tag_name" class="select2-icons form-select"
                                                name="video_tag_name">
                                                @foreach ($data['searchTopics'] as $key => $subtopics)
                                                    <option value="{{ $subtopics }}" data-icon="ri-wordpress-fill">
                                                        {{ strtoupper($subtopics) }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <div class="form-floating form-floating-outline">
                                            <select id="api_type" class="select2-icons form-select" name="api_type">
                                                <option value="pixabay" data-icon="ri-wordpress-fill" selected>Pixabay</option>
                                                <option disabled value="pexels" data-icon="ri-wordpress-fill">Pexels</option>
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
                                        <button type="button" class="btn btn-primary me-4 d-none save_select_videos"
                                            style=" float: inline-end;">
                                            <i class="ri-save-3-line me-sm-1 me-0"></i>
                                            Save Select Videos
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
                                </div>
                            </form>
                        </div>
                        <div class="tab-pane fade" id="navs-saved-video-section" role="tabpanel">
                            <!-- Saved videos will be loaded here -->
                            <div class="row" id="saved_videos">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal to show video --}}
    <div class="modal fade" id="template-video-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Video</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <iframe src=""
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        allowfullscreen alt="template-video" class="template-modal-video"
                        style="width: 100%; height: 500px;"></iframe>
                </div>
            </div>
        </div>
    </div>
@endsection

<!-- Page Scripts -->
@section('page-script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    {{-- @vite(['resources/assets/js/tables-datatables-extensions.js']) --}}
    @vite(['resources/assets/js/pages-videos-stock.js'])

    <script>
        $(document).ready(function() {
            $(document).on('click', '#saved-video-tab-btn', function(e) {
                e.preventDefault();
            });
        });
    </script>
@endsection
