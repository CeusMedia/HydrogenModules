var ModuleAdminMail	= {};
ModuleAdminMail.Queue = {
	init: function(){
		var form = jQuery('#form-admin-mail-queue');
		var master = form.find('#admin-mail-queue-list-all-items-toggle');
		var boxes = form.find('.checkbox-mail');
		master.on('input', function(){
			var value = jQuery(this).prop('checked');
			boxes.prop('checked', value);
		});
		form.find('#action-button-remove').on('click', function(){
			var ids = [];
			boxes.each(function(){
				var box = jQuery(this);
				if(box.prop('checked'))
					ids.push(box.data('id'));
			});
			form.find('#input_type').val('remove');
			form.find('#input_ids').val(ids.join(','));
			form.submit();
		});
	}
};
ModuleAdminMail.TemplateEditor	= {
	templateId: 0,
	init: function(templateId){
		this.templateId = templateId;
		ModuleAce.verbose = false;
		var reloadFrameBySelector = function(selector){
			jQuery(selector).each(function(){
				if(this.nodeName.toLowerCase() === 'iframe' )
					this.contentWindow.location.reload();
			});
		};
		var onHtmlUpdate	= function(chance){
			reloadFrameBySelector('.template-preview-iframe-container iframe');
			reloadFrameBySelector("#modal-admin-mail-template-preview-html iframe");
			reloadFrameBySelector("#frame-template-preview");
		};
		var onTextUpdate	= function(chance){
			reloadFrameBySelector('.template-preview-iframe-container iframe');
			reloadFrameBySelector("#modal-admin-mail-template-preview-text iframe");
			reloadFrameBySelector("#frame-template-preview");
		};
		var onStyleUpdate	= function(chance){
			reloadFrameBySelector('.template-preview-iframe-container iframe');
			reloadFrameBySelector("#modal-admin-mail-template-preview-html iframe");
			reloadFrameBySelector("#frame-template-preview");
		};
		ModuleAceAutoSave.applyToEditor(
			ModuleAce.applyTo("#input_template_html"),
			"ajax/admin/mail/template/saveHtml/"+templateId,
			{callbacks: {update: onHtmlUpdate}}
		);
		ModuleAceAutoSave.applyToEditor(
			ModuleAce.applyTo("#input_template_plain"),
			"ajax/admin/mail/template/savePlain/"+templateId,
			{callbacks: {update: onTextUpdate}}
		);
		ModuleAceAutoSave.applyToEditor(
			ModuleAce.applyTo("#input_template_css"),
			"ajax/admin/mail/template/saveCss/"+templateId,
			{callbacks: {update: onStyleUpdate}}
		);
		jQuery("#admin-mail-template-edit li a").on("click", function(){
			var targetUrl	= jQuery(this).attr("href").substr(1);
			jQuery.ajax({
				url: "./ajax/admin/mail/template/setTab/"+targetUrl
			});
		});
	}
};
