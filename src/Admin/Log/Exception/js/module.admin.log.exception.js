let ModuleAdminLogException	= {};
ModuleAdminLogException.Index = {
	init: function(){
		let form = jQuery('#form-admin-log-exception');
		let master = form.find('#admin-log-exception-list-all-items-toggle');
		let boxes = form.find('.checkbox-item');
		master.on('input', function(){
			let value = jQuery(this).prop('checked');
			boxes.prop('checked', value);
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
