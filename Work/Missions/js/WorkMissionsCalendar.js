var WorkMissionsCalendar = {
	year: null,
	month: null,
	monthCurrent: 0,
	monthShow: 0,
	pathIcons: "http://img.int1a.net/famfamfam/silk/",
	userId: 0,

	checkForUpdate: function(){
		$.ajax({
			url: "./work/mission/checkForUpdate/"+WorkMissionsCalendar.userId,
			dataType: "json",
			success: function(json){
				if(!json)
					return;
				$.ajax({
					url: "work/mission/ajaxRenderIndex",
					dataType: "json",
					success: function(json){
						WorkMissionsList.renderDayListDayControls(json);
					}
				});
			}
		});
	},

	init: function(){
		$("#work-mission-view-type-0").removeAttr("disabled");
		$("#work-mission-view-type-0").click(function(){WorkMissions.changeView(0);});
//		setInterval(WorkMissionsCalendar.checkForUpdate, 10000);
	},

	initContextMenu: function(){
		cmContextMenu.init("#mission-calendar tbody ul li");
		cmContextMenu.containment = "#mission-calendar";
		cmContextMenu.assignRenderer("#mission-calendar tbody tr td", function(menu, elem){
			menu.addItem("<h4><big>"+elem.data("day")+"."+elem.data("month")+"."+elem.data("year")+"</big></h4>");
			var url = "./work/mission/add/?type=0&dayStart="+elem.data("date")+"&dayEnd="+elem.data("date");
			menu.addLinkItem(url, "neue Aufgabe", WorkMissionsCalendar.pathIcons+"script_add.png");
			var url = "./work/mission/add/?type=1&dayStart="+elem.data("date")+"&dayEnd="+elem.data("date");
			menu.addLinkItem(url, "neuer Termin", WorkMissionsCalendar.pathIcons+"date_add.png");
		});
		cmContextMenu.onShow = function(contextMenu){contextMenu.find("#context-date input").datepicker();};
		cmContextMenu.onChange = function(){$("#mission-calendar").css({opacity: 0.5});};
		cmContextMenu.assignRenderer("#mission-calendar tbody ul li", function(menu, elem){
			var missionId = elem.data("id");
			if(elem.data("title"))
				menu.addItem("<h4>"+elem.data("title")+"</h4>");
			if(elem.data("project"))
				menu.addItem("<small>Projekt: </small>"+elem.data("project"));
			if(elem.data("date")){
				var div = $("<div></div>").attr("id", "context-date");
				var value = elem.data("date").replace(/ /,"");
				var input = $("<input/>").attr({id: "input_date", value: elem.data("date"), class: "small", readonly: "readonly"});
				input.on("change", function(event){
					cmContextMenu.hide(event, true);
					document.location.href = "./work/mission/changeDay/"+missionId+"?date="+$(this).val();
				});
				var label = $("<small>").append("Datum: ");/*.append(elem.data("date"));*/
				menu.addItem(div.append(label).append(input));
			}
			if(elem.data("time"))
				menu.addItem("<small>Zeit: </small>"+elem.data("time"));
			if(elem.data("priority"))
				menu.addItem("<small>Priorität: </small>"+menu.labels.priorities[elem.data("priority")]);
			if(elem.data("status"))
				menu.addItem("<small>Status: </small>"+menu.labels.states[elem.data("status")]);

			menu.addItem();

			var actions = [{
					url: "./work/mission/changeDay/"+missionId+"?date=-1",
//					icon: WorkMissionsCalendar.pathIcons+"arrow_left.png",
					icon: "arrow-left",
					label: '1 Tag eher',
//					size: 'small',
				},{
					url: "./work/mission/changeDay/"+missionId+"?date=%2B1",
//					icon: WorkMissionsCalendar.pathIcons+"arrow_right.png",
					icon: "arrow-right",
					label: '1 Tag später',
//					size: 'small',
				},{
					url: "./work/mission/view/"+missionId,
//					icon: WorkMissionsCalendar.pathIcons+"eye.png",
					icon: "eye-open",
					label: 'anzeigen',
//					size: 'small',
				},{
					url: "./work/mission/edit/"+missionId,
//					icon: WorkMissionsCalendar.pathIcons+"pencil.png",
					icon: "pencil",
					label: 'bearbeiten',
//					size: 'small',
				}
			];

			var button;
			var btnGroupMove = $("<div></div>").addClass("btn-group");
			for(var i=0; i<actions.length; i++){
				icon = $("<i/>").attr("class", "icon-"+actions[i].icon);
				button = $("<a></a>").addClass("btn").html(icon);
				if(typeof actions[i].size !== "undefined" && actions[i].size.length)
					button.addClass("btn-"+actions[i].size);
				button.attr("title", actions[i].label);
				button.attr("href", actions[i].url);
				btnGroupMove.append(button);
			}
			menu.addItem(btnGroupMove);
//			for(var i=0; i<actions.length; i++)
//				menu.addLinkItem(actions[i].url, actions[i].label, actions[i].icon);
		});
	},

	setMonth: function(change){
		if(change === 0){
			this.year	= new Date().getFullYear();
			this.month	= new Date().getMonth() + 1;
		}
		else{
			this.month += change;
			if(this.month > 12){
				this.year++;
				this.month = 1;
			}
			if(this.month < 1){
				this.year--;
				this.month = 12;
			}
		}
		$.ajax({																//  store action using AJAX
			url: "./work/mission/setFilter/month/"+this.year+"-"+this.month,						//  URL to set changed filter
			dataType: "json",
			success: function(json){											//  on response
				WorkMissionsList.renderDayListDayControls(json);				//  render day lists and controls
			}
		});
	}
}

