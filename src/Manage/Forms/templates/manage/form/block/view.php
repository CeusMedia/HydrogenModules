<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

/** @var \CeusMedia\HydrogenFramework\Environment $env */
/** @var object $block */

$modelBlock	= new Model_Form_Block( $env );

$blocks		= $modelBlock->getAll( [], ['title' => 'ASC'] );

foreach( $blocks as $item ){
	if( preg_match( '/\[block_'.$item->identifier.'\]/', $block->content ) ){
		$block->content	= preg_replace( '/\[block_'.$item->identifier.'\]/', $item->content, $block->content );
	}
}

$iconList	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-list'] );
$iconView	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-eye'] );
$iconEdit	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-pencil'] );
$iconSave	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-check'] );

$buttonCancel	= HtmlTag::create( 'a', $iconList.'&nbsp;zur Liste', [
	'href'	=> './manage/form/block',
	'class'	=> 'btn',
] );
$buttonEdit	= HtmlTag::create( 'a', $iconEdit.'&nbsp;bearbeiten', [
	'href'	=> './manage/form/block/edit/'.$block->blockId,
	'class'	=> 'btn btn-primary',
] );

return HtmlTag::create( 'div', [
	HtmlTag::create( 'div', [
		HtmlTag::create( 'h2', '<span class="muted">Block:</span> '.$block->title ),
		HtmlTag::create( 'div', [
			HtmlTag::create( 'span', 'Shortcode: ' ),
			HtmlTag::create( 'tt', '[block_'.$block->identifier.']' ),
		] ),
	] ),
	HtmlTag::create( 'br' ),
	HtmlTag::create( 'form', $block->content, [
		'class'	=> 'cmforms',
		'style' => 'border: 2px solid gray; padding: 2em;'
	] ),
	HtmlTag::create( 'div', [
		HtmlTag::create( 'hr' ),
		join( ' ', [$buttonCancel, $buttonEdit] ),
	] ),
] );
