<?php

//print_m( $transferTargets );die;

$iconAdd		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-plus' ) );
$iconView		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-eye' ) );
$iconEdit		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-pencil' ) );

$iconMail		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-envelope-o' ) );
$iconReceiver	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-at' ) );
$iconTransfer	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-upload' ) );
$iconImport		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-download' ) );
$iconExchange	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-exchange' ) );

$iconsType	= array(
	Model_Form::TYPE_NORMAL		=> UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-right' ) ),
	Model_Form::TYPE_CONFIRM	=> UI_HTML_Tag::create( 'i', '', array( 'class' =>'fa fa-fw fa-check' ) ),
);

$iconsStatus	= array(
	Model_Form::STATUS_DISABLED		=> UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-remove' ) ),
	Model_Form::STATUS_NEW			=> UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-pencil' ) ),
	Model_Form::STATUS_ACTIVATED	=> UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-play' ) ),
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
	Model_Form::STATUS_DISABLED		=> UI_HTML_Tag::create( 'label', $iconsStatus[Model_Form::STATUS_DISABLED].' '.$statuses[Model_Form::STATUS_DISABLED], array( 'class' => 'label label-inverse' ) ),
	Model_Form::STATUS_NEW			=> UI_HTML_Tag::create( 'label', $iconsStatus[Model_Form::STATUS_NEW].' '.$statuses[Model_Form::STATUS_NEW], array( 'class' => 'label label-warning' ) ),
	Model_Form::STATUS_ACTIVATED	=> UI_HTML_Tag::create( 'label', $iconsStatus[Model_Form::STATUS_ACTIVATED].' '.$statuses[Model_Form::STATUS_ACTIVATED], array( 'class' => 'label label-success' ) ),
);

$listLabelsType		= array(
	Model_Form::TYPE_NORMAL		=> UI_HTML_Tag::create( 'label', $iconsType[Model_Form::TYPE_NORMAL].' '.$types[Model_Form::TYPE_NORMAL], array( 'class' => 'label' ) ),
	Model_Form::TYPE_CONFIRM	=> UI_HTML_Tag::create( 'label', $iconsType[Model_Form::TYPE_CONFIRM].' '.$types[Model_Form::TYPE_CONFIRM], array( 'class' => 'label' ) ),
);

$modelForm	= new Model_Form( $env );
$modelMail	= new Model_Form_Mail( $env );

