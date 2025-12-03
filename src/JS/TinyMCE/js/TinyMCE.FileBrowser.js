if(typeof tinymce !== "undefined"){															//  tinyMCE is available
	tinymce.FileBrowser = {																	//  define file browser
		options: {},
		initOpener: function(options) {														//  call in editor
			this.options = jQuery.extend({
				labelHeading: 'Datei-Browser',
				sizeWidth: jQuery(window).width() * 0.9,
				sizeHeight: jQuery(window).height() * 0.80
			}, options);
		},
		initBrowser: function(){															//  call in file browser
			jQuery("#container-list-items").show();
			jQuery(".trigger-folder").on("click", function(){
//				jQuery("body").animate({opacity: "0.5"},250);
				jQuery("body").css({opacity: "0.75"});
				document.location.href = jQuery(this).data("url");
			});
			jQuery(".trigger-submit").on("click", function(){								// bind submit trigger
				tinymce.FileBrowser.submit(jQuery(this).data());				//
			});
		},
		open: function(callback, value, meta) {
			if(settings.JS_TinyMCE.version.startsWith('8'))
				return this.openV8(callback, value, meta);
			return this.openV4(callback, value, meta);
		},
		openV4: function(callback, value, meta) {
			var browserPath = 'manage/tinyMce/' + meta.filetype;							// script URL
			var options = {
				file : tinymce.Config.envUri + browserPath,									// use an absolute path!
				title : tinymce.FileBrowser.options.labelHeading,
				width : tinymce.FileBrowser.options.sizeWidth,
				height : tinymce.FileBrowser.options.sizeHeight,
				resizable : "yes",
				inline : "yes",																// this parameter only has an effect if you use the inlinepopups plugin!
				close_previous : "yes"
			};
//			console.log(options);
			tinyMCE.activeEditor.windowManager.open(options, {
				callback: callback,
				value: value,
				meta: meta,
			});
		},
		openV8: function(callback, value, meta) {
			var options = {
				url : tinymce.Config.envUri + 'manage/tinyMce/' + meta.filetype,			// use an absolute path!
				title : tinymce.FileBrowser.options.labelHeading,
				width : tinymce.FileBrowser.options.sizeWidth,
				height : tinymce.FileBrowser.options.sizeHeight,
				resizable : "yes",
				inline : "yes",																// this parameter only has an effect if you use the inlinepopups plugin!
				close_previous : "yes"
			};
			tinyMCE.activeEditor.windowManager.openUrl(options, {
				callback: callback,
				value: value,
				meta: meta,
			});
			const messageListener = function(event){
				if (event.data.type === 'fileSelected') {
					callback(event.data.url, {text: event.data.filename});					// return data by TinyMCE file_picker callback
					window.removeEventListener('message', messageListener);					// remove listener again
					tinymce.activeEditor.windowManager.close();								// close dialog
				}
			};
			window.addEventListener('message', messageListener, false );
		},
		submit: function (data) {
			if(settings.JS_TinyMCE.version.startsWith('8'))
				return this.submitV8(data);
			return this.submitV4(data);
		},
		submitV4: function (data) {
			var editor = parent.tinymce.editors[0];
			var params = editor.windowManager.windows[1].params;
//			var label = data.url.split('/').pop();
			var label = data.label;
			if(params.meta.filetype == 'image')												//  provide image and alt text for the image dialog
				params.callback(data.url, {alt: label});
			else if(params.meta.filetype == 'file')											//  provide file and text for the link dialog
				params.callback(data.url, {text: label});
//			else if(params.meta.filetype == 'media')										//  provide alternative source and posted for the media dialog
//			 	params.callback(data.url, {source2: 'alt.ogg', poster: 'image.jpg'});
//			}
			editor.windowManager.windows[1].close();										// close file browser window
		},
		submitV8: function (data) {
			window.parent.postMessage({														//  send selected data to editor
				type: 'fileSelected',
				url: data.url,
				filename: data.label
			}, '*');																		// '*' allows communication across all domains
		}
	}
}
