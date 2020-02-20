<?php
$tabs	= $view->renderTabs( $env, 'message' );

$message->text	= '';
$message->html	= '';
foreach( $message->object->getParts( FALSE ) as $part ){
	if( $part instanceof \CeusMedia\Mail\Message\Part\HTML )
		$message->html	= $part->getContent();
	else if( $part instanceof \CeusMedia\Mail\Message\Part\Text )
		$message->text	= $part->getContent();
}
$fieldText	= UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'div', array(
		UI_HTML_Tag::create( 'h4', 'Plain Text' ),
		xmp( $message->text, TRUE ),
	), array( 'class' => 'span12' ) ),
), array( 'class' => 'row-fluid' ) );

$fieldHtml	= UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'div', array(
		UI_HTML_Tag::create( 'h4', 'HTML' ),
		UI_HTML_Tag::create( 'iframe', $message->html, array(
			'style'			=> 'width: 100%; height: 600px; border: 1px solid gray;',
			'frameborder'	=> 0,
		) ),
	), array( 'class' => 'span12' ) ),
), array( 'class' => 'row-fluid' ) );

if( !$message->text )
	$fieldText	= '';
if( !$message->html )
	$fieldHtml	= '';

$iconList		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-list' ) );
$buttonCancel	= UI_HTML_Tag::create( 'a', $iconList.'&nbsp;zur Liste', array(
	'href'	=> './work/mail/group/message',
	'class'	=> 'btn',
) );

$iconParse		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-refresh' ) );
$buttonParse	= '';
if( $message->status == Model_Mail_Group_Message::STATUS_NEW ){
	$buttonParse	= UI_HTML_Tag::create( 'a', $iconParse.'&nbsp;noch einmal einlesen', array(
		'href'		=> './work/mail/group/message/parseAgainFromRaw/'.$message->mailGroupMessageId,
		'class'		=> 'btn btn-small',
	) );
}

return $tabs.UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'h3', 'Mail: '.$message->object->getSubject() ),
	UI_HTML_Tag::create( 'div', array(
		$fieldText,
		$fieldHtml,
		UI_HTML_Tag::create( 'div', join( ' ', array(
			$buttonCancel,
			$buttonParse
		) ), array( 'class' => 'buttonbar' ) ),
	), array( 'class' => 'content-panel-inner' ) ),
), array( 'class' => 'content-panel' ) );
