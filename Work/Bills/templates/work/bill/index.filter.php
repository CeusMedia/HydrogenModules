<?php

$optType	= array( '' => '- alle -' ) + $words['types'];
$optType	= UI_HTML_Elements::Options( $optType, $env->getSession()->get( 'filter_work_bill_type' ) );

$optStatus	= array( '' => '- alle -' ) +$words['states'];
$optStatus	= UI_HTML_Elements::Options( $optStatus, $env->getSession()->get( 'filter_work_bill_status' ) );

$w	= (object) $words['index-filter'];

return '
		<h4>'.$w->heading.'</h4>
		<div class="row-fluid">
			<div class="span12">
				<label for="input_type">'.$w->labelType.'</label>
				<select name="type" id="input_type" class="span12">'.$optType.'</select>
			</div>
		</div>
		<div class="row-fluid">
			<div class="span12">
				<label for="input_status">'.$w->labelStatus.'</label>
				<select name="status" id="input_status" class="span12">'.$optStatus.'</select>
			</div>
		</div>
		<div class="row-fluid">
			<div class="span12">
				<label for="input_start">'.$w->labelStart.'</label>
				<input type="text" name="start" id="input_start" class="span12" value="'.''.'"/>
			</div>
		</div>
		<div class="row-fluid">
			<div class="span12">
				<label for="input_end">'.$w->labelEnd.'</label>
				<input type="text" name="end" id="input_end" class="span12" value="'.''.'"/>
			</div>
		</div>
		<div class="buttonbar">
			<button type="submit" name="filter" class="btn btn-small"><i class="icon-filter"></i>&nbsp;'.$w->buttonFilter.'</button>
			<a href="./work/bill/" class="btn btn-small"><i class="icon-remove-circle"></i>&nbsp;'.$w->buttonReset.'</a>
		</div>

<script>
$(document).ready(function(){
	$("#input_start, #input_end").datepicker({
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
