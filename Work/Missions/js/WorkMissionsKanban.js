var WorkMissionsKanban = {
	userId: 0,

	init: function(){
	},

	loadCurrentList: function(onSuccess){
		var onSuccess = typeof onSuccess !== "undefined" ? onSuccess : function(){};
		WorkMissionsList.blendOut(100);
		$("#day-lists-empty").hide();
		$.ajax({
			url: "./work/mission/ajaxRenderIndex",
			dataType: "json",
			success: function(json){
				$("#message-loading-list").remove();
				$("#day-lists .visible-desktop").html(json.lists.large);
				$("#day-lists .hidden-desktop").html(json.lists.large);
				$("#mission-folders").equalize({
					equalize: 'height',
			//		reset: true,
					children: 'ul.sortable'
				});
				onSuccess();
			},
			error: function(xhr, message){
				console.log(message);
			}
		});
	},

	initBlockMovability: function(){
		$(".sortable").sortable({
			connectWith: ".sortable",
			dropOnEmpty: true,
			receive: function( event, ui ){
				$.ajax({
					url: "./work/mission/kanban/ajaxSetMissionStatus",
					data: {
						missionId: ui.item.context.dataset.id,
						status: event.target.id.replace(/^[a-z-]+/, "")
					},
					method: "POST",
				})
			}
		}).disableSelection();
	},

	initContextMenu: function(){
	}
}