$rows		= [];
foreach( $forms as $form ){
	$customerMail	= UI_HTML_Tag::create( 'small', '- keine Empfänger-E-Mail zugewiesen -', array( 'class' => "muted" ) );
	if( $form->customerMailId > 0 ){
		$mail			= $modelMail->get( $form->customerMailId );
		$customerMail	= UI_HTML_Tag::create( 'small', array(
			UI_HTML_Tag::create( 'a', $iconMail.'&nbsp;'.$mail->title, array( 'href' => './manage/form/mail/edit/'.$form->customerMailId.'?from=manage/form'.( $page ? '/'.$page : '' ) ) ),
		) );
	}

	$managerMail	= UI_HTML_Tag::create( 'em', '- keine -', array( 'class' => "muted" ) );
	if( $form->managerMailId > 0 ){
		$mail			= $modelMail->get( $form->managerMailId );
		$managerMail	= UI_HTML_Tag::create( 'small', array(
			UI_HTML_Tag::create( 'a', $iconMail.'&nbsp;'.$mail->title, array( 'href' => './manage/form/mail/edit/'.$form->managerMailId.'?from=manage/form'.( $page ? '/'.$page : '' ) ) ),
		) );
	}

	$linkEdit	= UI_HTML_Tag::create( 'a', $form->title, array( 'href' => './manage/form/edit/'.$form->formId ) );
	$receivers	= [];
	if( strlen( trim( $form->receivers ) ) ){
		foreach( preg_split( '/\s*,\s*/', $form->receivers ) as $receiver )
			if( preg_match( '/^\S+@\S+$/', $receiver ) )
				$receivers[]	= preg_replace( '/^(\S+)@\S+$/', '\\1', $receiver );
	}
	$receivers	= $receivers ? $iconReceiver.'&nbsp;'.join( ', ', $receivers ) : '-';
	$receivers	= UI_HTML_Tag::create( 'small', $receivers );

	$transfers	= '';
	if( count( $form->transfers ) ){
		$list	= [];
		foreach( $form->transfers as $transfer ){
			$list[]	= $transferTargets[$transfer->formTransferTargetId]->title;
		}
		$list		= 'Transfers:'.PHP_EOL.' - '.implode( PHP_EOL.' - ', $list );
		$label		= count( $form->transfers );
		$transfers	= UI_HTML_Tag::create( 'span', $iconTransfer.'&nbsp;'.$label, ['class' => 'label label-info', 'title' => $list] );
	}

	$importers	= '';
	if( count( $form->imports ) ){
		$list	= [];
		foreach( $form->imports as $import ){
			$list[]	= $import->title;
		}
		$list		= 'Imports:'.PHP_EOL.' - '.implode( PHP_EOL.' - ', $list );
		$label		= count( $form->imports );
		$importers	= UI_HTML_Tag::create( 'span', $iconImport.'&nbsp;'.$label, ['class' => 'label label-info', 'title' => $list] );
	}

	$rows[]	= UI_HTML_Tag::create( 'tr', array(
		UI_HTML_Tag::create( 'td', UI_HTML_Tag::create( 'small', $form->formId ), array( 'style' => 'text-align: right' ) ),
		UI_HTML_Tag::create( 'td', $linkEdit.'<br/>'.$customerMail, array( 'data-class' => 'data-autocut' ) ),
		UI_HTML_Tag::create( 'td', $listLabelsStatus[$form->status].'<br/>'.$listLabelsType[$form->type] ),
		UI_HTML_Tag::create( 'td', $receivers.'<br/>'.$managerMail, array( 'data-class' => 'autocut' ) ),
		UI_HTML_Tag::create( 'td', $importers.'<br/>'.$transfers ),
	) );
}
$colgroup	= UI_HTML_Elements::ColumnGroup( '40px', '', '160px', '260px', '40px' );
$thead		= UI_HTML_Tag::create( 'thead', UI_HTML_Tag::create( 'tr', array(
	UI_HTML_Tag::create( 'th', 'ID', array( 'style' => 'text-align: right' ) ),
	UI_HTML_Tag::create( 'th', 'Titel / E-Mail an Absender' ),
	UI_HTML_Tag::create( 'th', 'Typ / Zustand' ),
	UI_HTML_Tag::create( 'th', 'Empfänger und -E-Mail' ),
	UI_HTML_Tag::create( 'th', UI_HTML_Tag::create( 'acronym', $iconExchange, ['title' => 'Datenübertragung'] ) ),
) ) );
$tbody		= UI_HTML_Tag::create( 'tbody', $rows );
$table		= UI_HTML_Tag::create( 'table', array( $colgroup, $thead, $tbody ), array( 'class' => 'table table-fixed table-striped table-condensed' ) );

$heading	= UI_HTML_Tag::create( 'h2', 'Formulare' );
$linkAdd	= UI_HTML_Tag::create( 'a', $iconAdd.'&nbsp;neues Formular', array(
	'href'	=> './manage/form/add',
	'class'	=> 'btn btn-success',
) );

$pagination	= '';
if( $pages > 1 ){
	\CeusMedia\Bootstrap\Icon::$defaultSet	= 'fontawesome';
	$pagination	= new \CeusMedia\Bootstrap\Nav\PageControl( './manage/form/', $page, $pages );
	$pagination->patternUrl	= '%s';
}
$buttonbar	= UI_HTML_Tag::create( 'div', join( '&nbsp;', array( $linkAdd, $pagination ) ), array( 'class' => 'buttonbar' ) );


return UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'h3', 'Formulare' ),
	UI_HTML_Tag::create( 'div', array(
		$table,
		$buttonbar,
	), array( 'class' => 'content-panel-inner' ) ),
), array( 'class' => 'content-panel' ) );
