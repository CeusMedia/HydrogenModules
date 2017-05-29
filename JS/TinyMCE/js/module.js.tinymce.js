var ModuleJsTinyMce = {
	applyAuto: function(){
		if(jQuery(settings.JS_TinyMCE.auto_selector).size()){
			jQuery(settings.JS_TinyMCE.auto_selector).each(function(nr){
				var options = {};
				if(settings.JS_TinyMCE.auto_tools)
					options.tools = settings.JS_TinyMCE.auto_tools;
				var mode = settings.JS_TinyMCE.auto_mode;
				if(jQuery(this).data("tinymce-mode"))
					mode = $(this).data("tinymce-mode");
				options = tinymce.Config.apply(options, mode);
				if(!jQuery(this).attr("id"))
					jQuery(this).attr("id", "TinyMCE-"+nr);
				options.selector = "#"+jQuery(this).attr("id");
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
