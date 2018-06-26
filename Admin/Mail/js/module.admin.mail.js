var ModuleAdminMail	= {};
ModuleAdminMail.TemplateEditor	= {
	templateId: 0,
	init: function(templateId){
		this.templateId = templateId;
		ModuleAce.verbose = false;
		var onUpdate	= function(chance){
			jQuery("#modal-admin-mail-template-preview .modal-body iframe").get(0).contentWindow.location.reload();
			jQuery("#frame-template-preview").get(0).contentWindow.location.reload();
		};
		ModuleAceAutoSave.applyToEditor(
			ModuleAce.applyTo("#input_template_html"),
			"admin/mail/template/ajaxSaveHtml/"+templateId,
			{callbacks: {update: onUpdate}}
		);
		ModuleAceAutoSave.applyToEditor(
			ModuleAce.applyTo("#input_template_plain"),
			"admin/mail/template/ajaxSavePlain/"+templateId,
			{callbacks: {update: onUpdate}}
		);
		ModuleAceAutoSave.applyToEditor(
			ModuleAce.applyTo("#input_template_css"),
			"admin/mail/template/ajaxSaveCss/"+templateId,
			{callbacks: {update: onUpdate}}
		);
	/*	onUpdate();*/
		jQuery("#admin-mail-template-edit li a").on("click", function(){
			var targetUrl	= jQuery(this).attr("href").substr(1);
			jQuery.ajax({
				url: "./admin/mail/template/ajaxSetTab/"+targetUrl
			});
		});
	}
};
