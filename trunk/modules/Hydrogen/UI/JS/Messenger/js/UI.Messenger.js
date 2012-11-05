UI = typeof UI === "undefined" ? {} : UI;
UI.Messenger	= {
	status: 0,
	timeSlideDown: "slow",
	timeSlideUp: "slow",
	timeRemove: 5000,
	__init: function(){
		if(!this.status){
			var value;		
			if(typeof config !== "undefined"){
				this.timeSlideDown	= config.module_ui_js_messenger_slideDown;
				this.timeSlideUp	= config.module_ui_js_messenger_slideUp;
				this.timeRemove		= config.module_ui_js_messenger_autoRemove;
			}
			this.status = 2;
		}
	},
	discardMessage: function(item){
		var id;
		if(!(id = item.attr("id")))
			item.attr("id", (id = "tmp-msg-remove"));
		this.hideMessage(id);
	},
	hideMessage:	function(messageId){
		this.__init();
		$("#"+messageId+" div.button").hide();
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
		this.__init();
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
