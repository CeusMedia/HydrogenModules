var FormEditor = {
	applyAceEditor: function(selector, options){
		var options = jQuery.extend({
			minLines: 15,
			maxLines: 35,
		}, options);
		var textarea = jQuery(selector).hide();
		var editor = ace.edit("content_editor", options);
		editor.getSession().setValue(textarea.val());
		editor.setFontSize(14);
		editor.session.on("change", function(){
			var value = editor.getSession().getValue();
			textarea.val(value);
		});
		textarea.data('ace-editor', editor);
	}
};
