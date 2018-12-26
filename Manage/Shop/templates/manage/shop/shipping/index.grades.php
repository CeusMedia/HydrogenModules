<?php
$iconRemove	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-remove' ) );

$heads	= array();
$cols	= array();
foreach( $zones as $zone ){
	$input	= UI_HTML_Tag::create( 'input', NULL, array(
		'type'	=> 'number',
		'step'	=> '0.01',
		'min'	=> 0,
		'name'	=> 'price['.$zone->zoneId.']',
		'id'	=> 'input_price_'.$zone->zoneId,
		'class'	=> 'span10',
		'value'	=> number_format( 0, 2 ),
	) );
	$label		= UI_HTML_Tag::create( 'label', $input, array( 'class' => 'checkbox' ) );
	$heads[]	= UI_HTML_Tag::create( 'th', $zone->title, array( 'class' => 'cell-price' ) );
	$cols[]		= UI_HTML_Tag::create( 'td', $input, array( 'class' => 'cell-price' ) );
}
$thead		= UI_HTML_Tag::create( 'thead', UI_HTML_Tag::create( 'tr', $heads ) );
$tbody		= UI_HTML_Tag::create( 'tbody', UI_HTML_Tag::create( 'tr', $cols ) );
$listZones	= UI_HTML_Tag::create( 'table', array( $thead, $tbody ), array( 'class' => 'table table-condensed table-striped' ) );

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
			UI_HTML_Tag::create( 'label', 'Gewicht in Gramm' ),
			UI_HTML_Tag::create( 'div', array(
				UI_HTML_Tag::create( 'div', array(
					UI_HTML_Tag::create( 'input', NULL, array(
						'type'	=> 'text',
						'name'	=> 'weight',
						'id'	=> 'input_weight',
						'class'	=> 'span12',
					) ),
				), array( 'class' => 'span6' ) ),
				UI_HTML_Tag::create( 'div', array(
					UI_HTML_Tag::create( 'label', join( array(
						UI_HTML_Tag::create( 'input', NULL, array(
							'type'		=> 'checkbox',
							'name'		=> 'fallback',
							'id'		=> 'input_fallback',
							'value'		=> 1,
							'oninput'	=> 'ModuleManageShopShipping.toggleModalWeight()',
						) ),
						'&nbsp;alles Andere'
					) ), array( 'class' => 'checkbox' ) ),
				), array( 'class' => 'span6' ) ),
			) ),
		), array( 'class' => 'span12' ) ),
	), array( 'class' => 'row-fluid' ) ),
	UI_HTML_Tag::create( 'div', array(
		UI_HTML_Tag::create( 'div', array(
			UI_HTML_Tag::create( 'label', 'Preise' ),
			$listZones
		), array( 'class' => 'span12' ) ),
	), array( 'class' => 'row-fluid' ) ),
);
$modalGrade	= new \CeusMedia\Bootstrap\Modal();
$modalGrade->setId( 'modalAddGrade' );
$modalGrade->setBody( $modalBody )->setHeading( 'Neue Gewichtsklasse' );
$modalGrade->setFormAction( './manage/shop/shipping/addGrade' );
$modalGrade->setSubmitButtonClass( 'btn btn-primary' );
$modalGrade->setSubmitButtonLabel( 'speichern' );
$modalGrade->setSubmitButtonIconClass( 'fa fa-fw fa-check' );
$modalGrade->setCloseButtonClass( 'btn' );
$modalGrade->setCloseButtonLabel( 'abbrechen' );
$modalGrade->setCloseButtonIconClass( 'fa fa-fw fa-arrow-left' );

$modalGradeTrigger	= new \CeusMedia\Bootstrap\Modal\Trigger();
$modalGradeTrigger->setModalId( 'modalAddGrade' );
$modalGradeTrigger->setLabel( 'neue Gewichtsklasse' )->setIcon( 'fa fa-fw fa-plus' );
$modalGradeTrigger->setAttributes( array( 'class' => 'btn btn-success' ) );

$rows	= array();
foreach( $grades as $grade ){
	$buttonRemove	= UI_HTML_Tag::create( 'a', $iconRemove.'&nbsp;entfernen', array( 'href' => './manage/shop/shipping/removeGrade/'.$grade->gradeId, 'class' => 'btn btn-danger btn-small' ) );
	$buttonRemove	= UI_HTML_Tag::create( 'a', $iconRemove, array( 'href' => './manage/shop/shipping/removeGrade/'.$grade->gradeId, 'class' => 'btn btn-inverse btn-mini' ) );
	$weight			= $grade->weight.' g';
	if( $grade->fallback )
		$weight	= '*';
	$rows[]	= UI_HTML_Tag::create( 'tr', array(
		UI_HTML_Tag::create( 'td', $grade->title ),
		UI_HTML_Tag::create( 'td', $weight ),
		UI_HTML_Tag::create( 'td', $buttonRemove, array( 'style' => 'text-align: right' ) ),
	) );
}
$thead	= UI_HTML_Tag::create( 'tr', array( UI_HTML_Tag::create( 'th', 'Titel' ), UI_HTML_Tag::create( 'th', 'Maximalgewicht' ) ) );
$thead	= UI_HTML_Tag::create( 'thead', $thead );
$tbody	= UI_HTML_Tag::create( 'tbody', $rows );
$table	= UI_HTML_Tag::create( 'table', array( $thead, $tbody ), array( 'class' => 'table' ) );
$panelGrades	= UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'h3', 'Gewichtsklassen' ),
	UI_HTML_Tag::create( 'div', array(
		$table,
		UI_HTML_Tag::create( 'div', $modalGradeTrigger, array( 'class' => 'buttonbar' ) ),
	), array( 'class' => 'content-panel-inner' ) ),
), array( 'class' => 'content-panel' ) );

return $panelGrades.$modalGrade->render();
?>
