<?php

use CeusMedia\Bootstrap\Icon;
use CeusMedia\Bootstrap\Nav\PageControl;
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;

/** @var WebEnvironment $env */
/** @var \CeusMedia\HydrogenFramework\View $view */
/** @var array $transferTargets */
/** @var array $forms */
/** @var int $page */
/** @var int $pages */

//print_m( $transferTargets );die;

/* In Future
Icon::$defaultSet	= 'fontawesome';
Icon::$defaultSize	= ['fixed'];

$iconAdd		= new Icon( 'plus' );
*/

$iconAdd		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-plus'] );
$iconView		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-eye'] );
$iconEdit		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-pencil'] );

$iconMail		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-envelope-o'] );
$iconReceiver	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-at'] );
$iconTransfer	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-upload'] );
$iconImport		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-download'] );
$iconExchange	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-exchange'] );

$iconsType		= [
	Model_Form::TYPE_NORMAL		=> HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-arrow-right'] ),
	Model_Form::TYPE_CONFIRM	=> HtmlTag::create( 'i', '', ['class' =>'fa fa-fw fa-check'] ),
];

$iconsStatus	= [
	Model_Form::STATUS_DISABLED		=> HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-remove'] ),
	Model_Form::STATUS_NEW			=> HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-pencil'] ),
	Model_Form::STATUS_ACTIVATED	=> HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-play'] ),
];

$statuses	= [
	-1		=> 'deaktiviert',
	0		=> 'in Arbeit',
	1		=> 'aktiviert',
];
$types		= [
	0		=> 'direkter Versand',
	1		=> 'Double-Opt-In',
];

$listLabelsStatus	= [
	Model_Form::STATUS_DISABLED		=> HtmlTag::create( 'label', $iconsStatus[Model_Form::STATUS_DISABLED].' '.$statuses[Model_Form::STATUS_DISABLED], ['class' => 'label label-inverse'] ),
	Model_Form::STATUS_NEW			=> HtmlTag::create( 'label', $iconsStatus[Model_Form::STATUS_NEW].' '.$statuses[Model_Form::STATUS_NEW], ['class' => 'label label-warning'] ),
	Model_Form::STATUS_ACTIVATED	=> HtmlTag::create( 'label', $iconsStatus[Model_Form::STATUS_ACTIVATED].' '.$statuses[Model_Form::STATUS_ACTIVATED], ['class' => 'label label-success'] ),
];

$listLabelsType		= [
	Model_Form::TYPE_NORMAL		=> HtmlTag::create( 'label', $iconsType[Model_Form::TYPE_NORMAL].' '.$types[Model_Form::TYPE_NORMAL], ['class' => 'label'] ),
	Model_Form::TYPE_CONFIRM	=> HtmlTag::create( 'label', $iconsType[Model_Form::TYPE_CONFIRM].' '.$types[Model_Form::TYPE_CONFIRM], ['class' => 'label'] ),
];

$modelForm	= new Model_Form( $env );
$modelMail	= new Model_Form_Mail( $env );

