var FormMissionFilter = {
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
	clearQuery: function(){
		if(!FormMissionFilter.form.size())
			return false;
		FormMissionFilter.form.find("#filter_query").val("");
		FormMissionFilter.form.submit();
		return true;
	}
};




function showOptionals(elem){
	var form = $(elem.form);
	var name = $(elem).attr("name");
	var type = $(elem).attr("type");
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


$(document).ready(function(){
	var site = $("body.controller-work-mission");
	if(site.size()){
		$("body.controller-work-mission #input_day").add("#input_dayStart").add("#input_dayEnd").datepicker({
			dateFormat: "yy-mm-dd",
	//		appendText: "(yyyy-mm-dd)",
	//		buttonImage: "/images/datepicker.gif",
	//		changeMonth: true,
	//		changeYear: true,
	//		gotoCurrent: true,
	//		autoSize: true,
			nextText: "nächster Monat",
			prevText: "vorheriger Monat",
			yearRange: "c:c+2",
			monthNames: monthNames
		});
		//  @link	http://trentrichardson.com/examples/timepicker/
		$("body.controller-work-mission #input_timeStart").timepicker({});
		$("body.controller-work-mission #input_timeEnd").timepicker({});
		$("body.controller-work-mission #input_type").trigger("change");
	}
});
