
var ModuleManageContentLocale = {

	init: function(){
//		$("#form_content-editor").show();
		this.scrollToActiveListItem($("#list-files").show());
	},

	onCodeMirrorChange: function(cm){
		$(cm.getTextArea()).next("div.CodeMirror").addClass("changed");
	},

	onCodeMirrorSave: function(cm){
		$.ajax({
			url: "./ajax/manage/content/saveContent",
			data: {content: cm.getValue()},
			dataType: "json",
			method: "post",
			context: cm,
			success: function(json){
				if(json && json.status && json.status === "data"){
					$(this.getTextArea()).next("div.CodeMirror").removeClass("changed");
				}
				else
					alert("Error on saving changes.");
			}
		});
	},

	scrollToActiveListItem: function(list){
		if(list.find("li.active").length){
			var pos = list.find("li.active").offset().top;
			pos -= list.offset().top;
			if(pos > list.height() / 2){
				pos -= list.height() / 2;
				pos += list.find("li.active").outerHeight() / 2;
				list.animate({scrollTop: pos}, 0);
			}
		}
		list.css("margin", 0);
	}
};
