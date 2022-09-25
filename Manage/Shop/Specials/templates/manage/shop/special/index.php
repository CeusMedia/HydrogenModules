<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$iconAdd	= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-plus' ) );

$bridgeMap	= [];
foreach( $catalogs as $bridge )
	$bridgeMap[$bridge->data->bridgeId]	= $bridge->data->title;

$rows	= [];
foreach( $specials as $special ){
	$link	= HtmlTag::create( 'a', $special->title, array(
		'href'	=> './manage/shop/special/edit/'.$special->shopSpecialId,
	) );
	$rows[]	= HtmlTag::create( 'tr', array(
		HtmlTag::create( 'td', $bridgeMap[$special->bridgeId] ),
		HtmlTag::create( 'td', $link ),
	) );
}
$buttonbar	= HtmlTag::create( 'div', array(
	HtmlTag::create( 'a', $iconAdd.'&nbsp;neue Spezialität', array(
		'href'	=> './manage/shop/special/add',
		'class'	=> 'btn btn-primary',
	) )
), array( 'class' => 'buttonbar' ) );
$colgroup	= UI_HTML_Elements::ColumnGroup( '30%', '70%' );
$tableHeads	= UI_HTML_Elements::TableHeads( array( 'Katalog', 'Artikel' ) );
$thead	= HtmlTag::create( 'thead', $tableHeads );
$tbody	= HtmlTag::create( 'tbody', $rows );
$table	= HtmlTag::create( 'table', array( $colgroup, $thead, $tbody ), array( 'class' => 'table' ) );

return HtmlTag::create( 'div', array(
	HtmlTag::create( 'h3', 'Spezialitäten' ),
	HtmlTag::create( 'div', array(
		$table,
		$buttonbar,
	), array( 'class' => 'content-panel-inner' ) ),
), array( 'class' => 'content-panel' ) );
