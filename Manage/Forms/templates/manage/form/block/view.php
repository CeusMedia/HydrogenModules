<?php

$modelBlock	= new Model_Form_Block( $env );

$blocks		= $modelBlock->getAll( array(), array( 'title' => 'ASC' ) );

foreach( $blocks as $item ){
	if( preg_match( '/\[block_'.$item->identifier.'\]/', $block->content ) ){
		$block->content	= preg_replace( '/\[block_'.$item->identifier.'\]/', $item->content, $block->content );
	}
}

$iconList	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-list' ) );
$iconView	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-eye' ) );
$iconEdit	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-pencil' ) );
$iconSave	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) );

$buttonCancel	= UI_HTML_Tag::create( 'a', $iconList.'&nbsp;zur Liste', array(
	'href'	=> './manage/form/block',
	'class'	=> 'btn',
) );
$buttonEdit	= UI_HTML_Tag::create( 'a', $iconEdit.'&nbsp;bearbeiten', array(
	'href'	=> './manage/form/block/edit/'.$block->blockId,
	'class'	=> 'btn btn-primary',
) );

return UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'div', array(
		UI_HTML_Tag::create( 'h2', '<span class="muted">Block:</span> '.$block->title ),
		UI_HTML_Tag::create( 'div', array(
			UI_HTML_Tag::create( 'span', 'Shortcode: ' ),
			UI_HTML_Tag::create( 'tt', '[block_'.$block->identifier.']' ),
		) ),
	), array() ),
	UI_HTML_Tag::create( 'br' ),
	UI_HTML_Tag::create( 'form', $block->content, array(
		'class'	=> 'cmforms',
		'style' => 'border: 2px solid gray; padding: 2em;'
	) ),
	UI_HTML_Tag::create( 'div', array(
		UI_HTML_Tag::create( 'hr' ),
		join( ' ', array( $buttonCancel, $buttonEdit ) ),
	), array() ),
), array() );
