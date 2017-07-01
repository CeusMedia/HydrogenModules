if(typeof tinymce !== "undefined"){															//  tinyMCE is available
	tinymce.FileBrowser = {																	//  define file browser
		options: {},
		initOpener: function(options) {														//  call in editor
			this.options = jQuery.extend({
				labelHeading: 'Datei-Browser',
				sizeWidth: jQuery(window).width()*0.9,
				sizeHeight: jQuery(window).height()*0.80
			}, options);
		},
		initBrowser: function(){															//  call in file browser
			jQuery("#container-list-items").show();
			jQuery(".trigger-folder").bind("click", function(){
//				jQuery("body").animate({opacity: "0.5"},250);
				jQuery("body").css({opacity: "0.75"});
				document.location.href = jQuery(this).data("url");
			});
			jQuery(".trigger-submit").bind("click", function(){								// bind submit trigger
				tinymce.FileBrowser.submit(jQuery(this).data());				//
			});
		},
		open: function(callback, value, meta) {
			var browserPath = 'manage/tinyMce/' + meta.filetype;							// script URL
			tinyMCE.activeEditor.windowManager.open({
				file : tinymce.Config.envUri + browserPath,									// use an absolute path!
				title : tinymce.FileBrowser.options.labelHeading,
				width : tinymce.FileBrowser.options.sizeWidth,
				height : tinymce.FileBrowser.options.sizeHeight,
				resizable : "yes",
				inline : "yes",																// this parameter only has an effect if you use the inlinepopups plugin!
				close_previous : "yes"
			}, {
				callback: callback,
				value: value,
				meta: meta,
			});
			return false;
		},
		submit: function (data) {
//			console.log(data);
			var editor = parent.tinymce.editors[0];
			var params = editor.windowManager.windows[1].params;
//			console.log(params);
			var label = data.url.split('/').pop();
			if(params.meta.filetype == 'image')												//  provide image and alt text for the image dialog
				params.callback(data.url, {alt: label});
			else if(params.meta.filetype == 'file')											//  provide file and text for the link dialog
				params.callback(data.url, {text: label});
//			else if(params.meta.filetype == 'media')										//  provide alternative source and posted for the media dialog
//			 	params.callback(data.url, {source2: 'alt.ogg', poster: 'image.jpg'});
//			}
			editor.windowManager.windows[1].close();										// close file browser window
		}
	}
}
