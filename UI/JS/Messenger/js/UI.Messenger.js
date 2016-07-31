UI = typeof UI === "undefined" ? {} : UI;
UI.Messenger	= {
	status: 0,
	timeSlideDown: "slow",
	timeSlideUp: "slow",
	timeRemove: 5000,
	__init: function(){
		if(!UI.Messenger.status){
			if(typeof settings !== "undefined"){
				if(typeof settings.UI_JS_Messenger !== "undefined"){
					UI.Messenger.timeSlideDown	= settings.UI_JS_Messenger.slideDown;
					UI.Messenger.timeSlideUp	= settings.UI_JS_Messenger.slideUp;
					UI.Messenger.timeRemove		= settings.UI_JS_Messenger.autoRemove;
				}
			}

			if(UI.Messenger.timeRemove){
				$(document).ready(function(){
					$("#layout-messenger>ul>li").each(function(){
						UI.Messenger.autoRemoveMessage(this);
					});
				});
			}
			UI.Messenger.status = 2;
		}
	},
	autoRemoveMessage: function(item){
		var messageId = 'message-'+Math.round(Math.random()*1000000);
		var callback = 'UI.Messenger.hideMessage("'+messageId+'")';
		if(!UI.Messenger.timeRemove)
			return;
		$(item).attr("id",messageId);
		window.setTimeout(callback, UI.Messenger.timeRemove);
	},
	discardMessage: function(item){
		var id;
		if(!(id = item.attr("id")))
			item.attr("id", (id = "tmp-msg-remove"));
		UI.Messenger.hideMessage(id);
	},
	hideMessage: function(messageId){
		UI.Messenger.__init();
		$("#"+messageId+" div.button").hide();
		$("#"+messageId).slideUp(UI.Messenger.timeSlideUp, function(){
			$(this).remove();
			if(!$("#layout-messenger>ul>li").size())
				$("#layout-messenger>ul").remove();
		})
	},
	noteSuccess: function(message, sticky){
		return UI.Messenger.renderMessage(message, 'success', sticky);
	},
	noteNotice: function(message, sticky){
		return UI.Messenger.renderMessage(message, 'notice', sticky);
	},
	noteError: function(message, sticky){
		return UI.Messenger.renderMessage(message, 'error', sticky);
	},
	noteFailure: function(message, sticky){
		return UI.Messenger.renderMessage(message, 'failure', sticky);
	},
	renderMessage: function(message, typeClass, sticky){
		UI.Messenger.__init();
		container = $("#layout-messenger");
		if(!$("ul",container).size())
			container.prepend($("<ul></ul>"));
		list = $("#layout-messenger>ul");
		item = $("<li></li>").addClass(typeClass).html(message);
		list.append(item.hide());
		item.slideDown(UI.Messenger.timeSlideDown);
		if(UI.Messenger.timeRemove && !sticky)
			UI.Messenger.autoRemoveMessage(item);
	}
};
UI.Messenger.__init();
