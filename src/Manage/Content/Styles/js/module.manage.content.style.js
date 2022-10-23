var ModuleManageStyle = {

	file: null,

	init: function(file){
		ModuleManageStyle.file = file;
		$("#panel-file-editor").show();
	},

	onCodeMirrorChange: function(cm){
		$(cm.getTextArea()).next("div.CodeMirror").addClass("changed");
	},

	onCodeMirrorSave: function(cm){
		$.ajax({
			url: "./manage/content/style/ajaxSaveContent",
			data: {
				file: ModuleManageStyle.file,
				content: cm.getValue()
			},
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
	}
};
