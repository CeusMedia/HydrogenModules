<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Bootstrap\Icon;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;

/** @var WebEnvironment $env */
/** @var Entity_Form_Block $block */
/** @var Entity_Form_Block[] $blocks */

foreach( $blocks as $item )
	if( preg_match( '/\[block_'.$item->identifier.']/', $block->content ) )
		$block->content	= preg_replace( '/\[block_'.$item->identifier.']/', $item->content, $block->content );

$iconList	= new Icon( 'list' );
$iconView	= new Icon( 'eye' );
$iconEdit	= new Icon( 'pencil' );
$iconSave	= new Icon( 'check' );

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
