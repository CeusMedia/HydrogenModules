<?php
$iconJoin		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-sign-in' ) );
$iconRegister	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-bell-o' ) );
$iconUnregister	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-sign-out' ) );

$list	= UI_HTML_Tag::create( 'div', 'Keine gefunden.', array( 'class' => 'alert alert-info' ) );
if( $groups ){
	$list	= array();
	foreach( $groups as $group ){
		$group->type	= $group->mailGroupId;
		$title		= UI_HTML_Tag::create( 'big', $group->title );
		$title		= UI_HTML_Tag::create( 'big', $group->title );
		$members	= UI_HTML_Tag::create( 'small', count( $group->members ).' Teilnehmer' );
		$buttons	= array();
		if( $group->type == 1 )
			$buttons[]	= UI_HTML_Tag::create( 'a', $iconJoin.'&nbsp;beitreten', array(
				'href'	=> './info/mail/group/join/'.$group->mailGroupId,
				'class'	=> 'btn btn-small',
			) );
		if( $group->type == 1 )
			$buttons[]	= UI_HTML_Tag::create( 'a', $iconRegister.'&nbsp;Beitritt beantragen', array(
				'href'	=> './info/mail/group/register/'.$group->mailGroupId,
				'class'	=> 'btn btn-small',
			) );
		if( $group->type )
			$buttons[]	= UI_HTML_Tag::create( 'a', $iconUnregister.'&nbsp;austreten', array(
				'href'	=> './info/mail/group/unregister/'.$group->mailGroupId,
				'class'	=> 'btn btn-small',
			) );
		$type	= UI_HTML_Tag::create( 'abbr', $words['types'][$group->mailGroupId], array( 'title' => $words['types-description'][$group->mailGroupId] ) );
		$list[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', $title.'<br/>'.$members ),
			UI_HTML_Tag::create( 'td', $group->address ),
			UI_HTML_Tag::create( 'td', $type ),
			UI_HTML_Tag::create( 'td', join( ' ', $buttons ), array( 'style' => 'text-align: right' ) ),
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
