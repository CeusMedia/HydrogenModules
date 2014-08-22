<?php

$optType		= /*array( '' => '- alle -' ) + */$words['types'];
$optType		= UI_HTML_Elements::Options( $optType, $env->getSession()->get( 'filter_work_bill_type' ) );

$optStatus		= /*array( '' => '- alle -' ) + */$words['states'];
$optStatus		= UI_HTML_Elements::Options( $optStatus, $env->getSession()->get( 'filter_work_bill_status' ) );

$optOrder		= array( 'date' => 'Fälligkeit', 'status' => 'Status', 'type' => 'Typ' );
$optOrder		= UI_HTML_Elements::Options( $optOrder, $filters->get( 'order' ) );

$optDirection	= array( 'ASC' => 'aufsteigend', 'DESC' => 'absteigend' );
$optDirection	= UI_HTML_Elements::Options( $optDirection, $filters->get( 'direction' ) );

$filterStart	= $filters->get( 'start' );
$filterEnd		= $filters->get( 'end' );

$w	= (object) $words['index-filter'];

return '
<div class="content-panel">
	<div class="content-panel-inner">
		<h4>'.$w->heading.'</h4>
		<form name="work_bills_filter" action="./work/bill/filter" method="post">
			<div class="row-fluid">
				<div class="span12">
					<label for="input_type">'.$w->labelType.'</label>
					<select multiple="multiple" rows="2" name="type[]" id="input_type" class="span12" style="height: 3em">'.$optType.'</select>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_status">'.$w->labelStatus.'</label>
					<select multiple="multiple" rows="2"  name="status[]" id="input_status" class="span12" style="height: 3em">'.$optStatus.'</select>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span6">
					<label for="input_start">'.$w->labelStart.'</label>
					<input type="text" name="start" id="input_start" class="span12 datepicker" value="'.$filterStart.'"/>
				</div>
				<div class="span6">
					<label for="input_end">'.$w->labelEnd.'</label>
					<input type="text" name="end" id="input_end" class="span12 datepicker" value="'.$filterEnd.'"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_order">'.$w->labelOrder.'</label>
					<select name="order" id="input_order" class="span12">'.$optOrder.'</select>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_direction">'.$w->labelDirection.'</label>
					<select name="order" id="input_order" class="span12">'.$optDirection.'</select>
				</div>
			</div>
			<div class="buttonbar">
				<button type="submit" name="filter" class="btn btn-small btn-info"><i class="icon-filter icon-white"></i>&nbsp;'.$w->buttonFilter.'</button>
				<a href="./work/bill/filter/reset" class="btn btn-small btn-inverse"><i class="icon-remove-circle icon-white"></i>&nbsp;'.$w->buttonReset.'</a>
			</div>
		</form>
	</div>
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
		yearRange: "c-10:c+10",
		monthNames: monthNames
	});
});
</script>
';
