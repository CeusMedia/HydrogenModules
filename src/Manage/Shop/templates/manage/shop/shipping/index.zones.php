<?php
use CeusMedia\Bootstrap\Modal\Dialog as BootstrapModalDialog;
use CeusMedia\Bootstrap\Modal\Trigger as BootstrapModalTrigger;
use CeusMedia\Common\UI\HTML\Tag as Html;

$iconRemove	= Html::create( 'i', '', ['class' => 'fa fa-fw fa-remove'] );

$heads	= [];
$cols	= [];
foreach( $grades as $grade ){
	$input	= Html::create( 'input', NULL, array(
		'type'	=> 'number',
		'step'	=> '0.01',
		'min'	=> 0,
		'name'	=> 'price['.$grade->gradeId.']',
		'id'	=> 'input_price_'.$grade->gradeId,
		'class'	=> 'span12',
		'value'	=> number_format( 0, 2 ),
	) );
	$label		= Html::create( 'label', $input, ['class' => 'checkbox'] );
	$heads[]	= Html::create( 'th', $grade->title, ['class' => 'cell-price'] );
	$cols[]		= Html::create( 'td', $input, ['class' => 'cell-price'] );
}
$thead		= Html::create( 'thead', Html::create( 'tr', $heads ) );
$tbody		= Html::create( 'tbody', Html::create( 'tr', $cols ) );
$listGrades	= Html::create( 'table', [$thead, $tbody], ['class' => 'table table-condensed table-striped'] );

$listCountries	= [];
foreach( $countryMap as $countryCode => $countryLabel ){
	if( in_array( $countryCode, $zoneCountries ) )
		continue;
	$input	= Html::create( 'input', NULL, [
		'type'	=> 'checkbox',
		'name'	=> 'country[]',
		'id'	=> 'input_country_'.$countryCode,
		'value'	=> $countryCode,
	] );
	$label	= Html::create( 'label', $input.'&nbsp;'.$countryLabel, ['class' => 'checkbox'] );
	$listCountries[]	= Html::create( 'li', $label );
}
$listCountries	= Html::create( 'ul', $listCountries, ['class' => 'unstyled'] );
$modalBody	= array(
	Html::create( 'div', array(
		Html::create( 'div', array(
			Html::create( 'label', 'Titel', ['class' => 'mandatory required'] ),
			Html::create( 'input', NULL, [
				'type'		=> 'text',
				'name'		=> 'title',
				'id'		=> 'input_title',
				'class'		=> 'span12',
				'required'	=> 'required',
			] ),
		), ['class' => 'span12'] ),
	), ['class' => 'row-fluid'] ),
	Html::create( 'div', array(
		Html::create( 'div', array(
			Html::create( 'label', 'Länder' ),
			Html::create( 'div', Html::create( 'label', join( array(
				Html::create( 'input', NULL, array(
					'type'		=> 'checkbox',
					'name'		=> 'fallback',
					'id'		=> 'input_fallback',
					'value'		=> '1',
					'oninput'	=> 'ModuleManageShopShipping.toggleModalCountries()',
				) ),
				'&nbsp;weltweit'
			) ), ['class' => 'checkbox'] ) ),
			Html::create( 'div', $listCountries, array(
				'id'	=> 'modal-countries',
				'class'	=> '',
				'style'	=> 'max-height: 120px; overflow: hidden; overflow-y: auto; padding: 0.5em; border: 1px solid gray; border: 1px solid rgba(127, 127, 127, 0.45); border-radius: 0.3em 0em 0em 0.3em',
			) ),
		), ['class' => 'span12'] ),
	), ['class' => 'row-fluid'] ),
	Html::create( 'div', array(
		Html::create( 'div', array(
			Html::create( 'label', 'Preise' ),
			$listGrades
		), ['class' => 'span12'] ),
	), ['class' => 'row-fluid'] ),
);
$modalZone	= new BootstrapModalDialog( 'modalAddZone' );
$modalZone->setBody( $modalBody )->setHeading( 'Neue Zone' );
$modalZone->setFormAction( './manage/shop/shipping/addZone' );
$modalZone->setSubmitButtonClass( 'btn btn-primary' );
$modalZone->setSubmitButtonLabel( 'speichern' );
$modalZone->setSubmitButtonIconClass( 'fa fa-fw fa-check' );
$modalZone->setCloseButtonClass( 'btn' );
$modalZone->setCloseButtonLabel( 'abbrechen' );
$modalZone->setCloseButtonIconClass( 'fa fa-fw fa-arrow-left' );

$modalZoneTrigger	= new BootstrapModalTrigger( 'modalAddZone' );
$modalZoneTrigger->setLabel( 'neue Zone' )->setIcon( 'fa fa-fw fa-plus' );
$modalZoneTrigger->setAttributes( ['class' => 'btn btn-success'] );
//$buttonAdd	= Html::create( 'a', $iconAdd.'&nbsp;neue Zone', ['href' => './manage/shop/shipping/addZone', 'class' => 'btn btn-success'] );
$rows	= [];
foreach( $zones as $zone ){
	$buttonRemove	= Html::create( 'a', $iconRemove.'&nbsp;entfernen', ['href' => './manage/shop/shipping/removeZone/'.$zone->zoneId, 'class' => 'btn btn-danger btn-small'] );
	$buttonRemove	= Html::create( 'a', $iconRemove, ['href' => './manage/shop/shipping/removeZone/'.$zone->zoneId, 'class' => 'btn btn-inverse btn-mini'] );
	if( $zone->fallback )
		$countries	= '*';
	else{
		$countries		= [];
		foreach( $zone->countries as $countryCode )
			$countries[]	= Html::create( 'abbr', $countryCode, ['title' => $countryMap[$countryCode]] );
		$countries	= join( ', ', $countries );
	}
	$rows[]	= Html::create( 'tr', array(
		Html::create( 'td', $zone->title ),
		Html::create( 'td', $countries ),
		Html::create( 'td', $buttonRemove, ['style' => 'text-align: right'] ),
	) );
}
$thead	= Html::create( 'tr', [Html::create( 'th', 'Titel' ), Html::create( 'th', 'Länder' )] );
$thead	= Html::create( 'thead', $thead );
$tbody	= Html::create( 'tbody', $rows );
$table	= Html::create( 'table', [$thead, $tbody], ['class' => 'table'] );
$panelZones	= Html::create( 'div', array(
	Html::create( 'h3', 'Zonen' ),
	Html::create( 'div', array(
		$table,
		Html::create( 'div', $modalZoneTrigger, ['class' => 'buttonbar'] ),
	), ['class' => 'content-panel-inner'] ),
), ['class' => 'content-panel'] );

return $panelZones.$modalZone->render();
