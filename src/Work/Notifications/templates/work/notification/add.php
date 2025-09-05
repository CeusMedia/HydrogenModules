<?php

/** @var array<string,array<string,int|float|string>> $words */

use CeusMedia\Bootstrap\Icon;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$w	= (object) $words['add'];

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
						'name'	=> 'key',
						'id'	=> 'input_key',
						'class'	=> 'span12',
					] )
				], ['class' => 'span12'] )
			], ['class' => 'row-fluid'] ),
			HtmlTag::create( 'div', [
				HtmlTag::create( 'div', [
					HtmlTag::create( 'label', $w->labelContent, ),
					HtmlTag::create( 'textarea', '', [
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
			'action'	=> 'work/notification/add',
			'method'	=> 'post'
		] )
	], ['class' => 'content-panel-inner'] )
], ['class' => 'content-panel content-panel-form'] );

