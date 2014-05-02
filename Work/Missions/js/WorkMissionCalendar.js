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

