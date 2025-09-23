<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Bootstrap\Icon;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

/** @var CeusMedia\HydrogenFramework\Environment $env */
/** @var Entity_Notification_Message $message */
/** @var string $from */
/** @var array<string,array<string,int|float|string>> $words */

$w	= (object) $words['view'];

$iconConfirm	= Icon::create( 'check' );

$buttonCheck	= HtmlTag::create( 'button', $iconConfirm.'&nbsp;'.$w->buttonConfirm, [
	'type'	=> 'submit',
	'name'	=> 'confirm',
	'value'	=> '1',
	'class'	=> 'btn btn-success',
] );

return HtmlTag::create( 'div', [
	HtmlTag::create( 'h3', '<span class="muted">'.$w->heading.':</span>&nbsp;'.$message->title ),
	HtmlTag::create( 'div', [
		HtmlTag::create( 'form', [
			HtmlTag::create( 'div', $message->content, ['class' => ''] ),
			HtmlTag::create( 'div', $buttonCheck, ['class' => 'buttonbar'] ),
		], [
			'href'		=> './work/notification/view',
			'method'	=> 'POST',
		] ),
	], ['class' => 'content-panel-inner'] )
], ['class' => 'content-panel'] );
