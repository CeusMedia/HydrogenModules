var WorkMissionsKanban = {
	userId: 0,

	init: function(){
	},

	loadCurrentList: function(onSuccess){
		var onSuccess = typeof onSuccess !== "undefined" ? onSuccess : function(){};
//		WorkMissionsList.blendOut(100);
		$("#day-lists-empty").hide();
		$.ajax({
			url: "./ajax/work/mission/kanban/renderIndex",
			dataType: "json",
			success: function(json){
				$("#message-loading-list").remove();
				$("#day-lists .visible-desktop").html(json.data.lists.large);
				$("#day-lists .hidden-desktop").html(json.data.lists.large);
//				$("#mission-folders").equalize({
				$(".work-mission-kanban-lane-item").equalize({
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
//				console.log(ui);
				$.ajax({
					url: "./ajax/work/mission/kanban/setMissionStatus",
					data: {
						missionId: ui.item[0].dataset.id,
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