$rows		= [];
foreach( $forms as $form ){
	$customerMail	= HtmlTag::create( 'small', '- keine Empfänger-E-Mail zugewiesen -', ['class' => "muted"] );
	if( $form->customerMailId > 0 ){
		$mail			= $modelMail->get( $form->customerMailId );
		$customerMail	= HtmlTag::create( 'small', [
			HtmlTag::create( 'a', $iconMail.'&nbsp;'.$mail->title, ['href' => './manage/form/mail/edit/'.$form->customerMailId.'?from=manage/form'.( $page ? '/'.$page : '' )] ),
		] );
	}

	$managerMail	= HtmlTag::create( 'em', '- keine -', ['class' => "muted"] );
	if( $form->managerMailId > 0 ){
		$mail			= $modelMail->get( $form->managerMailId );
		$managerMail	= HtmlTag::create( 'small', [
			HtmlTag::create( 'a', $iconMail.'&nbsp;'.$mail->title, ['href' => './manage/form/mail/edit/'.$form->managerMailId.'?from=manage/form'.( $page ? '/'.$page : '' )] ),
		] );
	}

	$linkEdit	= HtmlTag::create( 'a', $form->title, ['href' => './manage/form/edit/'.$form->formId] );
	$receivers	= [];
	if( strlen( trim( $form->receivers ) ) ){
		foreach( preg_split( '/\s*,\s*/', $form->receivers ) as $receiver )
			if( preg_match( '/^\S+@\S+$/', $receiver ) )
				$receivers[]	= preg_replace( '/^(\S+)@\S+$/', '\\1', $receiver );
	}
	$receivers	= $receivers ? $iconReceiver.'&nbsp;'.join( ', ', $receivers ) : '-';
	$receivers	= HtmlTag::create( 'small', $receivers );

	$transfers	= '';
	if( count( $form->transfers ) ){
		$list	= [];
		foreach( $form->transfers as $transfer ){
			$list[]	= $transferTargets[$transfer->formTransferTargetId]->title;
		}
		$list		= 'Transfers:'.PHP_EOL.' - '.implode( PHP_EOL.' - ', $list );
		$label		= count( $form->transfers );
		$transfers	= HtmlTag::create( 'span', $iconTransfer.'&nbsp;'.$label, ['class' => 'label label-info', 'title' => $list] );
	}

	$importers	= '';
	if( count( $form->imports ) ){
		$list	= [];
		foreach( $form->imports as $import ){
			$list[]	= $import->title;
		}
		$list		= 'Imports:'.PHP_EOL.' - '.implode( PHP_EOL.' - ', $list );
		$label		= count( $form->imports );
		$importers	= HtmlTag::create( 'span', $iconImport.'&nbsp;'.$label, ['class' => 'label label-info', 'title' => $list] );
	}

	$rows[]	= HtmlTag::create( 'tr', [
		HtmlTag::create( 'td', HtmlTag::create( 'small', $form->formId ), ['style' => 'text-align: right'] ),
		HtmlTag::create( 'td', $linkEdit.'<br/>'.$customerMail, ['data-class' => 'data-autocut'] ),
		HtmlTag::create( 'td', $listLabelsStatus[$form->status].'<br/>'.$listLabelsType[$form->type] ),
		HtmlTag::create( 'td', $receivers.'<br/>'.$managerMail, ['data-class' => 'autocut'] ),
		HtmlTag::create( 'td', $importers.'<br/>'.$transfers ),
	] );
}
$colgroup	= HtmlElements::ColumnGroup( '40px', '', '160px', '260px', '40px' );
$thead		= HtmlTag::create( 'thead', HtmlTag::create( 'tr', [
	HtmlTag::create( 'th', 'ID', ['style' => 'text-align: right'] ),
	HtmlTag::create( 'th', 'Titel / E-Mail an Absender' ),
	HtmlTag::create( 'th', 'Typ / Zustand' ),
	HtmlTag::create( 'th', 'Empfänger und -E-Mail' ),
	HtmlTag::create( 'th', HtmlTag::create( 'acronym', $iconExchange, ['title' => 'Datenübertragung'] ) ),
] ) );
$tbody		= HtmlTag::create( 'tbody', $rows );
$table		= HtmlTag::create( 'table', [$colgroup, $thead, $tbody], ['class' => 'table table-fixed table-striped table-condensed'] );

$heading	= HtmlTag::create( 'h2', 'Formulare' );
$linkAdd	= HtmlTag::create( 'a', $iconAdd.'&nbsp;neues Formular', [
	'href'	=> './manage/form/add',
	'class'	=> 'btn btn-success',
] );

$pagination	= '';
if( $pages > 1 ){
	Icon::$defaultSet	= 'fontawesome';
	$pagination	= new PageControl( './manage/form/', $page, $pages );
	$pagination->patternUrl	= '%s';
}
$buttonbar	= HtmlTag::create( 'div', join( '&nbsp;', [$linkAdd, $pagination] ), ['class' => 'buttonbar'] );

return HtmlTag::create( 'div', [
	HtmlTag::create( 'h3', 'Formulare' ),
	HtmlTag::create( 'div', [
		$table,
		$buttonbar,
	], ['class' => 'content-panel-inner'] ),
], ['class' => 'content-panel'] );
