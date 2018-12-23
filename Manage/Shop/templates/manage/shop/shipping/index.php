<?php
$iconSave	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-okay' ) );
$iconAdd	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-plus' ) );
$iconRemove	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-remove' ) );

$priceMatrix	= array();
foreach( $prices as $price ){
	if( !isset( $priceMatrix[$price->zoneId] ) )
		$priceMatrix[$price->zoneId]	= array();
	$priceMatrix[$price->zoneId][$price->gradeId]	= $price->price;
}

/*  --  PANEL: PRICES  --  */
$panelPrices	= '';
if( $zones || $grades ){
	$rows	= array();
	$thead	= array( UI_HTML_Tag::create( 'th', 'Zonen \ Gewichtsklassen' ) );
	foreach( $grades as $grade )
		$thead[]	= UI_HTML_Tag::create( 'th', $grade->title, array( 'class' => 'cell-price' ) );
	$thead	= UI_HTML_Tag::create( 'thead', UI_HTML_Tag::create( 'tr', $thead ) );
	foreach( $zones as $zone ){
		$row	= array( UI_HTML_Tag::create( 'th', $zone->title ) );
		foreach( $grades as $grade ){
			$price	= $priceMatrix[$zone->zoneId][$grade->gradeId];
			$row[]	= UI_HTML_Tag::create( 'td', number_format( $price, 2, ',', '.' ), array( 'class' => 'cell-price' ) );
		}
		$rows[]	= UI_HTML_Tag::create( 'tr', $row );
	}
	$tbody	= UI_HTML_Tag::create( 'tbody', $rows );
	$table	= UI_HTML_Tag::create( 'table', array( $thead, $tbody ), array( 'class' => 'table' ) );
	$panelPrices	= UI_HTML_Tag::create( 'div', array(
		UI_HTML_Tag::create( 'h3', 'Versandkosten' ),
		UI_HTML_Tag::create( 'div', $table, array( 'class' => 'content-panel-inner' ) ),
	), array( 'class' => 'content-panel' ) );
}

$style	= '
<style>
table th.cell-price,
table td.cell-price {
	text-align: right;
	}
table td.cell-price:after {
	content: " €";
	}

</style>';

/*  --  PANEL: ZONES  --  */
$buttonAdd	= UI_HTML_Tag::create( 'a', $iconAdd.'&nbsp;neue Zone', array( 'href' => './manage/shop/shipping/addZone', 'class' => 'btn btn-success' ) );
$rows	= array();
foreach( $zones as $zone ){
	$buttonRemove	= UI_HTML_Tag::create( 'a', $iconRemove.'&nbsp;entfernen', array( 'href' => './manage/shop/shipping/removeZone/'.$zone->zoneId, 'class' => 'btn btn-danger btn-small' ) );
	$rows[]	= UI_HTML_Tag::create( 'tr', array(
		UI_HTML_Tag::create( 'td', $zone->title ),
		UI_HTML_Tag::create( 'td', $buttonRemove, array( 'style' => 'text-align: right' ) ),
	) );
}
$thead	= '';//UI_HTML_Tag::create( 'tr', array( UI_HTML_Tag::create( 'th', '.' ), UI_HTML_Tag::create( 'th', '..' ) ) );
$thead	= UI_HTML_Tag::create( 'thead', $thead );
$tbody	= UI_HTML_Tag::create( 'tbody', $rows );
$table	= UI_HTML_Tag::create( 'table', array( $thead, $tbody ), array( 'class' => 'table' ) );
$panelZones	= UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'h3', 'Zonen' ),
	UI_HTML_Tag::create( 'div', array(
		$table,
		UI_HTML_Tag::create( 'div', $buttonAdd, array( 'class' => 'buttonbar' ) ),
	), array( 'class' => 'content-panel-inner' ) ),
), array( 'class' => 'content-panel' ) );


/*  --  PANEL: GRADES  --  */
$buttonAdd	= UI_HTML_Tag::create( 'a', $iconAdd.'&nbsp;neue Klasse', array( 'href' => './manage/shop/shipping/addZone', 'class' => 'btn btn-success' ) );
$rows	= array();
foreach( $grades as $grade ){
	$buttonRemove	= UI_HTML_Tag::create( 'a', $iconRemove.'&nbsp;entfernen', array( 'href' => './manage/shop/shipping/removeGrade/'.$grade->gradeId, 'class' => 'btn btn-danger btn-small' ) );
	$rows[]	= UI_HTML_Tag::create( 'tr', array(
		UI_HTML_Tag::create( 'td', $grade->title ),
		UI_HTML_Tag::create( 'td', $buttonRemove, array( 'style' => 'text-align: right' ) ),
	) );
}
$thead	= '';//UI_HTML_Tag::create( 'tr', array( UI_HTML_Tag::create( 'th', '.' ), UI_HTML_Tag::create( 'th', '..' ) ) );
$thead	= UI_HTML_Tag::create( 'thead', $thead );
$tbody	= UI_HTML_Tag::create( 'tbody', $rows );
$table	= UI_HTML_Tag::create( 'table', array( $thead, $tbody ), array( 'class' => 'table' ) );
$panelGrades	= UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'h3', 'Gewichtsklassen' ),
	UI_HTML_Tag::create( 'div', array(
		$table,
		UI_HTML_Tag::create( 'div', $buttonAdd, array( 'class' => 'buttonbar' ) ),
	), array( 'class' => 'content-panel-inner' ) ),
), array( 'class' => 'content-panel' ) );


$tabs	= View_Manage_Shop::renderTabs( $env, 'shipping' );

return $tabs.'
<!--<h3>Versandkosten</h3>-->
'.$panelPrices.'
<div class="row-fluid">
	<div class="span6">'.$panelZones.'</div>
	<div class="span6">'.$panelGrades.'</div>
</div>'.$style;
