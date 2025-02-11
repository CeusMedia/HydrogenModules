<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Bootstrap\Modal\Dialog as ModalDialog;
use CeusMedia\Bootstrap\Modal\Trigger as ModalTrigger;
use CeusMedia\Common\ADT\JSON\Parser as JsonParser;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;

/** @var WebEnvironment $env */
/** @var array $fillTransfers */
/** @var Entity_Form_Fill $fill */
/** @var Entity_Form $form */
/** @var object[] $transferTargetMap */

$page		= (int) $env->getRequest()->get( 'page' );

$iconList	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-list'] );
$iconView	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-eye'] );
$iconEdit	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-pencil'] );
$iconCheck	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-check'] );
$iconSave	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-check'] );
$iconResend	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-reload'] );
$iconRemove	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-remove'] );
$iconForm	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-th'] );
$iconExport	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-download'] );
$iconInfo	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-info'] );

$statuses	= [
	Model_Form_Fill::STATUS_NEW			=> HtmlTag::create( 'label', 'unbestätigt', ['class' => 'label'] ),
	Model_Form_Fill::STATUS_CONFIRMED	=> HtmlTag::create( 'label', 'gültig', ['class' => 'label label-success'] ),
	Model_Form_Fill::STATUS_HANDLED		=> HtmlTag::create( 'label', 'behandelt', ['class' => 'label label-info'] ),
];

$helperPerson	= new View_Helper_Form_Fill_Person( $env );
$helperPerson->setFill( $fill );
$helperPerson->setForm( $form );

$helperData		= new View_Helper_Form_Fill_Data( $env );
$helperData->setFill( $fill );
$helperData->setForm( $form );
$helperData->setMode( View_Helper_Form_Fill_Data::MODE_EXTENDED );


//  --  PANEL: FACTS  --  //
$datetime	= HtmlTag::create( 'div', 'Zeitpunkt: '.date( 'd.m.Y H:i:s', $fill->createdAt ) );
$status		= HtmlTag::create( 'div', 'Zustand: '.$statuses[$fill->status] );
$referer	= '';
if( $fill->referer ){
	$referer	= HtmlTag::create( 'a', 'ausgefülltes Formular', ['href' => $fill->referer, 'target' => '_blank'] );
	$referer	= HtmlTag::create( 'div', 'Webseite: '.$referer );
}
$formLink	= HtmlTag::create( 'a', $iconForm.'&nbsp;'.$form->title, ['href' => './manage/form/edit/'.$form->formId] );
$formLink	= HtmlTag::create( 'div', 'Formular: '.$formLink );
$panelFacts	= HtmlTag::create( 'div', [
	HtmlTag::create( 'h3', 'Fakten' ),
	HtmlTag::create( 'div', [
		$datetime,
		$referer,
		$formLink,
		$status,
	], ['class' => 'content-panel-inner'] ),
], ['class' => 'content-panel'] );


//  --  PANEL: TRANSFERS  --  //
$parser			= new JsonParser;
$mapper			= new Logic_Form_Transfer_DataMapper( $env );
$panelTransfers	= '';
if( $fillTransfers ){
	$rows	= [];
	$modals	= [];
	foreach( $fillTransfers as $fillTransfer ){
		$targetTitle	= $transferTargetMap[$fillTransfer->formTransferTargetId]->title;
		$status			= $iconCheck.'&nbsp;erfolgreich';
		if( (int) $fillTransfer->status !== Model_Form_Fill_Transfer::STATUS_SUCCESS )
			$status	= HtmlTag::create( 'abbr', $iconRemove.'&nbsp;gescheitert', ['title' => $fillTransfer->message] );

		$button			= '';
		if( $fillTransfer->data ){
			$formData		= json_decode( $fillTransfer->data, TRUE );
			$modalId		= 'transfer-report-'.$fillTransfer->formFillTransferId;
			$ruleSet		= $parser->parse( $form->transferRules[$fillTransfer->formTransferRuleId]->rules );
			try{
				$transferData	= $mapper->applyRulesToFormData( $formData, $ruleSet );
			}
			catch( Exception $e ){
				$transferData	= [];
				$env->getMessenger()->noteFailure( 'Export-Regeln lassen sich nicht anwenden. Fehler: '.$e->getMessage() );
			}

			$modalBody	= [
				HtmlTag::create( 'h4', 'Formulardaten' ),
				arrayToTable( $formData ),
				HtmlTag::create( 'h4', 'Transferdaten' ),
				arrayToTable( $transferData ),
			];
			if( in_array( (int) $fillTransfer->status, [Model_Form_Fill_Transfer::STATUS_ERROR, Model_Form_Fill_Transfer::STATUS_EXCEPTION] ) ){
				$modalBody[]	= HtmlTag::create( 'h4', 'Fehlermeldung' );
				$modalBody[]    = HtmlTag::create( 'pre', str_replace( $this->env->uri, '', $fillTransfer->message ), ['style' => 'font-size: 10px'] );
				if( !empty( $fillTransfer->trace ) ){
					$modalBody[]	= HtmlTag::create( 'h4', 'Aufrufstapel' );
					$modalBody[]    = HtmlTag::create( 'pre', str_replace( $this->env->uri, '', $fillTransfer->trace ), ['style' => 'font-size: 10px'] );
				}
			}

			$modal	= new ModalDialog( $modalId );
			$modal->setHeading( 'Datenweitergabe an '.$targetTitle );
			$modal->setBody( join( $modalBody ) );
			$modal->setCloseButtonLabel( 'schließen' );
			$modal->setCloseButtonIconClass( 'fa fa-fw fa-close' );
			$modals[]	= $modal;

			$button		= new ModalTrigger( $modalId );
			$button->setLabel( $iconInfo )->setClass( 'btn-info btn-mini' );
		}
		$rows[]			= HtmlTag::create( 'tr', [
			HtmlTag::create( 'td', $targetTitle ),
			HtmlTag::create( 'td', $status ),
			HtmlTag::create( 'td', $button ),
		] );
	}
	$tbody	= HtmlTag::create( 'tbody', $rows );
	$panelTransfers	= HtmlTag::create( 'div', [
		HtmlTag::create( 'div', [
			HtmlTag::create( 'h3', 'Datenweitergabe' ),
			HtmlTag::create( 'div', [
				HtmlTag::create( 'table', $tbody, ['class' => 'table table-condensed'] ),
			] ),
		], ['class' => 'content-panel-inner'] ),
	], ['class' => 'content-panel'] ).join( $modals );
}

