<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

/** @var array<string> $rights */
/** @var array<int|string,string> $words */
/** @var int|string $folderId */

if( !in_array( 'addFolder', $rights ) )
	return '';

$iconAdd	= HtmlTag::create( 'i', NULL, ['class' => 'icon-plus icon-white'] );

return HtmlTag::create( 'div', [
	HtmlTag::create( 'h4', $words['addFolder']['heading'] ),
	HtmlTag::create( 'div', [
		HtmlTag::create( 'div', [
			HtmlTag::create( 'div', [
				HtmlTag::create( 'div', [
					HtmlTag::create( 'label', [
						$words['addFolder']['labelFolder']
					], ['for' => 'input_folder'] ),
					HtmlTag::create( 'input', NULL, [
						'type'		=> 'text',
						'name'		=> 'folder',
						'id'		=> 'input_folder',
						'required'	=> 'required',
						'value'		=> $request->get( 'input_folder' )
					] ),
				], ['class' => 'span12'] )
			], ['class' => 'row-fluid'] ),
			HtmlTag::create( 'div', [
				HtmlTag::create( 'button', [
					$iconAdd.' '.$words['addFolder']['buttonSave'],
				], [
					'type'	=> 'submit',
					'name'	=> 'save',
					'class'	=> 'btn btn-small btn-success',
					] )
			], ['class' => 'buttonbar'] )
		], ['action' => './manage/download/addFolder/'.$folderId, 'method' => 'post'] )
	], ['class' => 'content-panel-inner'] )
], ['class' => 'content-panel'] );
