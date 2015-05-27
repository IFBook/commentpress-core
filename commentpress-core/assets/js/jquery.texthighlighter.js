CommentPress.textselector=new function(){var a=this;if("undefined"!==typeof CommentpressTextSelectorSettings){this.popover=CommentpressTextSelectorSettings.popover}this.container="";this.container_set=function(b){this.container=b};this.container_get=function(){return this.container};this.selections_by_textblock={};this.selections_by_comment={};this.selection_sent={};this.selection_get=function(){return this.selection_get_current(document.getElementById(a.container))};this.selection_clear=function(){if(window.getSelection){if(window.getSelection().empty){window.getSelection().empty()}else{if(window.getSelection().removeAllRanges){window.getSelection().removeAllRanges()}}}else{if(document.selection){document.selection.empty()}}};this.selection_build_for_comments=function(){jQuery("#comments_sidebar li.selection-exists").each(function(d){var c,f,e,b,j,g,h;c=jQuery(this).prop("id");f=c.split("-")[2];e="#comment-"+f;b=jQuery(this).attr("class").split(/\s+/);jQuery.each(b,function(i,k){if(k.match("sel_start-")){j=parseInt(k.split("sel_start-")[1])}if(k.match("sel_end-")){g=parseInt(k.split("sel_end-")[1])}h={start:j,end:g};a.selections_by_comment[e]=h})})};this.selection_save_for_comment=function(c){var b="#comment-"+c;var d=a.selection_sent;this.selections_by_comment[b]=d};this.selection_recall_for_comment=function(e){var f,d,b,c;c="#comment-"+e;if(c in this.selections_by_comment){f=this.selections_by_comment[c];d=jQuery.get_text_sig_by_comment_id(c);b="textblock-"+d;a.selection_restore(document.getElementById(b),f);jQuery("#"+b).wrapSelection({fitToWord:false}).addClass("inline-highlight")}};this.selection_save_for_textblock=function(b){var c=a.selection_get_current(document.getElementById(b));if(!(b in this.selections_by_textblock)){this.selections_by_textblock[b]=[]}this.selections_by_textblock[b].push(c)};this.selection_recall_for_textblock=function(c){if(c in this.selections_by_textblock){for(var b=0,d;d=this.selections_by_textblock[c][b++];){a.selection_restore(document.getElementById(c),d);jQuery("#"+c).wrapSelection({fitToWord:false}).addClass("inline-highlight")}}};if(window.getSelection&&document.createRange){this.selection_get_current=function(e){var c,b,d;c=window.getSelection().getRangeAt(0);b=c.cloneRange();b.selectNodeContents(e);b.setEnd(c.startContainer,c.startOffset);d=b.toString().length;return{text:c.toString(),start:d,end:d+c.toString().length}};this.selection_restore=function(c,e){var b=0,k=document.createRange(),j=[c],f,g=false,m=false,d;k.setStart(c,0);k.collapse(true);while(!m&&(f=j.pop())){if(f.nodeType==3){var l=b+f.length;if(!g&&e.start>=b&&e.start<=l){k.setStart(f,e.start-b);g=true}if(g&&e.end>=b&&e.end<=l){k.setEnd(f,e.end-b);m=true}b=l}else{var h=f.childNodes.length;while(h--){j.push(f.childNodes[h])}}}d=window.getSelection();d.removeAllRanges();d.addRange(k)}}else{if(document.selection&&document.body.createTextRange){this.selection_get_current=function(e){var b,c,d;b=document.selection.createRange();c=document.body.createTextRange();c.moveToElementText(e);c.setEndPoint("EndToStart",b);d=c.text.length;return{text:b.text,start:d,end:d+b.text.length}};this.selection_restore=function(d,c){var b;b=document.body.createTextRange();b.moveToElementText(d);b.collapse(true);b.moveEnd("character",c.end);b.moveStart("character",c.start);b.select()}}}this.selection_send_to_editor=function(b){var c;c=a.selection_get();jQuery("#text_selection").val(c.start+","+c.end);a.selection_sent=c;if(b){if(cp_tinymce=="1"){if(jQuery("#wp-comment-wrap").hasClass("html-active")){a.selection_add_to_textarea(c.text,"replace")}else{a.selection_add_to_tinymce(c.text,"replace")}}else{a.selection_add_to_textarea(c.text,"replace")}}else{if(cp_tinymce=="1"){if(jQuery("#wp-comment-wrap").hasClass("html-active")){setTimeout(function(){jQuery("#comment").focus()},200)}else{setTimeout(function(){tinymce.activeEditor.focus()},200)}}else{setTimeout(function(){jQuery("#comment").focus()},200)}}};this.selection_clear_from_editor=function(){jQuery("#text_selection").val("")};this.selection_add_to_textarea=function(c,b){if(b=="prepend"){content=jQuery("#comment").val()}else{content=""}setTimeout(function(){jQuery("#comment").val("<strong>["+c+"]</strong>\n\n"+content);jQuery("#comment").focus()},200)};this.selection_add_to_tinymce=function(c,b){if(b=="prepend"){content=tinymce.activeEditor.getContent()}else{content=""}tinymce.activeEditor.setContent("<p><strong>["+c+"]</strong></p>"+content,{format:"html"});setTimeout(function(){tinymce.activeEditor.selection.select(tinymce.activeEditor.getBody(),true);tinymce.activeEditor.selection.collapse(false);tinymce.activeEditor.focus()},200)};this.highlighter_activate=function(){jQuery(".textblock").highlighter({selector:".holder",minWords:1,complete:function(b){}})};this.highlighter_deactivate=function(){jQuery(".textblock").highlighter("destroy")};this.highlights_clear=function(){jQuery(".inline-highlight").each(function(b){var c=jQuery(this).contents();jQuery(this).replaceWith(c)})};this.init=function(){a.reset();a.selection_build_for_comments();var b;b='<input type="hidden" name="text_selection" id="text_selection" value="" />';jQuery(b).appendTo("#commentform");jQuery(a.popover).appendTo("body");a.highlighter_activate();jQuery(".holder").mousedown(function(){return false});jQuery(".btn-left-comment").click(function(){var d,f,c,e;jQuery(".holder").hide();d=a.container_get();a.selection_save_for_textblock(d);a.selection_send_to_editor(false);cp_scroll_comments(jQuery("#respond"),cp_scroll_speed);e=jQuery("#"+d).wrapSelection({fitToWord:false}).addClass("inline-highlight");return false});jQuery(".btn-left-quote").click(function(){var d,f,c,e;jQuery(".holder").hide();d=a.container_get();a.selection_save_for_textblock(d);a.selection_send_to_editor(true);cp_scroll_comments(jQuery("#respond"),cp_scroll_speed);e=jQuery("#"+d).wrapSelection({fitToWord:false}).addClass("inline-highlight");return false});jQuery(".btn-right").click(function(){jQuery(".holder").hide();var c="";a.container_set(c);return false});jQuery("#container").on("click",".textblock",function(){var c,d;d=a.container_get();c=jQuery(this).prop("id");if(d!=c){a.container_set(c);a.highlights_clear()}});jQuery("#comments_sidebar").on("mouseenter","li.comment.selection-exists",function(e){var c,d;c=jQuery(this).prop("id");d=c.split("-")[2];a.selection_recall_for_comment(d)});jQuery("#comments_sidebar").on("mouseleave","li.comment.selection-exists",function(c){a.highlights_clear()})};this.reset=function(){}};jQuery(document).ready(function(a){a(document).on("commentpress-document-ready",function(b){CommentPress.textselector.init()});a(document).on("commentpress-reset-actions",function(b){CommentPress.textselector.reset()});a(document).on("commentpress-ajax-comment-added",function(c,b){if(b.match("#comment-")){b=parseInt(b.split("#comment-")[1])}CommentPress.textselector.selection_save_for_comment(b);CommentPress.textselector.selection_clear_from_editor()});a(document).on("commentpress-ajax-comment-added-scrolled",function(b){CommentPress.textselector.highlights_clear()})});