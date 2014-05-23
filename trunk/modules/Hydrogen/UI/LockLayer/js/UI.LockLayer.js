var LockLayer = {

	defaultMessageKey: "loading",
	messages: {
		loading: "Loading ...",
		processing: "Processing ...",
		wait: "Please wait ..."
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

	init: function () {
		"use strict";
		if (!this.isSetUp) {
			this.layerBack = $("<div></div>").attr("id", "layer-lock-back").appendTo($("body"));
			this.layerFront = $("<div></div>").attr("id", "layer-lock-front").appendTo(this.layerBack);
//			this.layerBack = $("#layer-lock-back");
//			this.layerFront = $("#layer-lock-front");
			this.isSetUp = true;
			this.settings = settings.UI_LockLayer;
			if (this.settings.auto && this.settings.auto_class) {
				var delay = this.settings.auto_delay;
				var selector = "." + this.settings.auto_class;
				this.applyTo(selector, delay);
			}
		}
	},

	applyTo: function (selector, delay) {
		var delay = typeof delay === "undefined" ? 0 : parseInt(delay);
		$(selector).each(function(){
			$(this).bind("click", {elem: this}, function (event) {
				var elem = $(event.data.elem);
				var label = LockLayer.defaultMessageKey;								//  init label as key of default message
				if (LockLayer.messages.hasOwnProperty(label))							//  for valid default message key
					label = LockLayer.messages[label];									//  set label to default message
				if (elem.data("locklayer-label"))										//  clicked element has special label
					label = elem.data("locklayer-label");								//  set label to special label
				else if (elem.attr("title"))											//  otherwise check element title
					label = elem.attr("title");											//  and set at label if found
				else if (elem.attr("alt"))												//  otherwise check element alternative text
					label = elem.attr("alt");											//  and set at label if found
//				else if (elem.html())													// 	...
//					label = elem.html();												//  ...
				LockLayer.lock(label, delay);											//  lock user interface with label
			});
		});
	},

	lock: function (message, delay) {
		"use strict";
		if (this.isSetUp) {
			var duration = this.settings.fade ? this.settings.fade_duration_in : 0;
			this.setMessage(message);
			if (!this.isLocked) {
				this.isLocked = true;
				if (delay)
					window.setTimeout(function(){
						LockLayer.layerBack.fadeIn(duration);
					}, delay);
				else
					this.layerBack.fadeIn(duration);
			}
		}
	},

	setMessage: function (message) {
		"use strict";
		if (this.isSetUp) {
			var defaultMessage = this.messages[this.defaultMessageKey];
			message = typeof message === "string" ? message : "";
			message = message.length ? message : defaultMessage;
			if (this.messages.hasOwnProperty(message)) {
				message = this.messages[message];
			}
			var label = $("<div></div>").addClass("layer-lock-message").html(message);
			this.layerFront.html(label);
		}
	},

	unlock: function (delay) {
		"use strict";
		if (this.isSetUp) {
			if (this.isLocked) {
				var duration = this.settings.fade ? this.settings.fade_duration_out : 0;
				this.isLocked = false;
				this.layerBack.fadeOut(duration);
			}
		}
	}
};

//$(document).ready(function(){
	LockLayer.init();
//});
