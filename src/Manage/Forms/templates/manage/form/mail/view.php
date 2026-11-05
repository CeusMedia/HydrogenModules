<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Bootstrap\Icon;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;

/** @var WebEnvironment $env */
/** @var Entity_Form_Mail $mail */
/** @var array<string,array<string,string>> $words */

$w	= (object) $words['view'];

$modelMail	= new Model_Form_Mail( $env );

$iconList	= new Icon( 'list' );
$iconView	= new Icon( 'eye' );
$iconEdit	= new Icon( 'pencil' );
$iconSave	= new Icon( 'check' );

$buttonCancel	= HtmlTag::create( 'a', $iconList.'&nbsp;'.$w->buttonList, [
	'href'	=> './manage/form/mail',
	'class'	=> 'btn',
] );
$buttonEdit	= HtmlTag::create( 'a', $iconEdit.'&nbsp;'.$w->buttonEdit, [
	'href'	=> './manage/form/mail/edit/'.$mail->mailId,
	'class'	=> 'btn btn-primary',
] );
if( $mail->format == Model_Form_Mail::FORMAT_TEXT )
	$mail->content	= nl2br( $mail->content );

return HtmlTag::create( 'div', [
	HtmlTag::create( 'div', [
		HtmlTag::create( 'h2', '<span class="muted">'.$w->headingPrefix.'</span> '.$mail->title ),
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
