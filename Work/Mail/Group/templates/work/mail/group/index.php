<?php
$iconAdd			= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-plus' ) );
$iconCancel			= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-left' ) );
$iconUsers			= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-users' ) );

$helperTimestamp	= new View_Helper_TimePhraser( $env );

$statusLabels	= array(
	-9	=> 'archiviert',
	-2	=> 'gesperrt',
	-1	=> 'inaktiv',
	0	=> 'neu',
	1	=> 'aktiv',
);
$statusClasses	= array(
	-9	=> 'label-info',
	-2	=> 'label-error',
	-1	=> '',
	0	=> 'label-warning',
	1	=> 'label-success',
);

$list	= UI_HTML_Tag::create( 'div', 'Keine gefunden.', array( 'class' => 'alert alert-info' ) );
if( count( $groups ) ){
	$list	= array();
	foreach( $groups as $group ){
		$label	= UI_HTML_Tag::create( 'a', $group->title, array( 'href' => './work/mail/group/edit/'.$group->mailGroupId ) );
		$status	= UI_HTML_Tag::create( 'span', $statusLabels[$group->status], array( 'class' => 'label '.$statusClasses[$group->status] ) );
		$list[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', $label ),
			UI_HTML_Tag::create( 'td', $group->address ),
			UI_HTML_Tag::create( 'td', $status ),
			UI_HTML_Tag::create( 'td', count( $group->members ) ),
			UI_HTML_Tag::create( 'td', $helperTimestamp->convert( $group->createdAt, TRUE, 'vor' ) ),
		) );
	}
	$thead	= UI_HTML_Tag::create( 'thead', UI_HTML_Elements::TableHeads( array(
		'Titel',
		'Adresse',
		'Zustand',
		$iconUsers,
		'erstellt',
	) ) );
	$tbody	= UI_HTML_Tag::create( 'tbody', $list );
	$list	= UI_HTML_Tag::create( 'table', array( $thead, $tbody ), array( 'class' => 'table table-fixed' ) );
}

$panelGroups	= UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'h3', 'Heading' ),
	UI_HTML_Tag::create( 'div', array(
		$list,
		UI_HTML_Tag::create( 'div', array(
/*			UI_HTML_Tag::create( 'a', $iconCancel.'&nbsp;...', array( 'href' => './work/mail/group', 'class' => 'btn' ) ),*/
			UI_HTML_Tag::create( 'a', $iconAdd.'&nbsp;hinzufügen', array( 'href' => './work/mail/group/add', 'class' => 'btn btn-primary' ) ),
		), array( 'class' => 'buttonbar' ) )
	), array( 'class' => 'content-panel-inner' ) )
), array( 'class' => 'content-panel' ) );

$tabs	= $view->renderTabs( $env );

return $tabs.$panelGroups;
