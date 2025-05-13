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
      data:data,
      processData: false,
      data: data,
      dataType:'json',
      contentType: false,
      beforeSend: function () {
        showBSPLoader();
      },
      complete: function () {
        hideBSPLoader();
      },
      success: function (data) {
        showSweetAlert('success', 'Store!', 'Your image has been successfully saved to your design template!.');
      },
      error: function(xhr, status, error) {
        hideBSPLoader();
        console.log(xhr.responseText);
        showSweetAlert('error', 'Error!', 'Something went wrong.');
    }
    });
  })

  $(document).on('click', '#nextButton', function (e) {
    e.preventDefault();
    var page =  parseInt($("#total_page").val()) + parseInt(1); 
    $("#total_page").val(page);
    getImages();
  })

  $(document).on('click', '#previousButton', function (e) {
    e.preventDefault();
    var page =  parseInt($("#total_page").val()) - parseInt(1); 
    $("#total_page").val(page);
    
    getImages();
  })

  function getImages(){
    $.ajax({
      type: 'POST',
      url: `get-image-management`,
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      data:{
        'type':$("#select2Icons").val(),
        'page':$("#total_page").val(),
        'api_type':$("#api_type").val()
     },
     beforeSend: function () {
      showBSPLoader();
     },
     complete: function () {
      hideBSPLoader();
     },
      success: function (data) {
        
          var image = "";
          var getData = "";
          if($("#api_type").val() == "pexels"){
            getData = data['photos'];
          }else{
           
            getData = data['hits'];
          }

          console.log(getData);
          
          $.each(getData, function (i, settings) {
            var image_url = "";
              if($("#api_type").val() == "pexels"){
                image_url = settings.src.large;
              }else{
                image_url = settings.largeImageURL;
              }

              image +='<div class="col-lg-3 col-md-6 col-sm-12">'+
                '<div class="card drag-item cursor-move mb-lg-0 mb-6">'+
               
                  '<div class="card-body text-center" style=>'+
                  '<label class="form-check m-0" style="float: right">'+
                  '<input type="checkbox" class="form-check-input" value='+image_url+' name="selectImages[]">'+
                '</label>'+
                  '<img src='+image_url+' style="width: 96%; height:auto">'+
                  '</div>'+
                '</div>'+
              '</div>';
              
          })
          $("#sortable-cards").html(image);
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
