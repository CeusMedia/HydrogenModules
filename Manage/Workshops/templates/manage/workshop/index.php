<?php

$iconAdd		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-plus' ) );

$list	= '<em>Keine Workshops vorhanden.</em>';
if( $workshops ){
	$list	= array();
	foreach( $workshops as $item ){
		$link	= UI_HTML_Tag::create( 'a', $item->title, array(
			'href'	=> './manage/workshop/edit/'.$item->workshopId,
		) );
		$list[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', $link ),
			UI_HTML_Tag::create( 'td', date( 'd.m.Y H:i', $item->modifiedAt ) ),

		), array() );
	}
	$list	= UI_HTML_Tag::create( 'table', $list, array( 'class' => 'table table-striped' ) );
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
