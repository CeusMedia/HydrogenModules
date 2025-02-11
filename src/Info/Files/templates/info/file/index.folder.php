<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;

/** @var WebEnvironment $env */
/** @var array $words */
/** @var int|string $folderId */
/** @var array $rights */

if( !in_array( 'addFolder', $rights ) )
	return '';

$iconAdd		= HtmlTag::create( 'i', '', ['class' => 'icon-plus icon-white'] );
$inputFolder	= HtmlTag::create( 'input', NULL, [
	'type'		=> 'text',
	'name'		=> 'folder',
	'id'		=> 'input_folder',
	'required'	=> 'required',
	'value'		=> htmlentities( $env->getRequest()->get( 'input_folder', '' ), HTML_ENTITIES, 'UTF-8' ),
] );

$buttonSave		= HtmlTag::create( 'button', $iconAdd.' '.$words['addFolder']['buttonSave'], [
	'type'		=> 'submit',
	'name'		=> 'save',
	'class'		=> 'btn btn-small btn-success'
] );

return HtmlTag::create( 'div', [
	HtmlTag::create( 'h4', $words['addFolder']['heading'] ),
	HtmlTag::create( 'div', [
		HtmlTag::create( 'form', [
			HtmlTag::create( 'div', [
				HtmlTag::create( 'div', [
					HtmlTag::create( 'label', $words['addFolder']['labelFolder'], ['for' => 'input_folder'] ),
					$inputFolder,
				], ['class' => 'span12'] ),
			], ['class' => 'row-fluid'] ),
			HtmlTag::create( 'div', [
				$buttonSave,
			], ['class' => 'buttonbar'] ),
		], [
			'action'	=> './info/file/addFolder/'.$folderId,
			'method'	=> 'post',
		] ),
	], ['class' => 'content-panel-inner'] ),
], ['class' => 'content-panel'] );
