<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

//print_m( $transferTargets );die;

$iconAdd		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-plus' ) );
$iconView		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-eye' ) );
$iconEdit		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-pencil' ) );

$iconMail		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-envelope-o' ) );
$iconReceiver	= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-at' ) );
$iconTransfer	= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-upload' ) );
$iconImport		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-download' ) );
$iconExchange	= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-exchange' ) );

$iconsType	= array(
	Model_Form::TYPE_NORMAL		=> HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-right' ) ),
	Model_Form::TYPE_CONFIRM	=> HtmlTag::create( 'i', '', array( 'class' =>'fa fa-fw fa-check' ) ),
);

$iconsStatus	= array(
	Model_Form::STATUS_DISABLED		=> HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-remove' ) ),
	Model_Form::STATUS_NEW			=> HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-pencil' ) ),
	Model_Form::STATUS_ACTIVATED	=> HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-play' ) ),
);

$statuses	= array(
	-1		=> 'deaktiviert',
	0		=> 'in Arbeit',
	1		=> 'aktiviert',
);
$types		= array(
	0		=> 'direkter Versand',
	1		=> 'Double-Opt-In',
);

$listLabelsStatus	= array(
	Model_Form::STATUS_DISABLED		=> HtmlTag::create( 'label', $iconsStatus[Model_Form::STATUS_DISABLED].' '.$statuses[Model_Form::STATUS_DISABLED], array( 'class' => 'label label-inverse' ) ),
	Model_Form::STATUS_NEW			=> HtmlTag::create( 'label', $iconsStatus[Model_Form::STATUS_NEW].' '.$statuses[Model_Form::STATUS_NEW], array( 'class' => 'label label-warning' ) ),
	Model_Form::STATUS_ACTIVATED	=> HtmlTag::create( 'label', $iconsStatus[Model_Form::STATUS_ACTIVATED].' '.$statuses[Model_Form::STATUS_ACTIVATED], array( 'class' => 'label label-success' ) ),
);

$listLabelsType		= array(
	Model_Form::TYPE_NORMAL		=> HtmlTag::create( 'label', $iconsType[Model_Form::TYPE_NORMAL].' '.$types[Model_Form::TYPE_NORMAL], array( 'class' => 'label' ) ),
	Model_Form::TYPE_CONFIRM	=> HtmlTag::create( 'label', $iconsType[Model_Form::TYPE_CONFIRM].' '.$types[Model_Form::TYPE_CONFIRM], array( 'class' => 'label' ) ),
);

$modelForm	= new Model_Form( $env );
$modelMail	= new Model_Form_Mail( $env );

$rows		= [];
foreach( $forms as $form ){
	$customerMail	= HtmlTag::create( 'small', '- keine Empfänger-E-Mail zugewiesen -', array( 'class' => "muted" ) );
	if( $form->customerMailId > 0 ){
		$mail			= $modelMail->get( $form->customerMailId );
		$customerMail	= HtmlTag::create( 'small', array(
			HtmlTag::create( 'a', $iconMail.'&nbsp;'.$mail->title, array( 'href' => './manage/form/mail/edit/'.$form->customerMailId.'?from=manage/form'.( $page ? '/'.$page : '' ) ) ),
		) );
	}

	$managerMail	= HtmlTag::create( 'em', '- keine -', array( 'class' => "muted" ) );
	if( $form->managerMailId > 0 ){
		$mail			= $modelMail->get( $form->managerMailId );
		$managerMail	= HtmlTag::create( 'small', array(
			HtmlTag::create( 'a', $iconMail.'&nbsp;'.$mail->title, array( 'href' => './manage/form/mail/edit/'.$form->managerMailId.'?from=manage/form'.( $page ? '/'.$page : '' ) ) ),
		) );
	}

	$linkEdit	= HtmlTag::create( 'a', $form->title, array( 'href' => './manage/form/edit/'.$form->formId ) );
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

	$rows[]	= HtmlTag::create( 'tr', array(
		HtmlTag::create( 'td', HtmlTag::create( 'small', $form->formId ), array( 'style' => 'text-align: right' ) ),
		HtmlTag::create( 'td', $linkEdit.'<br/>'.$customerMail, array( 'data-class' => 'data-autocut' ) ),
		HtmlTag::create( 'td', $listLabelsStatus[$form->status].'<br/>'.$listLabelsType[$form->type] ),
		HtmlTag::create( 'td', $receivers.'<br/>'.$managerMail, array( 'data-class' => 'autocut' ) ),
		HtmlTag::create( 'td', $importers.'<br/>'.$transfers ),
	) );
}
$colgroup	= UI_HTML_Elements::ColumnGroup( '40px', '', '160px', '260px', '40px' );
$thead		= HtmlTag::create( 'thead', HtmlTag::create( 'tr', array(
	HtmlTag::create( 'th', 'ID', array( 'style' => 'text-align: right' ) ),
	HtmlTag::create( 'th', 'Titel / E-Mail an Absender' ),
	HtmlTag::create( 'th', 'Typ / Zustand' ),
	HtmlTag::create( 'th', 'Empfänger und -E-Mail' ),
	HtmlTag::create( 'th', HtmlTag::create( 'acronym', $iconExchange, ['title' => 'Datenübertragung'] ) ),
) ) );
$tbody		= HtmlTag::create( 'tbody', $rows );
$table		= HtmlTag::create( 'table', array( $colgroup, $thead, $tbody ), array( 'class' => 'table table-fixed table-striped table-condensed' ) );

$heading	= HtmlTag::create( 'h2', 'Formulare' );
$linkAdd	= HtmlTag::create( 'a', $iconAdd.'&nbsp;neues Formular', array(
	'href'	=> './manage/form/add',
	'class'	=> 'btn btn-success',
) );

$pagination	= '';
if( $pages > 1 ){
	\CeusMedia\Bootstrap\Icon::$defaultSet	= 'fontawesome';
	$pagination	= new \CeusMedia\Bootstrap\Nav\PageControl( './manage/form/', $page, $pages );
	$pagination->patternUrl	= '%s';
}
$buttonbar	= HtmlTag::create( 'div', join( '&nbsp;', array( $linkAdd, $pagination ) ), array( 'class' => 'buttonbar' ) );


return HtmlTag::create( 'div', array(
	HtmlTag::create( 'h3', 'Formulare' ),
	HtmlTag::create( 'div', array(
		$table,
		$buttonbar,
	), array( 'class' => 'content-panel-inner' ) ),
), array( 'class' => 'content-panel' ) );
