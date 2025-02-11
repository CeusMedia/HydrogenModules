<?php
use CeusMedia\Bootstrap\Modal\Dialog as BootstrapModalDialog;
use CeusMedia\Bootstrap\Modal\Trigger as BootstrapModalTrigger;
use CeusMedia\Common\UI\HTML\Tag as Html;

$iconRemove	= Html::create( 'i', '', ['class' => 'fa fa-fw fa-remove'] );

$heads	= [];
$cols	= [];
foreach( $zones as $zone ){
	$input	= Html::create( 'input', NULL, array(
		'type'	=> 'number',
		'step'	=> '0.01',
		'min'	=> 0,
		'name'	=> 'price['.$zone->zoneId.']',
		'id'	=> 'input_price_'.$zone->zoneId,
		'class'	=> 'span10',
		'value'	=> number_format( 0, 2 ),
	) );
	$label		= Html::create( 'label', $input, ['class' => 'checkbox'] );
	$heads[]	= Html::create( 'th', $zone->title, ['class' => 'cell-price'] );
	$cols[]		= Html::create( 'td', $input, ['class' => 'cell-price'] );
}
$thead		= Html::create( 'thead', Html::create( 'tr', $heads ) );
$tbody		= Html::create( 'tbody', Html::create( 'tr', $cols ) );
$listZones	= Html::create( 'table', [$thead, $tbody], ['class' => 'table table-condensed table-striped'] );

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
			Html::create( 'label', 'Gewicht in Gramm' ),
			Html::create( 'div', array(
				Html::create( 'div', array(
					Html::create( 'input', NULL, [
						'type'	=> 'text',
						'name'	=> 'weight',
						'id'	=> 'input_weight',
						'class'	=> 'span12',
					] ),
				), ['class' => 'span6'] ),
				Html::create( 'div', array(
					Html::create( 'label', join( array(
						Html::create( 'input', NULL, array(
							'type'		=> 'checkbox',
							'name'		=> 'fallback',
							'id'		=> 'input_fallback',
							'value'		=> 1,
							'oninput'	=> 'ModuleManageShopShipping.toggleModalWeight()',
						) ),
						'&nbsp;alles Andere'
					) ), ['class' => 'checkbox'] ),
				), ['class' => 'span6'] ),
			) ),
		), ['class' => 'span12'] ),
	), ['class' => 'row-fluid'] ),
	Html::create( 'div', array(
		Html::create( 'div', array(
			Html::create( 'label', 'Preise' ),
			$listZones
		), ['class' => 'span12'] ),
	), ['class' => 'row-fluid'] ),
);
$modalGrade	= new BootstrapModalDialog( 'modalAddGrade' );
$modalGrade->setBody( $modalBody )->setHeading( 'Neue Gewichtsklasse' );
$modalGrade->setFormAction( './manage/shop/shipping/addGrade' );
$modalGrade->setSubmitButtonClass( 'btn btn-primary' );
$modalGrade->setSubmitButtonLabel( 'speichern' );
$modalGrade->setSubmitButtonIconClass( 'fa fa-fw fa-check' );
$modalGrade->setCloseButtonClass( 'btn' );
$modalGrade->setCloseButtonLabel( 'abbrechen' );
$modalGrade->setCloseButtonIconClass( 'fa fa-fw fa-arrow-left' );

$modalGradeTrigger	= new BootstrapModalTrigger( 'modalAddGrade' );
$modalGradeTrigger->setLabel( 'neue Gewichtsklasse' )->setIcon( 'fa fa-fw fa-plus' );
$modalGradeTrigger->setAttributes( ['class' => 'btn btn-success'] );

$rows	= [];
foreach( $grades as $grade ){
	$buttonRemove	= Html::create( 'a', $iconRemove.'&nbsp;entfernen', ['href' => './manage/shop/shipping/removeGrade/'.$grade->gradeId, 'class' => 'btn btn-danger btn-small'] );
	$buttonRemove	= Html::create( 'a', $iconRemove, ['href' => './manage/shop/shipping/removeGrade/'.$grade->gradeId, 'class' => 'btn btn-inverse btn-mini'] );
	$weight			= $grade->weight.' g';
	if( $grade->fallback )
		$weight	= '*';
	$rows[]	= Html::create( 'tr', array(
		Html::create( 'td', $grade->title ),
		Html::create( 'td', $weight ),
		Html::create( 'td', $buttonRemove, ['style' => 'text-align: right'] ),
	) );
}
$thead	= Html::create( 'tr', [Html::create( 'th', 'Titel' ), Html::create( 'th', 'Maximalgewicht' )] );
$thead	= Html::create( 'thead', $thead );
$tbody	= Html::create( 'tbody', $rows );
$table	= Html::create( 'table', [$thead, $tbody], ['class' => 'table'] );
$panelGrades	= Html::create( 'div', array(
	Html::create( 'h3', 'Gewichtsklassen' ),
	Html::create( 'div', array(
		$table,
		Html::create( 'div', $modalGradeTrigger, ['class' => 'buttonbar'] ),
	), ['class' => 'content-panel-inner'] ),
), ['class' => 'content-panel'] );

return $panelGrades.$modalGrade->render();
