var ModuleAdminMail	= {};
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
			"admin/mail/template/ajaxSaveHtml/"+templateId,
			{callbacks: {update: onHtmlUpdate}}
		);
		ModuleAceAutoSave.applyToEditor(
			ModuleAce.applyTo("#input_template_plain"),
			"admin/mail/template/ajaxSavePlain/"+templateId,
			{callbacks: {update: onTextUpdate}}
		);
		ModuleAceAutoSave.applyToEditor(
			ModuleAce.applyTo("#input_template_css"),
			"admin/mail/template/ajaxSaveCss/"+templateId,
			{callbacks: {update: onStyleUpdate}}
		);
		jQuery("#admin-mail-template-edit li a").on("click", function(){
			var targetUrl	= jQuery(this).attr("href").substr(1);
			jQuery.ajax({
				url: "./admin/mail/template/ajaxSetTab/"+targetUrl
			});
		});
	}
};
