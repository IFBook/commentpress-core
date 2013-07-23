(function() {

	tinymce.create('tinymce.plugins.CommentBlock', {
	
		init : function(ed, url) {
		
			var commentblockHTML;
			commentblockHTML = '<img src="' + url + '/trans.gif" class="mceCommentBlock mceItemNoResize" title="Comment Block" />';
			
			// add button
			ed.addButton('commentblock', {
				title : 'Insert a Comment Block',
				image : url+'/commentblock.jpg',
				cmd : 'COMMENTPRESS_CommentBlock'
			});
			
			// register commands
			ed.addCommand('COMMENTPRESS_CommentBlock', function() {
				ed.execCommand('mceInsertContent', 0, commentblockHTML);
			});
			
			// add listeners to handle more break
			this._handleCommentBlock(ed, url);
			
			// add keyboard shortcut
			//ed.addShortcut('alt+shift+0', ed.getLang('wp_more_desc'), 'COMMENTPRESS_CommentBlock');
			
		},

		getInfo : function() {
			return {
				longname : "Comment Block Shortcode",
				author : 'Christian Wach',
				authorurl : 'http://haystack.co.uk/',
				infourl : 'http://futureofthebook.org/commentpress/',
				version : "1.0"
			};
		},

		_handleCommentBlock : function(ed, url) {
			var commentblockHTML = '<img src="' + url + '/trans.gif" class="mceCommentBlock mceItemNoResize" title="Comment Block" />';

			// Load plugin specific CSS into editor
			ed.onInit.add(function() {
				ed.dom.loadCSS(url + '/content.css');
			});

			// Display commentblock instead of img in element path
			ed.onPostRender.add(function() {
				if (ed.theme.onResolveName) {
					ed.theme.onResolveName.add(function(th, o) {
						if (o.node.nodeName == 'IMG') {
							if ( ed.dom.hasClass(o.node, 'mceCommentBlock') )
								o.name = 'commentblock';
						}

					});
				}
			});

			// replace commentblock with images
			ed.onBeforeSetContent.add(function(ed, o) {
				o.content = o.content.replace(/<!--commentblock-->/g, commentblockHTML);
			});

			// replace images with commentblock
			ed.onPostProcess.add(function(ed, o) {
				if (o.get)
					o.content = o.content.replace(/<img[^>]+>/g, function(im) {
						if (im.indexOf('class="mceCommentBlock') !== -1) {
							im = '<!--commentblock-->';
						}
						return im;
					});
			});

			// Set active buttons if user selected pagebreak or more break
			ed.onNodeChange.add(function(ed, cm, n) {
				cm.setActive('commentblock', n.nodeName === 'IMG' && ed.dom.hasClass(n, 'mceCommentBlock'));
			});
		}
	});
	tinymce.PluginManager.add('commentblock', tinymce.plugins.CommentBlock);
})();