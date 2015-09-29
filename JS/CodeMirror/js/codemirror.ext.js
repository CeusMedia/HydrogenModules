$(document).ready(function(){
	CodeMirror.apply = function(selector, options, useDataAttributes){
		var options = $.extend({
			extraKeys: {}
		}, options);
		var container = $(selector);
		container.each(function(){
			if(useDataAttributes){
				var key, option;
				for(key in $(this).data()){
					if(key.match(/^codemirror/)){
						option = key.replace(/^codemirror/, "");
						option = option[0].toLowerCase() + option.substr(1);
						options[option] = $(this).data(key);
					}
				}
			}
			if(settings.JS_CodeMirror.auto_option_fullscreen){
				options.extraKeys['F11'] = function(cm) {
					CodeMirror.setFullScreen(cm, !CodeMirror.isFullScreen(cm));
				};
				options.extraKeys['Esc'] = function(cm) {
					if (CodeMirror.isFullScreen(cm)) {
						CodeMirror.setFullScreen(cm, false);
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
			var mirror = CodeMirror.fromTextArea($(this).get(0), options);
			if(typeof options.height === "undefined"){
				mirror.setSize("100%", $(this).height());
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
			$(this).data("codemirror", mirror);
		});
	};
	CodeMirror.isFullScreen = function(cm){
		return /\bCodeMirror-fullscreen\b/.test(cm.getWrapperElement().className);
	};
	CodeMirror.getWinHeight = function(){
		return window.innerHeight || (document.documentElement || document.body).clientHeight;
	};
	CodeMirror.setFullScreen = function(cm, full){
		var wrap = cm.getWrapperElement();
		if (full) {
			wrap.className += " CodeMirror-fullscreen";
			wrap.style.height = CodeMirror.getWinHeight() + "px";
			document.documentElement.style.overflow = "hidden";
		} else {
			wrap.className = wrap.className.replace(" CodeMirror-fullscreen", "");
			wrap.style.height = "";
			document.documentElement.style.overflow = "";
		}
		cm.refresh();
	};
	CodeMirror.on(window, "resize", function() {
		var showing = document.body.getElementsByClassName("CodeMirror-fullscreen")[0];
		if (!showing) return;
		showing.CodeMirror.getWrapperElement().style.height = CodeMirror.getWinHeight() + "px";
	});
});
