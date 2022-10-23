var WorkTimer = {
	interval: null,
	selector: ".work-timer",
	formatSeconds: function( duration, space = ' ' ){
		var seconds	= duration % 60;
		duration	= ( duration - seconds ) / 60;
		var minutes	= duration % 60;
		duration	= ( duration - minutes ) / 60;
		var hours	= duration % 24;
		var days	= ( duration - hours ) / 24;
		duration	= seconds ? space + str_pad( seconds, 2, 0, "STR_PAD_LEFT" ) + "s" : "";
		duration	= ( minutes ? space + ( hours ? str_pad( minutes, 2, 0, "STR_PAD_LEFT" ) + "m" : minutes + "m" ) : "" ) + duration;
		duration	= ( hours ? space + ( days ? str_pad( hours, 2, 0, "STR_PAD_LEFT" ) + "h" :  hours + "h" ) : "" ) + duration;
		duration	= ( days ? space + days + "d" : "" ) + duration;
		return duration.toString().trim();
	},
	init: function( selector, space = ' ' ){
		if( typeof selector === "undefined" )
			selector = WorkTimer.selector;
		if(!jQuery(selector).length)
			return;
		jQuery(selector).each(function(){
			var _this = $(this);
			window.setInterval(function(){
				var seconds	= jQuery(_this).data("value") + 1;
				jQuery(_this).data("value", seconds);
				jQuery(_this).html(WorkTimer.formatSeconds(seconds, space));
			}, 1000 );
		});
	}
};