//  --  BUTTONS  --  //
$buttonList	= HtmlTag::create( 'a', $iconList.'&nbsp;zur Liste', [
	'href'	=> './manage/form/fill'.( $page ? '/'.$page : '' ),
	'class'	=> 'btn',
] );
$buttonConfirm	= HtmlTag::create( 'a', $iconCheck.'&nbsp;als bestätigt markieren', [
	'href'	=> './manage/form/fill/markAsConfirmed/'.$fill->fillId.( $page ? '&page='.$page : '' ),
	'class'	=> 'btn btn-success',
] );
if( $fill->status != Model_Form_Fill::STATUS_NEW )
	$buttonConfirm	= '';

$buttonHandled	= HtmlTag::create( 'a', $iconCheck.'&nbsp;als behandelt markieren', [
	'href'	=> './manage/form/fill/markAsHandled/'.$fill->fillId.( $page ? '&page='.$page : '' ),
	'class'	=> 'btn btn-info',
] );
if( $fill->status != Model_Form_Fill::STATUS_CONFIRMED )
	$buttonHandled	= '';

$buttonRemove	= HtmlTag::create( 'a', $iconRemove.'&nbsp;entfernen', [
	'href'		=> './manage/form/fill/remove/'.$fill->fillId.( $page ? '&page='.$page : '' ),
	'class'		=> 'btn btn-danger',
	'onclick'	=> "if(!confirm('Wirklich ?'))return false;"
] );

$buttonExport	= HtmlTag::create( 'a', $iconExport.'&nbsp;exportieren', [
	'href'		=> './manage/form/fill/export/csv/fill/'.$fill->fillId,
	'class'		=> 'btn',
] );

$buttons	= join( ' ', [$buttonList, $buttonExport, $buttonConfirm, $buttonHandled, $buttonRemove] );
$buttonbar	= HtmlTag::create( 'div', $buttons, ['class' => 'buttonbar'] );


$heading	= HtmlTag::create( 'h2', [
	HtmlTag::create( 'span', 'Eintrag: ', ['class' => 'muted'] ),
	$form->title,
] );

return HtmlTag::create( 'div', [
	$heading,
	HtmlTag::create( 'div', [
		HtmlTag::create( 'div', [
			$helperPerson->render(),
		], ['class' => 'span8'] ),
		HtmlTag::create( 'div', [
			$panelFacts,
			$panelTransfers,
		], ['class' => 'span4'] ),
	], ['class' => 'row-fluid'] ),
	$helperData->render(),
	$buttonbar,
] );


function arrayToTable( array $data ): string
{
	$list	= [];
	foreach( $data as $key => $value ){
		$list[]	= HtmlTag::create( 'tr', [
			HtmlTag::create( 'th', $key ),
			HtmlTag::create( 'td', $value ),
		] );
	}
	$tbody	= HtmlTag::create( 'tbody', $list );
	return HtmlTag::create( 'table', $tbody, ['class' => 'table table-condensed table-bordered'] );
}
