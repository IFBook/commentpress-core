function cpajax_reenable_featured_comments(){"undefined"!=typeof featured_comments&&jQuery.is_function_defined("featured_comments_click")&&featured_comments_click()}function cpajax_reenable_comment_upvoter(){"undefined"!=typeof comment_upvoter&&jQuery.is_function_defined("comment_upvoter_click")&&comment_upvoter_click()}CommentPress.ajax={},CommentPress.ajax.comments=new function(){var me=this,$=jQuery.noConflict();this.cpajax_submitting=!1,this.cpajax_form={},this.cpajax_error={},"undefined"!=typeof CommentpressAjaxSettings&&(this.cpajax_live=CommentpressAjaxSettings.cpajax_live,this.cpajax_ajax_url=CommentpressAjaxSettings.cpajax_ajax_url,this.cpajax_spinner_url=CommentpressAjaxSettings.cpajax_spinner_url,this.cpajax_post_id=CommentpressAjaxSettings.cpajax_post_id,this.cpajax_lang=CommentpressAjaxSettings.cpajax_lang),this.init=function(){},this.dom_ready=function(){me.updater(me.cpajax_live),$("#respond_title").after('<div id="cpajax_error_msg"></div>'),$("#submit").after('<img src="'+me.cpajax_spinner_url+'" id="loading" alt="'+me.cpajax_lang[0]+'" />'),$("#loading").hide(),me.cpajax_form=$("#commentform"),me.cpajax_error=$("#cpajax_error_msg"),me.cpajax_error.hide(),me.initialise_form(),me.reassign_comments(),me.listeners()},this.listeners=function(){$(document).on("commentpress-document-ready",function(e){me.reassign_comments(),cpajax_reenable_featured_comments(),cpajax_reenable_comment_upvoter()}),$(document).on("fee-after-save",function(e){me.reassign_comments(),cpajax_reenable_featured_comments(),cpajax_reenable_comment_upvoter()})},this.reset=function(){$("#loading").hide(),$("#submit").removeAttr("disabled"),$("#submit").show(),addComment.enableForm(),me.cpajax_submitting=!1},this.updater=function(e){"1"==e?CommentpressAjaxSettings.interval=window.setInterval(me.update,5e3):window.clearInterval(CommentpressAjaxSettings.interval)},this.update=function(){me.cpajax_submitting||$.post(me.cpajax_ajax_url,{action:"cpajax_get_new_comments",last_count:CommentpressAjaxSettings.cpajax_comment_count,post_id:me.cpajax_post_id},function(e,a){"success"==a&&me.callback(e)},"json")},this.callback=function(data){var diff,i,comment;if(diff=parseInt(data.cpajax_comment_count)-parseInt(CommentpressAjaxSettings.cpajax_comment_count),diff>0)for(i=1;diff>=i;i++)comment=eval("data.cpajax_new_comment_"+i),me.add_new_comment($(comment.markup),comment.text_sig,comment.parent,comment.id),CommentpressAjaxSettings.cpajax_comment_count++},this.add_new_comment=function(e,a,t,n){var m,o,s,i,r,c,p,l,d;m=$("div.comments_container"),m.find("#li-comment-"+n)[0]||(o="#para_wrapper-"+a,s="#para_heading-"+a,i=$(o+" ol.commentlist:first"),"0"!=t?(r="#li-comment-"+t,c=$(r+" > ol.children:first"),c[0]?e.hide().addClass("comment-highlighted").appendTo(c).slideDown("fast",function(){e.addClass("comment-fade")}):e.wrap('<ol class="children" />').parent().addClass("comment-highlighted").hide().appendTo(r).slideDown("fast",function(){e.parent().addClass("comment-fade")})):i[0]?e.hide().addClass("comment-highlighted").appendTo(i).slideDown("fast",function(){e.addClass("comment-fade")}):e.wrap('<ol class="commentlist" />').parent().addClass("comment-highlighted").hide().prependTo(o).slideDown("fast",function(){e.parent().addClass("comment-fade")}),l=parseInt($(s+" a span.cp_comment_num").text()),d=l+1,me.update_comments_para_heading(s,d),p=$(s),p.addClass("notransition"),p.hasClass("heading-fade")&&p.removeClass("heading-fade"),p.hasClass("heading-highlighted")&&p.removeClass("heading-highlighted"),p.addClass("heading-highlighted"),p.removeClass("notransition"),p.height(),p.addClass("heading-fade"),me.update_para_icon(a,d),me.reassign_comments(),cpajax_reenable_featured_comments(),cpajax_reenable_comment_upvoter(),$(document).trigger("commentpress-ajax-new-comment-added",[n]))},this.reassign_comments=function(){var e,a,t,n,m,o,e=$("#comments_sidebar .comment-wrapper .comment-assign");e.show(),e.draggable({helper:"clone",cursor:"move"}),a=$("#content .post .textblock"),a.droppable({accept:".comment-assign",hoverClass:"selected_para selected_dropzone",drop:function(e,a){t=$(this).prop("id").split("-")[1],n={resizable:!1,height:160,zIndex:3999,modal:!0,dialogClass:"wp-dialog",buttons:{Yes:function(){$(this).dialog("option","disabled",!0),$(".ui-dialog-buttonset").html('<img src="'+me.cpajax_spinner_url+'" id="loading" alt="'+me.cpajax_lang[0]+'" />'),$(".ui-dialog-title").html(me.cpajax_lang[9]),$(".cp_alert_text").html(me.cpajax_lang[10]),me.reassign(t,a)},Cancel:function(){$(this).dialog("close"),$(this).dialog("destroy"),$(this).remove()}}},m=me.cpajax_lang[8],o=$('<div><p class="cp_alert_text">'+m+"</p></div>"),o.prop("title",me.cpajax_lang[7]).appendTo("body").dialog(n)}})},this.reassign=function(e,a){var t,n,m,o,s;t=$(a.draggable).prop("id").split("-")[1],n=$(a.draggable).closest("li.comment"),m=n,o=n.siblings("li.comment"),0==o.length&&(s=n.parent("ol.commentlist"),m=s),$(m).slideUp("slow",function(){$.post(me.cpajax_ajax_url,{action:"cpajax_reassign_comment",text_signature:e,comment_id:t},function(e,a){"success"==a?document.location.reload(!0):alert(a)},"json")})},this.add_comment=function(e){var a,t,n,m,o,s,i,r,c,p;a=me.cpajax_form.find("#text_signature").val(),t=me.cpajax_form.find("#comment_parent").val(),n="#para_wrapper-"+a,m="#para_heading-"+a,$(n).removeClass("no_comments"),"0"!=t?(o="#li-comment-"+t,s=$(o+" > ol.children:first"),p=s[0]?me.nice_append(e,o+" > ol.children:first > li:last",s,o+" > ol.children:first > li:last"):me.nice_append(e,o+" > ol.children:first",o,o+" > ol.children:first > li:last")):(i=$(n+" > ol.commentlist:first"),p=i[0]?me.nice_append(e,n+" > ol.commentlist:first > li:last",i,n+" > ol.commentlist:first > li:last"):me.nice_prepend(e,n+" > ol.commentlist:first",n,n+" > ol.commentlist:first > li:last")),$("#respond").slideUp("fast",function(){addComment.cancelForm()}),r=parseInt($(m+" a span.cp_comment_num").text()),c=r+1,me.update_comments_para_heading(m,c),me.update_para_icon(a,c),""!=a?CommentPress.common.content.scroll_page($("#textblock-"+a)):CommentPress.theme.viewport.scroll_to_top(0,cp_scroll_speed),me.reassign_comments(),me.cpajax_form.find("#comment").val(""),cpajax_reenable_featured_comments(),cpajax_reenable_comment_upvoter(),$(document).trigger("commentpress-ajax-comment-added",[p])},this.nice_append=function(e,a,t,n){var m;if("undefined"!=typeof e&&null!==e)return e.find(a).clone().hide().appendTo(t),m=me.cleanup(a,n)},this.nice_prepend=function(e,a,t,n){var m;if("undefined"!=typeof e&&null!==e)return e.find(a).clone().hide().prependTo(t),m=me.cleanup(a,n)},this.cleanup=function(e,a){var t,n,m;return t=$(a).prop("id"),n="#comment-"+t.toString().split("-")[2],m=$(n),m.addClass("comment-highlighted"),$(e).slideDown("slow",function(){$("#comments_sidebar .sidebar_contents_wrapper").scrollTo(m,{duration:cp_scroll_speed,axis:"y",onAfter:function(){m.addClass("comment-fade"),$(document).trigger("commentpress-ajax-comment-added-scrolled")}})}),n},this.update_comments_para_heading=function(e,a){$(e+" a span.cp_comment_num").text(a.toString()),1==a&&$(e+" a span.cp_comment_word").text(me.cpajax_lang[11]),a>1&&$(e+" a span.cp_comment_word").text(me.cpajax_lang[12])},this.update_para_icon=function(e,a){var t;t="#textblock-"+e,$(t+" span small").text(a.toString()),1==a&&($(t+" span.commenticonbox a.para_permalink").addClass("has_comments"),$(t+" span small").css("visibility","visible"))},this.initialise_form=function(){$("#commentform").off("submit"),$("#commentform").on("submit",function(e){var a;if(me.cpajax_submitting=!0,me.cpajax_error.hide(),me.cpajax_form.find("#author")[0]){if(""==me.cpajax_form.find("#author").val())return me.cpajax_error.html('<span class="error">'+me.cpajax_lang[1]+"</span>"),me.cpajax_error.show(),me.cpajax_submitting=!1,!1;if(""==me.cpajax_form.find("#email").val())return me.cpajax_error.html('<span class="error">'+me.cpajax_lang[2]+"</span>"),me.cpajax_error.show(),me.cpajax_submitting=!1,!1;if(a=/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/,!a.test(me.cpajax_form.find("#email").val()))return me.cpajax_error.html('<span class="error">'+me.cpajax_lang[3]+"</span>"),me.cpajax_error.show(),e.preventDefault&&e.preventDefault(),me.cpajax_submitting=!1,!1}return"1"==cp_tinymce&&(tinyMCE.triggerSave(),addComment.disableForm()),""==me.cpajax_form.find("#comment").val()?(me.cpajax_error.html('<span class="error">'+me.cpajax_lang[4]+"</span>"),me.cpajax_error.show(),addComment.enableForm(),me.cpajax_submitting=!1,!1):($(this).ajaxSubmit({beforeSubmit:function(){$("#loading").show(),$("#submit").prop("disabled","disabled"),$("#submit").hide()},error:function(e){var a;return me.cpajax_error.empty(),a=e.responseText.match(/<p>(.*)<\/p>/),me.cpajax_error.html('<span class="error">'+a[1]+"</span>"),me.cpajax_error.show(),me.reset(),!1},success:function(e){var a;a=$($.parseHTML?$.parseHTML(e):e);try{me.add_comment(a),me.reset()}catch(t){me.reset(),alert(me.cpajax_lang[6]+"\n\n"+t)}}}),!1)})}},CommentPress.ajax.comments.init(),jQuery(document).ready(function(e){CommentPress.ajax.comments.dom_ready()});