$(document).ready(function(){
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
