let InfoDashboard = {
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
					let list = [];
					$("ul.thumbnails>li").each(function(){
						list.push($(this).data("panel-id"))
					});
					jQuery.ajax({
						url: "./ajax/info/dashboard/saveOrder",
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
			let title = prompt('Neuen Titel', jQuery(this).data('title'));
			let dashboardId = jQuery(this).data('dashboard-id');
			if(title)
				InfoDashboard.rename(dashboardId, title);
		});
	},
	rename: function(dashboardId, title){
		jQuery.ajax({
			url: './ajax/info/dashboard/rename',
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
	},
	loadPanel: function(panelId, panelUrl) {
		let panelContainer	= jQuery("#dashboard-panel-" + panelId + " .dashboard-panel-container");
		jQuery.ajax({
			url: "./" + panelUrl + "/" + panelId,
//			mimeType: "application/json",		// @todo enable after panels of all other modules have migrated, to enforce/await JSON response
			context: panelContainer,
			success: function(json) {
				if(json.data)
					this.html(json.data);
				else
					this.html(json);
			},
			error: function(request, message, error){
				let alert = '<div class="alert-error">' + message + '</div>';
				this.html(alert);
			}
		});
	}
};
