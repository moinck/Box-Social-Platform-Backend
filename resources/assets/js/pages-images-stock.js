/**
 * DataTables Extensions (jquery)
 */

"use strict";

$(function () {
    $(document).on("click", "#save-image-data-btn", function () {
        var custom_tag_name = $("#custom_tag_name").val();
        if (custom_tag_name.length == 0) {
            toastr.error("Please enter tag name.");
            return;
        }
        const form = $("#stock_images_management")[0]; // Get the DOM element
        const data = new FormData(form);
        data.append("custom_tag_name", custom_tag_name);
        $.ajax({
            type: "POST",
            url: `image-management/store`,
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
                    $("#saved-img-count").text(response.savedImagesCount);
                    $(".save_select_images").addClass("d-none");
                    $("#save-data-modal").modal("hide");
                    showSweetAlert(
                        "success",
                        "Store!",
                        "Your image has been successfully saved to your design template!."
                    );
                } else {
                    showSweetAlert("error", "Info!", "Please select 1 image.");
                }
            },
            error: function (xhr, status, error) {
                hideBSPLoader();
                console.log(xhr.responseText);
                if (xhr.status == 422) {
                    showSweetAlert("error", "Oops!", xhr.responseJSON.message);
                } else {
                    showSweetAlert("error", "Oops!", "Something went wrong.");
                }
            },
        });
    });

    $(document).on("click", "#nextButton", function (e) {
        e.preventDefault();
        var select2Icons = $("#select2Icons").val();
        if (select2Icons.length == 0) {
            toastr.error("Please select 1 topic to search images.");
            return;
        }
        var page = parseInt($("#total_page").val()) + parseInt(1);
        $("#total_page").val(page);
        getImages();
    });

    $(document).on("click", "#previousButton", function (e) {
        e.preventDefault();
        var select2Icons = $("#select2Icons").val();
        if (select2Icons.length == 0) {
            toastr.error("Please select 1 topic to search images.");
            return;
        }
        var page = parseInt($("#total_page").val()) - parseInt(1);
        $("#total_page").val(page);

        getImages();
    });

    function getImages() {
        $.ajax({
            type: "POST",
            url: `get-image-management`,
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            data: {
                type: $("#select2Icons").val(),
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
                // console.log(response);

                // Check if the request was successful
                if (!response.success) {
                    showSweetAlert("error", "Error", response.message);
                    return;
                }

                // Access the data from the response
                var getData = response.data;
                var totalCount = 0;

                // Determine the total count based on the API type
                if ($("#api_type").val() == "pexels") {
                    totalCount = getData.photos ? getData.photos.length : 0;
                    getData = getData.photos;
                } else {
                    totalCount = getData.hits ? getData.hits.length : 0;
                    getData = getData.hits;
                }

                if (totalCount == 0) {
                    showSweetAlert("info", "Oops!", "No images were found. Try a different search.");
                    return;
                }
                var newImage = "";

                // console.log(getData);
                $.each(getData, function (i, settings) {
                    var image_url = "";
                    var id = "";
                    if ($("#api_type").val() == "pexels") {
                        image_url = settings.src.large;
                        id = settings.id;
                    } else {
                        image_url = settings.largeImageURL;
                        id = settings.id;
                    }

                    // image += '<div class="col-lg-3 col-md-6 col-sm-12">' +
                    //     '<div class="card drag-item cursor-move mb-lg-0 mb-6">' +

                    //     '<div class="card-body text-center" style=>' +
                    //     '<label class="form-check m-0" style="float: right">' +
                    //     '<input type="checkbox" class="form-check-input search-image-checkbox" value=' + image_url + ' name="selectImages[]">' +
                    //     '</label>' +
                    //     '<img src=' + image_url + ' style="width: 96%; height:auto">' +
                    //     '</div>' +
                    //     '</div>' +
                    //     '</div>';

                    // show 25 img in 1 column
                    if (i % 25 === 0) {
                        newImage += `
                            <div class="col-lg-3">
                        `;
                    }
                    newImage += `
                            <div class="col-md mb-md-0 mb-5">
                                <div class="form-check custom-option custom-option-image custom-option-image-check">
                                    <input class="form-check-input search-image-checkbox" type="checkbox" name="selectImages[]" id="search-image-${id}" value="${image_url}"/>
                                    <label class="form-check-label custom-option-content" for="search-image-${id}">
                                    <span class="custom-option-body">
                                        <img src="${image_url}" alt="cbImg" />
                                    </span>
                                    </label>
                                </div>
                            </div>
                    `;
                    if (i % 25 === 24) {
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

    $(document).on("click", ".search_btn", function (e) {
        e.preventDefault();
        var select2Icons = $("#select2Icons").val();
        if (select2Icons.length == 0) {
            toastr.options = {
                progressBar: true,
            };
            toastr.error("Please select 1 topic to search images.");
            return;
        }

        $("#total_page").val(1);
        getImages();
    });

    // String Matcher function
    var substringMatcher = function (strs) {
        return function findMatches(q, cb) {
            var matches, substrRegex;
            matches = [];
            substrRegex = new RegExp(q, "i");
            $.each(strs, function (i, str) {
                if (substrRegex.test(str)) {
                    matches.push(str);
                }
            });

            cb(matches);
        };
    };
    var substringMatcher1 = function (strs) {
        return function findMatches(q, cb) {
            var matches, substrRegex;
            matches = [];
            substrRegex = new RegExp(q, "i");
            $.each(strs, function (i, str) {
                if (substrRegex.test(str)) {
                    matches.push(str);
                }
            });

            cb(matches);
        };
    };

    var searchTopics = [
        'first time buyer mortgages',
        'happy young person with door key',
        'happy young couple standing outside house',
        'holding door key',
        'happy person with thumbs up',
        'confused person',
        'person with question mark',
        'remortgage',
        'piggy bank',
        'save money',
        'savings',
        'clock',
        'times up',
        'time is now',
        'mortgage broker',
        'happy person or couple with thumbs up',
        'move home',
        'moving home',
        'for sale sign outside house',
        'life insurance',
        'critical illness',
        'health',
        'family protection',
        'income protection',
        'protect income',
        'protect house',
        'protect family',
        'national day',
        'protect home',
        'home insurance',
        'accidents in the home',
        'bad credit',
        'poor credit',
        'credit score',
        'happy person with thumbs up',
        'confused person',
        'person with question mark',
        'sad person',
        'business owner',
        'self employed',
        'boss',
        'manager',
        'company accounts',
        'hmrc',
        'tax return',
        'happy middle-aged person with thumbs up',
        'key workers',
        'nurses',
        'doctors',
        'uk police',
        'uk firefighters',
        'uk military',
        'armed forces',
        'right to buy',
        'happy couple over 60 years old',
    ];

    if (isRtl) {
        $(".typeahead-search").attr("dir", "rtl");
        $(".typeahead-saved-tag-search").attr("dir", "rtl");
    }

    // Basic
    // --------------------------------------------------------------------
    $(".typeahead-search").typeahead(
        {
            hint: !isRtl,
            highlight: true,
            minLength: 1,
        },
        {
            name: "searchTopics",
            source: substringMatcher(searchTopics),
        }
    );

    // saved topics typeahead
    var savedTopics = [];
    $(document).on('click', '.save_select_images', function () {
        $(".typeahead-saved-tag-search").val('');
        $('.typeahead-saved-tag-search').typeahead('destroy');
        $.ajax({
            type: "GET",
            url: `/stock-image-management/get/saved-topics`,
            success: function (response) {
                Object.entries(response.data).forEach(([key, value]) => {
                    savedTopics.push(value);
                });
            },
        });
        $(".typeahead-saved-tag-search").typeahead(
            {
                hint: !isRtl,
                highlight: true,
                minLength: 1,
            },
            {
                name: "savedTopics",
                source: substringMatcher1(savedTopics),
            }
        );
    });
    // --------------------------------------------------------------------
});
