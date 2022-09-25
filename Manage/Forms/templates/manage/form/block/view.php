<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$modelBlock	= new Model_Form_Block( $env );

$blocks		= $modelBlock->getAll( array(), array( 'title' => 'ASC' ) );

foreach( $blocks as $item ){
	if( preg_match( '/\[block_'.$item->identifier.'\]/', $block->content ) ){
		$block->content	= preg_replace( '/\[block_'.$item->identifier.'\]/', $item->content, $block->content );
	}
}

$iconList	= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-list' ) );
$iconView	= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-eye' ) );
$iconEdit	= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-pencil' ) );
$iconSave	= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) );

$buttonCancel	= HtmlTag::create( 'a', $iconList.'&nbsp;zur Liste', array(
	'href'	=> './manage/form/block',
	'class'	=> 'btn',
) );
$buttonEdit	= HtmlTag::create( 'a', $iconEdit.'&nbsp;bearbeiten', array(
	'href'	=> './manage/form/block/edit/'.$block->blockId,
	'class'	=> 'btn btn-primary',
) );

return HtmlTag::create( 'div', array(
	HtmlTag::create( 'div', array(
		HtmlTag::create( 'h2', '<span class="muted">Block:</span> '.$block->title ),
		HtmlTag::create( 'div', array(
			HtmlTag::create( 'span', 'Shortcode: ' ),
			HtmlTag::create( 'tt', '[block_'.$block->identifier.']' ),
		) ),
	), array() ),
	HtmlTag::create( 'br' ),
	HtmlTag::create( 'form', $block->content, array(
		'class'	=> 'cmforms',
		'style' => 'border: 2px solid gray; padding: 2em;'
	) ),
	HtmlTag::create( 'div', array(
		HtmlTag::create( 'hr' ),
		join( ' ', array( $buttonCancel, $buttonEdit ) ),
	), array() ),
), array() );
