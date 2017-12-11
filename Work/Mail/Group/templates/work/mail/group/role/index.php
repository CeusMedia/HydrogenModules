<?php

$iconAdd			= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-plus' ) );
$iconCancel			= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-left' ) );
$iconGroups			= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-users' ) );
$iconUsers			= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-user' ) );

$helperTimestamp	= new View_Helper_TimePhraser( $env );

$list	= UI_HTML_Tag::create( 'div', 'Keine gefunden.', array( 'class' => 'alert alert-info' ) );
if( count( $roles ) ){
	$list	= array();
	foreach( $roles as $role ){
		$label	= UI_HTML_Tag::create( 'a', $role->title, array( 'href' => './work/mail/group/role/edit/'.$role->mailGroupRoleId ) );
//		$status	= UI_HTML_Tag::create( 'span', $statusLabels[$group->status], array( 'class' => 'label '.$statusClasses[$group->status] ) );
		$list[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', $label ),
	//		UI_HTML_Tag::create( 'td', $role->host ),
	//		UI_HTML_Tag::create( 'td', $status ),
	//		UI_HTML_Tag::create( 'td', count( $group->members ) ),
			UI_HTML_Tag::create( 'td', $helperTimestamp->convert( $role->createdAt, TRUE, 'vor' ) ),
		) );
	}
	$thead	= UI_HTML_Tag::create( 'thead', UI_HTML_Elements::TableHeads( array(
		'Titel',
//		'Adresse',
//		'Zustand',
//		$iconUsers,
		'erstellt',
	) ) );
	$tbody	= UI_HTML_Tag::create( 'tbody', $list );
	$list	= UI_HTML_Tag::create( 'table', array( $thead, $tbody ), array( 'class' => 'table table-fixed' ) );
}

$panelRoles	= UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'h3', 'Rollen' ),
	UI_HTML_Tag::create( 'div', array(
		$list,
		UI_HTML_Tag::create( 'div', array(
/*			UI_HTML_Tag::create( 'a', $iconCancel.'&nbsp;...', array( 'href' => './work/mail/group', 'class' => 'btn' ) ),*/
			UI_HTML_Tag::create( 'a', $iconAdd.'&nbsp;hinzufügen', array( 'href' => './work/mail/group/role/add', 'class' => 'btn btn-primary' ) ),
		), array( 'class' => 'buttonbar' ) )
	), array( 'class' => 'content-panel-inner' ) )
), array( 'class' => 'content-panel' ) );

$tabs	= $view->renderTabs( $env, 3 );

return $tabs.$panelRoles;
