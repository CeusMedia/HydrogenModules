/**
 *	Enables textareas to grow and shrink with its contents.
 *	@name		cmGrowText
 *	@type		jQuery
 *	@cat		Plugins/UI
 *	@author		Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright	2013 Christian Würker <christian.wuerker@ceusmedia.de> (http://ceusmedia.de)
 *	@license	LGPL/CC
 *	@option		string		autoEnable				Setup on init
 *	@option		float		lineHeightFactor		Default line height factor in HTML5 (=1.231)
 *	@option		integer		maxHeight				Upper height limit, none by default
 *	@option		integer		maxLines				Upper line limit, none by default
 *	@option		integer		minHeight				Lower height limit, none by default
 *	@option		integer		minLines				Lower line limit, none by default
 */
(function($){
	jQuery.fn.cmGrowText = function(method){

		var methods = {															//  methods callable using constructor
			disable: function(){
				$(this).unbind("keyup.cmGrowText keydown.cmGrowText");			//  unbind events
				return true;
			},
			enable: function(){
				var area = $(this);
				var settings = area.data('settings');							//  shortcut settings
				var lineHeight = parseInt(area.css("line-height"));				//  get height of text lines
				if(isNaN(lineHeight)){											//  no numeric value has been given
					var lineHeightFactor = settings.lineHeightFactor;			//  ...
					var fontSize = parseInt(area.css("font-size"));				//  ...
					lineHeight = fontSize * lineHeightFactor;					//  calculate default (normal) line height
				}
				var events = "keyup.cmGrowText keydown.cmGrowText";				//  events to apply resize trigger to (@todo kriss: convert to setting)
				area.data({														//  store environment information on text area
					paddingTop: parseInt(area.css("padding-top")),				//  note top padding
					paddingBottom: parseInt(area.css("padding-bottom")),		//  note bottom padding
					lineHeight: lineHeight,										//  ...
					lines: 0													//  prepare line count property, used by resize method
				}).bind(events, {area: area}, function(event){					//  bind callback if a text is about to or has been changed
					resize(event);
				}).trigger("keyup.cmGrowText");
				return true;													//  indicate that setup succeeded
			},
			getOption: function(key){
				var settings = $(this).data('settings');						//  shortcut settings
				if(typeof(settings[key]) !== 'undefined')						//  option key is defined
					return settings[key];										//  return option value
				return null;
			},
			init: function(options){
				var settings = jQuery.extend({									//  options and defaults
					autoEnable: true,											//  setup on init
					lineHeightFactor: 1.231,
					maxHeight: 0,
					maxLines: 0,
					minHeight: 0,
					minLines: 0
				}, options);
				$(this).data("settings", settings);
				return this.each(function(){									//  iterate found text areas
					if(settings.autoEnable)
						$(this).cmGrowText("enable");
				});
			},
			setOption: function(key, value){
				var area = $(this);												//  shortcut text area
				var settings = area.data('settings');							//  shortcut settings
				if(typeof(settings[key]) == 'undefined')						//  option key is not defined
					return false;
				settings[key] = value;											//  set new option value
				area.data('settings', settings);								//  store changed settings
				area.cmGrowText("disable");
				area.cmGrowText("enable");
				console.log("setOption: "+key+": "+value);
			}
		}

		// Method calling logic
		if(methods[method])														//  called method is defined
			return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));	//  call method with arguments
		else if(typeof(method) === 'object' || ! method)						//  options object or nothing given
			return methods.init.apply(this, arguments);							//  call init method with arguments
		$.error( 'Method ' +  method + ' does not exist on cmGrowText' );		//  report invalid method call

		function countLines(string){
			var m = string.match(new RegExp("\\n", "g"));						//  ...
			return m ? m.length : 0;											//  ...
		}

		function resize(event){
			var area = event.data.area;											//  ...
			var settings = area.data('settings');								//  shortcut settings
			var isNewLine = event.type == "keydown" && event.keyCode == 13;		//  enter has been pressed
			var space = isNewLine ? 2 : 1;										//  one empty line below text, if enter is pressed two empty lines for the moment to avoid scroll bars
			var lines = countLines(area.val()) + space;							//  calculate text lines
			if(parseInt(settings.maxLines))										//  upper line limit has been set
				lines = Math.min(settings.maxLines, lines);						//  cut lines by upper limit  
			if(parseInt(settings.minLines))										//  lower line limit has been set
				lines = Math.max(settings.minLines, lines);						//  cut lines by lower limit
			if(area.data("lines") != lines){									//  number of lines has changed since last event
				var height = lines * area.data("lineHeight");					//  calculate height by lines
				height += area.data("paddingTop") + area.data("paddingBottom");	//  add top and bottom paddings
				if(parseInt(settings.maxHeight))
					height = Math.min(settings.maxHeight, height);
				if(settings.maxHeight)
					height = Math.max(settings.minHeight, height);
				area.height(height).data("lines", lines);						//  set new height of text area and store new number of lines
			}
		}
	};
})( jQuery );
