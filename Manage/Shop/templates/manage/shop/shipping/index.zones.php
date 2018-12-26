<?php

$iconRemove	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-remove' ) );

$heads	= array();
$cols	= array();
foreach( $grades as $grade ){
	$input	= UI_HTML_Tag::create( 'input', NULL, array(
		'type'	=> 'number',
		'step'	=> '0.01',
		'min'	=> 0,
		'name'	=> 'price['.$grade->gradeId.']',
		'id'	=> 'input_price_'.$grade->gradeId,
		'class'	=> 'span12',
		'value'	=> number_format( 0, 2 ),
	) );
	$label		= UI_HTML_Tag::create( 'label', $input, array( 'class' => 'checkbox' ) );
	$heads[]	= UI_HTML_Tag::create( 'th', $grade->title, array( 'class' => 'cell-price' ) );
	$cols[]		= UI_HTML_Tag::create( 'td', $input, array( 'class' => 'cell-price' ) );
}
$thead		= UI_HTML_Tag::create( 'thead', UI_HTML_Tag::create( 'tr', $heads ) );
$tbody		= UI_HTML_Tag::create( 'tbody', UI_HTML_Tag::create( 'tr', $cols ) );
$listGrades	= UI_HTML_Tag::create( 'table', array( $thead, $tbody ), array( 'class' => 'table table-condensed table-striped' ) );

$listCountries	= array();
foreach( $countryMap as $countryCode => $countryLabel ){
	if( in_array( $countryCode, $zoneCountries ) )
		continue;
	$input	= UI_HTML_Tag::create( 'input', NULL, array(
		'type'	=> 'checkbox',
		'name'	=> 'country[]',
		'id'	=> 'input_country_'.$countryCode,
		'value'	=> $countryCode,
	) );
	$label	= UI_HTML_Tag::create( 'label', $input.'&nbsp;'.$countryLabel, array( 'class' => 'checkbox' ) );
	$listCountries[]	= UI_HTML_Tag::create( 'li', $label );
}
$listCountries	= UI_HTML_Tag::create( 'ul', $listCountries, array( 'class' => 'unstyled' ) );
$modalBody	= array(
	UI_HTML_Tag::create( 'div', array(
		UI_HTML_Tag::create( 'div', array(
			UI_HTML_Tag::create( 'label', 'Titel', array( 'class' => 'mandatory required') ),
			UI_HTML_Tag::create( 'input', NULL, array(
				'type'		=> 'text',
				'name'		=> 'title',
				'id'		=> 'input_title',
				'class'		=> 'span12',
				'required'	=> 'required',
			) ),
		), array( 'class' => 'span12' ) ),
	), array( 'class' => 'row-fluid' ) ),
	UI_HTML_Tag::create( 'div', array(
		UI_HTML_Tag::create( 'div', array(
			UI_HTML_Tag::create( 'label', 'Preise' ),
			$listGrades
		), array( 'class' => 'span12' ) ),
	), array( 'class' => 'row-fluid' ) ),
	UI_HTML_Tag::create( 'div', array(
		UI_HTML_Tag::create( 'div', array(
			UI_HTML_Tag::create( 'label', 'Länder' ),
			UI_HTML_Tag::create( 'div', UI_HTML_Tag::create( 'label', join( array(
				UI_HTML_Tag::create( 'input', NULL, array(
					'type'		=> 'checkbox',
					'name'		=> 'fallback',
					'id'		=> 'input_fallback',
					'value'		=> '1',
					'oninput'	=> 'ModuleManageShopShipping.toggleModalCountries()',
				) ),
				'&nbsp;weltweit'
			) ), array( 'class' => 'checkbox' ) ) ),
			UI_HTML_Tag::create( 'div', $listCountries, array(
				'id'	=> 'modal-countries',
				'class'	=> '',
				'style'	=> 'max-height: 120px; overflow: hidden; overflow-y: auto; padding: 0.5em; border: 1px solid gray; border: 1px solid rgba(127, 127, 127, 0.45); border-radius: 0.3em 0em 0em 0.3em',
			) ),
		), array( 'class' => 'span12' ) ),
	), array( 'class' => 'row-fluid' ) ),
);
$modalZone	= new \CeusMedia\Bootstrap\Modal();
$modalZone->setId( 'modalAddZone' );
$modalZone->setBody( $modalBody )->setHeading( 'Neue Zone' );
$modalZone->setFormAction( './manage/shop/shipping/addZone' );
$modalZone->setSubmitButtonClass( 'btn btn-primary' );
$modalZone->setSubmitButtonLabel( 'speichern' );
$modalZone->setSubmitButtonIconClass( 'fa fa-fw fa-check' );
$modalZone->setCloseButtonClass( 'btn' );
$modalZone->setCloseButtonLabel( 'abbrechen' );
$modalZone->setCloseButtonIconClass( 'fa fa-fw fa-arrow-left' );

$modalZoneTrigger	= new \CeusMedia\Bootstrap\Modal\Trigger();
$modalZoneTrigger->setModalId( 'modalAddZone' );
$modalZoneTrigger->setLabel( 'neue Zone' )->setIcon( 'fa fa-fw fa-plus' );
$modalZoneTrigger->setAttributes( array( 'class' => 'btn btn-success' ) );
//$buttonAdd	= UI_HTML_Tag::create( 'a', $iconAdd.'&nbsp;neue Zone', array( 'href' => './manage/shop/shipping/addZone', 'class' => 'btn btn-success' ) );
$rows	= array();
foreach( $zones as $zone ){
	$buttonRemove	= UI_HTML_Tag::create( 'a', $iconRemove.'&nbsp;entfernen', array( 'href' => './manage/shop/shipping/removeZone/'.$zone->zoneId, 'class' => 'btn btn-danger btn-small' ) );
	$buttonRemove	= UI_HTML_Tag::create( 'a', $iconRemove, array( 'href' => './manage/shop/shipping/removeZone/'.$zone->zoneId, 'class' => 'btn btn-inverse btn-mini' ) );
	if( $zone->fallback )
		$countries	= '*';
	else{
		$countries		= array();
		foreach( $zone->countries as $countryCode )
			$countries[]	= UI_HTML_Tag::create( 'abbr', $countryCode, array( 'title' => $countryMap[$countryCode] ) );
		$countries	= join( ', ', $countries );
	}
	$rows[]	= UI_HTML_Tag::create( 'tr', array(
		UI_HTML_Tag::create( 'td', $zone->title ),
		UI_HTML_Tag::create( 'td', $countries ),
		UI_HTML_Tag::create( 'td', $buttonRemove, array( 'style' => 'text-align: right' ) ),
	) );
}
$thead	= UI_HTML_Tag::create( 'tr', array( UI_HTML_Tag::create( 'th', 'Titel' ), UI_HTML_Tag::create( 'th', 'Länder' ) ) );
$thead	= UI_HTML_Tag::create( 'thead', $thead );
$tbody	= UI_HTML_Tag::create( 'tbody', $rows );
$table	= UI_HTML_Tag::create( 'table', array( $thead, $tbody ), array( 'class' => 'table' ) );
$panelZones	= UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'h3', 'Zonen' ),
	UI_HTML_Tag::create( 'div', array(
		$table,
		UI_HTML_Tag::create( 'div', $modalZoneTrigger, array( 'class' => 'buttonbar' ) ),
	), array( 'class' => 'content-panel-inner' ) ),
), array( 'class' => 'content-panel' ) );

return $panelZones.$modalZone->render();
