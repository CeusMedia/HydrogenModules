let WorkMissions = {
	currentDay: 0,
	mode: null,
	changeView: function(type){
		document.location.href = "./work/mission?view="+parseInt(type);
	},

	filter: function(form){
		$("#day-lists").stop(true);
		WorkMissionsList.disable();
		$.ajax({
			url: './work/mission/filter?reset',
			data: $(form).serialize(),
			type: "POST",
			dataType: "json",
			success: function(json){
				WorkMissions.loadLists();
			}
		});
		return false;
	},

	init: function(mode){
		WorkMissionsFilter.__init(mode);
		this.mode = mode;
		let site = $("body.controller-work-mission");
		if(!site.length)
			return;

		if(site.hasClass('action-calendar')){
			WorkMissions.showTotalDayMissionCount($("#mission-calendar li").length);
		}
		if(site.hasClass('action-index') && mode === 'now'){
			WorkMissions.showDayTable(WorkMissions.currentDay);
			$("#input-import").on("click", function(){
				$("#input-serial").trigger("click")
			});
			$("#input-serial").on("change", function(){
				let value = $("#input-serial").val().replace(/\\/g,"/");
				$("#input-import").val(value.split(/\//).pop());
				let text = "Alle bisherigen Aufgaben und Termine werden gelöscht. Wirklich importieren?";
				if(confirm(text))
					$("#input-import").get(0).form.submit();
				else
					$("#input-import").val("");
			});
		}
	},
	moveMissionStartDate: function(missionId, date){
		WorkMissionsList.blendOut(150);
		$.ajax({
			url: './ajax/work/mission/changeDay/'+missionId,
			data: {date: date},
			dataType: "json",
			success: function(json){
				WorkMissionsList.renderDayListDayControls(json);
//				WorkMissions.showDayTable(WorkMissions.currentDay, false);
			}
		});
	},
	showDayTable: function(day, permanent, onSuccess){
		WorkMissions.currentDay = day;
		WorkMissionsList.blendOut(150);
		if(permanent)
			$.ajax({
				url: "./ajax/work/mission/selectDay/"+day,
				dataType: "json",
				success: function(json){
					WorkMissionsList.renderDayListDayControls(json.data);
//					WorkMissionsList.loadCurrentListAndDayControls(onSuccess);
				}
			});
		else
			WorkMissionsList.loadCurrentListAndDayControls(onSuccess);
	},
	showTotalDayMissionCount: function(sum){
		if(typeof sum === "undefined"){
			let sum = 0;
			$("#day-controls-large span.badge").each(function(){
				sum += parseInt($(this).html());
			})
		}
		$("#number-total").html(sum).show();
	}
};
