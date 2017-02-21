var InfoDashboard = {
	init: function(){
		if(jQuery(".thumbnails.sortable>li").size() > 1){
			/* @see http://api.jqueryui.com/sortable/ */
			jQuery(".thumbnails.sortable").sortable({
				containment_: "parent",
				opacity: 0.25,
				revert: 250,
				tolerance: "pointer",
				cursor: "move",
				forceHelperSize: true,
				forcePlaceholderSize: true,
				placeholder: "sortable-placeholder",
				items: "> li",
			//	handle: ".dashboard-panel-handle .handle-button-move",
				handle: ".dashboard-panel-handle",
				stop: function( event, ui ) {
					var list = [];
					$("ul.thumbnails>li").each(function(){
						list.push($(this).data("panel-id"))
					});
					jQuery.ajax({
						url: "./info/dashboard/ajaxSaveOrder",
						mathodType: "post",
						dataType: "json",
						data: {list: list.join(",")}
					});
				}
			});
		}

		jQuery(".trigger-myModalInfoDashboardAdd").bind("click", function(){
			$("#myModalInfoDashboardAdd").modal("toggle");
			return false;
		})
		jQuery(".trigger-myModalInfoDashboardAddPanel").bind("click", function(){
			$("#myModalInfoDashboardAddPanel").modal("toggle");
			return false;
		})

		jQuery(".button-rename-board").bind("click", function(){
			var title = prompt('Neuen Titel', jQuery(this).data('title'));
			var dashboardId = jQuery(this).data('dashboard-id');
			if(title) InfoDashboard.rename(dashboardId, title);
		});
	},
	rename: function(dashboardId, title){
		jQuery.ajax({
			url: './info/dashboard/ajaxRename',
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
