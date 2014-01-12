var ManageCustomerRating = {
	calculateColor: function(ratio, base){
		base = typeof base === "undefined" ? 0 : parseInt(base);				//  sanitize base
		var hue = 255 - base;													//  calculate base hue
		var r	= ratio < 0.5 ? 255 : Math.round((1 - ratio) * 2 * hue) + base;	//  calculate red channel
		var g	= ratio > 0.5 ? 255 : Math.round(ratio * 2 * hue) + base;		//  calculate green channel
		var b	= base;															//  calculate blue channel
		return "rgb("+r+","+g+","+b+")";										//  return RGB property value
	},
	initSliders: function(selector){
		$(selector).each(function(){
			var input = $(this).parent().parent().find("input");
			input.on("change", {slider: $(this)}, function(event){
				console.log($(this).val().replace(/,/, "."));
				var value = parseFloat($(this).val().replace(/,/, "."));
				var color = "rgb(255,255,255)";
				if(!isNaN(value)){
					value = Math.max(1, Math.min( 5, value));
					$(this).val(value);
					event.data.slider.slider("value", value);
					color = ManageCustomerRating.calculateColor((value - 1) / 4);
					if(event.data.slider.hasClass("inverse"))
						color = ManageCustomerRating.calculateColor(Math.abs(5 - value) / 4);
				}
				event.data.slider.css("background", color);
			});
			$(this).slider({
				value: 0,
				min: 0.9,
				max: 5,
				step: 0.1,
				slide: function( event, ui ){
					var color = "rgb(255,255,255)";
					if(ui.value >= 1){
						input.val(ui.value);
						color = ManageCustomerRating.calculateColor((ui.value - 1) / 4);
						if($(this).hasClass("inverse"))
							color = ManageCustomerRating.calculateColor(Math.abs(5 - ui.value) / 4);
					}
					else
						input.val("");
					$(this).css("background", color);
				}
			});
			input.val("");
		});
	}
};
