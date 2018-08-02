<?php

$page		= (int) $env->getRequest()->get( 'page' );

$iconList	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-list' ) );
$iconView	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-eye' ) );
$iconEdit	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-pencil' ) );
$iconCheck	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) );
$iconSave	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) );
$iconResend	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-reload' ) );
$iconRemove	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-remove' ) );
$iconForm	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-th' ) );
$iconExport	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-download' ) );

$statuses	= array(
	Model_Form_Fill::STATUS_NEW			=> UI_HTML_Tag::create( 'label', 'unbestätigt', array( 'class' => 'label' ) ),
	Model_Form_Fill::STATUS_CONFIRMED	=> UI_HTML_Tag::create( 'label', 'gültig', array( 'class' => 'label label-success' ) ),
	Model_Form_Fill::STATUS_HANDLED		=> UI_HTML_Tag::create( 'label', 'behandelt', array( 'class' => 'label label-info' ) ),
);

$helperPerson	= new View_Helper_Form_Fill_Person( $env );
$helperPerson->setFill( $fill );
$helperPerson->setForm( $form );

$helperData		= new View_Helper_Form_Fill_Data( $env );
$helperData->setFill( $fill );
$helperData->setForm( $form );

$datetime	= UI_HTML_Tag::create( 'div', 'Zeitpunkt: '.date( 'd.m.Y H:i:s', $fill->createdAt ) );
$status		= UI_HTML_Tag::create( 'div', 'Zustand: '.$statuses[$fill->status] );
$referer	= '';
if( $fill->referer ){
	$referer	= UI_HTML_Tag::create( 'a', 'ausgefülltes Formular', array( 'href' => $fill->referer, 'target' => '_blank' ) );
	$referer	= UI_HTML_Tag::create( 'div', 'Webseite: '.$referer );
}
$formLink	= UI_HTML_Tag::create( 'a', $iconForm.'&nbsp;'.$form->title, array( 'href' => './manage/form/edit/'.$form->formId ) );
$formLink	= UI_HTML_Tag::create( 'div', 'Formular: '.$formLink );
$panelFacts	= UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'div', array(
		$helperPerson->render(),
	), array( 'class' => 'span8' ) ),
	UI_HTML_Tag::create( 'div', array(
		UI_HTML_Tag::create( 'div', array(
			UI_HTML_Tag::create( 'h3', 'Fakten' ),
			UI_HTML_Tag::create( 'div', array(
				$datetime,
				$referer,
				$formLink,
				$status,
			), array( 'class' => 'content-panel-inner' ) ),
		), array( 'class' => 'content-panel' ) ),
	), array( 'class' => 'span4' ) ),
), array( 'class' => 'row-fluid' ) );

$buttonList	= UI_HTML_Tag::create( 'a', $iconList.'&nbsp;zur Liste', array(
	'href'	=> './manage/form/fill'.( $page ? '/'.$page : '' ),
	'class'	=> 'btn',
) );
$buttonConfirm	= UI_HTML_Tag::create( 'a', $iconCheck.'&nbsp;als bestätigt markieren', array(
	'href'	=> './manage/form/markAsConfirmed/'.$fill->fillId.( $page ? '&page='.$page : '' ),
	'class'	=> 'btn btn-success',
) );
if( $fill->status != Model_Form_Fill::STATUS_NEW )
	$buttonConfirm	= '';

$buttonHandled	= UI_HTML_Tag::create( 'a', $iconCheck.'&nbsp;als behandelt markieren', array(
	'href'	=> './manage/form/markAsHandled/'.$fill->fillId.( $page ? '&page='.$page : '' ),
	'class'	=> 'btn btn-info',
) );
if( $fill->status != Model_Form_Fill::STATUS_CONFIRMED )
	$buttonHandled	= '';

$buttonRemove	= UI_HTML_Tag::create( 'a', $iconRemove.'&nbsp;entfernen', array(
	'href'		=> './manage/form/fill/remove/'.$fill->fillId.( $page ? '&page='.$page : '' ),
	'class'		=> 'btn btn-danger',
	'onclick'	=> "if(!confirm('Wirklich ?'))return false;"
) );

$buttonExport	= UI_HTML_Tag::create( 'a', $iconExport.'&nbsp;exportieren', array(
	'href'		=> './manage/form/fill/export/csv/fill/'.$fill->fillId,
	'class'		=> 'btn',
) );

$buttons	= join( ' ', array( $buttonList, $buttonExport, $buttonConfirm, $buttonHandled, $buttonRemove ) );
$buttonbar	= UI_HTML_Tag::create( 'div', $buttons, array( 'class' => 'buttonbar' ) );
$heading	= UI_HTML_Tag::create( 'h2', array(
	UI_HTML_Tag::create( 'span', 'Eintrag: ', array( 'class' => 'muted' ) ),
	$form->title,
) );
return $heading.$panelFacts.$helperData->render().$buttonbar;
