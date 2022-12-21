<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$iconSave	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-check'] );
$iconAdd	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-plus'] );
$iconRemove	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-remove'] );

/*  --  PANEL: PRICES  --  */
$panelPrices	= '';
if( $zones || $grades ){
	$rows	= [];
	$thead	= [HtmlTag::create( 'th', 'Zonen \ Gewichtsklassen' )];
	foreach( $grades as $grade )
		$thead[]	= HtmlTag::create( 'th', $grade->title, ['class' => 'cell-price'] );
	$thead	= HtmlTag::create( 'thead', HtmlTag::create( 'tr', $thead ) );
	foreach( $zones as $zone ){
		$row	= [HtmlTag::create( 'th', $zone->title )];
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
			$row[]	= HtmlTag::create( 'td', $input, ['class' => 'cell-price'] );
		}
		$rows[]	= HtmlTag::create( 'tr', $row );
	}
	$tbody	= HtmlTag::create( 'tbody', $rows );
	$table	= HtmlTag::create( 'table', [$thead, $tbody], ['class' => 'table'] );
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
				), ['class' => 'buttonbar'] ),
			), array(
				'action'	=> './manage/shop/shipping/setPrices',
				'method'	=> 'POST',
			) ),
		), ['class' => 'content-panel-inner'] ),
	), ['class' => 'content-panel'] );
}

return $panelPrices;
