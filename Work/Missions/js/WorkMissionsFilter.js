var WorkMissionsFilter = {
	baseUrl: "./work/mission/",
	form: null,
	__init: function(mode){
/*		this.form = $("#form_mission_filter");
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
*/

		/*  --  MODES  --  */
		var i, button;
		var mode = mode;
		$(['archive','now','future']).each(function(nr, entry){
			button = $("#work-mission-view-mode-"+entry);
			button.removeAttr("disabled").removeClass("disabled");
			if(entry === mode)
				button.addClass("active").css("cursor", "default");
			else
				button.bind("click", {mode: entry}, function(event){
					document.location.href = "./work/mission/"+event.data.mode;
			});
		});
/*		for(i=0; i<3; i++){
			button = $("#work-mission-view-tense-"+i);
			button.removeAttr("disabled").removeClass("disabled");
			if(i === tense)
				button.addClass("active").css("cursor", "default");
			else
				button.bind("click", {tense: i}, function(event){
					document.location.href = "./work/mission/switchTense/"+event.data.tense;
			});
		}
*/
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
		this.initFilter("projects");
		this.initFilter("states");
		this.initFilter("priorities");
		this.initFilter("types");
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

	initFilter: function(filterName){
		var container	= $("#"+filterName);
		var inputs		= container.find("ul li input");
		var button		= container.find("button");
		container.find("ul").bind("click", function(event){							//  bind click event on dropdown list
			event.stopPropagation();												//  to stop propagation to avoid close event of bootstrap
		});
		inputs.bind("change", function(event){										//  bind change event on every checkbox
			//  store changed filter
			var value = event.target.checked ? 1 : 0;								//  get check status as integer
			var id = event.target.value;											//  get ID of filter to set
			$.ajax({																//  store action using AJAX
				url: "./work/mission/setFilter/"+filterName+"/"+id+"/"+value,		//  URL to set changed filter
					success: function(){											//  on response
					WorkMissionsList.loadCurrentListAndDayControls();				//  reload day lists and controls
				}
			});

			//  count checked and unchecked checkboxes
			var i, value;
			var countChecked = 0;
			var countUnchecked = 0;
			for(i=0; i<inputs.size(); i++){											//  iterate checkboxes
				value = inputs.eq(i).prop("checked") ? 1 : 0;						//  get check state
				countChecked += value;												//  count if checked
				countUnchecked -= value - 1;										//  count if unchecked
			}

			//  check all checkboxes if none is checked anymore, since the backend will automatically enable all, too
			if(!countChecked){														//  no checkbox is checked
				inputs.prop("checked", "checked");									//  check all checkboxes
				countUnchecked = 0;													//  reset number of unchecked checkboxes
				countChecked = inputs.size();										//  reset number if checked checkboxes
			}

			//  mark filter button if filters have changed
			if(countUnchecked)														//  atleast one checkbox is unchecked
				button.addClass("btn-info")											//  mark filter as changed
			else																	//  no checkbox is unchecked
				button.removeClass("btn-info");										//  mark filter as unchanged
		});
	}
};
