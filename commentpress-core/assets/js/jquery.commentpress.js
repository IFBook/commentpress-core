function commentpress_setup_init(){CommentPress.setup.navigation.init(),CommentPress.setup.content.init(),CommentPress.setup.comments.init(),CommentPress.setup.activity.init(),jQuery(document).trigger("commentpress-initialised")}var msie_detected=!1;if("undefined"!=typeof cp_msie&&(msie_detected=!0),"undefined"!=typeof CommentpressSettings){var cp_wp_adminbar,cp_wp_adminbar_height,cp_wp_adminbar_expanded,cp_bp_adminbar,cp_comments_open,cp_special_page,cp_tinymce,cp_tinymce_version,cp_promote_reading,cp_is_mobile,cp_is_touch,cp_is_tablet,cp_cookie_path,cp_multipage_page,cp_template_dir,cp_plugin_dir,cp_toc_chapter_is_page,cp_show_subpages,cp_default_sidebar,cp_is_signup_page,cp_scroll_speed,cp_min_page_width,cp_textblock_meta;cp_wp_adminbar=CommentpressSettings.cp_wp_adminbar,cp_wp_adminbar_height=parseInt(CommentpressSettings.cp_wp_adminbar_height),cp_wp_adminbar_expanded=parseInt(CommentpressSettings.cp_wp_adminbar_expanded),cp_bp_adminbar=CommentpressSettings.cp_bp_adminbar,cp_comments_open=CommentpressSettings.cp_comments_open,cp_special_page=CommentpressSettings.cp_special_page,cp_tinymce=CommentpressSettings.cp_tinymce,cp_tinymce_version=CommentpressSettings.cp_tinymce_version,cp_promote_reading=CommentpressSettings.cp_promote_reading,cp_is_mobile=CommentpressSettings.cp_is_mobile,cp_is_touch=CommentpressSettings.cp_is_touch,cp_is_tablet=CommentpressSettings.cp_is_tablet,cp_cookie_path=CommentpressSettings.cp_cookie_path,cp_multipage_page=CommentpressSettings.cp_multipage_page,cp_template_dir=CommentpressSettings.cp_template_dir,cp_plugin_dir=CommentpressSettings.cp_plugin_dir,cp_toc_chapter_is_page=CommentpressSettings.cp_toc_chapter_is_page,cp_show_subpages=CommentpressSettings.cp_show_subpages,cp_default_sidebar=CommentpressSettings.cp_default_sidebar,cp_is_signup_page=CommentpressSettings.cp_is_signup_page,cp_scroll_speed=CommentpressSettings.cp_js_scroll_speed,cp_min_page_width=CommentpressSettings.cp_min_page_width,cp_textblock_meta=CommentpressSettings.cp_textblock_meta}var CommentPress=CommentPress||{};CommentPress.settings={},CommentPress.settings.textblock=new function(){this.marker_mode="marker",this.setMarkerMode=function(e){this.marker_mode=e},this.getMarkerMode=function(){return this.marker_mode}},CommentPress.setup={},CommentPress.setup.navigation=new function(){var e=this,t=jQuery.noConflict();this.init=function(){e.headings(),e.menu()},this.headings=function(){t("h3.activity_heading").css("cursor","pointer"),t("#toc_sidebar").on("click","h3.activity_heading",function(e){var n;e.preventDefault(),n=t(this).next("div.paragraph_wrapper"),n.css("width",t(this).parent().css("width")),n.slideToggle("slow",function(){n.css("width","auto")})})},this.menu=function(){t("#toc_sidebar").on("click","ul#toc_list li a",function(e){if("0"==cp_toc_chapter_is_page){var n;n=t(this).parent().find("ul"),n.length>0&&("0"==cp_show_subpages&&t(this).next("ul").slideToggle(),e.preventDefault())}})}},CommentPress.setup.content=new function(){var e=this,t=jQuery.noConflict();this.init=function(){e.title_links(),e.textblocks(),e.para_markers(),e.comment_icons(),e.links_in_textblocks(),e.footnotes_compatibility()},this.title_links=function(){t("#container").on("click",".post_title a",function(e){e.preventDefault();var t="";CommentPress.theme.viewport.align_content(t,"marker")})},this.textblocks=function(){"0"==cp_is_mobile&&"0"==cp_textblock_meta&&(t("#container").on("mouseover",".textblock",function(e){t(this).addClass("textblock-in")}),t("#container").on("mouseout",".textblock",function(e){t(this).removeClass("textblock-in")})),t("#container").on("click",".textblock",function(e){var n;n=t(this).prop("id"),n=n.split("textblock-")[1],CommentPress.theme.viewport.align_content(n,CommentPress.settings.textblock.getMarkerMode()),t(document).trigger("commentpress-textblock-clicked")})},this.para_markers=function(){t("#container").on("click","span.para_marker a",function(e){e.preventDefault(),t(document).trigger("commentpress-paramarker-clicked")}),t("#container").on("mouseenter","span.para_marker a",function(e){var n;n=t(this).parent().next().children(".comment_count"),n.addClass("js-hover")}),t("#container").on("mouseleave","span.para_marker a",function(e){var n;n=t(this).parent().next().children(".comment_count"),n.removeClass("js-hover")})},this.comment_icons=function(){t("#container").on("click",".commenticonbox",function(e){var n;e.preventDefault(),e.stopPropagation(),n=t(this).children("a.para_permalink").prop("href").split("#")[1],CommentPress.theme.viewport.align_content(n,"auto"),t(document).trigger("commentpress-commenticonbox-clicked")}),t("#container").on("click","a.para_permalink",function(e){e.preventDefault()}),t("#container").on("mouseenter","a.para_permalink",function(e){var n;n=t(this).prop("href").split("#")[1],t("span.para_marker a#"+n).addClass("js-hover")}),t("#container").on("mouseleave","a.para_permalink",function(e){var n;n=t(this).prop("href").split("#")[1],t("span.para_marker a#"+n).removeClass("js-hover")})},this.links_in_textblocks=function(){t("#container").on("click","a.cp_para_link",function(e){var n;e.preventDefault(),n=t(this).prop("href").split("#")[1],CommentPress.theme.viewport.align_content(n,"auto")})},this.footnotes_compatibility=function(){t("#container").on("click","span.footnotereverse a, a.footnote-back-link",function(e){var n;e.preventDefault(),n=t(this).prop("href").split("#")[1],t.quick_scroll_page("#"+n,100)}),t("#container").on("click",".simple-footnotes ol li > a",function(e){var n;n=t(this).prop("href"),n.match("#return-note-")&&(e.preventDefault(),n=n.split("#")[1],t.quick_scroll_page("#"+n,100))}),t("#container").on("click","a.simple-footnote, sup.footnote a, sup a.footnote-identifier-link, a.zp-ZotpressInText",function(e){var n;e.preventDefault(),n=t(this).prop("href").split("#")[1],t.quick_scroll_page("#"+n,100)})},this.workflow_tabs=function(e,n){t("#literal .post").css("display","none"),t("#original .post").css("display","none"),t("#container").on("click","content-tabs li h2 a",function(i){var s;i.preventDefault(),s=this.href.split("#")[1],t(".post").css("display","none"),t(".workflow-wrapper").css("min-height","0"),t(".workflow-wrapper").css("padding-bottom","0"),t("#"+s+".workflow-wrapper").css("min-height",e),t("#"+s+".workflow-wrapper").css("padding-bottom",n),t("#"+s+" .post").css("display","block"),t("#content-tabs li").removeClass("default-content-tab"),t(this).parent().parent().addClass("default-content-tab")})}},CommentPress.setup.comments=new function(){var e=this,t=jQuery.noConflict();this.init=function(){e.header(),e.minimiser(),e.comment_block_permalinks(),e.comment_permalinks(),e.comment_permalink_copy_links()},this.header=function(){t("#sidebar").on("click","#comments_header h2 a",function(e){e.preventDefault(),CommentPress.theme.sidebars.activate_sidebar("comments")})},this.minimiser=function(){t("#sidebar").on("click","#cp_minimise_all_comments",function(e){e.preventDefault(),t("#comments_sidebar div.paragraph_wrapper").slideUp(),t.unhighlight_para()})},this.comment_block_permalinks=function(){"1"!=cp_special_page&&(t("a.comment_block_permalink").css("cursor","pointer"),t("#comments_sidebar").on("click","a.comment_block_permalink",function(e){var n,i,s,o,a,r,c,p,_,m;e.preventDefault(),n=t(this).parent().prop("id").split("para_heading-")[1],i=t(this).parent().next("div.paragraph_wrapper"),s=t("#para_wrapper-"+n).find("ol.commentlist"),o=!1,a=i.css("display"),"none"==a&&(o=!0),"undefined"!=typeof n&&(""!==n&&"pingbacksandtrackbacks"!=n?(r=t("#textblock-"+n),o?(t.unhighlight_para(),t.highlight_para(r),t.scroll_page(r)):"0"==cp_promote_reading?t("#para_wrapper-"+n).find("#respond")[0]?t.unhighlight_para():s[0]||(t.unhighlight_para(),t.highlight_para(r),t.scroll_page(r)):t.is_highlighted(r)&&t.unhighlight_para()):(t.unhighlight_para(),"pingbacksandtrackbacks"!=n&&(CommentPress.theme.viewport.scroll_to_top(0,cp_scroll_speed),page_highlight=!page_highlight))),"0"==cp_promote_reading&&"pingbacksandtrackbacks"!=n&&"y"==cp_comments_open&&(c=t("#comment_post_ID").prop("value"),p=t("#para_wrapper-"+n+" .reply_to_para").prop("id"),_=p.split("-")[1],m=t("#para_wrapper-"+n).find("#respond")[0],s.length>0&&s[0]?(o||m)&&addComment.moveFormToPara(_,n,c):(m||(i.css("display","none"),o=!0),addComment.moveFormToPara(_,n,c))),i.slideToggle("slow",function(){o&&t.scroll_comments(t("#para_heading-"+n),cp_scroll_speed)})}))},this.comment_permalinks=function(){t("#comments_sidebar").on("click",".comment_permalink",function(e){var n,i,s;e.preventDefault(),n=this.href.split("#")[1],"1"==cp_special_page?(i=CommentPress.theme.header.get_offset(),t.scrollTo(t("#"+n),{duration:cp_scroll_speed,axis:"y",offset:i})):(t.unhighlight_para(),s=t.get_text_sig_by_comment_id("#"+n),"pingbacksandtrackbacks"!=s&&t.scroll_page_to_textblock(s),t.scroll_comments(t("#"+n),cp_scroll_speed))})},this.comment_permalink_copy_links=function(){t("#comments_sidebar").on("click",".comment_permalink_copy",function(e){var n;n=t(this).parent().attr("href"),n&&window.prompt("Copy this link, then paste into where you need it",n)})},this.comment_rollovers=function(){t("#comments_sidebar").on("mouseenter",".comment-wrapper",function(e){t(this).addClass("background-highlight")}),t("#comments_sidebar").on("mouseleave",".comment-wrapper",function(e){t(this).removeClass("background-highlight")})}},CommentPress.setup.activity=new function(){var e=this,t=jQuery.noConflict();this.init=function(){e.header(),e.minimiser(),e.headings(),e.see_in_context_links()},this.header=function(){t("#sidebar").on("click","#activity_header h2 a",function(e){e.preventDefault(),CommentPress.theme.sidebars.activate_sidebar("activity")})},this.minimiser=function(){t("#sidebar").on("click","#cp_minimise_all_activity",function(e){e.preventDefault(),t("#activity_sidebar div.paragraph_wrapper").slideUp()})},this.headings=function(){t("h3.activity_heading").css("cursor","pointer"),t("#activity_sidebar").on("click","h3.activity_heading",function(e){var n;e.preventDefault(),n=t(this).next("div.paragraph_wrapper"),n.css("width",t(this).parent().css("width")),n.slideToggle("slow",function(){n.css("width","auto")})})},this.see_in_context_links=function(){"1"!=cp_special_page&&t("#activity_sidebar").on("click","a.comment_on_post",function(e){var n,i,s,o,a,r;e.preventDefault(),CommentPress.theme.sidebars.activate_sidebar("comments"),n=this.href.split("#")[1],i=t("#"+n),s=i.parents("div.paragraph_wrapper").map(function(){return this}),s.length>0&&(o=t(s[0]),o.show(),"1"==cp_special_page?(a=CommentPress.theme.header.get_offset(),t.scrollTo(i,{duration:cp_scroll_speed,axis:"y",offset:a})):(t.unhighlight_para(),r=o.prop("id").split("-")[1],t.scroll_page_to_textblock(r),t("#comments_sidebar .sidebar_contents_wrapper").scrollTo(i,{duration:cp_scroll_speed,axis:"y",onAfter:function(){t.highlight_comment(i)}})))})}},function($){var highlighted_para="";$.highlight_para=function(e){"object"==typeof e&&e.addClass("selected_para")},$.unhighlight_para=function(){var e=$(".textblock");e.removeClass("selected_para")},$.get_highlighted_para=function(){return highlighted_para},$.is_highlighted=function(e){return"object"!=typeof e?!1:e.hasClass("selected_para")?!0:!1},$.set_sidebar_height=function(){var e=$("#sidebar"),t=$("#sidebar_inner"),n=$("#toc_sidebar"),i=$("#"+$.get_sidebar_name()+"_sidebar .sidebar_header"),s=$.get_sidebar_pane(),o=e.offset().top,a=$.get_element_adjust(e),r=$.get_element_adjust(t),c=o+a+r,p=n.position().top,_=$.get_element_adjust(n),m=p+_,l=0;"none"!=i.css("display")&&(l=i.height()+$.get_element_adjust(i));var h=$.get_element_adjust(s);if("1"==cp_is_signup_page)var d=$.css_to_num($.px_to_num($("#content").css("margin-bottom")));else var d=$.css_to_num($.px_to_num($("#page_wrapper").css("margin-bottom")));var u=$(window).height(),g=$(window).scrollTop(),f=u+g,b=f-(c+m+l+h+d);return $("#sidebar div.sidebar_contents_wrapper").css("height",b+"px"),b},$.get_element_adjust=function(e){var t=$.css_to_num($.px_to_num(e.css("borderTopWidth"))),n=$.css_to_num($.px_to_num(e.css("borderBottomWidth"))),i=$.css_to_num($.px_to_num(e.css("padding-top"))),s=$.css_to_num($.px_to_num(e.css("padding-bottom"))),o=$.css_to_num($.px_to_num(e.css("margin-top"))),a=$.css_to_num($.px_to_num(e.css("margin-bottom"))),r=t+n+i+s+o+a;return r},$.get_sidebar_pane=function(){var e=$.get_sidebar_name();return $("#"+e+"_sidebar .sidebar_minimiser")},$.get_sidebar_name=function(){var e="toc";return"comments"==cp_default_sidebar&&(e="comments","y"==cp_toc_on_top&&(e="toc")),"activity"==cp_default_sidebar&&(e="activity","y"==cp_toc_on_top&&(e="toc")),e},$.get_current_menu_item_id=function(){var e,t,n,i,s=0;if(e=$(".current_page_item"),e.length>0)if(t=e.prop("id"),t.length>0)s=t.split("-")[2];else{i=e.prop("class"),n=i.split(" ");for(var o,a=0;o=n[a++];)if(o.match("page-item-")){s=o.split("-")[2];break}}return s},$.in_array=function(e,t,n){var i,s=!1,o=!!n;for(i in t)if(o&&t[i]===e||!o&&t[i]==e){s=!0;break}return s},$.remove_from_array=function(e,t){for(var n=0;n<t.length;n++)if(e===t[n]){t.splice(n,1);break}return t},$.is_object=function(e){return e instanceof Array?!1:null!==e&&"object"==typeof e},$.is_function_defined=function(function_name){return eval("typeof("+function_name+") == typeof(Function)")?!0:!1},$.px_to_num=function(e){return parseInt(e.substring(0,e.length-2))},$.css_to_num=function(e){if(e&&""!=e){var t=parseFloat(e);return"NaN"==t.toString()?0:t}return 0},$.frivolous=function(e){alert(e)},$.highlight_comment=function(e){e.addClass("notransition"),e.hasClass("comment-fade")&&e.removeClass("comment-fade"),e.hasClass("comment-highlighted")&&e.removeClass("comment-highlighted"),e.addClass("comment-highlighted"),e.removeClass("notransition"),e.height(),e.addClass("comment-fade")},$.get_text_sig_by_comment_id=function(e){var t,n,i,s;return i="",e.match("#comment-")&&(t=parseInt(e.split("#comment-")[1])),n=$("#comment-"+t).parents("div.paragraph_wrapper").map(function(){return this}),n.length>0&&(s=$(n[0]),i=s.prop("id").split("-")[1]),i},$.on_load_scroll_to_comment=function(){var e,t,n;e=document.location.toString(),e.match("#comment-")&&(t=e.split("#comment-")[1],n=$("#comment-"+t),n.length&&("0"==cp_is_mobile||"1"==cp_is_tablet)&&$.scrollTo(n,{duration:cp_scroll_speed,axis:"y",offset:CommentPress.theme.header.get_offset()}))},$.scroll_comments=function(e,t,n){switch(arguments.length){case 2:n="noflash";break;case 3:break;default:throw new Error("illegal argument count")}("0"==cp_is_mobile||"1"==cp_is_tablet)&&("flash"==n?$("#comments_sidebar .sidebar_contents_wrapper").scrollTo(e,{duration:t,axis:"y",onAfter:function(){$.highlight_comment(e),$(document).trigger("commentpress-comments-scrolled")}}):$("#comments_sidebar .sidebar_contents_wrapper").scrollTo(e,{duration:t,onAfter:function(){$(document).trigger("commentpress-comments-scrolled")}}))},$.scroll_page=function(e){"undefined"!=typeof e&&("0"==cp_is_mobile||"1"==cp_is_tablet)&&$.scrollTo(e,{duration:1.5*cp_scroll_speed,axis:"y",offset:CommentPress.theme.header.get_offset()})},$.quick_scroll_page=function(e,t){"undefined"!=typeof e&&("0"==cp_is_mobile||"1"==cp_is_tablet)&&$.scrollTo(e,{duration:1.5*t,axis:"y",offset:CommentPress.theme.header.get_offset()})},$.scroll_page_to_textblock=function(e){var t;""!==e?(t=$("#textblock-"+e),$.highlight_para(t),$.scroll_page(t)):(page_highlight===!1&&CommentPress.theme.viewport.scroll_to_top(0,cp_scroll_speed),page_highlight=!page_highlight)}}(jQuery),jQuery(document).ready(function(e){commentpress_setup_init()});