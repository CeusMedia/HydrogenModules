<?php

$optType	= $words['types'];
$optType	= UI_HTML_Elements::Options( $optType, $bill->type );

$optStatus	= $words['states'];
$optStatus	= UI_HTML_Elements::Options( $optStatus, $bill->status );

$date		= strtotime( substr( $bill->date, 0, 4 ).'-'.substr( $bill->date, 4, 2).'-'.substr( $bill->date, 6, 2 ) );


$w			= (object) $words['edit'];

return '
<h3>'.$w->heading.'</h3>
<form action="./work/bill/edit/'.$bill->billId.'" method="post">
	<div class="row-fluid">
		<div class="span6">
			<label for="input_title">'.$w->labelTitle.'</label>
			<input type="text" name="title" id="input_title" class="span12" value="'.htmlentities( $bill->title ).'"/>
		</div>
		<div class="span3">
			<label for="input_price">'.$w->labelPrice.'</label>
			<input type="text" name="price" id="input_price" class="span12" value="'.$bill->price.'"/>
		</div>
		<div class="span3">
			<label for="input_date">'.$w->labelDate.'</label>
			<input type="text" name="date" id="input_date" class="span12" value="'.date( "Y-m-d", $date ).'"/>
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
';
