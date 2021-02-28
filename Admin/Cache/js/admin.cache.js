var ModuleAdminCache = {
	init: function(){
		$("button.btn-cache-remove").on("click", ModuleAdminCache.remove);
	},
	remove: function(){
		var row = $(this).parent().parent();
		$.ajax({
			url: "./ajax/admin/cache/remove",
			data: {key: row.data("key")},
			type: "post",
			context: row,
			dataType: "json",
			success: function(response){
				$(this).remove();
			}
		});
	}
};
$(document).ready(ModuleAdminCache.init);
