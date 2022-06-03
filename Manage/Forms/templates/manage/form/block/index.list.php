<?php

$iconAdd	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-plus' ) );
$iconView	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-eye' ) );
$iconEdit	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-pencil' ) );

$rows		= [];
foreach( $blocks as $block ){
	$linkView	= UI_HTML_Tag::create( 'a', $iconView.'&nbsp;anzeigen', array(
		'href'	=> './manage/form/block/view/'.$block->blockId,
		'class'	=> 'btn btn-mini btn-info',
	) );
	$linkEdit	= UI_HTML_Tag::create( 'a', $block->title, array( 'href' => './manage/form/block/edit/'.$block->blockId ) );
	$rows[]	= UI_HTML_Tag::create( 'tr', array(
		UI_HTML_Tag::create( 'td', UI_HTML_Tag::create( 'small', $block->blockId ), array( 'style' => 'text-align: right' ) ),
		UI_HTML_Tag::create( 'td', $linkEdit, array( 'class' => 'autocut' ) ),
		UI_HTML_Tag::create( 'td', '<small><tt style="letter-spacing: -0.5px">'.$block->identifier.'</tt></small>', array( 'class' => 'autocut' ) ),
		UI_HTML_Tag::create( 'td', $linkView ),
	) );
}
$colgroup	= UI_HTML_Elements::ColumnGroup( '40px', '', '35%', '100px' );
$thead		= UI_HTML_Tag::create( 'thead', UI_HTML_Tag::create( 'tr', array(
	UI_HTML_Tag::create( 'th', 'ID', array( 'style' => 'text-align: right' ) ),
	UI_HTML_Tag::create( 'th', 'Titel' ),
	UI_HTML_Tag::create( 'th', 'Shortcode' ),
) ) );
$tbody		= UI_HTML_Tag::create( 'tbody', $rows );
$table		= UI_HTML_Tag::create( 'table', array( $colgroup, $thead, $tbody ), array( 'class' => 'table table-fixed table-striped table-condensed' ) );

$linkAdd	= UI_HTML_Tag::create( 'a', $iconAdd.'&nbsp;neuer Block', array(
	'href'	=> './manage/form/block/add',
	'class'	=> 'btn btn-success'
) );

$pagination	= new \CeusMedia\Bootstrap\Nav\PageControl( './manage/form/block', $page, $pages );

return '
<div class="content-panel">
	<h3>Blöcke</h3>
	<div class="content-panel-inner">
		'.$table.'
		<div class="buttonbar">
			'.$linkAdd.'
			'.$pagination.'
		</div>
	</div>
</div>';
