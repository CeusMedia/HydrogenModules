<?php
use CeusMedia\Common\UI\HTML\Indicator;

/** @var array<string,array<string,string>> $words */

$w				= (object) $words['index'];
$wc				= (object) $words['criteria'];

$url			= './manage/customer/rating/add/'.$customer->customerId;
$iconAdd		= '<i class="icon-plus icon-white"></i>';
$buttonAdd		= '<button type="button" class="btn btn-small btn-primary" onclick="document.location.href=\''.$url.'\';">'.$iconAdd.' '.$w->buttonAdd.'</button>';

$panelInfo		= '
<div class="muted"><em><small>'.$w->noEntries.'</small></em></div><br/>
'.$buttonAdd.'
';
$panelRatings	= '';

if( $customer->ratings ){
	$indicator1	= new Indicator();
	$indicator2	= new Indicator( ['invertColor' => TRUE] );

	$averages	= [0, 0, 0, 0, 0, 0, 0];

	$rows = [];
	foreach( $customer->ratings as $rating ){
		$averages[0]	+= $rating->affability;
		$averages[1]	+= $rating->guidability;
		$averages[2]	+= $rating->growthRate;
		$averages[3]	+= $rating->profitability;
		$averages[4]	+= $rating->paymentMoral;
		$averages[5]	+= $rating->adherence;
		$averages[6]	+= $rating->uptightness;

		$rows[]	= '<tr>
		<td>'.date( 'Y-m-d', $rating->timestamp ).'</td>
		<td>'.number_format( $rating->index, 1, ',', '.' ).'</td>
		<td>'.( $rating->index ? $indicator1->build( abs( $rating->index ) - 0.5, 4.5 ) : '' ).'</td>
		<td data-value="'.$rating->affability.'"></td>
		<td data-value="'.$rating->guidability.'"></td>
		<td data-value="'.$rating->growthRate.'"></td>
		<td data-value="'.$rating->profitability.'"></td>
		<td data-value="'.$rating->paymentMoral.'"></td>
		<td data-value="'.$rating->adherence.'"></td>
		<td data-value="'.$rating->uptightness.'" data-inverse="yes"></td>
	</tr>';
	}
	for( $i=0; $i<7; $i++ )
		$averages[$i]	= round( $averages[$i] / count( $customer->ratings ), 1 );

	$tendency	= '-';
	if( $customer->tendency > 0 )
		$tendency	= '<span class="tendency-up">&plus;'.round( $customer->tendency * 50, 0 ).'%</span>';
	else if( $customer->tendency < 0 )
		$tendency	= '<span class="tendency-down">&minus;'.round( abs( $customer->tendency * 50 ), 0 ).'%</span>';

	$panelInfo	= '
<h4>'.$w->headingSummary.'</h4>
<div class="row-fluid">
	<div class="span3">
		<dl class="dl-horizontal ratings">
			<dt>'.$w->labelLastIndex.'</dt>
			<dd class="rate" data-value="'.( $customer->lastRate ).'">'.number_format( $customer->lastRate, 1, ',', '.' ).'</dd>
			<dt>'.$w->labelAverageIndex.'</dt>
			<dd class="rate" data-value="'.$customer->index.'">'.number_format( $customer->index, 1, ',', '.' ).'</dd>
			<dt>'.$w->labelVariance.'</dt>
			<dd>'.number_format( $customer->variance, 1, ',', '.' ).'</dd>
			<dt>'.$w->labelTendency.'</dt>
			<dd>'.$tendency.'</dd>
		</dl>
	</div>
	<div class="span3">
		<dl class="dl-horizontal ratings">
			<dt>'.$wc->affability.'</dt>
			<dd class="rate" data-value="'.$averages[0].'">'.number_format( $averages[0], 1, ',', '.' ).'</dd>
			<dt>'.$wc->guidability.'</dt>
			<dd class="rate" data-value="'.$averages[1].'">'.number_format( $averages[1], 1, ',', '.' ).'</dd>
			<dt>'.$wc->growthRate.'</dt>
			<dd class="rate" data-value="'.$averages[2].'">'.number_format( $averages[2], 1, ',', '.' ).'</dd>
			<dt>'.$wc->profitability.'</dt>
			<dd class="rate" data-value="'.$averages[3].'">'.number_format( $averages[3], 1, ',', '.' ).'</dd>
			<dt>'.$wc->paymentMoral.'</dt>
			<dd class="rate" data-value="'.$averages[4].'">'.number_format( $averages[4], 1, ',', '.' ).'</dd>
			<dt>'.$wc->adherence.'</dt>
			<dd class="rate" data-value="'.$averages[5].'">'.number_format( $averages[5], 1, ',', '.' ).'</dd>
			<dt>'.$wc->uptightness.'</dt>
			<dd class="rate" data-value="'.$averages[6].'" data-inverse="1">'.number_format( $averages[6], 1, ',', '.' ).'</dd>
		</dl>
	</div>
</div>
<br/>';

	$panelRatings	= '
<div id="panel-customer-ratings">
	<h4>'.$w->headingList.'</h4>
	<table class="table ratings table-striped table-condensed">
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
			<th>'.$w->headDate.'</th>
			<th>&empty;</th>
			<th>'.$w->headIndex.'</th>
			<th><small class="-muted">'.$wc->affability.'</small></th>
			<th><small class="-muted">'.$wc->guidability.'</small></th>
			<th><small class="-muted">'.$wc->growthRate.'</small></th>
			<th><small class="-muted">'.$wc->profitability.'</small></th>
			<th><small class="-muted">'.$wc->paymentMoral.'</small></th>
			<th><small class="-muted">'.$wc->adherence.'</small></th>
			<th><small class="-muted">'.$wc->uptightness.'</small></th>
		</tr>
		</thead>
		<tbody>
			'.join( $rows ).'
		</tbody>
	</table>
</div>
'.$buttonAdd.'
<br/>';
}

$tabs		= View_Manage_Customer::renderTabs( $env, $customerId, 'rating/'.$customerId );

return '
<h3><span class="muted">'.$w->headingPrefix.'</span> '.$customer->title.'</h3>
'.$tabs.'
'.$panelInfo.'
'.$panelRatings.'
<script>
$(document).ready(function(){
	$("table td, dd").each(function(){
		var color;
		var cell = $(this);
		if(cell.data("value") > 0){
			color = ManageCustomerRating.calculateColor((cell.data("value") - 1) / 4, 63);
			if(cell.data("inverse"))
				color = ManageCustomerRating.calculateColor(Math.abs(5 - cell.data("value")) / 4, 63);
			cell.css("background-color", color);
			cell.addClass("colored");
		}
	});
});
</script>
<style>
table.ratings {
	width: 100%;
	min-width: 800px;
	max-width: 1250px;
	box-shadow: 0px 0px 0px 1px gray;
	}

table.ratings th {
	background-color: #EEE;
	border-bottom: 1px solid #BBB;
	}
table.ratings td.colored {
	text-align: center;
	color: white;
	text-shadow: 1px 1px 2px black;
	border-color: #FFF;
	}
dl.ratings dt {
	padding: 0.2em 0em;
	}
dl.ratings dd {
	text-align: center;
	font-size: 1.1em;
	padding: 0.2em 1em;
	border-radius: 1em;
	margin-bottom: 1px;
	width: 30px;
	}
dl.ratings dd.rate {
	}
span.tendency-up {
	color: green;
	font-weight: bold;
	}
span.tendency-up:after {
	}
span.tendency-down {
	color: red;
	}
span.tendency-down:after {
	}
</style>
';
?>