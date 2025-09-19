let WorkMissionsList = {
	sortBy: 'priority',
	sortDir: 'ASC',
	blendOut: function(duration){
		let _d = typeof duration == "undefined" ? 500 : duration;
		$("#day-list-large>*, #day-list-small>*").stop(true).animate({opacity: 0.33}, _d);

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
		let _onSuccess = typeof onSuccess !== "undefined" ? onSuccess : function(){};
		WorkMissionsList.blendOut(250);
		$("#day-lists-empty").hide();
		$.ajax({
			url: "./ajax/work/mission/renderIndex",
			dataType: "json",
			success: function(json){
				$("#message-loading-list").remove();
				WorkMissionsList.renderDayListDayControls(json.data);
				WorkMissions.showTotalDayMissionCount();
				_onSuccess();
			},
			error: function(xhr, error){
				console.log(error);
			}
		});
	},
	makeTableSortable: function(jq, options){
		let _options = $.extend({order: null, direction: "ASC"}, options);
		$("body").data("tablesort-options", _options);
		jq.find("tr th div.sortable").each(function(){
			if($(this).data("column")){
				$(this).removeClass("sortable").parent().addClass("sortable");
				if($(this).data("column") == _options.order){
					$(this).parent().addClass("ordered");
					$(this).parent().addClass("direction-"+_options.direction.toLowerCase());
				}
				$(this).on("click",function(){
					let head = $(this);
					let options = $("body").data("tablesort-options");
					let column = head.data("column");
					let direction = options.direction;
					if( options.order === column )
						direction = direction === "ASC" ? "DESC" : "ASC";
					let url = "./work/mission/filter/?order="+column+"&direction="+direction;
					document.location.href = url;
				});
			}
		});
	},
	renderDayListDayControls: function(data){
		$("#day-list-large").html(""+data.lists.large).stop(true);
		$("#day-list-small").html(""+data.lists.small).stop(true);
		data.count ? $("#day-lists-empty").hide() : $("#day-lists-empty").show();
		data.total ? $("#mission-folders").show() : $("#mission-folders").hide();
		WorkMissionsList.makeTableSortable($("#layout-content table"),{
			url: "./work/mission/filter/",
			order: WorkMissionsList.sortBy,
			direction: WorkMissionsList.sortDir
		});
		if(typeof data.buttons !== "undefined"){
			$("#day-controls-small").html(data.buttons.small);
			$("#day-controls-large").html(data.buttons.large);
		}
		$("#day-controls-large a.btn.active").removeClass("active");
		$("#day-controls-large a.btn").eq(data.day).addClass("active");
		$("#day-controls-small li:eq("+data.day+") a").tab("show");
	},
	setPage: function(page){
		$.ajax({																//  store action using AJAX
			url: "./ajax/work/mission/setFilter/page/"+page,					//  URL to set changed filter
				success: function(){										//  on response
					WorkMissionsList.loadCurrentListAndDayControls();			//  reload day lists and controls
				}
		});
	},
	toggleCheckboxes: function(){
		$("input").filter("[name*=missionIds]").each(function(){
			if($(this).is(':checked')){
				$(this).prop("checked", false);
				$(this).parent().parent().removeClass("warning");
			}
			else{
				$(this).prop("checked", !$(this).is(':checked'));
				$(this).parent().parent().addClass("warning");
			}
		});
	}
}
