let ModuleAdminMail	= {};
ModuleAdminMail.Queue = {
	init: function(){
		let form = jQuery('#form-admin-mail-queue');
		let master = form.find('#admin-mail-queue-list-all-items-toggle');
		let boxes = form.find('.checkbox-mail');
		master.on('input', function(){
			let value = jQuery(this).prop('checked');
			boxes.prop('checked', value);
		});
		form.find('#action-button-abort').on('click', function(){
			let ids = [];
			boxes.each(function(){
				let box = jQuery(this);
				if(box.prop('checked'))
					ids.push(box.data('id'));
			});
			form.find('#input_type').val('abort');
			form.find('#input_ids').val(ids.join(','));
			form.submit();
		});
		form.find('#action-button-retry').on('click', function(){
			let ids = [];
			boxes.each(function(){
				let box = jQuery(this);
				if(box.prop('checked'))
					ids.push(box.data('id'));
			});
			form.find('#input_type').val('retry');
			form.find('#input_ids').val(ids.join(','));
			form.submit();
		});
		form.find('#action-button-remove').on('click', function(){
			let ids = [];
			boxes.each(function(){
				let box = jQuery(this);
				if(box.prop('checked'))
					ids.push(box.data('id'));
			});
			form.find('#input_type').val('remove');
			form.find('#input_ids').val(ids.join(','));
			form.submit();
		});
	}
};

ModuleAdminMail.Queue.Filter = {
	init: function(){
		new ModuleAdminMailBootstrapTypeahead('#input_receiverAddress', {
			url: 'ajax/admin/mail/queue/findReceiverAddress',
			minLength: 3,
		}).init();

		$("#input_receiverAddress").bind('change input', function(event){
			$("#input_receiverAddress").data('last-change-valid', ('undefined' !== typeof event.isTrigger));
		});
		$("#input_receiverAddress").bind('blur', function(event){
			if(0 !== this.value.length){
				let container = $(this);
				if(!container.data('last-change-valid'))
					container.val('');
			}
		});
	}
};

ModuleAdminMail.TemplateEditor	= {
	templateId: 0,
	init: function(templateId){
		this.templateId = templateId;
		ModuleAce.verbose = false;
		let reloadFrameBySelector = function(selector){
			jQuery(selector).each(function(){
				if(this.nodeName.toLowerCase() === 'iframe' )
					this.contentWindow.location.reload();
			});
		};
		let onHtmlUpdate	= function(chance){
			reloadFrameBySelector('.template-preview-iframe-container iframe');
			reloadFrameBySelector("#modal-admin-mail-template-preview-html iframe");
			reloadFrameBySelector("#frame-template-preview");
		};
		let onTextUpdate	= function(chance){
			reloadFrameBySelector('.template-preview-iframe-container iframe');
			reloadFrameBySelector("#modal-admin-mail-template-preview-text iframe");
			reloadFrameBySelector("#frame-template-preview");
		};
		let onStyleUpdate	= function(chance){
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
			let targetUrl	= jQuery(this).attr("href").substr(1);
			jQuery.ajax({
				url: "./ajax/admin/mail/template/setTab/"+targetUrl
			});
		});
	}
};

class ModuleAdminMailBootstrapTypeahead {
	constructor(selector, options) {
		this.$elements = $(selector);
		this.options = $.extend({
			url: '',
			queryParam: 'q',
			minLength: 1,
			items: 8
		}, options || {});
	}

	init() {
		var self = this;
		self.$elements.typeahead({
			minLength: self.options.minLength,
			items: self.options.items,
			source: function (query, process) {
				var data = {};
				data[self.options.queryParam] = query;
				$.ajax({
					url: self.options.url,
					type: 'GET',
					data: data,
					dataType: 'json',
					success: function (response) {
						// response must be an array
						process(response.data.items);
					},
					error: function () {
						process([]);
					}
				});
			}
		});
		return self;
	}
}
