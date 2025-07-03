$(function(){$(document).on("click",".save_select_images",function(){const a=$("#stock_images_management")[0],e=new FormData(a);$.ajax({type:"POST",url:"image-management/store",headers:{"X-CSRF-TOKEN":$('meta[name="csrf-token"]').attr("content")},data:e,processData:!1,data:e,dataType:"json",contentType:!1,beforeSend:function(){showBSPLoader()},complete:function(){hideBSPLoader()},success:function(t){t.success?($(".search-image-checkbox").prop("checked",!1),$("#saved-img-count").text(t.savedImagesCount),$(".save_select_images").addClass("d-none"),showSweetAlert("success","Store!","Your image has been successfully saved to your design template!.")):showSweetAlert("error","Info!","Please select 1 image.")},error:function(t,r,o){hideBSPLoader(),console.log(t.responseText),showSweetAlert("error","Error!","Something went wrong.")}})}),$(document).on("click","#nextButton",function(a){a.preventDefault();var e=$("#select2Icons").val();if(e==null){toastr.error("Please select 1 topic to search images.");return}var t=parseInt($("#total_page").val())+parseInt(1);$("#total_page").val(t),n()}),$(document).on("click","#previousButton",function(a){a.preventDefault();var e=$("#select2Icons").val();if(e==null){toastr.error("Please select 1 topic to search images.");return}var t=parseInt($("#total_page").val())-parseInt(1);$("#total_page").val(t),n()});function n(){$.ajax({type:"POST",url:"get-image-management",headers:{"X-CSRF-TOKEN":$('meta[name="csrf-token"]').attr("content")},data:{type:$("#select2Icons").val(),page:$("#total_page").val(),api_type:$("#api_type").val()},beforeSend:function(){showBSPLoader()},complete:function(){hideBSPLoader()},success:function(a){var e="",t="";$("#api_type").val()=="pexels"?t=a.photos:t=a.hits,$.each(t,function(r,o){var s="",c="";$("#api_type").val()=="pexels"?(s=o.src.large,c=o.id):(s=o.largeImageURL,c=o.id),r%25===0&&(e+=`
                            <div class="col-lg-3">
                        `),e+=`
                            <div class="col-md mb-md-0 mb-5">
                                <div class="form-check custom-option custom-option-image custom-option-image-check">
                                    <input class="form-check-input search-image-checkbox" type="checkbox" name="selectImages[]" id="search-image-${c}" value="${s}"/>
                                    <label class="form-check-label custom-option-content" for="search-image-${c}">
                                    <span class="custom-option-body">
                                        <img src="${s}" alt="cbImg" />
                                    </span>
                                    </label>
                                </div>
                            </div>
                    `,r%25===24&&(e+=`
                            </div>
                        `)}),$("#sortable-cards").html(e)},error:function(a){hideBSPLoader(),console.log(a)}})}$(document).on("click",".search_btn",function(a){a.preventDefault();var e=$("#select2Icons").val();if(e==null){toastr.options={progressBar:!0},toastr.error("Please select 1 topic to search images.");return}$("#total_page").val(1),n()})});
