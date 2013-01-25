/**
 *	jQuery Plugin for clearing contents of input fields and textareas.
 *	@name		cmClearInput
 *	@type		jQuery
 *	@cat		Plugins/UI
 *	@author		Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright	2013 Christian Würker <christian.wuerker@ceusmedia.de> (http://ceusmedia.de)
 *	@license	LGPL/CC
 *	@option		offsetTop			vertical offset (downwards from top border of input field)
 *	@option		offsetRight			horizontal offset (to the left from right border of input field)
 */
(function($){
	jQuery.fn.cmClearInput = function(method){

		var methods = {															//  methods callable using constructor
			init: function(options){
				var settings = jQuery.extend({									//  options and defaults
					offsetTop: 5,												//  vertical offset (downwards from top border of input field)
					offsetRight: 6												//  horizontal offset (to the left from right border of input field)
				}, options);

				return this.each(function(){									//  apply plugin to matching elements
					setupTrigger($(this), settings);							//  set up trigger, its events and input field events
				});
			}
		}

		function setupTrigger(input, settings){
			var trigger = $("<span></span>").addClass("cmClearInput-trigger");	//  create trigger
			var data = {input: input, trigger: trigger};						//  collect data for events
			positionTrigger(input, trigger.insertAfter(input), settings);		//  inject and position trigger
			input.bind("change.updateClearTrigger", data, function(event){		//  bind custom event on input field ...
				updateTriggerVisibility($(this), event.data.trigger);			//  ... to update trigger on change
			}).trigger("change.updateClearTrigger");							//  call this event initially
			trigger.bind("click", data, function(event){						//  bind click event on trigger
				event.data.input.val("");										//  clear input field
				event.data.input.trigger("keyup.*");							//  trigger bound key events
				event.data.input.trigger("change.*");							//  trigger bound change events
			});
		}

		function positionTrigger(input, trigger, settings){
			input.parent().css("position", "relative");							//  position container relative for absolute positioned trigger
			var left = input.outerWidth();										//  horizontal position is outer width of input field ...
			left -= parseInt(input.css("border-right"));						//  ... minus right border ...
			left -= parseInt(input.css("margin-right"));						//  ... minus right margin ...
			left -= trigger.width() + settings.offsetRight;						//  ... minus trigger width and offset ...
			left = (left / input.parent().width() * 100) + "%";					//  ... relative to container around input field
			var top = input.position().top + parseInt(input.css("margin-top"));	//  vertical space above input field ...
			top += parseInt(input.css("border-top")) + settings.offsetTop;		//  ... including border and trigger offset
			trigger.css({left: left, top: top});								//  position trigger
		}

		function updateTriggerVisibility(input, trigger){
			var isVisible = trigger.is(":visible");								//  store trigger visibility
			var shouldBe = input.val().length > 0;								//  note if input field has a value
			if(!isVisible && shouldBe)											//  input field has value but trigger is hidden
				trigger.show();													//  show trigger
			else if(isVisible && !shouldBe)										//  input field is empty but trigger is visible
				trigger.hide();													//  hide trigger
		}

		// Method calling logic
		if(methods[method])																			//  called method is defined
			return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));			//  call method with arguments
		else if(typeof(method) === 'object' || ! method)											//  options object or nothing given
			return methods.init.apply(this, arguments);												//  call init method with arguments
		$.error( 'Method ' +  method + ' does not exist on cmClearInput' );							//  report invalid method call
	};
})( jQuery );
