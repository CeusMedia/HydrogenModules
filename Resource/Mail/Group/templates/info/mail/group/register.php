<?php

$list	= UI_HTML_Tag::create( 'div', 'Keine gefunden.', array( 'class' => 'alert alert-info' ) );
if( $groups ){
	$list	= array();
	foreach( $groups as $group ){
		$title		= UI_HTML_Tag::create( 'big', $group->title );
		$title		= UI_HTML_Tag::create( 'big', $group->title );
		$members	= UI_HTML_Tag::create( 'small', count( $group->members ).' Teilnehmer' );
		$list[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', $title.'<br/>'.$members ),
			UI_HTML_Tag::create( 'td', $group->address ),
			UI_HTML_Tag::create( 'td', $group->address ),
		) );
	}
	$tbody	= UI_HTML_Tag::create(' tbody', $list );
	$list	= UI_HTML_Tag::create( 'table', array( $tbody ), array( 'class' => 'table table-fixed' ) );
}

$panelList	= UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'h3', 'Gruppen' ),
	UI_HTML_Tag::create( 'div', array(
		$list,
	), array( 'class' => 'content-panel-inner' ) ),
), array( 'class' => 'content-panel' ) );

return $panelList;
