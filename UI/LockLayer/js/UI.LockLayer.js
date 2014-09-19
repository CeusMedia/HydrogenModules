var LockLayer = {

	defaultType: "loading",
	messages: {
		loading: "Loading ...",
		processing: "Processing ...",
		waiting: "Please wait ..."
	},
	settings: {},
	options: {
		fade: true,
		fadeDurationLock: 500,
		fadeDurationUnlock: 150,
	},
	isLocked: false,
	isSetUp: false,
	layerBack: null,
	layerFront: null,

	applyTo: function (selector, delay, fade) {
		var delay	= typeof delay === "undefined" ? this.settings.default_delay : parseInt(delay);					//  sanitize delay
		$(selector).each(function(){													//  iterate found elements
			var eventName = this.nodeName === "FORM" ? "submit" : "click";				// 	event name for forms differs from clicks
			var eventData = {elem: this, delay: delay, fade: fade};						//  collect event data for callback function
			$(this).bind(eventName, eventData, LockLayer.onLockTrigger);				//  bind callback with data to event
		});
	},

	applyToButtons: function (context, delay, fade) {
		var context = context ? $(context) : $("body");									//  define context
		var buttons = context.find("button");
	    LockLayer.applyTo(buttons.filter("[type=button][onclick*=location]"), delay, fade);			//  apply to redirecting buttons
	},

	applyToForms: function (context, delay, fade) {
		var context = context ? $(context) : $("body");									//  define context
	    LockLayer.applyTo(context.find("form[action][method]"), delay, fade);
	},

	applyToLinks: function (context, delay, fade) {
		var context = context ? $(context) : $("body");									//  define context
		var links = context.find("a[href]").not("[href*=#]");							//  find all links without fragment
	    LockLayer.applyTo(links.not("[target]"), delay, fade);							//  apply to links without target
	    LockLayer.applyTo(links.filter("[target=_self]"), delay, fade);					//  apply to links with target to self
	},

	init: function () {
		"use strict";
		if (!this.isSetUp) {
			this.layerBack = $("<div></div>").attr("id", "layer-lock-back").appendTo($("body"));
			this.layerFront = $("<div></div>").attr("id", "layer-lock-front").appendTo(this.layerBack);
//			this.layerBack = $("#layer-lock-back");
//			this.layerFront = $("#layer-lock-front");
			this.isSetUp = true;
			this.settings = settings.UI_LockLayer;
			if (this.settings.enabled) {
				LockLayer.applyToButtons();
				LockLayer.applyToLinks();
				LockLayer.applyToForms();
			}
			this.layerBack.bind("click", LockLayer.unlock);								//  @todo: make this configurable
		}
	},

	lockByElement: function (elem, delay, fade) {
		var elem = $(elem);
		if (!elem.size())
			throw "LockLayer::lockByElement: No element given";

		//  message type
		var type = LockLayer.defaultType;												//  set default message type
		if (elem.data("locklayer-type"))												//  clicked element has message type
			if (LockLayer.messages.hasOwnProperty(elem.data("locklayer-type")))			//  element message type is valid
				type = elem.data("locklayer-type");										//  set element message type 

		//  message
		var message = LockLayer.messages[type];											//  set label from default messages by message type
		if (elem.data("locklayer-label"))												//  clicked element has special label
			message = elem.data("locklayer-label");										//  set label to special label
		else if (elem.attr("title"))													//  otherwise check element title
			message = elem.attr("title");												//  and set at label if found
		else if (elem.attr("alt"))														//  otherwise check element alternative text
			message = elem.attr("alt");													//  and set at label if found

		//  delay
		if (typeof delay === "undefined") {												//  no delay has been given
			delay = this.settings.default_delay;										//  set default delay
			if (typeof elem.data("locklayer-delay") !== "undefined")					//  element defines delay
                delay = elem.data("locklayer-delay");									//  set element delay
		}
		delay = Math.max(0, Math.min(1000, parseInt(delay)));							//  sanitize delay

		//  fade
		var durationIn = 0;																//  assume no duration of fade in
		var durationOut = 0;															//  assume no duration of fade out
		if (typeof fade === "undefined") {												//  no fade has been given
			fade = this.settings.default_fade;											//  set default fade
			if (elem.data("locklayer-fade"))											//  element defines fade
				fade = elem.data("locklayer-fade");										//  set element fade
		}
		if (fade) {																		//  fading is enabled
			durationIn = this.settings.default_fade_duration_in;						//  get default duration of fade in
			durationOut = this.settings.default_fade_duration_out;						//  get default duration of fade out
			if (elem.data("locklayer-fade-duration-in"))								//  element defines duration of fade in
				durationIn = elem.data("locklayer-fade-duration-in");					//  set duration of fade in
			if (elem.data("locklayer-fade-duration-out"))								//  element defines duration of fade out
				durationOut = elem.data("locklayer-fade-duration-out");					//  set duration of fade out
		}
		durationIn = Math.max(0, Math.min(1000, parseInt(durationIn)));					//  sanitize duration of fade in
		durationOut = Math.max(0, Math.min(1000, parseInt(durationOut)));				//  sanitize duration of fade out
		LockLayer.lock(message, delay, fade, durationIn, durationOut);					//  lock user interface with label
	}, 

	lock: function (message, delay, fade, durationIn, durationOut) {
		"use strict";
		if (this.isSetUp) {
			this.setMessage(message);
			if (!this.isLocked) {
				this.isLocked = true;
				this.layerBack.data("duration-out", durationOut);
				if (delay)
					window.setTimeout(function(){
						LockLayer.layerBack.fadeIn(durationIn);
					}, delay);
				else
					this.layerBack.fadeIn(durationIn);
			}
		}
	},

	onLockTrigger: function (event) {
		if (event.data.elem.nodeName !== "FORM")
			if(event.which === 2 || event.ctrlKey)										//  middle mouse button or pressed control key
				return;																	//  do not lock
		LockLayer.lockByElement(event.data.elem);										//  lock user interface with label of element
//		//  block form submit - for testing only
//		if (event.data.elem.nodeName === "FORM")
//			return false;
	},

	setMessage: function (message) {
		"use strict";
		if (this.isSetUp) {
			var defaultMessage = this.messages[this.defaultType];
			message = typeof message === "string" ? message : "";
			message = message.length ? message : defaultMessage;
			if (this.messages.hasOwnProperty(message)) {
				message = this.messages[message];
			}
			var label = $("<div></div>").addClass("layer-lock-message").html(message);
			this.layerFront.html(label);
		}
	},

	unlock: function () {
		"use strict";
		if (LockLayer.isSetUp) {
			if (LockLayer.isLocked) {
				var durationOut = LockLayer.layerBack.data("duration-out");
				LockLayer.isLocked = false;
				LockLayer.layerBack.fadeOut(durationOut);
			}
		}
	}
};
