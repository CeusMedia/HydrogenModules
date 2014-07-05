<?php

$optType	= $words['types'];
$optType	= UI_HTML_Elements::Options( $optType );

$optStatus	= $words['states'];
$optStatus	= UI_HTML_Elements::Options( $optStatus, "0" );

$w			= (object) $words['add'];

return '
<h3>'.$w->heading.'</h3>
<form action="./work/bill/add" method="post">
	<div class="row-fluid">
		<div class="span6">
			<label for="input_title">'.$w->labelTitle.'</label>
			<input type="text" name="title" id="input_title" class="span12" value=""/>
		</div>
		<div class="span3">
			<label for="input_price">'.$w->labelPrice.'</label>
			<input type="text" name="price" id="input_price" class="span12" value=""/>
		</div>
		<div class="span3">
			<label for="input_date">'.$w->labelDate.'</label>
			<input type="text" name="date" id="input_date" class="span12" value="" autocomplete="off"/>
		</div>
	</div>
	<div class="row-fluid">
		<div class="span6">
			<label for="input_type">'.$w->labelType.'</label>
			<select name="type" id="input_type" class="span12">'.$optType.'</select>
		</div>
		<div class="span6">
			<label for="input_status">'.$w->labelStatus.'</label>
			<select name="status" id="input_status" class="span12">'.$optStatus.'</select>
		</div>
	</div>
	<div class="buttonbar">
		<a href="./work/bill" class="btn"><i class="icon-arrow-left"></i>&nbsp;zurück</a>
		<button type="submit" class="btn btn-success" name="save"><i class="icon-ok icon-white"></i>&nbsp;speichern</button>
	</div>
</form>
<script>
$(document).ready(function(){
	$("#input_date").datepicker({
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
});
</script>
';
