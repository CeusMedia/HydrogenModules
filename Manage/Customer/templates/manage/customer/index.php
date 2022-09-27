<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Indicator;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$w			= (object) $words['index'];

$table		= '<div class="muted"><em><small>'.$w->empty.'</small></em></div><br/>';

$indicator	= new Indicator();

if( $customers ){
	$list	= [];
	foreach( $customers as $customer ){
		$index	= '-';
		$graph	= '';
		if( isset( $customer->rating ) && $customer->rating ){
			$graph	= $indicator->build( abs( 5 - $customer->rating->index ) + 0.5, 4.5 );
			$index	= number_format( $customer->rating->index, 1 );
		}
		$url	= './manage/customer/edit/'.$customer->customerId;
		$link	= HtmlTag::create( 'a', $customer->title, array( 'href' => $url ) );
		$list[]	= HtmlTag::create( 'tr', array(
			HtmlTag::create( 'td', $link ),
			HtmlTag::create( 'td', $index ),
			HtmlTag::create( 'td', $graph ),
		) );
	}
	$colgroup	= HtmlElements::ColumnGroup( '60%', '10%', '30%' );
	$heads		= HtmlElements::TableHeads( array(
		'Kunde',
		'Index',
		'Graph',
	) );
	$thead		= HtmlTag::create( 'thead', $heads );
	$tbody		= HtmlTag::create( 'tbody', $list );
	$table		= HtmlTag::create( 'table', $colgroup.$thead.$tbody, array( 'class' => 'table table-striped' ) );
}
$iconAdd	= '<i class="icon-plus icon-white"></i>';
$buttonAdd	= HtmlTag::create( 'a', $iconAdd.' neuer Kunde', array( 'href' => './manage/customer/add', 'class' => 'btn not-btn-small btn-primary' ) );

return '
<h3>'.$w->heading.'</h3>
'.$table.'
<br/>
'.$buttonAdd;
?>