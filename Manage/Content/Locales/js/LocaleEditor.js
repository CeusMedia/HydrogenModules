var LocaleEditor = {
	language: null,
	fieldId: null,
	type: null,
	setupCodeMirror: function(){
		var options = {
			gutter: true,
			fixedGutter: true,
			lineNumbers: true,
			lineWrapping: false,
			indentUnit: 4,
			tabSize: 4,
			indentWithTabs: true,
			theme: "default",
			mode: "htmlmixed",
			extraKeys: {
				"F11": function(cm) {
					CodeMirror.setFullScreen(cm, !CodeMirror.isFullScreen(cm));
				},
				"Esc": function(cm) {
					if (CodeMirror.isFullScreen(cm)) CodeMirror.setFullScreen(cm, false);
				},
				"Ctrl-S": function(cm) {
					$.ajax({
						url: "./manage/content/locale/ajaxSaveContent/",
						data: {
							content: cm.getValue(),
							language: LocaleEditor.language,
							type: LocaleEditor.type,
							fileId: LocaleEditor.fileId,
						},
						dataType: "json",
						method: "post",
						success: function(json){
							if(json){
								textarea.next("div.CodeMirror").removeClass("changed");
								$("#page-preview-iframe-container iframe").get(0).contentWindow.location.reload();
							}
							else
								alert("Error on saving changes.");
						}
					});
				}
			}
		};
		var textarea = $("textarea#input_content");
		if(!textarea.is(":visible"))
			return;
		var mirror = CodeMirror.fromTextArea(textarea.get(0), options);
		mirror.on("change", function(instance, update){
			textarea.next("div.CodeMirror").addClass("changed");
		});
		textarea.data({
			codemirror: mirror,
			language: LocaleEditor.language,
			type: LocaleEditor.type,
			fileId: LocaleEditor.fileId,
		});
		mirror.setSize("auto",textarea.height());	//  set same size as textarea
		$("#hint").html("Press <b>F11</b> for fullscreen editing.");
	}
}
