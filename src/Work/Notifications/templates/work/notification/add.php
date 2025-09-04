<?php


use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

return HtmlTag::create( 'div', [
	HtmlTag::create( 'h3', 'Add' ),
	HtmlTag::create( 'div', [
		HtmlTag::create( 'form', [
			HtmlTag::create( 'div', [
				HtmlTag::create( 'div', [
					HtmlTag::create( 'label', 'Titel', ),
					HtmlTag::create( 'input', NULL, [
						'type'	=> 'text',
						'name'	=> 'key',
						'id'	=> 'input_key',
						'class'	=> 'span12',
					] )
				], ['class' => 'span12'] )
			], ['class' => 'row-fluid'] ),
			HtmlTag::create( 'div', [
				HtmlTag::create( 'a', 'zurück', [
					'href'	=> './work/notification',
					'class'	=> 'btn btn-small',
				] ),
				HtmlTag::create( 'button', 'speichern', [
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

