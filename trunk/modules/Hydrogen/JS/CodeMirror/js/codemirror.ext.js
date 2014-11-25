$(document).ready(function(){
	CodeMirror.apply = function(selector, options, useDataAttributes){
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
			var mirror = CodeMirror.fromTextArea($(this).get(0), options);
			if(typeof options.height === "undefined")
				mirror.setSize("100%", $(this).height());
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
