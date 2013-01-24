/**
 *	jQuery Plugin for clearing contents of input fields and textareas.
 *	@name		cmClearInput
 *	@type		jQuery
 *	@cat		Plugins/UI
 *	@author		Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright	2013 Christian Würker <christian.wuerker@ceusmedia.de> (http://ceusmedia.de)
 *	@license	LGPL/CC
 */
(function($){
	jQuery.fn.cmClearInput = function(method){

		var methods = {																				//  methods callable using constructor
			init: function(options){
				var settings = jQuery.extend({														//  options and defaults
					easeIn: 'linear',																//  easing of showing Animation
					easeOut: 'linear',																//  easing of hiding Animation
					speedIn: 0,																		//  speed of showing Animation
					speedOut: 0																		//  speed of hiding Animation
				}, options);

				return this.each(function(){			//
					var input = $(this);
					var trigger = $("<span></span>").addClass("cmClearInput-trigger");
					trigger.insertAfter(input);
					positionTrigger(input, trigger);
					input.bind("change.updateClearTrigger",{trigger: trigger}, function(event){
						if($(this).val().length)
							event.data.trigger.show();
						else
							event.data.trigger.hide();
					}).trigger("change.updateClearTrigger");
					trigger.bind("click", {input: input}, function(event){
						event.data.input.val("").trigger("change.updateClearTrigger");
					});
				});
			}
		}

		// Method calling logic
		if(methods[method])																			//  called method is defined
			return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));			//  call method with arguments
		else if(typeof(method) === 'object' || ! method)											//  options object or nothing given
			return methods.init.apply(this, arguments);												//  call init method with arguments
		$.error( 'Method ' +  method + ' does not exist on cmSelectBox' );							//  report invalid method call

		function positionTrigger(input, trigger){
			input.parent().css("position", "relative");
			var left = input.width() + parseInt(input.css("padding-right")) - 11 - 4;
			var topPosition = input.position().top;
			var topMargin = parseInt(input.css("margin-top"));
			var topHeight = ((input.height() - 11) / 2);
			var topPadding = parseInt(input.css("padding-top"));
			var top = Math.ceil(topPosition + topMargin + topPadding + topHeight);
/*			console.log({
				topPosition: topPosition,
				topMargin: topMargin,
				topPadding: topPadding,
				topHeight: topHeight,
				});
*/			trigger.css("left", left).css("top", top)
		}
	};
})( jQuery );
