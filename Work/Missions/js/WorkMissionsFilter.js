var WorkMissionsFilter = {
	baseUrl: "./work/mission/",
	form: null,
	__init: function(mode){
		$("#filter_query").bind("keydown", function(event){
			if(event.keyCode == 13)
				$("#button_filter_search").trigger("click");
		})
		$("#button_filter_search").bind("click", function(){
			var uri = "setFilter/query/"+encodeURI($("#filter_query").val());
			document.location.href = WorkMissionsFilter.baseUrl + uri;
		})
		$("#button_filter_search_reset").bind("click", function(){
			if($("#filter_query").val().length)
				document.location.href = WorkMissionsFilter.baseUrl+"setFilter/query/";
		})
		$("#button_filter_reset").bind("click", function(){
			document.location.href = WorkMissionsFilter.baseUrl+"filter/?reset";
		});
		this.initFilter("projects", "filter-work-missions-projects-list", "modal-work-mission-filter-projects-trigger");
		this.initFilter("states", "states");
		this.initFilter("priorities", "priorities");
		this.initFilter("types", "types");
		this.initFilter("workers", "filter-work-missions-workers-list", "modal-work-mission-filter-workers-trigger");
		this.initFilterOptionIconsHover();
		this.updateFilterReset(true);
	},
	/**
	 *	@deprecated	not used anymore
	 *	@todo		remove
	 */
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
		if(!WorkMissionsFilter.form.size())
			return false;
		WorkMissionsFilter.form.find("#filter_query").val("");
		WorkMissionsFilter.form.submit();
		return true;
	},

	initFilter: function(filterName, listId, buttonId){
		var container	= $("#"+listId);
		var checkboxes	= container.find("input[type=checkbox]");
		var button		= container.find("button.dropdown-toggle");
		if(buttonId)
			button		= $("#"+buttonId);
		container.find("ul").bind("click", function(event){							//  bind click event on dropdown list
			event.stopPropagation();												//  to stop propagation to avoid close event of bootstrap
		});
		container.find(".trigger-select-this").bind("click", function(event){						//  bind click event on ...
			WorkMissionsList.blendOut(250);
			var id = $(this).data("id");
			checkboxes.each(function(nr){
				$(this).prop("checked", $(this).val() == id ? "checked" : null);
			});
			WorkMissionsFilter.updateButtonClass(button, checkboxes);
			$.ajax({																//  store action using AJAX
				url: "./work/mission/setFilter/"+filterName+"/"+id+"/1/1",			//  URL to reset all any only set this value
				dataType: "json",
				success: function(json){											//  on response
					WorkMissionsList.renderDayListDayControls(json);				//  render day lists and controls
				},
				error: function(a,b){
					console.log(b);
				}
			});
			return false;
		});
		container.find(".trigger-select-all").bind("click", function(event){						//  bind click event on ...
			WorkMissionsList.blendOut(250);
			checkboxes.each(function(nr){
				$(this).prop("checked", "checked");
			});
			WorkMissionsFilter.updateButtonClass(button, checkboxes);
			$.ajax({																//  store action using AJAX
				url: "./work/mission/setFilter/"+filterName,						//  URL to reset changed filter to force all
				dataType: "json",
				success: function(json){											//  on response
					WorkMissionsList.renderDayListDayControls(json);				//  render day lists and controls
				},
				error: function(a,b){
					console.log(b);
				}
			});
			return false;
		});
		checkboxes.bind("change.clicked", function(event){							//  bind change event on every checkbox
			WorkMissionsList.blendOut(250);
			WorkMissionsFilter.updateButtonClass(button, checkboxes);
			//  store changed filter
			var value = event.target.checked ? 1 : 0;								//  get check status as integer
			var id = event.target.value;											//  get ID of filter to set
			$.ajax({																//  store action using AJAX
				url: "./work/mission/setFilter/"+filterName+"/"+id+"/"+value,		//  URL to set changed filter
				dataType: "json",
				success: function(json){											//  on response
					WorkMissionsList.renderDayListDayControls(json);				//  render day lists and controls
				},
				error: function(a,b){
					console.log(b);
				}
			});
		});
	},

	/**
	 *	Enabled icons within filter options to turn white on mouse hover.
	 */
	initFilterOptionIconsHover: function(){
		$("#work-mission-buttons ul.dropdown-menu li label>i").parent().hover(		//  get filter items with icon
			function(){																//  on mouse enter
				if($(this).children("i").hasClass("icon-white"))					//  if icon was white before
					$(this).children("i").data("wasWhite", true);					//  note that
				else																//  and if not
					$(this).children("i").addClass("icon-white")					//  paint it white
			}, function(){															//  on mouse leave
				if(!$(this).children("i").data("wasWhite"))							//  if icon was not white before
					$(this).children("i").removeClass("icon-white");				//  remove white paint
			}
		);
	},

	updateButtonClass: function(button, checkboxes){
		//  count checked and unchecked checkboxes
		var i, value;
		var countChecked = 0;
		var countUnchecked = 0;
		for(i=0; i<checkboxes.size(); i++){											//  iterate checkboxes
			value = checkboxes.eq(i).prop("checked") ? 1 : 0;						//  get check state
			countChecked += value;													//  count if checked
			countUnchecked -= value - 1;											//  count if unchecked
		}

		//  check all checkboxes if none is checked anymore, since the backend will automatically enable all, too
		if(!countChecked){															//  no checkbox is checked
			checkboxes.prop("checked", "checked");									//  check all checkboxes
			countUnchecked = 0;														//  reset number of unchecked checkboxes
			countChecked = checkboxes.size();										//  reset number if checked checkboxes
		}

		//  mark filter button if filters have changed
		if(countUnchecked)															//  atleast one checkbox is unchecked
			button.addClass("btn-info")												//  mark filter as changed
		else																		//  no checkbox is unchecked
			button.removeClass("btn-info");											//  mark filter as unchanged
		WorkMissionsFilter.updateFilterReset(true);
	},

	updateFilterReset: function(colored){
		var btn = $("#work-mission-buttons #button_filter_reset");					//  get reset button
		btn.prop("disabled", "disabled");											//  disable it by default
		if(colored)																	//  button uses colors
			btn.removeClass('btn-inverse').children("i").removeClass("icon-white");	//  remove color and icon paint
		if($("#work-mission-buttons #toolbar-filters .btn-info").size()){		//  at least one filter is set
			btn.prop("disabled", null);												//  enabled button
			if(colored)																//  button uses colors
				btn.addClass('btn-inverse').children("i").addClass("icon-white");	//  set button color and icon paint
		}
	}
};
