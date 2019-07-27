<?php
$iconAdd	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-plus' ) );

$bridgeMap	= array();
foreach( $catalogs as $bridge )
	$bridgeMap[$bridge->data->bridgeId]	= $bridge->data->title;

$rows	= array();
foreach( $specials as $special ){
	$link	= UI_HTML_Tag::create( 'a', $special->title, array(
		'href'	=> './manage/shop/special/edit/'.$special->shopSpecialId,
	) );
	$rows[]	= UI_HTML_Tag::create( 'tr', array(
		UI_HTML_Tag::create( 'td', $bridgeMap[$special->bridgeId] ),
		UI_HTML_Tag::create( 'td', $link ),
	) );
}
$buttonbar	= UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'a', $iconAdd.'&nbsp;neue Spezialität', array(
		'href'	=> './manage/shop/special/add',
		'class'	=> 'btn btn-primary',
	) )
), array( 'class' => 'buttonbar' ) );
$colgroup	= UI_HTML_Elements::ColumnGroup( '30%', '70%' );
$tableHeads	= UI_HTML_Elements::TableHeads( array( 'Katalog', 'Artikel' ) );
$thead	= UI_HTML_Tag::create( 'thead', $tableHeads );
$tbody	= UI_HTML_Tag::create( 'tbody', $rows );
$table	= UI_HTML_Tag::create( 'table', array( $colgroup, $thead, $tbody ), array( 'class' => 'table' ) );

return UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'h3', 'Spezialitäten' ),
	UI_HTML_Tag::create( 'div', array(
		$table,
		$buttonbar,
	), array( 'class' => 'content-panel-inner' ) ),
), array( 'class' => 'content-panel' ) );
