<?php


$indicator	= new UI_HTML_Indicator();

$list	= array();
foreach( $customers as $customer ){
	$index	= '-';
	$graph	= '';
	if( $customer->rating ){
		$graph	= $indicator->build( abs( 5 - $customer->rating->index ) + 0.5, 4.5 );
		$index	= number_format( $customer->rating->index, 1 );
	}
	$list[]	= '<tr>
	<td><a href="./manage/customer/edit/'.$customer->customerId.'">'.$customer->title.'</a></td>
	<td>'.$index.'</td>
	<td>'.$graph.'</td>
</tr>';
}
$list	= '<table class="table table-striped">
	<colgroup>
		<col width="60%"/>
		<col width="10%"/>
		<col width="30%"/>
	</colgroup>
	<thead>
		<tr>
			<th>Kunde</th>
			<th>Index</th>
			<th>Graph</th>
		</tr>
	</thead>
	<tbody>
		'.join( $list ).'
	</tbody>
</table>
<br/>
<button type="button" class="btn btn-small btn-primary" onclick="document.location.href=\'./manage/customer/add\';"><i class="icon-plus icon-white"></i> neuer Kunde</button>
';
return $list;
?>