<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

/** @var array<object> $blocks */
/** @var int $page */
/** @var int $pages */

$iconAdd	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-plus'] );
$iconView	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-eye'] );
$iconEdit	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-pencil'] );

$rows		= [];
foreach( $blocks as $block ){
	$linkView	= HtmlTag::create( 'a', $iconView.'&nbsp;anzeigen', [
		'href'	=> './manage/form/block/view/'.$block->blockId,
		'class'	=> 'btn btn-mini btn-info',
	] );
	$linkEdit	= HtmlTag::create( 'a', $block->title, ['href' => './manage/form/block/edit/'.$block->blockId] );
	$rows[]	= HtmlTag::create( 'tr', [
		HtmlTag::create( 'td', HtmlTag::create( 'small', $block->blockId ), ['style' => 'text-align: right'] ),
		HtmlTag::create( 'td', $linkEdit, ['class' => 'autocut'] ),
		HtmlTag::create( 'td', '<small><tt style="letter-spacing: -0.5px">'.$block->identifier.'</tt></small>', ['class' => 'autocut'] ),
		HtmlTag::create( 'td', $linkView ),
	] );
}
$colgroup	= HtmlElements::ColumnGroup( '40px', '', '35%', '100px' );
$thead		= HtmlTag::create( 'thead', HtmlTag::create( 'tr', [
	HtmlTag::create( 'th', 'ID', ['style' => 'text-align: right'] ),
	HtmlTag::create( 'th', 'Titel' ),
	HtmlTag::create( 'th', 'Shortcode' ),
] ) );
$tbody		= HtmlTag::create( 'tbody', $rows );
$table		= HtmlTag::create( 'table', [$colgroup, $thead, $tbody], ['class' => 'table table-fixed table-striped table-condensed'] );

$linkAdd	= HtmlTag::create( 'a', $iconAdd.'&nbsp;neuer Block', [
	'href'	=> './manage/form/block/add',
	'class'	=> 'btn btn-success'
] );

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
