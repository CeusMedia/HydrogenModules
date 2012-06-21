UI.Messenger	= {
	timeSlideDown:	"slow",
	timeSlideUp:	"slow",
	timeRemove:		5000,
	hideMessage:	function(messageId){
		$("#"+messageId).slideUp(this.timeSlideUp,function(){
			$(this).remove();
			if(!$("#layout-messenger>ul>li").size())
				$("#layout-messenger>ul").remove();
		})
	},
	noteSuccess:	function(message){
		return UI.Messenger.renderMessage(message,'success');
	},
	noteNotice:		function(message){
		return UI.Messenger.renderMessage(message,'notice');
	},
	noteError:		function(message){
		return UI.Messenger.renderMessage(message,'error');
	},
	noteFailure:	function(message){
		return UI.Messenger.renderMessage(message,'failure');
	},
	renderMessage:	function(message,typeClass){
		container	= $("#layout-messenger");
		if(!$("ul",container).size())
			container.prepend($("<ul></ul>"));
		list		= $("#layout-messenger>ul");
		messageId	= typeClass+'-'+Math.round(Math.random()*1000000);
		item		= $("<li></li>").attr('id',messageId).addClass(typeClass).html(message);
		list.append(item.hide());
		item.slideDown(this.timeSlideDown);
		window.setTimeout('UI.Messenger.hideMessage("'+messageId+'")',this.timeRemove);
	}
};
