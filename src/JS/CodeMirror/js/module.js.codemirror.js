var ModuleCodeMirror = {
	verbose: true,
	apply: function(selector, options, useDataAttributes){
		var options = $.extend({
			extraKeys: {}
		}, options);
		var container = $(selector);
		container.each(function(){
			var item = jQuery(this);
			if(item.data("applied-editor")){
				ModuleCodeMirror.log("CodeMirror: Apply skipped - some editor already applied");	//  in verbose mode: note fail in console log
				return;
			}
			item.data("applied-editor", "CodeMirror");
			if(useDataAttributes){
				var key, option;
				for(key in item.data()){
					if(key.match(/^codemirror/)){
						option = key.replace(/^codemirror/, "");
						option = option[0].toLowerCase() + option.substr(1);
						options[option] = item.data(key);
					}
				}
			}
			if(settings.JS_CodeMirror.auto_option_fullscreen){
				options.extraKeys['F11'] = function(cm) {
					ModuleCodeMirror.setFullScreen(cm, !ModuleCodeMirror.isFullScreen(cm));
				};
				options.extraKeys['Esc'] = function(cm) {
					if (ModuleCodeMirror.isFullScreen(cm)) {
						ModuleCodeMirror.setFullScreen(cm, false);
					}
				};
			}
			var parts, func, obj;
			if(typeof options.callbackSave !== "undefined"){
				parts = options.callbackSave.split(".");
				if(parts.length > 1){
					if(typeof window[parts[0]] === "object"){
						if(typeof window[parts[0]][parts[1]] === "function"){
							options.extraKeys['Ctrl-S'] = window[parts[0]][parts[1]];
						}
					}
				}
				else if(typeof window[options.callbackSave] === "function"){
					options.extraKeys['Ctrl-S'] = window[options.callbackSave];
				}
			}
			if(item.prop('readonly') || item.prop('disabled'))								//  if readonly or disabled
				options.readOnly = item.prop('disabled') ? "nocursor" : true;					//  set mode to readonly or readonly-nocursor
			var mirror = CodeMirror.fromTextArea(item.get(0), options);
			if(typeof options.height === "undefined"){
				mirror.setSize("100%", item.height());
			}
			if(typeof options.callbackChange !== "undefined"){
				parts = options.callbackChange.split(".");
				if(parts.length > 1){
					if(typeof window[parts[0]] === "object"){
						if(typeof window[parts[0]][parts[1]] === "function"){
							mirror.on("change", window[parts[0]][parts[1]]);
						}
					}
				}
				else if(typeof window[options.callbackChange] === "function"){
					mirror.on("change", window[options.callbackChange]);
				}
			}
			item.data("codemirror", mirror);
		});
	},
	isFullScreen: function(cm){
		return /\bCodeMirror-fullscreen\b/.test(cm.getWrapperElement().className);
	},
	getWinHeight: function(){
		return window.innerHeight || (document.documentElement || document.body).clientHeight;
	},
	log: function(){
		if(arguments.length === 0)
			throw "ModuleCodeMirror.log needs atleast 1 argument (as message)";
		if(ModuleCodeMirror.verbose && typeof console !== "undefined")
			console.log(ModuleCodeMirror.sprintf.apply(this, arguments));
	},
	setFullScreen: function(cm, full){
		var wrap = cm.getWrapperElement();
		if (full) {
			wrap.className += " CodeMirror-fullscreen";
			wrap.style.height = ModuleCodeMirror.getWinHeight() + "px";
			document.documentElement.style.overflow = "hidden";
		} else {
			wrap.className = wrap.className.replace(" CodeMirror-fullscreen", "");
			wrap.style.height = "";
			document.documentElement.style.overflow = "";
		}
		cm.refresh();
	},
	sprintf: function(message){
		if(arguments.length === 0)
			throw "sprintf needs atleast 1 argument (as message)";
		var msg = arguments[0];
		for(var i=1; i<arguments.length; i++)
			msg = msg.replace(/(%[ds])/, arguments[i]);
		return msg;
	}
}
