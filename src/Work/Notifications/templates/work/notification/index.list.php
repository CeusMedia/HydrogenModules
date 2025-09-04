<?php

/** @var Entity_Notification_Message[] $messages */

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$table	= HtmlTag::create( 'div', 'Keine', ['class' => 'alert alert-info'] );

if( [] !== $messages ){
	$rows	= [];
	foreach( $messages as $message ){
		$row[]	= HtmlTag::create( 'tr', [
			HtmlTag::create( 'td', $message->title ),
		] );
	}
	$thead	= HtmlTag::create( 'thead', '' );
	$tbody	= HtmlTag::create( 'tbody', $rows );
	$table	= HtmlTag::create( 'table', [$thead, $tbody], ['class' => 'table table-bordered'] );
}

return HtmlTag::create( 'div', [
	HtmlTag::create( 'h3', 'Benachrichtigungen' ),
	HtmlTag::create( 'div', [
		HtmlTag::create( 'div', [
			HtmlTag::create( 'div', [
				$table,
			], ['class' => 'span12'] )
		], ['class' => 'row-fluid'] ),
		HtmlTag::create( 'div', [
		], ['class' => 'buttonbar'] )
	], ['class' => 'content-panel-inner'] )
], ['class' => 'content-panel'] );

