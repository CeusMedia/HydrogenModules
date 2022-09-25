<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$iconSave	= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) );
$iconAdd	= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-plus' ) );
$iconRemove	= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-remove' ) );

/*  --  PANEL: PRICES  --  */
$panelPrices	= '';
if( $zones || $grades ){
	$rows	= [];
	$thead	= array( HtmlTag::create( 'th', 'Zonen \ Gewichtsklassen' ) );
	foreach( $grades as $grade )
		$thead[]	= HtmlTag::create( 'th', $grade->title, array( 'class' => 'cell-price' ) );
	$thead	= HtmlTag::create( 'thead', HtmlTag::create( 'tr', $thead ) );
	foreach( $zones as $zone ){
		$row	= array( HtmlTag::create( 'th', $zone->title ) );
		foreach( $grades as $grade ){
			$price	= $priceMatrix[$zone->zoneId][$grade->gradeId];
		//	$price	= number_format( $price, 2, ',', '.' );
			$input	= HtmlTag::create( 'input', NULL, array(
				'type'	=> 'number',
				'step'	=> '0.01',
				'min'	=> '0',
				'name'	=> 'price['.$zone->zoneId.']['.$grade->gradeId.']',
				'id'	=> 'input_price_'.$zone->zoneId.'_'.$grade->gradeId,
				'class'	=> 'span6',
				'value'	=> $price,
				'style'	=> 'text-align: right;',
			) )/*.'&nbsp;€'*/;
			$row[]	= HtmlTag::create( 'td', $input, array( 'class' => 'cell-price' ) );
		}
		$rows[]	= HtmlTag::create( 'tr', $row );
	}
	$tbody	= HtmlTag::create( 'tbody', $rows );
	$table	= HtmlTag::create( 'table', array( $thead, $tbody ), array( 'class' => 'table' ) );
	$panelPrices	= HtmlTag::create( 'div', array(
		HtmlTag::create( 'h3', 'Versandkosten' ),
		HtmlTag::create( 'div', array(
			HtmlTag::create( 'form', array(
				$table,
				HtmlTag::create( 'div', array(
					HtmlTag::create( 'button', $iconSave.'&nbsp;speichern', array(
						'type'	=> 'submit',
						'name'	=> 'save',
						'class'	=> 'btn btn-primary',
					) ),
				), array( 'class' => 'buttonbar' ) ),
			), array(
				'action'	=> './manage/shop/shipping/setPrices',
				'method'	=> 'POST',
			) ),
		), array( 'class' => 'content-panel-inner' ) ),
	), array( 'class' => 'content-panel' ) );
}

return $panelPrices;
