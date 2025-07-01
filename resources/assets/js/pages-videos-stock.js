/**
 * DataTables Extensions (jquery)
 */

"use strict";

$(function () {
    $(document).on("click", ".save_select_videos", function () {
        const form = $("#stock_videos_management")[0]; // Get the DOM element
        const data = new FormData(form);
        const selectedVideos = [];
        $.each($(".search-image-checkbox:checked"), function () {
            selectedVideos.push({
                video_url: $(this).val(),
                thumbnail_url: $(this).data("thumbnail"),
            });
        });
        data.append("selectedVideos", JSON.stringify(selectedVideos));
        $.ajax({
            type: "POST",
            url: `video-management/store`,
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            data: data,
            processData: false,
            dataType: "json",
            contentType: false,
            beforeSend: function () {
                showBSPLoader();
            },
            complete: function () {
                hideBSPLoader();
            },
            success: function (response) {
                if (response.success) {
                    // clear the selected checkboxes
                    $(".search-image-checkbox").prop("checked", false);
                    $("#saved-video-count").text(response.savedVideosCount);
                    $(".save_select_videos").addClass("d-none");

                    showSweetAlert("success", "Store!", response.message);
                } else {
                    showSweetAlert("error", "Info!", response.message);
                }
            },
            error: function (xhr, status, error) {
                hideBSPLoader();
                console.log(xhr.responseText);
                showSweetAlert("error", "Error!", "Something went wrong.");
            },
        });
    });

    $(document).on("click", "#nextButton", function (e) {
        e.preventDefault();
        var page = parseInt($("#total_page").val()) + parseInt(1);
        $("#total_page").val(page);
        getVideosData();

        if (page > 1) {
            $('#previousButton').removeClass('disabled');
        }
    });

    $(document).on("click", "#previousButton", function (e) {
        e.preventDefault();
        var page = parseInt($("#total_page").val()) - parseInt(1);
        if (page < 1) {
            page = 1;
            $('#previousButton').addClass('disabled');
        }
        $("#total_page").val(page);
        getVideosData();
    });

    // get videos data
    function getVideosData() {
        $.ajax({
            type: "POST",
            url: `get-video-management`,
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            data: {
                search_query: $("#video_tag_name").val(),
                page: $("#total_page").val(),
                api_type: $("#api_type").val(),
            },
            beforeSend: function () {
                showBSPLoader();
            },
            complete: function () {
                hideBSPLoader();
            },
            success: function (response) {
                var image = "";
                var newImage = "";
                var getData = "";
                if ($("#api_type").val() == "pexels") {
                    getData = response.data.videos;
                } else {
                    getData = response.data.hits;
                }

                $.each(getData, function (i, settings) {
                    var video_url = "";
                    var video_thumbnail_url = "";
                    var id = "";
                    if ($("#api_type").val() == "pexels") {
                        video_url = settings.video_files[1].link;
                        video_thumbnail_url = settings.image;
                        id = settings.id;
                    } else {
                        video_url = settings.videos.medium.url;
                        video_thumbnail_url = settings.videos.medium.thumbnail;
                        id = settings.id;
                    }

                    // show 25 img in 1 column
                    if (i % 15 === 0) {
                        newImage += `
                            <div class="col-lg-3">
                        `;
                    }
                    newImage += `
                            <div class="col-md mb-md-0 mb-5">
                                <div class="form-check custom-option custom-option-image custom-option-image-check">
                                    <input class="form-check-input search-image-checkbox" type="checkbox" name="selectedVideos[]" data-thumbnail="${video_thumbnail_url}" id="search-image-${id}" value="${video_url}"/>
                                    <label class="form-check-label custom-option-content" for="search-image-${id}">
                                    <span class="custom-option-body">
                                        <img src="${video_thumbnail_url}" data-video-url="${video_url}" class="video-thumbnail" alt="cbImg" />
                                    </span>
                                    </label>
                                </div>
                            </div>
                    `;
                    if (i % 15 === 14) {
                        newImage += `
                            </div>
                        `;
                    }
                });
                $("#sortable-cards").html(newImage);
            },
            error: function (error) {
                hideBSPLoader();
                console.log(error);
            },
        });
    }

    // search videos
    $(document).on("click", ".search_btn", function (e) {
        e.preventDefault();

        $("#total_page").val(1);
        getVideosData();
    });

    // show video in modal
    $(document).on("click", ".video-thumbnail", function (e) {
        e.preventDefault();
        var video_url = $(this).data("video-url");
        var video_tag_name = $(this).data("tag-name");
        if (video_tag_name && video_tag_name.length > 0) {
            $("#template-video-modal .modal-title").text(video_tag_name);
        }
        $("#template-video-modal").modal("show");
        $("#template-video-modal .template-modal-video").attr("src", video_url);
    });

    // get saved videos
    $(document).on(
        "shown.bs.tab",
        'button[data-bs-target="#navs-saved-video-section"]',
        function (e) {
            loadSavedVideos();
        }
    );

    // load saved videos
    function loadSavedVideos() {
        $.ajax({
            type: "GET",
            url: `/video-management/get/saved-videos`,
            success: function (response) {
                var getData = response.data;
                var savedVideos = "";
                $.each(getData, function (i, data) {
                    var video_url = data.video_url;
                    var video_thumbnail_url = data.thumbnail_url;
                    var video_tag_name = data.tag_name;
                    var id = data.id;

                    // show 25 img in 1 column
                    if (i % 5 === 0) {
                        savedVideos += `
                            <div class="row">
                        `;
                    }
                    savedVideos += `
                            <div class="col-md mb-md-0 mb-5">
                                <div class="form-check custom-option custom-option-image custom-option-image-check">
                                    <input class="form-check-input search-image-checkbox" type="checkbox" name="selectedSavedVideos[]" data-thumbnail="${video_thumbnail_url}" id="search-image-${id}" value="${video_url}"/>
                                    <label class="form-check-label custom-option-content" for="search-image-${id}">
                                    <span class="custom-option-body">
                                        <img src="${video_thumbnail_url}" data-video-url="${video_url}" data-tag-name="${video_tag_name}" class="video-thumbnail" alt="cbImg" />
                                    </span>
                                    </label>
                                </div>
                            </div>
                    `;
                    if (i % 5 === 4) {
                        savedVideos += `
                            </div>
                        `;
                    }
                });
                $("#saved_videos").html(savedVideos);
            },
            error: function (error) {
                console.log(error);
            },
        });
    }
});
