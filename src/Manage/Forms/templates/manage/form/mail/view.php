<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;

/** @var WebEnvironment $env */
/** @var object $mail */

$modelMail	= new Model_Form_Mail( $env );

$mails		= $modelMail->getAll( [], ['title' => 'ASC'] );

$iconList	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-list'] );
$iconView	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-eye'] );
$iconEdit	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-pencil'] );
$iconSave	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-check'] );

$buttonCancel	= HtmlTag::create( 'a', $iconList.'&nbsp;zur Liste', [
	'href'	=> './manage/form/mail',
	'class'	=> 'btn',
] );
$buttonEdit	= HtmlTag::create( 'a', $iconEdit.'&nbsp;bearbeiten', [
	'href'	=> './manage/form/mail/edit/'.$mail->mailId,
	'class'	=> 'btn btn-primary',
] );
if( $mail->format == Model_Form_Mail::FORMAT_TEXT )
	$mail->content	= nl2br( $mail->content );

return HtmlTag::create( 'div', [
	HtmlTag::create( 'div', [
		HtmlTag::create( 'h2', '<span class="muted">Mail:</span> '.$mail->title ),
	] ),
	HtmlTag::create( 'br' ),
	HtmlTag::create( 'div', $mail->content, [
		'style' => 'border: 2px solid gray; padding: 2em;'
	] ),
	HtmlTag::create( 'div', [
		HtmlTag::create( 'hr' ),
		join( ' ', [$buttonCancel, $buttonEdit] ),
	] ),
] );
