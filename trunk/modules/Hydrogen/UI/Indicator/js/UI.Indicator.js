/**
 *	 @todo		kriss: implement "usePercentage"
 */
if(typeof UI === "undefined")													//  no UI object defined yet
	var UI = {};																//  define empty UI object
UI.Indicator = {																//  define indicator UI object
	defaults: {																	//  define defailt options
		'id'				: null,												//  empty ID, override for fast access
		'classIndicator'	: 'indicator',										//  CSS class of indicator container
		'classInner'		: 'indicator-inner',								//  CSS class of indicator bar in track
		'classOuter'		: 'indicator-outer',								//  CSS class of indicator track in container
		'classPercentage'	: 'indicator-percentage',							//  CSS class of percentage display
		'classRatio'		: 'indicator-ratio',								//  CSS class of ratio display
		'length'			: 100,												//  length (in pixels) of indicator track
		'useColor'			: true,												//  whether to use color calculation
		'usePercentage'		: false,											//  whether to show percentage display
		'useRatio'			: false												//  whether to show ratio display
	},
	apply: function(selector, options){
		var options = $.extend(UI.Indicator.defaults, options);					//  realize options
		$(selector).each(function(nr){											//  iterate indicator matching selector
			var i = $(this);													//  shortcut found element
			if(i.data('total')){												//  found element contains indicator data
				i.html("").addClass(options.classIndicator).data({				//  empty indicator container and store date
					'option-length'		: options.length,						//
					'option-usecolor'	: options.useColor						//
				});
				if(!i.attr('id') && options.id)
					i.attr("id", options.id);									//  set ID on indicator container if configured
				var outer = $("<span></span>").addClass(options.classOuter);	//  create indicator track
				outer.css('width', options.length).appendTo(i);					//  set track width and append to indicator container
				outer.append($("<div></div>").addClass(options.classInner));	//  assign empty indicator bar to indicator track
				if(options.useRatio)											//  ratio is to be displayed
					i.append($("<span></span>").addClass(options.classRatio));	//  create ratio display and append to indicator
				UI.Indicator.set(i, i.data('value'));							//  call bar update
			}
		});
	},
	calculateColor: function(ratio, base){
		base = typeof base === "undefined" ? 0 : parseInt(base);				//  sanitize base
		var hue = 255 - base;													//  calculate base hue
		var r  = ratio < 0.5 ? 255 : Math.round((1 - ratio) * 2 * hue) + base;	//  calculate red channel
		var g  = ratio > 0.5 ? 255 : Math.round(ratio * 2 * hue) + base;		//  calculate green channel
		var b  = base;															//  calculate blue channel
		return 'rgb('+r+','+g+','+b+')';										//  return RGB property value
	},
	create: function(value, total, options){
		var indicator = $("<div></div>").data({value: value, total: total});	//  create indicator container with data
		UI.Indicator.apply(indicator, options);									//  render indicator content
		return indicator;														//  return created indicator element
	},
	decrease: function(selector, by){
		$(selector).each(function(nr){											//  iterate indicator matching selector
			value = parseFloat($(this).data('value'));							//  get current value
			if(value >= 1){														//  value can be decreased
				value -= typeof by === "undefined" ? 1 : parseInt(by);			//  decrease value
				UI.Indicator.set(this, value)									//  realize new value
			}
		});
	},
	increase: function(selector, by){
		$(selector).each(function(){											//  iterate indicator matching selector
			value = parseFloat($(this).data('value'));							//  get current value
			if(value < $(this).data('total')){									//  value can be increased
				value += typeof by === "undefined" ? 1 : parseInt(by);			//  increase value
				UI.Indicator.set(this, value)									//  realize new value
			}
		});
	},
	set: function(selector, value){
		$(selector).each(function(){											//  iterate elements matching to selector
			var i = $(this);													//  shortcut found element
			var total = parseFloat(i.data('total'));							//  get indicator total as float
			if(i.data('option-length') && total){								//  indicator length and total are defined
				value	= parseInt(Math.max(0, Math.min(total, value)));		//  keep value within range
				i.data('value', value);											//  set new value in element data
				var ratio = value / total;										//  calculate ratio
				var length = Math.round(ratio * i.data('option-length'));		//  calculate indicator bar length
				var bar = i.find('.indicator-inner').css('width', length);		//  shortcut indicator bar element and set width
				if(i.data('option-usecolor')){									//  color calculation is enabled
					var color = UI.Indicator.calculateColor(ratio, 0);			//  calculate color
					bar.css('background-color', color);							//  and set as background color on indicator bar
				}
				var label = i.data('value')+"/"+i.data('total')					//  assemble ratio label
				i.find(".indicator-ratio").html(label);							//  update ratio display
			}
		});
	}
};