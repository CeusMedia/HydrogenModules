var WorkMissions = {
	init: function(){
		var site = $("body.controller-work-mission");
		if(!site.size())
			return;

		if(site.hasClass('action-index')){
			WorkMissions.showDayTable(typeof missionShowDay != "undefined" ? missionShowDay : 0);
			$("#input-import").bind("click",function(){
				$("#input-serial").trigger("click")
			});
			$("#input-serial").bind("change",function(){
				var value = $("#input-serial").val().replace(/\\/g,"/");
				$("#input-import").val(value.split(/\//).pop());
				var text = "Alle bisherigen Aufgaben und Termine werden gelöscht. Wirklich importieren?";
				if(confirm(text))
					$("#input-import").get(0).form.submit();
				else
					$("#input-import").val("");
			});
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
			$("#input_dayStart").bind("change",function(){
				var dStart = $(this).val();
				var dEnd = $("#input_dayEnd").val();
				if(dEnd && dStart > dEnd)
					$("#input_dayEnd").datepicker("setDate", dStart);
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
	showDayTable: function(day,permanent){
		missionDay = day;
		if(permanent)
			$.ajax({url: "./work/mission/ajaxSelectDay/"+day});
		$("div.table-day").hide().filter("#table-"+day).show();
		$("#day-controls button").removeClass("active").eq(day).addClass("active");
	}	
}

var WorkMissionsCalendar = {
	year: null,
	month: null,
	monthCurrent: 0,
	monthShow: 0,
	init: function(){
		$("#work-mission-view-type-0").removeAttr("disabled");
		$("#work-mission-view-type-0").click(function(){WorkMissions.changeView(0);});
	},
	setMonth: function(change){
		url	= "./work/mission/calendar";
		if(change)
			url	+= "/"+this.year+"/"+(this.month + change);
		else{
			var date = new Date();
			url += "/"+date.getFullYear()+"/"+(date.getMonth() + 1);
		}
		document.location.href = url;
	}
}

var WorkMissionsList = {
	sortBy: 'priority',
	sortDir: 'ASC',
	init: function(){
		$("#work-mission-view-type-1").removeAttr("disabled");
		$("#work-mission-view-type-1").click(function(){WorkMissions.changeView(1);});
		WorkMissionsList.makeTableSortable($("#layout-content table"),{
			url: "./work/mission/filter/",
			order: WorkMissionsList.sortBy,
			direction: WorkMissionsList.sortDir
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
	}
}


var WorkMissionFilter = {
	form: null,
	__init: function(){
		this.form = $("#form_mission_filter");
		if(!this.form.size())
			return false;
		this.form.find(".optional-trigger").trigger("change");
		this.form.find("#filter_query").bind("change",function(){
			this.form.submit();
		});
		if(this.form.find("#filter_query").val().length)
			this.form.find("#reset-button-container").show();
		this.form.find("#reset-button-trigger").bind("click",this.clearQuery);
		return true;
	},
	changeView: function(elem){								//  @todo kriss: fix this hack!
		var val = parseInt($(elem).val());
		var url = "./work/mission/filter?status&";
		if(val)
			url += "states[]=4";
		else
			url += "states[]=0&states[]=1&states[]=2&states[]=3";
		document.location.href = url ;
	},
	clearQuery: function(){
		if(!WorkMissionFilter.form.size())
			return false;
		WorkMissionFilter.form.find("#filter_query").val("");
		WorkMissionFilter.form.submit();
		return true;
	}
};

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
