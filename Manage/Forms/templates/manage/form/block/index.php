<?php

$iconAdd	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-plus' ) );
$iconView	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-eye' ) );
$iconEdit	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-pencil' ) );

$modelBlock	= new Model_Form_Block( $env );

$rows		= array();
foreach( $modelBlock->getAll( array(), array( 'title' => 'ASC' ) ) as $block ){
	$linkView	= UI_HTML_Tag::create( 'a', $iconView.'&nbsp;anzeigen', array(
		'href'	=> './manage/form/block/view/'.$block->blockId,
		'class'	=> 'btn btn-mini btn-info',
	) );
	$linkEdit	= UI_HTML_Tag::create( 'a', $block->title, array( 'href' => './manage/form/block/edit/'.$block->blockId ) );
	$rows[]	= UI_HTML_Tag::create( 'tr', array(
		UI_HTML_Tag::create( 'td', $linkEdit ),
		UI_HTML_Tag::create( 'td', '<small><tt>[block_'.$block->identifier.']</tt></small>' ),
		UI_HTML_Tag::create( 'td', $linkView ),
	) );
}
$colgroup	= UI_HTML_Elements::ColumnGroup( '', '40%', '120px' );
$thead		= UI_HTML_Tag::create( 'thead', UI_HTML_Elements::TableHeads( array( 'Titel', 'Shortcode' ) ) );
$tbody		= UI_HTML_Tag::create( 'tbody', $rows );
$table		= UI_HTML_Tag::create( 'table', array( $colgroup, $thead, $tbody ), array( 'class' => 'table table-fixed table-striped table-condensed' ) );

$heading	= UI_HTML_Tag::create( 'h2', 'Blöcke' );

$linkAdd	= UI_HTML_Tag::create( 'a', $iconAdd.'&nbsp;neuer Block', array(
	'href'	=> './manage/form/block/add',
	'class'	=> 'btn btn-success'
) );
return $heading.$table.$linkAdd;
