$(document).ready(function(){
	$("#input_day").datepicker({
		dateFormat: "yy-mm-dd",
//		appendText: "(yyyy-mm-dd)",
//		buttonImage: "/images/datepicker.gif",
//		changeMonth: true,
//		changeYear: true,
		gotoCurrent: true,
		nextText: "nächster Monat",
		prevText: "vorheriger Monat",
		yearRange: "c:c+2",
		monthNames: monthNames,
		autoSize: true
	});
});
