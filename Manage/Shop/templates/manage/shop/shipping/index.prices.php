<?php
$iconSave	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) );
$iconAdd	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-plus' ) );
$iconRemove	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-remove' ) );

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
		//	$price	= number_format( $price, 2, ',', '.' );
			$input	= UI_HTML_Tag::create( 'input', NULL, array(
				'type'	=> 'number',
				'step'	=> '0.01',
				'min'	=> '0',
				'name'	=> 'price['.$zone->zoneId.']['.$grade->gradeId.']',
				'id'	=> 'input_price_'.$zone->zoneId.'_'.$grade->gradeId,
				'class'	=> 'span6',
				'value'	=> $price,
				'style'	=> 'text-align: right;',
			) )/*.'&nbsp;€'*/;
			$row[]	= UI_HTML_Tag::create( 'td', $input, array( 'class' => 'cell-price' ) );
		}
		$rows[]	= UI_HTML_Tag::create( 'tr', $row );
	}
	$tbody	= UI_HTML_Tag::create( 'tbody', $rows );
	$table	= UI_HTML_Tag::create( 'table', array( $thead, $tbody ), array( 'class' => 'table' ) );
	$panelPrices	= UI_HTML_Tag::create( 'div', array(
		UI_HTML_Tag::create( 'h3', 'Versandkosten' ),
		UI_HTML_Tag::create( 'div', array(
			UI_HTML_Tag::create( 'form', array(
				$table,
				UI_HTML_Tag::create( 'div', array(
					UI_HTML_Tag::create( 'button', $iconSave.'&nbsp;speichern', array(
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
