var ModuleAdminLogException	= {};
ModuleAdminLogException.Index = {
	init: function(){
		var form = jQuery('#form-admin-log-exception');
		var master = form.find('#admin-log-exception-list-all-items-toggle');
		var boxes = form.find('.checkbox-item');
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
