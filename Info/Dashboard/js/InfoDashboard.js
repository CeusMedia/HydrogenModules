var InfoDashboard = {
	init: function(){
		if(jQuery(".thumbnails.sortable>li").length > 1){
			/* @see http://api.jqueryui.com/sortable/ */
			jQuery(".thumbnails.sortable").sortable({
				containment_: "parent",
				opacity: 0.75,
				revert: 250,
				tolerance: "pointer",
				cursor: "move",
				forceHelperSize: true,
				forcePlaceholderSize: true,
				placeholder: "dashboard-panel span4 sortable-placeholder",
				items: "> li",
			//	handle: ".dashboard-panel-handle .handle-button-move",
				handle: ".dashboard-panel-handle",
/*				start: function(event, ui) {
					var placeholder	= jQuery("li.sortable-placeholder");
					placeholder.width(ui.helper.width()-2);
					placeholder.height(ui.helper.height()-3);
				},*/
				stop: function(event, ui) {
					var list = [];
					$("ul.thumbnails>li").each(function(){
						list.push($(this).data("panel-id"))
					});
					jQuery.ajax({
						url: "./info/dashboard/ajax/saveOrder",
						mathodType: "post",
						dataType: "json",
						data: {list: list}
					});
				}
			});
		}

		jQuery("#input_dashboardId").on("change", function(){
			InfoDashboard.select(jQuery(this).val());
		});
		jQuery(".trigger-myModalInfoDashboardAdd").on("click", function(){
			$("#myModalInfoDashboardAdd").modal("toggle");
			return false;
		});
		jQuery(".trigger-myModalInfoDashboardAddPanel").on("click", function(){
			$("#myModalInfoDashboardAddPanel").modal("toggle");
			return false;
		});
		jQuery(".button-rename-board").on("click", function(){
			var title = prompt('Neuen Titel', jQuery(this).data('title'));
			var dashboardId = jQuery(this).data('dashboard-id');
			if(title)
				InfoDashboard.rename(dashboardId, title);
		});
	},
	rename: function(dashboardId, title){
		jQuery.ajax({
			url: './info/dashboard/ajax/rename',
			data: {title: title},
			method: 'post',
			dataType: 'json',
			success: function(json){
				document.location.reload();
			}
		});
	},
	select: function(dashboardId){
		document.location = "./info/dashboard/select/"+dashboardId;
	}
};
