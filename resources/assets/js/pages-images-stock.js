/**
 * DataTables Extensions (jquery)
 */

'use strict';

$(function () {
    $(document).on('click', '.save_select_images', function () {
        const form = $("#stock_images_management")[0]; // Get the DOM element
        const data = new FormData(form);
        $.ajax({
            type: 'POST',
            url: `image-management/store`,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: data,
            processData: false,
            data: data,
            dataType: 'json',
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
                    $('.search-image-checkbox').prop('checked', false);
                    $('#saved-img-count').text(response.savedImagesCount);
                    $('.save_select_images').addClass('d-none');
                    
                    showSweetAlert('success', 'Store!', 'Your image has been successfully saved to your design template!.');
                } else {
                    showSweetAlert('error', 'Info!', 'Please select 1 image.');
                }
            },
            error: function (xhr, status, error) {
                hideBSPLoader();
                console.log(xhr.responseText);
                showSweetAlert('error', 'Error!', 'Something went wrong.');
            }
        });
    })

    $(document).on('click', '#nextButton', function (e) {
        e.preventDefault();
        var page = parseInt($("#total_page").val()) + parseInt(1);
        $("#total_page").val(page);
        getImages();
    })

    $(document).on('click', '#previousButton', function (e) {
        e.preventDefault();
        var page = parseInt($("#total_page").val()) - parseInt(1);
        $("#total_page").val(page);

        getImages();
    })

    function getImages() {
        $.ajax({
            type: 'POST',
            url: `get-image-management`,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                'type': $("#select2Icons").val(),
                'page': $("#total_page").val(),
                'api_type': $("#api_type").val()
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
                    getData = response['photos'];
                } else {

                    getData = response['hits'];
                }

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

                })
                $("#sortable-cards").html(newImage);
            },
            error: function (error) {
                hideBSPLoader();
                console.log(error);
            }
        });
    }

    $(document).on('click', '.search_btn', function (e) {
        e.preventDefault();

        $("#total_page").val(1);
        getImages()

    });
});
