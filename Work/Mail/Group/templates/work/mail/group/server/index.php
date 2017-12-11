<?php

$iconAdd			= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-plus' ) );
$iconCancel			= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-left' ) );
$iconGroups			= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-users' ) );
$iconUsers			= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-user' ) );

$helperTimestamp	= new View_Helper_TimePhraser( $env );

$list	= UI_HTML_Tag::create( 'div', 'Keine gefunden.', array( 'class' => 'alert alert-info' ) );
if( count( $servers ) ){
	$list	= array();
	foreach( $servers as $server ){
		$label	= UI_HTML_Tag::create( 'a', $server->title, array( 'href' => './work/mail/group/server/edit/'.$server->mailGroupServerId ) );
//		$status	= UI_HTML_Tag::create( 'span', $statusLabels[$group->status], array( 'class' => 'label '.$statusClasses[$group->status] ) );
		$list[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', $label ),
			UI_HTML_Tag::create( 'td', $server->host ),
	//		UI_HTML_Tag::create( 'td', $status ),
	//		UI_HTML_Tag::create( 'td', count( $group->members ) ),
			UI_HTML_Tag::create( 'td', $helperTimestamp->convert( $server->createdAt, TRUE, 'vor' ) ),
		) );
	}
	$thead	= UI_HTML_Tag::create( 'thead', UI_HTML_Elements::TableHeads( array(
		'Titel',
		'Adresse',
//		'Zustand',
//		$iconUsers,
		'erstellt',
	) ) );
	$tbody	= UI_HTML_Tag::create( 'tbody', $list );
	$list	= UI_HTML_Tag::create( 'table', array( $thead, $tbody ), array( 'class' => 'table table-fixed' ) );
}

$panelServers	= UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'h3', 'E-Mail-Servers' ),
	UI_HTML_Tag::create( 'div', array(
		$list,
		UI_HTML_Tag::create( 'div', array(
/*			UI_HTML_Tag::create( 'a', $iconCancel.'&nbsp;...', array( 'href' => './work/mail/group', 'class' => 'btn' ) ),*/
			UI_HTML_Tag::create( 'a', $iconAdd.'&nbsp;hinzufügen', array( 'href' => './work/mail/group/server/add', 'class' => 'btn btn-primary' ) ),
		), array( 'class' => 'buttonbar' ) )
	), array( 'class' => 'content-panel-inner' ) )
), array( 'class' => 'content-panel' ) );

$tabs	= $view->renderTabs( $env, 1 );

return $tabs.$panelServers;
