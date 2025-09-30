@extends('layouts/layoutMaster')

@section('title', 'Icon Management')

<!-- Vendor Styles -->
@section('vendor-style')
    @vite(['resources/assets/vendor/libs/@form-validation/form-validation.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/typeahead-js/typeahead.scss'])
@endsection

<!-- Vendor Scripts -->
@section('vendor-script')
    @vite(['resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/typeahead-js/typeahead.js'])
@endsection


@section('content')
    {{-- main content --}}
    <div class="row mt-5 mb-5">
        <div class="card mb-6">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0">Icon Management</h5>
                {{-- <small class="text-muted float-end">Default label</small> --}}
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-danger me-4 delete_select_icons d-none" style="float: inline-end;">
                        <span class="tf-icons ri-delete-bin-line ri-16px me-2"></span>Delete Select Icons
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
                                    Icon Search
                                </button>
                            </li>
                            <li class="nav-item">
                                <button type="button" id="saved-img-tab-btn" class="nav-link" role="tab"
                                    data-bs-toggle="tab" data-bs-target="#navs-saved-icon-section"
                                    aria-controls="navs-saved-icon-section" aria-selected="false">
                                    <i class="tf-icons ri-save-3-line me-2"></i>
                                    Saved Icons
                                    <span
                                        class="badge rounded-pill badge-center h-px-20 w-px-20 bg-label-success ms-2 pt-50"
                                        style="width: fit-content !important;"
                                        id="saved-icon-count">{{ @$savedIconsCount ?? 0 }}</span>
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="card-body pt-5">
                    <div class="tab-content p-0">
                        {{-- Icon Search tab --}}
                        <div class="tab-pane fade show active" id="navs-image-home-section" role="tabpanel">
                            <div class="row">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-body">
                                            {{-- search form --}}
                                            <form id="stock_icon_management" role="form" enctype="multipart/form-data"
                                                onsubmit="return false;">
                                                <div class="row">
                                                    <div class="col-md-6 mb-6">
                                                        <div class="form-floating form-floating-outline">
                                                            <input id="icon_search_input" name="icon_search_input"
                                                                class="form-control typeahead-search" type="search"
                                                                autocomplete="off" placeholder="Enter search topic" />
                                                        </div>
                                                    </div>
                                                    <div class="col-md-1 col-sm-12">
                                                        <button type="button"
                                                            class="clipboard-btn btn btn-primary me-2 waves-effect waves-light search_icon_btn">
                                                            Search
                                                        </button>
                                                    </div>
                                                    <div class="col-md-5 col-sm-12">
                                                        <button type="button"
                                                            class="btn btn-primary me-4 d-none save_select_icons"
                                                            style=" float: inline-end;">
                                                            <i class="ri-save-3-line me-sm-1 me-0"></i>
                                                            Save Select Icons
                                                        </button>
                                                    </div>
                                                </div>

                                                <br>
                                                <div class="mb-6 d-flex flex-wrap" id="icons-container"
                                                    style="max-height: 500px; overflow-y: scroll;gap:1rem">
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Saved Icons tab --}}
                        <div class="tab-pane fade" id="navs-saved-icon-section" role="tabpanel">
                            <div class="row">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-body">
                                            {{-- saved topics dropdown --}}
                                            <div class="col-6">
                                                <div class="form-floating form-floating-outline">
                                                    <select id="saved_tag_list"
                                                        class="select2-icons form-select tag-icon-name-select"
                                                        name="saved_tag_list" data-allow-clear="true">
                                                        <option value="">Select Tags to search</option>
                                                    </select>
                                                    <label for="saved_tag_list">Saved Tags</label>
                                                </div>
                                            </div>
                                            <div class="d-flex flex-wrap mt-5" id="saved_icon_container"
                                                style="max-height: 500px; overflow-y: scroll;gap:1rem">
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

    {{-- icon save modal --}}
    <div class="modal fade" id="save-icon-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modalCenterTitle">Add New Tag Name</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addNewTagNameForm" class="row g-5">
                        <div class="col-12">
                            <div class="input-group-1">
                                <input id="custom_tag_name" name="custom_tag_name"
                                    class="form-control typeahead-saved-icon-tag-search" type="text"
                                    placeholder="Enter tag name">
                            </div>
                        </div>
                        <div class="col-12 d-flex flex-wrap justify-content-center gap-4 row-gap-4 mt-5">
                            <button type="button" id="save-icon-data-btn"
                                class="btn btn-primary waves-effect waves-light">Save Icons</button>
                            <button type="button" class="btn btn-outline-secondary btn-reset waves-effect"
                                data-bs-dismiss="modal" aria-label="Close">
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

