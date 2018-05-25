<?php

$iconAdd		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-plus' ) );
$iconView		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-eye' ) );
$iconEdit		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-pencil' ) );

$iconMail		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-envelope-o' ) );
$iconReceiver	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-at' ) );

$statuses	= array(
	-1		=> 'deaktiviert',
	0		=> 'in Arbeit',
	1		=> 'aktiviert',
);
$types		= array(
	0		=> 'direkter Versand',
	1		=> 'mit Double-Opt-In',
);

$statuses	= array(
	Model_Form::STATUS_DISABLED		=> UI_HTML_Tag::create( 'label', $statuses[Model_Form::STATUS_DISABLED], array( 'class' => 'label label-inverse' ) ),
	Model_Form::STATUS_NEW			=> UI_HTML_Tag::create( 'label', $statuses[Model_Form::STATUS_NEW], array( 'class' => 'label label-warning' ) ),
	Model_Form::STATUS_ACTIVATED	=> UI_HTML_Tag::create( 'label', $statuses[Model_Form::STATUS_ACTIVATED], array( 'class' => 'label label-success' ) ),
);

$modelForm	= new Model_Form( $env );
$modelMail	= new Model_Form_Mail( $env );

$rows		= array();
foreach( $forms as $form ){
//	$linkView	= UI_HTML_Tag::create( 'a', 'anzeigen', array( 'href' => './?action=form_view&id='.$form->formId.'&test', 'class' => 'btn btn-small' ) );

	$mail	= UI_HTML_Tag::create( 'em', '- keine -', array( 'class' => "muted" ) );
	if( $form->mailId > 0 ){
		$mail	= $modelMail->get( $form->mailId );
		$mail	= UI_HTML_Tag::create( 'small', /*$iconMail.'&nbsp;'.*/$mail->title );
	}

	$linkEdit	= UI_HTML_Tag::create( 'a', $form->title, array( 'href' => './manage/form/edit/'.$form->formId ) );
	$receivers	= array();
	if( strlen( trim( $form->receivers ) ) ){
		foreach( preg_split( '/\s*,\s*/', $form->receivers ) as $receiver )
			if( preg_match( '/^\S+@\S+$/', $receiver ) )
				$receivers[]	= preg_replace( '/^(\S+)@\S+$/', '\\1', $receiver );
	}
	$receivers	= join( ', ', $receivers );
	$rows[]	= UI_HTML_Tag::create( 'tr', array(
		UI_HTML_Tag::create( 'td', UI_HTML_Tag::create( 'small', $form->formId ) ),
		UI_HTML_Tag::create( 'td', $linkEdit ),
		UI_HTML_Tag::create( 'td', UI_HTML_Tag::create( 'small', $types[$form->type] ) ),
		UI_HTML_Tag::create( 'td', $statuses[$form->status] ),
		UI_HTML_Tag::create( 'td', UI_HTML_Tag::create( 'small', $receivers ) ),//$linkView ),
		UI_HTML_Tag::create( 'td', $mail ),
	) );
/*	$type		= UI_HTML_Tag::create( 'small', $types[$form->type] );
	$receivers	= UI_HTML_Tag::create( 'small', $iconReceiver.'&nbsp;'.$receivers );
	$rows[]	= UI_HTML_Tag::create( 'tr', array(
		UI_HTML_Tag::create( 'td', UI_HTML_Tag::create( 'small', $form->formId ) ),
		UI_HTML_Tag::create( 'td', $linkEdit ),
		UI_HTML_Tag::create( 'td', $type ),
		UI_HTML_Tag::create( 'td', $statuses[$form->status] ),
		UI_HTML_Tag::create( 'td', $mail.'<br/>'.$receivers ),
	) );*/

}
$colgroup	= UI_HTML_Elements::ColumnGroup( '40px', '', '140px', '120px', '180', '260px' );
//$colgroup	= UI_HTML_Elements::ColumnGroup( '40px', '', '180px', '140px', '240px' );
$thead		= UI_HTML_Tag::create( 'thead', UI_HTML_Elements::TableHeads( array( 'ID', 'Titel', 'Typ', 'Zustand', 'Empfänger', 'Mail' ) ) );
//$thead		= UI_HTML_Tag::create( 'thead', UI_HTML_Elements::TableHeads( array( 'ID', 'Titel', 'Typ', 'Zustand', 'Mail/Empfänger' ) ) );
$tbody		= UI_HTML_Tag::create( 'tbody', $rows );
$table		= UI_HTML_Tag::create( 'table', array( $colgroup, $thead, $tbody ), array( 'class' => 'table table-fixed table-striped table-condensed' ) );

$heading	= UI_HTML_Tag::create( 'h2', 'Formulare' );
$linkAdd	= UI_HTML_Tag::create( 'a', $iconAdd.'&nbsp;neues Formular', array(
	'href'	=> './manage/form/add',
	'class'	=> 'btn btn-success',
) );
return $heading.$table.$linkAdd;
