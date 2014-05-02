var WorkMissionsFilter = {
	baseUrl: "./work/mission/",
	form: null,
	__init: function(){
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
		if(!WorkMissionsFilter.form.size())
			return false;
		WorkMissionsFilter.form.find("#filter_query").val("");
		WorkMissionsFilter.form.submit();
		return true;
	}
};
