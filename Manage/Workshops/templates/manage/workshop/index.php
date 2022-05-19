<?php

$iconAdd		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-plus' ) );

$list	= '<em>Keine Workshops vorhanden.</em>';
if( $workshops ){
	$list	= [];
	foreach( $workshops as $item ){
		$link	= UI_HTML_Tag::create( 'a', $item->title, array(
			'href'	=> './manage/workshop/edit/'.$item->workshopId,
		) );
		$status		= $words['statuses'][$item->status];
		$rank		= $words['ranks'][$item->rank];
		$list[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', $link ),
			UI_HTML_Tag::create( 'td', $status ),
			UI_HTML_Tag::create( 'td', $rank ),
			UI_HTML_Tag::create( 'td', date( 'd.m.Y H:i', $item->modifiedAt ) ),

		), array() );
	}
	$heads	= UI_HTML_Elements::TableHeads( array( 'Bezeichnung', 'Zustand', 'Anordnung', 'letzte Änderung' ) );
	$thead	= UI_HTML_Tag::create( 'thead', $heads );
	$tbody	= UI_HTML_Tag::create( 'tbody', $list );
	$list	= UI_HTML_Tag::create( 'table', array( $thead, $tbody ), array( 'class' => 'table table-striped' ) );
}

$panelList	= UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'h3', 'Workshops' ),
	UI_HTML_Tag::create( 'div', array(
		$list,
		UI_HTML_Tag::create( 'div', array(
			UI_HTML_Tag::create( 'a', $iconAdd.'&nbsp;neuer Workshop', array( 'href' => './manage/workshop/add', 'class' => 'btn btn-success' ) ),
		), array( 'class' => 'buttonbar' ) ),
	), array( 'class' => 'content-panel-inner' ) ),
), array( 'class' => 'content-panel' ) );

return $panelList;
