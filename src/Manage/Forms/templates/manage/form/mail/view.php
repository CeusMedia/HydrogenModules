<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$modelMail	= new Model_Form_Mail( $env );

$mails		= $modelMail->getAll( [], ['title' => 'ASC'] );

$iconList	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-list'] );
$iconView	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-eye'] );
$iconEdit	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-pencil'] );
$iconSave	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-check'] );

$buttonCancel	= HtmlTag::create( 'a', $iconList.'&nbsp;zur Liste', array(
	'href'	=> './manage/form/mail',
	'class'	=> 'btn',
) );
$buttonEdit	= HtmlTag::create( 'a', $iconEdit.'&nbsp;bearbeiten', array(
	'href'	=> './manage/form/mail/edit/'.$mail->mailId,
	'class'	=> 'btn btn-primary',
) );
if( $mail->format == Model_Form_Mail::FORMAT_TEXT )
	$mail->content	= nl2br( $mail->content );

return HtmlTag::create( 'div', array(
	HtmlTag::create( 'div', array(
		HtmlTag::create( 'h2', '<span class="muted">Mail:</span> '.$mail->title ),
	), [] ),
	HtmlTag::create( 'br' ),
	HtmlTag::create( 'div', $mail->content, array(
		'style' => 'border: 2px solid gray; padding: 2em;'
	) ),
	HtmlTag::create( 'div', array(
		HtmlTag::create( 'hr' ),
		join( ' ', [$buttonCancel, $buttonEdit] ),
	), [] ),
), [] );
