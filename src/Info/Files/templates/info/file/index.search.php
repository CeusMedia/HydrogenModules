<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;

/** @var WebEnvironment $env */
/** @var array $words */
/** @var ?string $search */
/** @var int|string|NULL $folderId */

$env->getPage()->js->addScriptOnReady( 'jQuery("#input_search").focus();' );

$iconSearch		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-search'] );
$inputSearch	= HtmlTag::create( 'input', NULL, [
	'type'	=> 'search',
	'name'	=> 'search',
	'id'	=> 'input_search',
	'value'	=> htmlentities( $search ?? '', ENT_QUOTES, 'UTF-8' ),
] );
$buttonSearch	= HtmlTag::create( 'button', $iconSearch.' '.$words['search']['buttonSave'], [
	'type'		=> 'submit',
	'name'		=> 'doSearch',
	'class'		=> 'btn btn-small',
] );

return HtmlTag::create( 'div', [
	HtmlTag::create( 'h4', $words['search']['heading'] ),
	HtmlTag::create( 'div', [
		HtmlTag::create( 'form', [
			HtmlTag::create( 'label', $words['search']['labelQuery'], ['for' => 'input_search'] ),
			$inputSearch,
			HtmlTag::create( 'div', $buttonSearch, ['class' => 'buttonbar'] ),
		], [
			'action'	=> './info/file'.( $folderId ? '/'.$folderId : '' ),
			'method'	=> 'get',
		] ),
	], ['class' => 'content-panel-inner'] ),
], ['class' => 'content-panel'] );
