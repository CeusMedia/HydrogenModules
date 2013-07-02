Module = typeof Module === "undefined" ? {} : Module;
Module.UI = typeof Module.UI === "undefined" ? {} : Module.UI;
if(typeof Module.UI.Session !== "undefined")
	throw "Conflict: Module.UI.Session is already defined.";

Module.UI.Session = {
	init: function(){
		if(typeof config.module_ui_session.keep === "undefined" )
			return;
		if(!config.module_ui_session.keep)
			return;
//		if(config.module_ui_session.keepalive.for.toLowerCase = "none")
//			return;
		var minutes = parseInt(config.module_ui_session.keep.minutes);
		if( minutes < 1)
			return;
		window.setInterval(
			function(){
				$.ajax({
					url: './ui/session/ajaxKeepAlive',
				});
			}, minutes * 60 * 1000
		);
	}

};
jQuery(document).ready(function(){
	Module.UI.Session.init();
});