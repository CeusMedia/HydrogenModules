<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;
use CeusMedia\HydrogenFramework\View;

/** @var WebEnvironment $env */
/** @var View $view */
/** @var object $message */
/** @var array $words */

$tabs	= $view->renderTabs( $env, 'message' );

$message->text	= '';
$message->html	= '';
foreach( $message->object->getParts( FALSE ) as $part ){
	if( $part instanceof \CeusMedia\Mail\Message\Part\HTML )
		$message->html	= $part->getContent();
	else if( $part instanceof \CeusMedia\Mail\Message\Part\Text )
		$message->text	= $part->getContent();
}

$fieldText	= HtmlTag::create( 'div', [
	HtmlTag::create( 'div', [
		HtmlTag::create( 'h4', 'Plain Text' ),
		xmp( $message->text, TRUE ),
	], ['class' => 'span12'] ),
], ['class' => 'row-fluid'] );

$fieldHtml	= HtmlTag::create( 'div', [
	HtmlTag::create( 'div', [
		HtmlTag::create( 'h4', 'HTML' ),
		HtmlTag::create( 'iframe', '', [
			'src'			=> './work/mail/group/message/html/'.$message->mailGroupMessageId,
			'style'			=> 'width: 100%; height: 600px; border: 1px solid gray;',
			'frameborder'	=> 0,
		] ),
	], ['class' => 'span12'] ),
], ['class' => 'row-fluid'] );

if( !$message->text )
	$fieldText	= '';
if( !$message->html )
	$fieldHtml	= '';

$iconList		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-list'] );
$buttonCancel	= HtmlTag::create( 'a', $iconList.'&nbsp;zur Liste', array(
	'href'	=> './work/mail/group/message',
	'class'	=> 'btn',
) );

$iconParse		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-refresh'] );
$buttonParse	= '';
if( $message->status == Model_Mail_Group_Message::STATUS_NEW ){
	$buttonParse	= HtmlTag::create( 'a', $iconParse.'&nbsp;noch einmal einlesen', [
		'href'		=> './work/mail/group/message/parseAgainFromRaw/'.$message->mailGroupMessageId,
		'class'		=> 'btn btn-small',
	] );
}

return $tabs.HtmlTag::create( 'div', [
	HtmlTag::create( 'h3', 'Mail: '.$message->object->getSubject() ),
	HtmlTag::create( 'div', [
		$fieldText,
		$fieldHtml,
		HtmlTag::create( 'div', join( ' ', [
			$buttonCancel,
			$buttonParse
		] ), ['class' => 'buttonbar'] ),
	], ['class' => 'content-panel-inner'] ),
], ['class' => 'content-panel'] );
