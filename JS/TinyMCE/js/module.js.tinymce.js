var ModuleJsTinyMce = {
	applyAuto: function(){
		if(jQuery(settings.JS_TinyMCE.auto_selector).size()){
			jQuery(settings.JS_TinyMCE.auto_selector).each(function(nr){
				var options = {};
				var textarea = jQuery(this);
				if(settings.JS_TinyMCE.auto_tools)
					options.tools = settings.JS_TinyMCE.auto_tools;
				var mode = settings.JS_TinyMCE.auto_mode;
				if(textarea.data("tinymce-mode"))
					mode = $(this).data("tinymce-mode");
				options.relative_urls = textarea.data("tinymce-relative") !== false;
				if(textarea.data("tinymce-mode"))
				options = tinymce.Config.apply(options, mode);
				if(!textarea.attr("id"))
					textarea.attr("id", "TinyMCE-"+nr);
				options.selector = "#"+textarea.attr("id");
				tinymce.init(options);
			});
		}
	},
	configAuto: function(options){
		var key;
		for(key in options){
			tinymce.Config[key] = options[key];
		}
	}
};
