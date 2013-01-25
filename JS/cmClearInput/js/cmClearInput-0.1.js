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
				}, options);

				return this.each(function(){			//
					var input = $(this);
					var trigger = $("<span></span>").addClass("cmClearInput-trigger");
					trigger.insertAfter(input);
					positionTrigger(input, trigger);
					input.bind("change.updateClearTrigger",{trigger: trigger}, function(event){
						updateTriggerVisibility($(this), event.data.trigger);
					}).trigger("change.updateClearTrigger");
					trigger.bind("click", {input: input}, function(event){
						event.data.input.val("");
						event.data.input.trigger("keyup.*");
						event.data.input.trigger("change.*");
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
			var left = input.outerWidth();
			left -= parseInt(input.css("border-right")) + parseInt(input.css("margin-right"));
			left -= trigger.width() + 5;
			var top = input.position().top + 6;
			top += parseInt(input.css("margin-top")) + parseInt(input.css("border-top"));
			trigger.css({
				left: (left / input.parent().width() * 100) + "%",
				top: top
			});
		}

		function updateTriggerVisibility(input, trigger){
			var isVisible = trigger.is(":visible");
			var shouldBe = input.val().length > 0;
			if(!isVisible && shouldBe)
				trigger.show();
			else if(isVisible && !shouldBe)
				trigger.hide();
		}
	};
})( jQuery );
