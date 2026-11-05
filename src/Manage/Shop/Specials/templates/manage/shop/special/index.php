<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

/** @var array<string,array<string,string>> $words */
$w	= (object) $words['index'];

$iconAdd	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-plus'] );

$bridgeMap	= [];
foreach( $catalogs as $bridge )
	$bridgeMap[$bridge->data->bridgeId]	= $bridge->data->title;

$rows	= [];
foreach( $specials as $special ){
	$link	= HtmlTag::create( 'a', $special->title, [
		'href'	=> './manage/shop/special/edit/'.$special->shopSpecialId,
	] );
	$rows[]	= HtmlTag::create( 'tr', array(
		HtmlTag::create( 'td', $bridgeMap[$special->bridgeId] ),
		HtmlTag::create( 'td', $link ),
	) );
}
$buttonbar	= HtmlTag::create( 'div', array(
	HtmlTag::create( 'a', $iconAdd.'&nbsp;'.$w->buttonAdd, [
		'href'	=> './manage/shop/special/add',
		'class'	=> 'btn btn-primary',
	] )
), ['class' => 'buttonbar'] );
$colgroup	= HtmlElements::ColumnGroup( '30%', '70%' );
$tableHeads	= HtmlElements::TableHeads( [$w->headCatalog, $w->headArticle] );
$thead	= HtmlTag::create( 'thead', $tableHeads );
$tbody	= HtmlTag::create( 'tbody', $rows );
$table	= HtmlTag::create( 'table', [$colgroup, $thead, $tbody], ['class' => 'table'] );

return HtmlTag::create( 'div', array(
	HtmlTag::create( 'h3', $w->heading ),
	HtmlTag::create( 'div', [
		$table,
		$buttonbar,
	], ['class' => 'content-panel-inner'] ),
), ['class' => 'content-panel'] );
