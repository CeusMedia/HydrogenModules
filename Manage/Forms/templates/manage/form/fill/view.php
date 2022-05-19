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
$iconInfo	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-info' ) );

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
$helperData->setMode(View_Helper_Form_Fill_Data::MODE_EXTENDED);


//  --  PANEL: FACTS  --  //
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
	UI_HTML_Tag::create( 'h3', 'Fakten' ),
	UI_HTML_Tag::create( 'div', array(
		$datetime,
		$referer,
		$formLink,
		$status,
	), array( 'class' => 'content-panel-inner' ) ),
), array( 'class' => 'content-panel' ) );



//  --  PANEL: TRANSFERS  --  //
$parser			= new ADT_JSON_Parser;
$mapper			= new Logic_Form_Transfer_DataMapper( $env );
$panelTransfers	= '';
if( $fillTransfers ){
	$rows	= [];
	$modals	= [];
	foreach( $fillTransfers as $fillTransfer ){
		$targetTitle	= $transferTargetMap[$fillTransfer->formTransferTargetId]->title;
		$status			= $iconCheck.'&nbsp;erfolgreich';
		if( (int) $fillTransfer->status !== Model_Form_Fill_Transfer::STATUS_SUCCESS )
			$status	= UI_HTML_Tag::create( 'abbr', $iconRemove.'&nbsp;gescheitert', array( 'title' => $fillTransfer->message ) );

		$button			= '';
		if( $fillTransfer->data ){
			$formData		= json_decode( $fillTransfer->data, TRUE );
			$modalId		= 'transfer-report-'.$fillTransfer->formFillTransferId;
			$ruleSet		= $parser->parse( $form->transferRules[$fillTransfer->formTransferRuleId]->rules, FALSE );
			try{
				$transferData	= $mapper->applyRulesToFormData( $formData, $ruleSet );
			}
			catch( Exception $e ){
				$transferData	= [];
				$env->getMessenger()->noteFailure( 'Export-Regeln lassen sich nicht anwenden. Fehler: '.$e->getMessage() );
			}

			$modalBody	= array(
				UI_HTML_Tag::create( 'h4', 'Formulardaten' ),
				arrayToTable( $formData ),
				UI_HTML_Tag::create( 'h4', 'Transferdaten' ),
				arrayToTable( $transferData ),
			);
			if( in_array( (int) $fillTransfer->status, array( Model_Form_Fill_Transfer::STATUS_ERROR, Model_Form_Fill_Transfer::STATUS_EXCEPTION ) ) ){
				$modalBody[]	= UI_HTML_Tag::create( 'h4', 'Fehlermeldung' );
				$modalBody[]    = UI_HTML_Tag::create( 'pre', str_replace( $this->env->uri, '', $fillTransfer->message ), array( 'style' => 'font-size: 10px' ) );
				if( !empty( $fillTransfer->trace ) ){
					$modalBody[]	= UI_HTML_Tag::create( 'h4', 'Aufrufstapel' );
					$modalBody[]    = UI_HTML_Tag::create( 'pre', str_replace( $this->env->uri, '', $fillTransfer->trace ), array( 'style' => 'font-size: 10px' ) );
				}
			}

			$modal	= new CeusMedia\Bootstrap\Modal\Dialog( $modalId );
			$modal->setHeading( 'Datenweitergabe an '.$targetTitle );
			$modal->setBody( join( $modalBody ) );
			$modal->setCloseButtonLabel( 'schließen' );
			$modal->setCloseButtonIconClass( 'fa fa-fw fa-close' );
			$modals[]	= $modal;

			$button		= new CeusMedia\Bootstrap\Modal\Trigger( $modalId );
			$button->setLabel( $iconInfo )->setClass( 'btn-info btn-mini' );
		}
		$rows[]			= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', $targetTitle ),
			UI_HTML_Tag::create( 'td', $status ),
			UI_HTML_Tag::create( 'td', $button ),
		) );
	}
	$tbody	= UI_HTML_Tag::create( 'tbody', $rows );
	$panelTransfers	= UI_HTML_Tag::create( 'div', array(
		UI_HTML_Tag::create( 'div', array(
			UI_HTML_Tag::create( 'h3', 'Datenweitergabe' ),
			UI_HTML_Tag::create( 'div', array(
				UI_HTML_Tag::create( 'table', $tbody, array( 'class' => 'table table-condensed' ) ),
			) ),
		), array( 'class' => 'content-panel-inner' ) ),
	), array( 'class' => 'content-panel' ) ).join( $modals );;
}


//  --  BUTTONS  --  //
$buttonList	= UI_HTML_Tag::create( 'a', $iconList.'&nbsp;zur Liste', array(
	'href'	=> './manage/form/fill'.( $page ? '/'.$page : '' ),
	'class'	=> 'btn',
) );
$buttonConfirm	= UI_HTML_Tag::create( 'a', $iconCheck.'&nbsp;als bestätigt markieren', array(
	'href'	=> './manage/form/fill/markAsConfirmed/'.$fill->fillId.( $page ? '&page='.$page : '' ),
	'class'	=> 'btn btn-success',
) );
if( $fill->status != Model_Form_Fill::STATUS_NEW )
	$buttonConfirm	= '';

$buttonHandled	= UI_HTML_Tag::create( 'a', $iconCheck.'&nbsp;als behandelt markieren', array(
	'href'	=> './manage/form/fill/markAsHandled/'.$fill->fillId.( $page ? '&page='.$page : '' ),
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

return UI_HTML_Tag::create( 'div', array(
	$heading,
	UI_HTML_Tag::create( 'div', array(
		UI_HTML_Tag::create( 'div', array(
			$helperPerson->render(),
		), array( 'class' => 'span8' ) ),
		UI_HTML_Tag::create( 'div', array(
			$panelFacts,
			$panelTransfers,
		), array( 'class' => 'span4' ) ),
	), array( 'class' => 'row-fluid' ) ),
	$helperData->render(),
	$buttonbar,
) );


function arrayToTable( $data ){
	$list	= [];
	foreach( $data as $key => $value ){
		$list[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'th', $key ),
			UI_HTML_Tag::create( 'td', $value ),
		) );
	}
	$tbody	= UI_HTML_Tag::create( 'tbody', $list );
	return UI_HTML_Tag::create( 'table', $tbody, array( 'class' => 'table table-condensed table-bordered' ) );
}
