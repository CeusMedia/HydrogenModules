<?php

$modelMail	= new Model_Form_Mail( $env );

$mails		= $modelMail->getAll( array(), array( 'title' => 'ASC' ) );

$iconList	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-list' ) );
$iconView	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-eye' ) );
$iconEdit	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-pencil' ) );
$iconSave	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) );

$buttonCancel	= UI_HTML_Tag::create( 'a', $iconList.'&nbsp;zur Liste', array(
	'href'	=> './manage/form/mail',
	'class'	=> 'btn',
) );
$buttonEdit	= UI_HTML_Tag::create( 'a', $iconEdit.'&nbsp;bearbeiten', array(
	'href'	=> './manage/form/mail/edit/'.$mail->mailId,
	'class'	=> 'btn btn-primary',
) );
if( $mail->format == Model_Form_Mail::FORMAT_TEXT )
	$mail->content	= nl2br( $mail->content );

return UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'div', array(
		UI_HTML_Tag::create( 'h2', '<span class="muted">Mail:</span> '.$mail->title ),
	), array() ),
	UI_HTML_Tag::create( 'br' ),
	UI_HTML_Tag::create( 'div', $mail->content, array(
		'style' => 'border: 2px solid gray; padding: 2em;'
	) ),
	UI_HTML_Tag::create( 'div', array(
		UI_HTML_Tag::create( 'hr' ),
		join( ' ', array( $buttonCancel, $buttonEdit ) ),
	), array() ),
), array() );
