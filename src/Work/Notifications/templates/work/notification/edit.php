<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

/** @var Entity_Notification_Message $message */
/** @var array<string,array<string,int|float|string>> $words */

use CeusMedia\Bootstrap\Icon;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$w	= (object) $words['edit'];

$iconCancel	= Icon::create( 'arrow-left' );
$iconSave	= Icon::create( 'check' );

return HtmlTag::create( 'div', [
	HtmlTag::create( 'h3', $w->heading ),
	HtmlTag::create( 'div', [
		HtmlTag::create( 'form', [
			HtmlTag::create( 'div', [
				HtmlTag::create( 'div', [
					HtmlTag::create( 'label', $w->labelTitle, ),
					HtmlTag::create( 'input', NULL, [
						'type'	=> 'text',
						'name'	=> 'title',
						'id'	=> 'input_title',
						'class'	=> 'span12',
						'value'	=> htmlentities( $message->title, ENT_QUOTES, 'UTF-8' ),
					] )
				], ['class' => 'span12'] )
			], ['class' => 'row-fluid'] ),
			HtmlTag::create( 'div', [
				HtmlTag::create( 'div', [
					HtmlTag::create( 'label', $w->labelContent, ),
					HtmlTag::create( 'textarea', htmlentities( $message->content, ENT_QUOTES, 'UTF-8' ), [
						'type'	=> 'text',
						'name'	=> 'content',
						'id'	=> 'input_content',
						'class'	=> 'span12 TinyMCE',
					] )
				], ['class' => 'span12'] )
			], ['class' => 'row-fluid'] ),
			HtmlTag::create( 'div', [
				HtmlTag::create( 'a', $iconCancel.'&nbsp;'.$w->buttonCancel, [
					'href'	=> './work/notification',
					'class'	=> 'btn btn-small',
				] ),
				' ',
				HtmlTag::create( 'button', $iconSave.'&nbsp;'.$w->buttonSave, [
					'type'	=> 'submit',
					'name'	=> 'save',
					'class'	=> 'btn btn-success',
				] ),
			], ['class' => 'buttonbar'] )
		], [
			'action'	=> 'work/notification/edit/'.$message->notificationMessageId,
			'method'	=> 'post'
		] )
	], ['class' => 'content-panel-inner'] )
], ['class' => 'content-panel content-panel-form'] );

