UI = typeof UI === "undefined" ? {} : UI;
UI.Messenger	= {
	status: 0,
	timeSlideDown: "slow",
	timeSlideUp: "slow",
	timeRemove: 5000,
	__init: function(){
		if(!UI.Messenger.status){
			if(typeof settings !== "undefined"){
				if(typeof settings.UI_Helper_Messenger_Bootstrap !== "undefined"){
					UI.Messenger.timeSlideDown	= settings.UI_Helper_Messenger_Bootstrap.slideDown;
					UI.Messenger.timeSlideUp	= settings.UI_Helper_Messenger_Bootstrap.slideUp;
					UI.Messenger.timeRemove		= settings.UI_Helper_Messenger_Bootstrap.autoRemove;
				}
			}

			if(UI.Messenger.timeRemove){
				$(document).ready(function(){
					$("#layout-messenger>div>div").each(function(){
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
			if(!$("#layout-messenger>div>div").size())
				$("#layout-messenger>div").remove();
		})
	},
	noteSuccess: function(message){
		return UI.Messenger.renderMessage(message,'success');
	},
	noteNotice: function(message){
		return UI.Messenger.renderMessage(message,'notice');
	},
	noteError: function(message){
		return UI.Messenger.renderMessage(message,'error');
	},
	noteFailure: function(message){
		return UI.Messenger.renderMessage(message,'failure');
	},
	renderMessage: function(message,typeClass){
		var list;
		UI.Messenger.__init();
		container = $("#layout-messenger");
		if(!$("div",container).size()){
			list = $("<div></div>");
			list.addClass("messenger-messages messenger-bootstrap");
			container.prepend(list);
		}
		list = $("#layout-messenger>div");
		item = $("<div></div>").html(message);
		item.addClass("messenger-"+typeClass+" alert-"+typeClass);
		list.append(item.hide());
		item.slideDown(UI.Messenger.timeSlideDown);
		UI.Messenger.autoRemoveMessage(item);
	}
};
UI.Messenger.__init();
