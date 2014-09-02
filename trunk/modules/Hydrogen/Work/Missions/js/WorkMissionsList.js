var WorkMissionsList = {
	sortBy: 'priority',
	sortDir: 'ASC',
	blendOut: function(duration){
		var duration = typeof duration == "undefined" ? 500 : duration;
		$("#day-lists div.table-day").stop(true).animate({opacity: 0.5}, duration);

	},
	init: function(){
		$("#work-mission-view-type-1").removeAttr("disabled");
		$("#work-mission-view-type-1").click(function(){WorkMissions.changeView(1);});
		WorkMissionsList.makeTableSortable($("#layout-content table"),{
			url: "./work/mission/filter/",
			order: WorkMissionsList.sortBy,
			direction: WorkMissionsList.sortDir
		});
	},
	loadCurrentListAndDayControls: function(onSuccess){
		var onSuccess = typeof onSuccess !== "undefined" ? onSuccess : function(){};
		WorkMissionsList.blendOut(100);
		$("#day-lists-empty").hide();
		$.ajax({
			url: "./work/mission/ajaxRenderList",
			dataType: "json",
			success: function(json){
				$("#message-loading-list").remove();
				WorkMissionsList.renderDayListDayControls(json);
				WorkMissions.showTotalDayMissionCount();
				onSuccess();
			},
			error: function(a, b, c){
				console.log(a);
				console.log(b);
				console.log(c);
			}
		});
	},
	makeTableSortable: function(jq, options){
		var options = $.extend({order: null, direction: "ASC"}, options);
		$("body").data("tablesort-options",options);
		jq.find("tr th div.sortable").each(function(){
			if($(this).data("column")){
				$(this).removeClass("sortable").parent().addClass("sortable");
				if($(this).data("column") == options.order){
					$(this).parent().addClass("ordered");
					$(this).parent().addClass("direction-"+options.direction.toLowerCase());
				}
				$(this).bind("click",function(){
					var head = $(this);
					var options = $("body").data("tablesort-options");
					var column = head.data("column");
					var direction = options.direction;
					if( options.order == column )
						direction = direction == "ASC" ? "DESC" : "ASC";
					var url = "./work/mission/filter/?order="+column+"&direction="+direction;
					document.location.href = url;
				});
			}
		});
	},
	renderDayListDayControls: function(data){
		$("#day-list-large").html(data.lists.large).stop(true);
		$("#day-list-small").html(data.lists.small).stop(true);
		if(!data.count)
			$("#day-lists-empty").show();
		WorkMissionsList.makeTableSortable($("#layout-content table"),{
			url: "./work/mission/filter/",
			order: WorkMissionsList.sortBy,
			direction: WorkMissionsList.sortDir
		});
		$("#day-controls-small").html(data.buttons.small);
		$("#day-controls-large").html(data.buttons.large);
		$("#day-controls-large a.btn.active").removeClass("active");
		$("#day-controls-large a.btn").eq(data.day).addClass("active");
		$("#day-controls-small li:eq("+data.day+") a").tab("show");
	}
}
