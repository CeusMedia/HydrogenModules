<?php
$indicator1	= new UI_HTML_Indicator();
$indicator2	= new UI_HTML_Indicator( array( 'invertColor' => TRUE ) );

foreach( $customer->ratings as $rating ){
	$rows[]	= '<tr>
		<td>'.date( 'Y-m-d', $rating->timestamp ).'</td>
		<td>'.number_format( $rating->index, 1 ).'</td>
		<td>'.( $rating->index ? $indicator1->build( abs( 5 - $rating->index ) + 0.5, 4.5 ) : '' ).'</td>
		<td data-value="'.$rating->affability.'"></td>
		<td data-value="'.$rating->guidability.'"></td>
		<td data-value="'.$rating->growthRate.'"></td>
		<td data-value="'.$rating->profitability.'"></td>
		<td data-value="'.$rating->paymentMoral.'"></td>
		<td data-value="'.$rating->adherence.'"></td>
		<td data-value="'.$rating->uptightness.'" data-inverse="yes"></td>
	</tr>';
}

$heads	= '';

return '
<script>
$(document).ready(function(){
	$("table td").each(function(){
		var color;
		var cell = $(this);
		if(cell.data("value") > 0){
			color = calculateColor((cell.data("value") - 1) / 4, 63);
			if(cell.data("inverse"))
				color = calculateColor(Math.abs(5 - cell.data("value")) / 4, 63);
			cell.css("background-color", color);
			cell.addClass("colored");
		}
	});
	
});

</script>
<style>

table {
	width: 100%;
	min-width: 800px;
	max-width: 1250px;
	box-shadow: 0px 0px 0px 1px gray;
	}

table th {
	background-color: #EEE;
	border-bottom: 1px solid #BBB;
	}
table td.colored {
	text-align: center;
	color: white;
	text-shadow: 1px 1px 2px black;
	border-color: #FFF;
	}

</style>
<h3>Kunde</h3>
<dl class="dl-horizontal">
	<dt>Name</dt>
	<dd>'.$customer->title.'</dd>
	<dt>letzter Index</dt>
	<dd><small class="muted">noch nicht implementiert</small></dd>
	<dt>Index-&empty;</dt>
	<dd>'.$customer->index.'</dd>
	<dt>Varianz</dt>
	<dd>'.number_format( $customer->variance, 1 ).'</dd>
	<dt>Tendenz</dt>
	<dd><small class="muted">noch nicht implementiert</small></dd>
</dl>
<div class="buttonbar">
	<button type="button" class="btn btn-small" onclick="document.location.href=\'./manage/customer\';"><i class="icon-arrow-left"></i> zurück</button>
	<button type="button" class="btn btn-small btn-primary" onclick="document.location.href=\'./manage/customer/rating/add/'.$customer->customerId.'\';"><i class="icon-plus icon-white"></i> bewerten</button>
	<button type="button" class="btn btn-small btn-inverse" onclick="document.location.href=\'./manage/customer/remove/'.$customer->customerId.'\';"><i class="icon-plus icon-white"></i> entfernen</button>
</div>
<br/>
<h3>Bewertungen</h3>
<table class="table">
	<colgroup>
		<col width="13%"/>
		<col width="4%"/>
		<col width="13%"/>
		<col width="10%"/>
		<col width="10%"/>
		<col width="10%"/>
		<col width="10%"/>
		<col width="10%"/>
		<col width="10%"/>
		<col width="10%"/>
	</colgroup>
	<thead>
		<tr>
		<th>Datum</th>
		<th>&empty;</th>
		<th>Index</th>
		<th><small class="-muted">Umgänglichkeit</small></th>
		<th><small class="-muted">Beratbarkeit</small></th>
		<th><small class="-muted">Wachstum</small></th>
		<th><small class="-muted">Rentabilität</small></th>
		<th><small class="-muted">Zahlungsmoral</small></th>
		<th><small class="-muted">Termintreue</small></th>
		<th><small class="-muted">Nervfaktor</small></th>
	</tr>
	</thead>
	<tbody>
		'.join( $rows ).'
	</tbody>
</table>

<br/>
';

?>
