(function(d){d.event.special.tripleclick={setup:function(h,g){var f=this,e=jQuery(f);e.bind("click",jQuery.event.special.tripleclick.handler)},teardown:function(g){var f=this,e=jQuery(f);e.unbind("click",jQuery.event.special.tripleclick.handler)},handler:function(h){var g=this,e=jQuery(g),f=e.data("clicks")||0;f+=1;if(f===3){f=0;h.type="tripleclick";jQuery.event.dispatch.apply(this,arguments)}e.data("clicks",f)}};function c(h){var g=h,e;try{e=h.previousSibling;while(e&&e.nodeType!=1){g=e;e=e.previousSibling}}catch(f){console.log(f);topOffset=-15;return g}return e?e:g}var a=true;var b={init:function(f){var i=d.extend({selector:".highlighter-container",minWords:0,complete:function(){}},f);var h=0;var g=0;var j=0;var e=false;var k;return this.each(function(){function l(x){if(a===false){return}var t="<span class='dummy'></span>";g=0;j=0;if(h!==x){return}var o=(navigator.appName==="Microsoft Internet Explorer");var n,u,w,p;var v;if(window.getSelection){n=window.getSelection();k=n.toString();if(d.trim(k)===""||k.split(" ").length<i.minWords){return}if(n.getRangeAt&&n.rangeCount){u=window.getSelection().getRangeAt(0);w=u.cloneRange();w.collapse(false);var m=document.createElement("div");m.innerHTML=t;var r=document.createElement("span");if(u.startOffset===0&&u.endOffset===0){var y=w.startContainer;var q=c(y);try{w.selectNode(q.lastChild)}catch(s){j=40;g=-15;w.selectNode(q)}w.collapse(false)}else{if(u.endOffset===0){g=-25;j=40}}if(h!==x){return}d(i.selector).hide();if(!o&&d.trim(k)===d.trim(w.startContainer.innerText)){w.startContainer.innerHTML+="<span class='dummy'>&nbsp;</span>";v=d(".dummy").offset();d(".dummy").remove()}else{if(!o&&d.trim(k)===d.trim(w.endContainer.innerText)){w.endContainer.innerHTML+="<span class='dummy'>&nbsp;</span>";v=d(".dummy").offset();d(".dummy").remove()}else{w.insertNode(r);v=d(r).offset();r.parentNode.removeChild(r)}}}}else{if(document.selection&&document.selection.createRange){u=document.selection.createRange();w=u.duplicate();k=w.text;if(d.trim(k)===""||k.split(" ").length<i.minWords){return}u.collapse(false);u.pasteHTML(t);w.setEndPoint("EndToEnd",u);w.select();v=d(".dummy").offset();d(".dummy").remove()}}d(i.selector).css("top",v.top+g+"px");d(i.selector).css("left",v.left+j+"px");d(i.selector).show(300,function(){i.complete(k)})}d(i.selector).hide();d(i.selector).css("position","absolute");d(document).bind("mouseup.highlighter",function(m){if(e){h=1;clicks=0;setTimeout(function(){l(1)},300);e=false}});d(this).bind("mouseup.highlighter",function(m){h=1;clicks=0;setTimeout(function(){l(1)},300)});d(this).bind("tripleclick.highlighter",function(m){h=3;setTimeout(function(){l(3)},200)});d(this).bind("dblclick.highlighter",function(m){h=2;setTimeout(function(){l(2)},300)});d(this).bind("mousedown.highlighter",function(m){d(i.selector).hide();e=true})})},enable:function(){a=true},disable:function(){a=false},destroy:function(e){return this.each(function(){d(document).unbind("mouseup.highlighter");d(this).unbind("mouseup.highlighter");d(this).unbind("tripleclick.highlighter");d(this).unbind("dblclick.highlighter");d(this).unbind("mousedown.highlighter")})}};d.fn.highlighter=function(e){if(b[e]){return b[e].apply(this,Array.prototype.slice.call(arguments,1))}else{if(typeof e==="object"||!e){return b.init.apply(this,arguments)}else{d.error("Method "+e+" does not exist on jQuery.highlighter")}}}})(jQuery);