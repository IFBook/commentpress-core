if("undefined"!==typeof CommentpressAjaxSettings){var cpajax_live=CommentpressAjaxSettings.cpajax_live;var cpajax_ajax_url=CommentpressAjaxSettings.cpajax_ajax_url;var cpajax_spinner_url=CommentpressAjaxSettings.cpajax_spinner_url;var cpajax_post_id=CommentpressAjaxSettings.cpajax_post_id}var cpajax_submitting=false;function cpajax_ajax_callback(data){var diff=parseInt(data.cpajax_comment_count)-parseInt(CommentpressAjaxSettings.cpajax_comment_count);if(diff>0){for(var i=1;i<=diff;i++){var comment=eval("data.cpajax_new_comment_"+i);cpajax_add_new_comment(jQuery(comment.markup),comment.text_sig,comment.parent,comment.id);CommentpressAjaxSettings.cpajax_comment_count++}}}function cpajax_add_new_comment(o,m,g,f){var n=jQuery("div.comments_container");if(n.find("#li-comment-"+f)[0]){return}var b="#para_wrapper-"+m;var i="#para_heading-"+m;var k=jQuery(b+" ol.commentlist:first");if(g!="0"){var h="#li-comment-"+g;var e=jQuery(h+" > ol.children:first");if(e[0]){o.hide().css("background","#c2d8bc").appendTo(e).slideDown("fast",function(){o.animate({backgroundColor:"#ffffff"},1000,function(){o.css("background","transparent")})})}else{o.wrap('<ol class="children" />').parent().css("background","#c2d8bc").hide().appendTo(h).slideDown("fast",function(){o.parent().animate({backgroundColor:"#ffffff"},1000,function(){o.parent().css("background","transparent")})})}}else{if(k[0]){o.hide().css("background","#c2d8bc").appendTo(k).slideDown("fast",function(){o.animate({backgroundColor:"#ffffff"},1000,function(){o.css("background","transparent")})})}else{o.wrap('<ol class="commentlist" />').parent().css("background","#c2d8bc").hide().prependTo(b).slideDown("fast",function(){o.parent().animate({backgroundColor:"#ffffff"},1000,function(){o.parent().css("background","transparent")})})}}var j=n.find(i+" a");var l=j.text().split(" ");var c=parseInt(l[0]);c++;l[0]=c;l[1]="Comments";if(c==1){l[1]="Comment"}j.text(l.join(" "));j.css("background","#c2d8bc");j.animate({backgroundColor:"#EFEFEF"},1000);var c=c.toString();var a="#textblock-"+m;var d=a+" span small";jQuery(d).html(c);if(c=="1"){jQuery(a+" span.commenticonbox a.para_permalink").addClass("has_comments");jQuery(d).css("visibility","visible")}commentpress_enable_comment_permalink_clicks();commentpress_setup_comment_headers()}function cpajax_ajax_update(){if(cpajax_submitting){return}jQuery.post(cpajax_ajax_url,{action:"cpajax_get_new_comments",last_count:CommentpressAjaxSettings.cpajax_comment_count,post_id:cpajax_post_id},function(a,b){if(b=="success"){cpajax_ajax_callback(a)}},"json")}function cpajax_ajax_updater(a){if(a=="1"){CommentpressAjaxSettings.interval=window.setInterval(cpajax_ajax_update,5000)}else{window.clearInterval(CommentpressAjaxSettings.interval)}}function cpajax_reassign_comments(){var a=jQuery("#comments_sidebar .comment-wrapper .comment-assign");a.show();a.draggable({helper:"clone",cursor:"move"});var b=jQuery("#content .post .textblock");b.droppable({accept:".comment-assign",hoverClass:"selected_para selected_dropzone",drop:function(f,g){var d=jQuery(this).attr("id").split("-")[1];var c={resizable:false,height:160,zIndex:3999,modal:true,dialogClass:"wp-dialog",buttons:{Yes:function(){jQuery(this).dialog("option","disabled",true);jQuery(".ui-dialog-buttonset").html('<img src="'+cpajax_spinner_url+'" id="loading" alt="'+cpajax_lang[0]+'" />');jQuery(".ui-dialog-title").html(cpajax_lang[9]);jQuery(".cp_alert_text").html(cpajax_lang[10]);cpajax_reassign(d,g)},Cancel:function(){jQuery(this).dialog("close");jQuery(this).dialog("destroy");jQuery(this).remove()}}};var e=cpajax_lang[8];var h=jQuery('<div><p class="cp_alert_text">'+e+"</p></div>");h.attr("title",cpajax_lang[7]).appendTo("body").dialog(c)}})}function cpajax_reassign(d,e){var c=jQuery(e.draggable).attr("id").split("-")[1];var f=jQuery(e.draggable).closest("li.comment");var b=f;var a=f.siblings("li.comment");if(a.length==0){var g=f.parent("ol.commentlist");b=g}jQuery(b).slideUp("slow",function(){jQuery.post(cpajax_ajax_url,{action:"cpajax_reassign_comment",text_signature:d,comment_id:c},function(h,i){if(i=="success"){document.location.reload(true)}else{alert(i)}},"json")})}jQuery(document).ready(function(f){cpajax_ajax_updater(cpajax_live);cpajax_reassign_comments();var a,d;function i(){jQuery("#respond_title").after('<div id="cpajax_error_msg"></div>');jQuery("#commentform").after('<img src="'+cpajax_spinner_url+'" id="loading" alt="'+cpajax_lang[0]+'" />');jQuery("#loading").hide();a=jQuery("#commentform");d=jQuery("#cpajax_error_msg");d.hide()}i();function e(){jQuery("#loading").hide();jQuery("#submit").removeAttr("disabled");jQuery("#submit").show();addComment.enableForm();cpajax_submitting=false}function h(m){var t=a.find("#text_signature").val();var o=a.find("#comment_parent").val();var k="#para_wrapper-"+t;var p="#para_heading-"+t;jQuery(k).removeClass("no_comments");if(o!="0"){var q="#li-comment-"+o;var n=jQuery(q+" > ol.children:first");if(n[0]){b(m,q+" > ol.children:first > li:last",n,q+" > ol.children:first > li:last")}else{b(m,q+" > ol.children:first",q,q+" > ol.children:first > li:last")}}else{var s=jQuery(k+" > ol.commentlist:first");if(s[0]){b(m,k+" > ol.commentlist:first > li:last",s,k+" > ol.commentlist:first > li:last")}else{c(m,k+" > ol.commentlist:first",k,k+" > ol.commentlist:first > li:last")}}jQuery("#respond").slideUp("fast",function(){addComment.cancelForm()});var r=m.find(p);jQuery(p).replaceWith(r);var l=r.text().split(" ")[0];g(t,l);commentpress_enable_comment_permalink_clicks();commentpress_setup_comment_headers();cpajax_reassign_comments();a.find("#comment").val("")}function b(k,m,n,l){if(k===undefined||k===null){return}k.find(m).hide().appendTo(n);j(m,l)}function c(k,m,n,l){if(k===undefined||k===null){return}k.find(m).hide().prependTo(n);j(m,l)}function j(m,l){var k=jQuery(l).attr("id");var n="#comment-"+k.split("-")[2];var o=jQuery(n);o.css("background","#c2d8bc");jQuery(m).slideDown("slow",function(){jQuery("#comments_sidebar .sidebar_contents_wrapper").scrollTo(o,{duration:cp_scroll_speed,axis:"y",onAfter:function(){o.animate({backgroundColor:"#ffffff"},1000,function(){o.css("background","transparent")})}})})}function g(n,l){var l=l.toString();var k="#textblock-"+n;var m=k+" span small";jQuery(m).html(l);if(l=="1"){jQuery(k+" span.commenticonbox a.para_permalink").addClass("has_comments");jQuery(m).css("visibility","visible")}if(n!=""){var o=jQuery(k);commentpress_scroll_page(o)}else{commentpress_scroll_to_top(0,cp_scroll_speed)}}jQuery("#commentform").on("submit",function(k){cpajax_submitting=true;d.hide();if(a.find("#author")[0]){if(a.find("#author").val()==""){d.html('<span class="error">'+cpajax_lang[1]+"</span>");d.show();cpajax_submitting=false;return false}if(a.find("#email").val()==""){d.html('<span class="error">'+cpajax_lang[2]+"</span>");d.show();cpajax_submitting=false;return false}var l=/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;if(!l.test(a.find("#email").val())){d.html('<span class="error">'+cpajax_lang[3]+"</span>");d.show();if(k.preventDefault){k.preventDefault()}cpajax_submitting=false;return false}}if(cp_tinymce=="1"){tinyMCE.triggerSave();addComment.disableForm()}if(a.find("#comment").val()==""){d.html('<span class="error">'+cpajax_lang[4]+"</span>");d.show();addComment.enableForm();cpajax_submitting=false;return false}jQuery(this).ajaxSubmit({beforeSubmit:function(){jQuery("#loading").show();jQuery("#submit").attr("disabled","disabled");jQuery("#submit").hide()},error:function(m){d.empty();var n=m.responseText.match(/<p>(.*)<\/p>/);d.html('<span class="error">'+n[1]+"</span>");d.show();e();return false},success:function(n){try{var m=jQuery(n);h(m);e()}catch(o){e();alert(cpajax_lang[6]+"\n\n"+o)}}});return false})});