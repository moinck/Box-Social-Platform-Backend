$(function(){$(document).on("change",".search-image-checkbox",function(){$(".search-image-checkbox:checked").length>0?$(".save_select_videos").removeClass("d-none"):$(".save_select_videos").addClass("d-none")}),$(document).on("click",".save_select_videos",function(){const e=$("#stock_videos_management")[0],a=new FormData(e),t=[];$.each($(".search-image-checkbox:checked"),function(){t.push({video_url:$(this).val(),thumbnail_url:$(this).data("thumbnail")})}),a.append("selectedVideos",JSON.stringify(t)),$.ajax({type:"POST",url:"/stock-video-management/store",headers:{"X-CSRF-TOKEN":$('meta[name="csrf-token"]').attr("content")},data:a,processData:!1,dataType:"json",contentType:!1,beforeSend:function(){showBSPLoader()},complete:function(){hideBSPLoader()},success:function(o){o.success?($(".search-image-checkbox").prop("checked",!1),$("#saved-video-count").text(o.savedVideosCount),$(".save_select_videos").addClass("d-none"),showSweetAlert("success","Store!",o.message)):showSweetAlert("error","Info!",o.message)},error:function(o,d,c){hideBSPLoader(),console.log(o.responseText),showSweetAlert("error","Error!","Something went wrong.")}})}),$(document).on("click","#nextButton",function(e){e.preventDefault();var a=parseInt($("#total_page").val())+parseInt(1);$("#total_page").val(a),l(),a>1&&$("#previousButton").removeClass("disabled")}),$(document).on("click","#previousButton",function(e){e.preventDefault();var a=parseInt($("#total_page").val())-parseInt(1);a<1&&(a=1,$("#previousButton").addClass("disabled")),$("#total_page").val(a),l()});function l(){$.ajax({type:"POST",url:"/stock-video-management/search",headers:{"X-CSRF-TOKEN":$('meta[name="csrf-token"]').attr("content")},data:{search_query:$("#video_tag_name").val(),page:$("#total_page").val(),api_type:$("#api_type").val()},beforeSend:function(){showBSPLoader()},complete:function(){hideBSPLoader()},success:function(e){var a="",t="",o=$("#video_tag_name").val();$("#api_type").val()=="pexels"?t=e.data.videos:t=e.data.hits,$.each(t,function(d,c){var s="",i="",n="";$("#api_type").val()=="pexels"?(s=c.video_files[1].link,i=c.image,n=c.id):(s=c.videos.medium.url,i=c.videos.medium.thumbnail,n=c.id),d%15===0&&(a+=`
                            <div class="col-lg-3">
                        `),a+=`
                            <div class="col-md mb-md-0 mb-5">
                                <div class="form-check custom-option custom-option-image custom-option-image-check">
                                    <input class="form-check-input search-image-checkbox" type="checkbox" name="selectedVideos[]" data-thumbnail="${i}" id="search-image-${n}" value="${s}"/>
                                    <label class="form-check-label custom-option-content" for="search-image-${n}">
                                    <span class="custom-option-body">
                                        <img src="${i}" data-video-url="${s}" data-tag-name="${o}" class="video-thumbnail" alt="video thumbnail" />
                                    </span>
                                    </label>
                                </div>
                            </div>
                    `,d%15===14&&(a+=`
                            </div>
                        `)}),$("#sortable-cards").html(a)},error:function(e){hideBSPLoader(),console.log(e)}})}$(document).on("click",".search_btn",function(e){e.preventDefault(),$("#total_page").val(1),l()}),$(document).on("click",".video-thumbnail",function(e){e.preventDefault();var a=$(this).data("video-url"),t=$(this).data("tag-name");t&&t.length>0&&$("#template-video-modal .modal-title").text(t),$("#template-video-modal").modal("show"),$("#template-video-modal .template-modal-video").attr("src",a)}),$(document).on("shown.bs.tab",'button[data-bs-target="#navs-saved-video-section"]',function(e){m()});function m(){$.ajax({type:"GET",url:"/stock-video-management/get/saved-videos",beforeSend:function(){showBSPLoader()},complete:function(){hideBSPLoader()},success:function(e){var a=e.data;if(a.length==0){var t="";t+=`
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body text-center">
                                    <i class="tf-icons ri-image-add-fill me-2"></i>
                                    No saved videos
                                </div>
                            </div>
                        </div>
                    `,$("#saved_videos").html(t)}else{var o="",d=e.savedVideosCount;$("#saved-video-count").text(d),$.each(a,function(c,s){var i=s.video_url,n=s.thumbnail_url,v=s.tag_name,r=s.id;c%5===0&&(o+=`
                                <div class="row">
                            `),o+=`
                                <div class="col-md mb-md-0 mb-5">
                                    <div class="form-check custom-option custom-option-image custom-option-image-check">
                                        <input class="form-check-input saved-image-checkbox" type="checkbox" name="selectedSavedVideos[]" data-thumbnail="${n}" data-id="${r}" id="search-image-${r}" value="${i}"/>
                                        <label class="form-check-label custom-option-content" for="search-image-${r}">
                                        <span class="custom-option-body">
                                            <img src="${n}" data-video-url="${i}" data-tag-name="${v}" class="video-thumbnail" alt="cbImg" />
                                        </span>
                                        </label>
                                    </div>
                                </div>
                        `,c%5===4&&(o+=`
                                </div>
                            `)}),$("#saved_videos").html(o)}},error:function(e){console.log(e)}})}$(document).on("click",".saved-image-checkbox",function(){$(".saved-image-checkbox:checked").length>0?$(".delete_select_images").removeClass("d-none"):$(".delete_select_images").addClass("d-none")}),$(document).on("click",".delete_select_images",function(e){e.preventDefault();var a=[];$.each($(".saved-image-checkbox:checked"),function(){console.log($(this).data("id")),a.push($(this).data("id"))}),$.ajax({type:"POST",url:"/stock-video-management/delete/saved-videos",headers:{"X-CSRF-TOKEN":$('meta[name="csrf-token"]').attr("content")},data:{selectedVideos:a},beforeSend:function(){showBSPLoader()},complete:function(){hideBSPLoader()},success:function(t){t.success?($(".saved-image-checkbox").prop("checked",!1),m(),showSweetAlert("success","Delete!",t.message)):showSweetAlert("error","Info!",t.message)},error:function(t){console.log(t)}})})});