@section('page-script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script>
        $(document).ready(function() {
            var select2 = $('.tag-icon-name-select');
            // add select2
            if (select2.length) {
                select2.each(function() {
                    var $this = $(this);
                    select2Focus($this);
                    $this.wrap('<div class="position-relative"></div>').select2({
                        placeholder: 'Select Search Topic',
                        dropdownParent: $this.parent()
                    });
                });
            }
            // --------------------------------------------------

            var icon_search_input = $('#icon_search_input');
            var loadCollectionUrl = "https://api.iconify.design/collection?prefix=mdi";
            var iconsContainer = $('#icons-container');
            var IconData = [];
            var currentPage = 0;
            var itemsPerPage = 120; // Reduced for better lazy loading experience
            var isLoading = false;
            var allIconsLoaded = false;

            // If search is empty, then load all icons
            $(document).on('input', "#icon_search_input", function() {
                if (icon_search_input.val().length == 0) {
                    resetIconsContainer();
                    loadIcons();
                }
            });
            // --------------------------------------------------

            // Load all icons once (since API returns all)
            $.ajax({
                url: loadCollectionUrl,
                type: 'GET',
                beforeSend: function() {
                    showBSPLoader();
                },
                complete: function() {
                    hideBSPLoader();
                },
                success: function(data) {
                    IconData = data.uncategorized;
                    loadIcons(); // Load first page
                    setupLazyLoading(); // Setup scroll listener
                }
            });
            // --------------------------------------------------

            // Function to reset icons container
            function resetIconsContainer() {
                iconsContainer.empty();
                currentPage = 0;
                allIconsLoaded = false;
                isLoading = false;
            }
            // --------------------------------------------------

            // Function to render icons per page
            function loadIcons() {
                if (isLoading || allIconsLoaded) return;

                isLoading = true;

                var start = currentPage * itemsPerPage;
                var end = start + itemsPerPage;
                var iconsToRender = IconData.slice(start, end);

                // Check if we've loaded all icons
                if (iconsToRender.length === 0 || end >= IconData.length) {
                    allIconsLoaded = true;
                    hideBSPLoader();
                    isLoading = false;
                    return;
                }

                // Show loading indicator if not first page
                if (currentPage > 0) {
                    showBSPLoader();
                }

                // Simulate a small delay for better UX (optional)
                setTimeout(function() {
                    iconsToRender.forEach(function(icon) {
                        iconsContainer.append(`
                            <div class="form-check custom-option custom-option-image custom-option-image-check" style="height: 100px;width: 100px;" title="${icon}">
                                <input class="form-check-input new-icon-checkbox" type="checkbox" name="selectIcons[]" data-icon-id="${icon}" value="https://api.iconify.design/mdi:${icon}.svg?color=%23000000" id="saved-icon-${icon}"/>
                                <label class="form-check-label custom-option-content" for="saved-icon-${icon}">
                                    <span class="custom-option-body">
                                        <img src="https://api.iconify.design/mdi:${icon}.svg?color=%23000000" data-icon-name="${icon}" alt="${icon}"/>
                                    </span>
                                </label>
                            </div>
                        `);
                    });

                    currentPage++;
                    isLoading = false;
                    hideBSPLoader();

                    // Check if we've loaded all icons after this batch
                    if (end >= IconData.length) {
                        allIconsLoaded = true;
                    }
                }, 100); // Small delay for smooth loading
            }
            // --------------------------------------------------

            // Function to setup lazy loading scroll listener
            function setupLazyLoading() {
                iconsContainer.on('scroll', function() {
                    var container = $(this);
                    var scrollTop = container.scrollTop();
                    var scrollHeight = container[0].scrollHeight;
                    var containerHeight = container.height();

                    // Load more when user scrolls to 80% of the content
                    var threshold = 0.8;
                    var triggerPoint = (scrollHeight - containerHeight) * threshold;

                    if (scrollTop >= triggerPoint && !isLoading && !allIconsLoaded) {
                        loadIcons();
                    }
                });
            }
            // --------------------------------------------------

            // Function to show loading indicator
            // function showLoadingIndicator() {
            //     if ($('#icons-loading-indicator').length === 0) {
            //         iconsContainer.append(`
        //             <div id="icons-loading-indicator" class="w-100 text-center py-3">
        //                 <div class="spinner-border spinner-border-sm text-primary" role="status">
        //                     <span class="visually-hidden">Loading...</span>
        //                 </div>
        //                 <small class="text-muted ms-2">Loading more icons...</small>
        //             </div>
        //         `);
            //     }
            // }

            // Function to remove loading indicator
            // function removeLoadingIndicator() {
            //     $('#icons-loading-indicator').remove();
            // }

            // for searching icons
            $(document).on('click', '.search_icon_btn', function() {
                // make loading false
                isLoading = false;
                allIconsLoaded = true;
                hideBSPLoader();
                searchFormSubmit();
            });
            // -----------------------------------------------------

            // search form submit
            $(document).on('submit', '#stock_icon_management', function(e) {
                e.preventDefault();
                // make loading false
                isLoading = false;
                allIconsLoaded = true;
                hideBSPLoader();
                searchFormSubmit();
            });
            // -----------------------------------------------------

            // search form submit function
            function searchFormSubmit() {
                var searchInput = icon_search_input.val();
                var searchUrl = "https://api.iconify.design/search?query=" + searchInput + "&prefix=mdi"
                if (searchInput.length > 0) {
                    $.ajax({
                        url: searchUrl,
                        type: "GET",
                        beforeSend: function() {
                            showBSPLoader();
                        },
                        complete: function() {
                            hideBSPLoader();
                        },
                        success: function(response) {
                            var searchIcons = response.icons;
                            if (searchIcons.length > 0) {
                                iconsContainer.empty();
                                searchIcons.forEach(function(icon) {
                                    iconsContainer.append(`
                                            <div class="form-check custom-option custom-option-image custom-option-image-check" style="height: 100px;width: 100px;" title="${icon}">
                                                <input class="form-check-input new-icon-checkbox" type="checkbox" name="selectIcons[]" data-icon-id="${icon}" value="https://api.iconify.design/${icon}.svg?color=%23000000" id="saved-icon-${icon}"/>
                                                <label class="form-check-label custom-option-content" for="saved-icon-${icon}">
                                                <span class="custom-option-body">
                                                    <img src="https://api.iconify.design/${icon}.svg?color=%23000000" data-icon-name="${icon}" alt="${icon}">
                                                </span>
                                                </label>
                                            </div>
                                        `);
                                });
                            } else {
                                iconsContainer.empty();
                                toastr.info('No icons found.try Something different.');
                            }
                        }
                    });
                } else {
                    toastr.error('Please enter search keyword');
                }
            }
            // ------------------------------------------------------


            // only show save button if any image is selected
            $(document).on('change', '.new-icon-checkbox', function() {
                if ($('.new-icon-checkbox:checked').length > 0) {
                    $('.save_select_icons').removeClass('d-none');
                } else {
                    $('.save_select_icons').addClass('d-none');
                }
            });
            // --------------------------------------------------

            // show save icon modal
            $(document).on('click', '.save_select_icons', function() {
                $('.typeahead-saved-icon-tag-search').typeahead('destroy');
                $('#save-icon-modal').modal('show');
            });
            // --------------------------------------------------

            // save icon Data
            $(document).on('click', '#save-icon-data-btn', function() {
                const form = $("#stock_icon_management")[0]; // Get the DOM element
                const data = new FormData(form);
                data.append("custom_tag_name", $('#custom_tag_name').val());
                if (data.get("custom_tag_name").length > 0) {
                    $.ajax({
                        url: "{{ route('icon-management.store') }}",
                        type: "POST",
                        headers: {
                            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                        },
                        data: data,
                        processData: false,
                        dataType: "json",
                        contentType: false,
                        beforeSend: function() {
                            showBSPLoader();
                        },
                        complete: function() {
                            hideBSPLoader();
                        },
                        success: function(response) {
                            if (response.success) {
                                $('#saved-icon-count').text(response.savedIconsCount);
                                $('#save-icon-modal').modal('hide');
                                $('.new-icon-checkbox:checked').each(function() {
                                    $(this).prop('checked', false);
                                });
                                $('.save_select_icons').addClass('d-none');
                                $('#custom_tag_name').val('');
                                $('#icons-container').empty();
                                icon_search_input.val('');
                                resetIconsContainer();
                                loadIcons();
                                showSweetAlert("success", "Store!",
                                    "Your icon has been successfully saved.")
                            }
                        },
                        error: function(xhr, status, error) {
                            console.log(error);
                            showSweetAlert("error", "Error!", "Something went wrong.");
                        }
                    });
                }
            });
            // --------------------------------------------------

            // load saved icons and tags when tabs change
            $(document).on('shown.bs.tab', 'button[data-bs-target="#navs-saved-icon-section"]', function(e) {
                loadSavedIcons();
                loadSavedTags();
            });
            // --------------------------------------------------

            // load saved icons
            function loadSavedIcons(filterTag = null) {
                $.ajax({
                    url: "{{ route('icon-management.get.saved-icon') }}",
                    type: "GET",
                    data: {
                        filterTag: filterTag
                    },
                    beforeSend: function() {
                        showBSPLoader();
                    },
                    complete: function() {
                        hideBSPLoader();
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#saved-icon-count').text(response.savedIconsCount);
                            var savedIconData = response.data;
                            var savedIconHtml = '';
                            savedIconData.forEach(function(icon) {
                                savedIconHtml += `
                                    <div class="form-check custom-option custom-option-image custom-option-image-check" style="height: 100px;width: 100px;" title="${icon.tag_name}">
                                        <input class="form-check-input saved-icon-checkbox" type="checkbox" name="savedSelectedIcons[]" data-icon-id="${icon.id}" value="${icon.icon_url}" id="saved-icon-${icon.id}"/>
                                        <label class="form-check-label custom-option-content" for="saved-icon-${icon.id}">
                                            <span class="custom-option-body">
                                                <img src="${icon.icon_url}" data-icon-name="${icon.tag_name}" alt="${icon.tag_name}"/>
                                            </span>
                                        </label>
                                    </div>
                                `;
                            });
                            $('#saved_icon_container').html(savedIconHtml);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log(error);
                        showSweetAlert("error", "Error!", "Something went wrong.");
                    }
                });
            }
            // -----------------------------------------------------

            // load saved tags
            function loadSavedTags() {
                $.ajax({
                    url: "{{ route('icon-management.get.saved-tag') }}",
                    type: "GET",
                    beforeSend: function() {
                        showBSPLoader();
                    },
                    complete: function() {
                        hideBSPLoader();
                    },
                    success: function(response) {
                        if (response.success) {
                            var savedTagNames = response.data;
                            var savedTopicsList = "";
                            savedTopicsList += `<option value="0">Select Tags to search</option>`;
                            $.each(savedTagNames, function(i, tagName) {
                                savedTopicsList +=
                                    `<option value="${tagName}">${tagName}</option>`;
                            });
                            $('#saved_tag_list').html(savedTopicsList);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log(error);
                        showSweetAlert("error", "Error!", "Something went wrong.");
                    }
                });
            }
            // -----------------------------------------------------

            // on change of saved topics list
            $(document).on('change', '#saved_tag_list', function() {
                var selectedTopic = $(this).val();
                if (selectedTopic != 0) {
                    loadSavedIcons(selectedTopic);
                } else {
                    loadSavedIcons();
                }
            })
            // -----------------------------------------------------

            // only show save button if any image is selected
            $(document).on('change', '.saved-icon-checkbox', function() {
                if ($('.saved-icon-checkbox:checked').length > 0) {
                    $('.delete_select_icons').removeClass('d-none');
                } else {
                    $('.delete_select_icons').addClass('d-none');
                }
            });
            // -----------------------------------------------------

            // delete saved icons
            $(document).on('click', '.delete_select_icons', function() {
                var deleteIconIds = [];
                $('.saved-icon-checkbox:checked').each(function() {
                    deleteIconIds.push($(this).data('icon-id'));
                });
                if (deleteIconIds.length > 0) {
                    $.ajax({
                        url: "{{ route('icon-management.delete.saved-icon') }}",
                        type: "POST",
                        headers: {
                            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                        },
                        data: {
                            deleteIconIds: deleteIconIds
                        },
                        beforeSend: function() {
                            showBSPLoader();
                        },
                        complete: function() {
                            hideBSPLoader();
                        },
                        success: function(response) {
                            if (response.success) {
                                $('#saved-icon-count').text(response.savedIconsCount);
                                $('.saved-icon-checkbox:checked').each(function() {
                                    $(this).prop('checked', false);
                                });
                                $('.delete_select_icons').addClass('d-none');
                                loadSavedIcons();
                                loadSavedTags();
                                showSweetAlert("success", "Delete!",
                                    "Your icon has been successfully deleted.")
                            }
                        },
                        error: function(xhr, status, error) {
                            console.log(error);
                            showSweetAlert("error", "Error!", "Something went wrong.");
                        }
                    });
                } else {
                    showSweetAlert('error', 'Info!', 'Please select 1 icon.');
                }
            });
            // -----------------------------------------------------
        });
    </script>
@endsection
