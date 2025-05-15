$(function(){$(document).on("click",".save_select_images",function(){const e=$("#stock_images_management")[0],a=new FormData(e);$.ajax({type:"POST",url:"image-management/store",headers:{"X-CSRF-TOKEN":$('meta[name="csrf-token"]').attr("content")},data:a,processData:!1,data:a,dataType:"json",contentType:!1,beforeSend:function(){showBSPLoader()},complete:function(){hideBSPLoader()},success:function(t){t.success?($(".search-image-checkbox").prop("checked",!1),showSweetAlert("success","Store!","Your image has been successfully saved to your design template!.")):showSweetAlert("error","Info!","Please select 1 image.")},error:function(t,r,o){hideBSPLoader(),console.log(t.responseText),showSweetAlert("error","Error!","Something went wrong.")}})}),$(document).on("click","#nextButton",function(e){e.preventDefault();var a=parseInt($("#total_page").val())+parseInt(1);$("#total_page").val(a),s()}),$(document).on("click","#previousButton",function(e){e.preventDefault();var a=parseInt($("#total_page").val())-parseInt(1);$("#total_page").val(a),s()});function s(){$.ajax({type:"POST",url:"get-image-management",headers:{"X-CSRF-TOKEN":$('meta[name="csrf-token"]').attr("content")},data:{type:$("#select2Icons").val(),page:$("#total_page").val(),api_type:$("#api_type").val()},beforeSend:function(){showBSPLoader()},complete:function(){hideBSPLoader()},success:function(e){var a="",t="";$("#api_type").val()=="pexels"?t=e.photos:t=e.hits,$.each(t,function(r,o){var c="",n="";$("#api_type").val()=="pexels"?(c=o.src.large,n=o.id):(c=o.largeImageURL,n=o.id),r%4===0&&(a+=`
                            <div class="row">
                        `),a+=`
                            <div class="col-md mb-md-0 mb-5">
                                <div class="form-check custom-option custom-option-image custom-option-image-check">
                                    <input class="form-check-input search-image-checkbox" type="checkbox" name="selectImages[]" id="search-image-${n}" value="${c}"/>
                                    <label class="form-check-label custom-option-content" for="search-image-${n}">
                                    <span class="custom-option-body">
                                        <img src="${c}" alt="cbImg" />
                                    </span>
                                    </label>
                                </div>
                            </div>
                    `,r%4===3&&(a+=`
                            </div>
                        `)}),$("#sortable-cards").html(a)},error:function(e){hideBSPLoader(),console.log(e)}})}$(document).on("click",".search_btn",function(e){e.preventDefault(),$("#total_page").val(1),s()})});
