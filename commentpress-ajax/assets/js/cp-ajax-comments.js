CommentPress.ajax={};CommentPress.ajax.comments=new function(){var me=this,$=jQuery.noConflict();this.cpajax_submitting=false;this.cpajax_form={};this.cpajax_error={};if("undefined"!==typeof CommentpressAjaxSettings){this.cpajax_live=CommentpressAjaxSettings.cpajax_live;this.cpajax_ajax_url=CommentpressAjaxSettings.cpajax_ajax_url;this.cpajax_spinner_url=CommentpressAjaxSettings.cpajax_spinner_url;this.cpajax_post_id=CommentpressAjaxSettings.cpajax_post_id;this.cpajax_lang=CommentpressAjaxSettings.cpajax_lang}this.init=function(){};this.dom_ready=function(){me.updater(me.cpajax_live);$("#respond_title").after('<div id="cpajax_error_msg"></div>');$("#submit").after('<img src="'+me.cpajax_spinner_url+'" id="loading" alt="'+me.cpajax_lang[0]+'" />');$("#loading").hide();me.cpajax_form=$("#commentform");me.cpajax_error=$("#cpajax_error_msg");me.cpajax_error.hide();me.initialise_form();me.reassign_comments();me.listeners()};this.listeners=function(){$(document).on("commentpress-document-ready",function(event){me.reassign_comments();cpajax_reenable_featured_comments();cpajax_reenable_comment_upvoter()});$(document).on("fee-after-save",function(event){me.reassign_comments();cpajax_reenable_featured_comments();cpajax_reenable_comment_upvoter()})};this.reset=function(){$("#loading").hide();$("#submit").removeAttr("disabled");$("#submit").show();addComment.enableForm();me.cpajax_submitting=false};this.updater=function(toggle){if(toggle=="1"){CommentpressAjaxSettings.interval=window.setInterval(me.update,5000)}else{window.clearInterval(CommentpressAjaxSettings.interval)}};this.update=function(){if(me.cpajax_submitting){return}$.post(me.cpajax_ajax_url,{action:"cpajax_get_new_comments",last_count:CommentpressAjaxSettings.cpajax_comment_count,post_id:me.cpajax_post_id},function(data,textStatus){if(textStatus=="success"){me.callback(data)}},"json")};this.callback=function(data){var diff,i,comment;diff=parseInt(data.cpajax_comment_count)-parseInt(CommentpressAjaxSettings.cpajax_comment_count);if(diff>0){for(i=1;i<=diff;i++){comment=eval("data.cpajax_new_comment_"+i);me.add_new_comment($(comment.markup),comment.text_sig,comment.parent,comment.id);CommentpressAjaxSettings.cpajax_comment_count++}}};this.add_new_comment=function(markup,text_sig,comm_parent,comm_id){var comment_container,para_id,head_id,comm_list,parent_id,child_list,head,head_array,comment_num,new_comment_count;comment_container=$("div.comments_container");if(comment_container.find("#li-comment-"+comm_id)[0]){return}para_id="#para_wrapper-"+text_sig;head_id="#para_heading-"+text_sig;comm_list=$(para_id+" ol.commentlist:first");if(comm_parent!="0"){parent_id="#li-comment-"+comm_parent;child_list=$(parent_id+" > ol.children:first");if(child_list[0]){markup.hide().addClass("comment-highlighted").appendTo(child_list).slideDown("fast",function(){markup.addClass("comment-fade")})}else{markup.wrap('<ol class="children" />').parent().addClass("comment-highlighted").hide().appendTo(parent_id).slideDown("fast",function(){markup.parent().addClass("comment-fade")})}}else{if(comm_list[0]){markup.hide().addClass("comment-highlighted").appendTo(comm_list).slideDown("fast",function(){markup.addClass("comment-fade")})}else{markup.wrap('<ol class="commentlist" />').parent().addClass("comment-highlighted").hide().prependTo(para_id).slideDown("fast",function(){markup.parent().addClass("comment-fade")})}}comment_num=parseInt($(head_id+" a span.cp_comment_num").text());new_comment_count=comment_num+1;me.update_comments_para_heading(head_id,new_comment_count);head=$(head_id);head.addClass("notransition");if(head.hasClass("heading-fade")){head.removeClass("heading-fade")}if(head.hasClass("heading-highlighted")){head.removeClass("heading-highlighted")}head.addClass("heading-highlighted");head.removeClass("notransition");head.height();head.addClass("heading-fade");me.update_para_icon(text_sig,new_comment_count);me.reassign_comments();cpajax_reenable_featured_comments();cpajax_reenable_comment_upvoter();$(document).trigger("commentpress-ajax-new-comment-added",[comm_id])};this.reassign_comments=function(){var draggers,droppers,text_sig,options,alert_text,div;var draggers=$("#comments_sidebar .comment-wrapper .comment-assign");draggers.show();draggers.draggable({helper:"clone",cursor:"move"});droppers=$("#content .post .textblock");droppers.droppable({accept:".comment-assign",hoverClass:"selected_para selected_dropzone",drop:function(event,ui){text_sig=$(this).prop("id").split("-")[1];options={resizable:false,width:400,height:200,zIndex:3999,modal:true,dialogClass:"wp-dialog",buttons:{Yes:function(){$(this).dialog("option","disabled",true);$(".ui-dialog-buttonset").html('<img src="'+me.cpajax_spinner_url+'" id="loading" alt="'+me.cpajax_lang[0]+'" />');$(".ui-dialog-title").html(me.cpajax_lang[9]);$(".cp_alert_text").html(me.cpajax_lang[10]);me.reassign(text_sig,ui)},Cancel:function(){$(this).dialog("close");$(this).dialog("destroy");$(this).remove()}}};alert_text=me.cpajax_lang[8];div=$('<div><p class="cp_alert_text">'+alert_text+"</p></div>");div.prop("title",me.cpajax_lang[7]).appendTo("body").dialog(options)}})};this.reassign=function(text_sig,ui){var comment_id,comment_item,comment_to_move,other_comments,comment_list;comment_id=$(ui.draggable).prop("id").split("-")[1];comment_item=$(ui.draggable).closest("li.comment");comment_to_move=comment_item;other_comments=comment_item.siblings("li.comment");if(other_comments.length==0){comment_list=comment_item.parent("ol.commentlist");comment_to_move=comment_list}$(comment_to_move).slideUp("slow",function(){$.post(me.cpajax_ajax_url,{action:"cpajax_reassign_comment",text_signature:text_sig,comment_id:comment_id},function(data,textStatus){if(textStatus=="success"){document.location.reload(true)}else{alert(textStatus)}},"json")})};this.add_comment=function(response){var text_sig,comm_parent,para_id,head_id,parent_id,child_list,comm_list,comment_num,new_comment_count,new_comm_id;text_sig=me.cpajax_form.find("#text_signature").val();comm_parent=me.cpajax_form.find("#comment_parent").val();para_id="#para_wrapper-"+text_sig;head_id="#para_heading-"+text_sig;$(para_id).removeClass("no_comments");if(comm_parent!="0"){parent_id="#li-comment-"+comm_parent;child_list=$(parent_id+" > ol.children:first");if(child_list[0]){new_comm_id=me.nice_append(response,parent_id+" > ol.children:first > li:last",child_list,parent_id+" > ol.children:first > li:last")}else{new_comm_id=me.nice_append(response,parent_id+" > ol.children:first",parent_id,parent_id+" > ol.children:first > li:last")}}else{comm_list=$(para_id+" > ol.commentlist:first");if(comm_list[0]){new_comm_id=me.nice_append(response,para_id+" > ol.commentlist:first > li:last",comm_list,para_id+" > ol.commentlist:first > li:last")}else{new_comm_id=me.nice_prepend(response,para_id+" > ol.commentlist:first",para_id,para_id+" > ol.commentlist:first > li:last")}}$("#respond").slideUp("fast",function(){addComment.cancelForm()});comment_num=parseInt($(head_id+" a span.cp_comment_num").text());new_comment_count=comment_num+1;me.update_comments_para_heading(head_id,new_comment_count);me.update_para_icon(text_sig,new_comment_count);if(text_sig!=""){CommentPress.common.content.scroll_page($("#textblock-"+text_sig))}else{CommentPress.theme.viewport.scroll_to_top(0,cp_scroll_speed)}me.reassign_comments();me.cpajax_form.find("#comment").val("");cpajax_reenable_featured_comments();cpajax_reenable_comment_upvoter();$(document).trigger("commentpress-ajax-comment-added",[new_comm_id])};this.nice_append=function(response,content,target,last){var new_comm_id;if("undefined"===typeof response||response===null){return}response.find(content).clone().hide().appendTo(target);new_comm_id=me.cleanup(content,last);return new_comm_id};this.nice_prepend=function(response,content,target,last){var new_comm_id;if("undefined"===typeof response||response===null){return}response.find(content).clone().hide().prependTo(target);new_comm_id=me.cleanup(content,last);return new_comm_id};this.cleanup=function(content,last){var last_id,new_comm_id,comment;last_id=$(last).prop("id");new_comm_id="#comment-"+(last_id.toString().split("-")[2]);comment=$(new_comm_id);comment.addClass("comment-highlighted");$(content).slideDown("slow",function(){$("#comments_sidebar .sidebar_contents_wrapper").scrollTo(comment,{duration:cp_scroll_speed,axis:"y",onAfter:function(){comment.addClass("comment-fade");$(document).trigger("commentpress-ajax-comment-added-scrolled")}})});return new_comm_id};this.update_comments_para_heading=function(head_id,new_comment_count){$(head_id+" a span.cp_comment_num").text(new_comment_count.toString());if(new_comment_count==1){$(head_id+" a span.cp_comment_word").text(me.cpajax_lang[11])}if(new_comment_count>1){$(head_id+" a span.cp_comment_word").text(me.cpajax_lang[12])}};this.update_para_icon=function(text_sig,new_comment_count){var textblock_id;textblock_id="#textblock-"+text_sig;$(textblock_id+" span small").text(new_comment_count.toString());if(new_comment_count==1){$(textblock_id+" span.commenticonbox a.para_permalink").addClass("has_comments");$(textblock_id+" span small").css("visibility","visible")}};this.initialise_form=function(){$("#commentform").off("submit");$("#commentform").on("submit",function(event){var filter;me.cpajax_submitting=true;me.cpajax_error.hide();if(me.cpajax_form.find("#author")[0]){if(me.cpajax_form.find("#author").val()==""){me.cpajax_error.html('<span class="error">'+me.cpajax_lang[1]+"</span>");me.cpajax_error.show();me.cpajax_submitting=false;return false}if(me.cpajax_form.find("#email").val()==""){me.cpajax_error.html('<span class="error">'+me.cpajax_lang[2]+"</span>");me.cpajax_error.show();me.cpajax_submitting=false;return false}filter=/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;if(!filter.test(me.cpajax_form.find("#email").val())){me.cpajax_error.html('<span class="error">'+me.cpajax_lang[3]+"</span>");me.cpajax_error.show();if(event.preventDefault){event.preventDefault()}me.cpajax_submitting=false;return false}}if(cp_tinymce=="1"){tinyMCE.triggerSave();addComment.disableForm()}if(me.cpajax_form.find("#comment").val()==""){me.cpajax_error.html('<span class="error">'+me.cpajax_lang[4]+"</span>");me.cpajax_error.show();addComment.enableForm();me.cpajax_submitting=false;return false}$(this).ajaxSubmit({beforeSubmit:function(){$("#loading").show();$("#submit").prop("disabled","disabled");$("#submit").hide()},error:function(request){var data;me.cpajax_error.empty();data=request.responseText.match(/<p>(.*)<\/p>/);me.cpajax_error.html('<span class="error">'+data[1]+"</span>");me.cpajax_error.show();me.reset();return false},success:function(data){var response;if($.parseHTML){response=$($.parseHTML(data))}else{response=$(data)}try{me.add_comment(response);me.reset()}catch(e){me.reset();alert(me.cpajax_lang[6]+"\n\n"+e)}}});return false})}};function cpajax_reenable_featured_comments(){if("undefined"!==typeof featured_comments){if(jQuery.is_function_defined("featured_comments_click")){featured_comments_click()}}}function cpajax_reenable_comment_upvoter(){if("undefined"!==typeof comment_upvoter){if(jQuery.is_function_defined("comment_upvoter_click")){comment_upvoter_click()}}}CommentPress.ajax.comments.init();jQuery(document).ready(function(a){CommentPress.ajax.comments.dom_ready()});