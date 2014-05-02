var WorkMissions = {
	currentDay: 0,
	changeDay: function(missionId, date){
		WorkMissionsList.disable(100);
		$.ajax({
			url: './work/mission/changeDay/'+missionId,
			data: {date: date},
			dataType: "json",
			success: function(json){
				$("#day-controls").html(json.buttons);
				$("#day-lists").html(json.lists).stop(true);
				WorkMissionsList.makeTableSortable($("#layout-content table"),{
					url: "./work/mission/filter/",
					order: WorkMissionsList.sortBy,
					direction: WorkMissionsList.sortDir
				});
				WorkMissionsList.enable(50);
				WorkMissions.showDayTable(WorkMissions.currentDay);
			}
		});
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
	loadLists: function(){
		$.ajax({
			url: './work/mission/ajaxRenderLists',
			dataType: "json",			
			success: function(json){
				$("#day-controls").html(json.buttons);
				$("#day-lists").html(json.lists).stop(true);
				WorkMissionsList.makeTableSortable($("#layout-content table"),{
					url: "./work/mission/filter/",
					order: WorkMissionsList.sortBy,
					direction: WorkMissionsList.sortDir
				});
				WorkMissionsList.enable(50);
				WorkMissions.showDayTable(WorkMissions.currentDay);
				
			}
		});
	},
	init: function(tense){
		var i, button;
		for(i=0; i<3; i++){
			button = $("#work-mission-view-tense-"+i);
			button.removeAttr("disabled").removeClass("disabled");			
			if(i === tense){
				button.addClass("active");
				button.css("cursor", "default");
			}
			else{
				button.bind("click", {tense: i}, function(event){
					document.location.href = "./work/mission/switchTense/"+event.data.tense;
				});
			}
		}

//		this.tense = tense;
		var site = $("body.controller-work-mission");
		if(!site.size())
			return;

		if(tense != 1)
			WorkMissions.showDayTable(0);

		if(site.hasClass('action-index')){
			WorkMissions.showDayTable(WorkMissions.currentDay);
			$("#input-import").bind("click", function(){
				$("#input-serial").trigger("click")
			});
			$("#input-serial").bind("change", function(){
				var value = $("#input-serial").val().replace(/\\/g,"/");
				$("#input-import").val(value.split(/\//).pop());
				var text = "Alle bisherigen Aufgaben und Termine werden gelöscht. Wirklich importieren?";
				if(confirm(text))
					$("#input-import").get(0).form.submit();
				else
					$("#input-import").val("");
			});
var sum = 0;
$("#day-controls span.badge").each(function(){
//	console.log($(this).html());
	sum += parseInt($(this).html());
})
$("#number-total").html(sum).show();

		}
		else{
			$("#input_title").focus();
			$("#input_dayWork, #input_dayDue, #input_dayStart, #input_dayEnd").datepicker({
				dateFormat: "yy-mm-dd",
		//		appendText: "(yyyy-mm-dd)",
		//		buttonImage: "/images/datepicker.gif",
		//		changeMonth: true,
		//		changeYear: true,
		//		gotoCurrent: true,
		//		autoSize: true,
				firstDay: 1,
				nextText: "nächster Monat",
				prevText: "vorheriger Monat",
				yearRange: "c:c+2",
				monthNames: monthNames
			});
			$("#input_dayWork").bind("change", function(event){
				var fieldEnd = $("#input_dayDue");
				if(fieldEnd.val() && $(this).val() > fieldEnd.val())
					fieldEnd.datepicker("setDate", $(this).val());
			});
			$("#input_dayStart").bind("change", function(event){
				var fieldEnd = $("#input_dayEnd");
				if(fieldEnd.val() && $(this).val() > fieldEnd.val())
					fieldEnd.datepicker("setDate", $(this).val());
			});

			//  @link	http://trentrichardson.com/examples/timepicker/
			$("#input_timeStart").timepicker({});
			$("#input_timeEnd").timepicker({});
			$("#input_type").trigger("change");
/*			console.log(missionDay);
			if( typeof missionDay !== "undefined" ){

				$("body.action-add #input_day").datepicker("setDate",missionDay);
				$("body.action-add #input_dayStart").datepicker("setDate",missionDay);
				$("body.action-add #input_dayEnd").datepicker("setDate",missionDay);
			}
*/		}
		
	},
	changeView: function(type){
		document.location.href = "./work/mission?view="+parseInt(type);
	},
	showDayTable: function(day, permanent){
		WorkMissions.currentDay = day;
		if(permanent)
			$.ajax({url: "./work/mission/ajaxSelectDay/"+day});
		$("div.table-day").hide().filter("#table-"+day).show();
//		$("#day-controls button").removeClass("active").eq(day).addClass("active");
		$("#day-controls li.active").removeClass("active");
		$("#day-controls li").eq(day).addClass("active");
	}	
}

function showOptionals(elem){
	var form = $(elem.form);
	var name = $(elem).attr("name");
	var type = $(elem).attr("type");
//	console.log("name: "+name);
//	console.log("type: "+type);
	var value = name+"-"+$(elem).val();
	if(type == "checkbox"){
		value = name+"-"+$(elem).prop("checked");
	}
	var toHide = form.find(".optional."+name).not("."+value);
	var toShow = form.find(".optional."+value);
	if(!$(elem).data("status")){
		toHide.hide();
		toShow.show();
		$(elem).data("status",1)
		return;
	}
	switch($(elem).data('animation')){
		case 'fade':
			toHide.fadeOut();
			toShow.fadeIn();
			break;
		case 'slide':
			toHide.slideUp($(elem).data('speed-hide'));
			toShow.slideDown($(elem).data('speed-show'));
			break;
		default:
			toHide.hide();
			toShow.show();
	}
}
