function showOptionals(elem,speed){
	var form = $(elem.form);
	var name = $(elem).attr("name");
	var type = name+"-"+$(elem).val();
	form.find(".optional."+name).not("."+type).hide();
	form.find(".optional."+type).show();
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
