if("undefined"!==typeof CommentpressAjaxSettings){var cpajax_live=CommentpressAjaxSettings.cpajax_live;var cpajax_ajax_url=CommentpressAjaxSettings.cpajax_ajax_url;var cpajax_spinner_url=CommentpressAjaxSettings.cpajax_spinner_url;var cpajax_post_id=CommentpressAjaxSettings.cpajax_post_id}var cpajax_submitting=false;function cpajax_reenable_featured_comments(){if("undefined"!==typeof featured_comments){if(jQuery.is_function_defined("featured_comments_click")){featured_comments_click()}}}jQuery(document).ready(function(f){var a,d;function h(){jQuery("#respond_title").after('<div id="cpajax_error_msg"></div>');jQuery("#submit").after('<img src="'+cpajax_spinner_url+'" id="loading" alt="'+cpajax_lang[0]+'" />');jQuery("#loading").hide();a=jQuery("#commentform");d=jQuery("#cpajax_error_msg");d.hide()}h();function e(){jQuery("#loading").hide();jQuery("#submit").removeAttr("disabled");jQuery("#submit").show();addComment.enableForm();cpajax_submitting=false}function g(j){var m=a.find("#comment_parent").val();var o=jQuery("ol.commentlist:first");if(m!="0"){var n="#li-comment-"+m;var l=jQuery(n+" > .children:first");if(l[0]){b(j,n+" .children:first > li:last",l,n+" .children:first > li:last")}else{b(j,n+" .children:first",n,n+" .children:first > li:last")}}else{if(o[0]){b(j,"ol.commentlist:first > li:last",o,"ol.commentlist:first > li:last")}else{b(j,"ol.commentlist:first","div.comments_container","ol.commentlist:first > li:last")}}commentpress_enable_comment_permalink_clicks();var k=j.find("#comments_in_page_wrapper div.comments_container > h3");jQuery("#comments_in_page_wrapper div.comments_container > h3").replaceWith(k);a.find("#comment").val("");cpajax_reenable_featured_comments()}function b(j,l,m,k){if(j===undefined||j===null){return}j.find(l).hide().appendTo(m);i(l,k)}function c(j,l,m,k){if(j===undefined||j===null){return}j.find(l).hide().prependTo(m);i(l,k)}function i(l,k){var j=jQuery(k).prop("id");var m="#comment-"+j.split("-")[2];var n=jQuery(m);addComment.cancelForm();n.css("background","#c2d8bc");jQuery(l).slideDown("slow",function(){jQuery.scrollTo(n,{duration:cp_scroll_speed,axis:"y",offset:commentpress_get_header_offset(),onAfter:function(){n.animate({backgroundColor:"#ffffff"},1000,function(){n.css("background","transparent")})}})})}jQuery("#commentform").on("submit",function(j){cpajax_submitting=true;d.hide();if(a.find("#author")[0]){if(a.find("#author").val()==""){d.html('<span class="error">'+cpajax_lang[1]+"</span>");d.show();cpajax_submitting=false;return false}if(a.find("#email").val()==""){d.html('<span class="error">'+cpajax_lang[2]+"</span>");d.show();cpajax_submitting=false;return false}var k=/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;if(!k.test(a.find("#email").val())){d.html('<span class="error">'+cpajax_lang[3]+"</span>");d.show();if(j.preventDefault){j.preventDefault()}cpajax_submitting=false;return false}}if(cp_tinymce=="1"){tinyMCE.triggerSave();addComment.disableForm()}if(a.find("#comment").val()==""){d.html('<span class="error">'+cpajax_lang[4]+"</span>");d.show();addComment.enableForm();cpajax_submitting=false;return false}jQuery(this).ajaxSubmit({beforeSubmit:function(){jQuery("#loading").show();jQuery("#submit").prop("disabled","disabled");jQuery("#submit").hide()},error:function(l){d.empty();var m=l.responseText.match(/<p>(.*)<\/p>/);d.html('<span class="error">'+m[1]+"</span>");d.show();e();return false},success:function(m){try{var l=jQuery(m);g(l);e()}catch(n){e();alert(cpajax_lang[6]+"\n\n"+n)}}});return false})});