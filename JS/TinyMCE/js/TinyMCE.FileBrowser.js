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
				tinymce.FileBrowser.submit(jQuery(this).data());							// 
			});
		},
		open: function(field_name, url, type, win) {
			var browserPath = 'manage/tinyMce/' + type + '?input=' + field_name;			// script URL
			tinyMCE.activeEditor.windowManager.open({
				file : tinymce.Config.envUri + browserPath,									// use an absolute path!
				title : tinymce.FileBrowser.options.labelHeading,
				width : tinymce.FileBrowser.options.sizeWidth,
				height : tinymce.FileBrowser.options.sizeHeight,
				resizable : "yes",
				inline : "yes",																// this parameter only has an effect if you use the inlinepopups plugin!
				close_previous : "yes"
			}, {
				window: win,
				input: field_name
			});
			return false;
		},
		submit: function (data) {
//	console.log(data);
//	console.log(parent.tinymce.Config);
			var form = parent.$('.mce-btn.mce-open').parent().parent().parent().parent();	//  not a form, but a form-like container
			form.find('input.mce-textbox').val("");
			parent.$('.mce-btn.mce-open').parent().find('.mce-textbox').val(data.url);
			var editor = parent.tinymce.editors[0];
			editor.windowManager.windows[1].close();										// close file browser window
		}
	}
}
