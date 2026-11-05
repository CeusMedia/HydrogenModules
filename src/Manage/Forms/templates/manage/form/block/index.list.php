<?php /** @noinspection PhpMultipleClassDeclarationsInspection */
declare(strict_types=1);

use CeusMedia\Bootstrap\Icon;
use CeusMedia\Bootstrap\Nav\PageControl;
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

/** @var array<object> $blocks */
/** @var int $page */
/** @var int $pages */
/** @var array<string,array<string,string>> $words */

$w			= (object) $words['index.list'];

$iconAdd	= new Icon( 'plus' );
$iconView	= new Icon( 'eye' );
$iconEdit	= new Icon( 'pencil' );

$rows		= [];
foreach( $blocks as $block ){
	$linkView	= HtmlTag::create( 'a', $iconView.'&nbsp;'.$w->buttonView, [
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
	HtmlTag::create( 'th', $w->headId, ['style' => 'text-align: right'] ),
	HtmlTag::create( 'th', $w->headTitle ),
	HtmlTag::create( 'th', $w->headIdentifier ),
] ) );
$tbody		= HtmlTag::create( 'tbody', $rows );
$table		= HtmlTag::create( 'table', [$colgroup, $thead, $tbody], ['class' => 'table table-fixed table-striped table-condensed'] );

$linkAdd	= HtmlTag::create( 'a', $iconAdd.'&nbsp;'.$w->buttonAdd, [
	'href'	=> './manage/form/block/add',
	'class'	=> 'btn btn-success'
] );

$pagination	= new PageControl( './manage/form/block', $page, $pages );

return '
<div class="content-panel">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		'.$table.'
		<div class="buttonbar">
			'.$linkAdd.'
			'.$pagination.'
		</div>
	</div>
</div>';
