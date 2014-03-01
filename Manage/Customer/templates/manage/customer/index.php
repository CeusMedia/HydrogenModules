<?php

$w			= (object) $words['index'];

$table		= '<div class="muted"><em><small>'.$w->empty.'</small></em></div><br/>';

$indicator	= new UI_HTML_Indicator();

if( $customers ){
	$list	= array();
	foreach( $customers as $customer ){
		$index	= '-';
		$graph	= '';
		if( isset( $customer->rating ) && $customer->rating ){
			$graph	= $indicator->build( abs( 5 - $customer->rating->index ) + 0.5, 4.5 );
			$index	= number_format( $customer->rating->index, 1 );
		}
		$url	= './manage/customer/edit/'.$customer->customerId;
		$link	= UI_HTML_Tag::create( 'a', $customer->title, array( 'href' => $url ) );
		$list[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', $link ),
			UI_HTML_Tag::create( 'td', $index ),
			UI_HTML_Tag::create( 'td', $graph ),
		) );
	}
	$colgroup	= UI_HTML_Elements::ColumnGroup( '60%', '10%', '30%' );
	$heads		= UI_HTML_Elements::TableHeads( array(
		'Kunde',
		'Index',
		'Graph',
	) );
	$thead		= UI_HTML_Tag::create( 'thead', $heads );
	$tbody		= UI_HTML_Tag::create( 'tbody', $list );
	$table		= UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody, array( 'class' => 'table table-striped' ) );
}
$iconAdd	= '<i class="icon-plus icon-white"></i>';
$buttonAdd	= UI_HTML_Tag::create( 'a', $iconAdd.' neuer Kunde', array( 'href' => './manage/customer/add', 'class' => 'btn not-btn-small btn-primary' ) );

return '
<h3>'.$w->heading.'</h3>
'.$table.'
<br/>
'.$buttonAdd;
?>