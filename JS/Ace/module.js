ModuleAce.verbose	= true;
ModuleAce.strict	= true;
try{
	var selector = "#not_editor";
	if(jQuery(selector).size()){
		var editor = ModuleAce.applyTo(selector, {
			options: {
				maxLines: 12,
				minLines: 2,
			},
			flags: {
				highlightActiveLine: false,
			}
		});
		console.log(ModuleAce.getFrom(selector));
	}
}
catch(e){
	console.log(e);
}

//ModuleAce.applyAuto();

ModuleAce.applyTo("#editor-area", {
	hotkeys:[{
    	key: "Ctrl+s",
    	name: "Save on demand",
		callback: function(editor){
			alert("Save on demand");
//			console.log(editor.getValue());
		}
	}],
	events: [{
		event: 'change',
		callback: function(){
			var textarea = jQuery("#editor-area");
			var editor = textarea.data('ace-editor-instance');
			var current = editor.session.getValue();
			var timeout = textarea.data('ace-editor-save-timeout');
			if(timeout)
				window.clearTimeout(timeout);
			if(current == textarea.data('original-value')){
				jQuery(editor.container).removeClass("changed");
				return;
			}
			jQuery(editor.container).addClass("changed");
			timeout = window.setTimeout(function(){
				jQuery(editor.container).removeClass("changed");
			}, 1000);
			textarea.data('ace-editor-save-timeout', timeout);
		}
	}],
});

jQuery(".ace-auto").each(function(){
	ModuleAce.log(ModuleAce.getFrom(this));
})
