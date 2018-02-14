<?php
$iconJoin		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-sign-in' ) );
$iconRegister	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-bell-o' ) );
$iconUnregister	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-sign-out' ) );

$list	= UI_HTML_Tag::create( 'div', 'Keine gefunden.', array( 'class' => 'alert alert-info' ) );
if( $groups ){
	$list	= array();
	foreach( $groups as $group ){
		$labelMessages	= $group->messages == 1 ? $group->messages.' Nachricht' : $group->messages.' Nachrichten';
		$labelMembers	= $group->members == 1 ? $group->members.' Mitglied' : $group->members.' Mitglieder';
		$description	= $group->description ? $group->description : '<em class="muted">Keine Beschreibung derzeit.</em>';
		$participation	= UI_HTML_Tag::create( 'abbr', $words['types'][$group->type], array( 'title' => $words['types-description'][$group->type] ) );
		$address		= UI_HTML_Tag::create( 'kbd', $group->address );
		$facts			= UI_HTML_Tag::create( 'small', join( '&nbsp;&nbsp;|&nbsp;&nbsp;', array(
			$labelMessages,
			$labelMembers,
			'Teilnahme: '.$participation,
			'Adresse: '.$address,
		) ), array( 'class' => "not-muted" ) );

		$title		= UI_HTML_Tag::create( 'big', $group->title );
		$subtitle	= strlen( trim( $group->subtitle ) ) ? UI_HTML_Tag::create( 'strong', $group->subtitle ).'<br/>' : '';

		$buttons	= array();
		if( in_array( $group->type, array( Model_Mail_Group::TYPE_PUBLIC, Model_Mail_Group::TYPE_JOIN ) ) )
			$buttons[]	= UI_HTML_Tag::create( 'a', $iconJoin.'&nbsp;beitreten', array(
				'href'	=> './info/mail/group/join/'.$group->mailGroupId,
				'class'	=> 'btn btn-small',
			) );
		if( $group->type == 2 )
			$buttons[]	= UI_HTML_Tag::create( 'a', $iconRegister.'&nbsp;Beitritt beantragen', array(
				'href'	=> './info/mail/group/register/'.$group->mailGroupId,
				'class'	=> 'btn btn-small',
			) );
		$buttons[]	= UI_HTML_Tag::create( 'a', $iconUnregister.'&nbsp;austreten', array(
			'href'	=> './info/mail/group/unregister/'.$group->mailGroupId,
			'class'	=> 'btn btn-small',
		) );
		$type	= UI_HTML_Tag::create( 'abbr', $words['types'][$group->type], array( 'title' => $words['types-description'][$group->type] ) );
		$list[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', $title.'<br/>'.$subtitle.$facts ),
			UI_HTML_Tag::create( 'td', join( ' ', $buttons ), array( 'style' => 'text-align: right' ) ),
		) );
	}
	$tbody	= UI_HTML_Tag::create(' tbody', $list );
	$list	= UI_HTML_Tag::create( 'table', array( $tbody ), array( 'class' => 'table table-striped table-fixed' ) );
}

$buttonbar	= '';
if( $filterPages > 1 ){
	$pagination	= new \CeusMedia\Bootstrap\PageControl( './info/mail/group', $filterPage, $filterPages );
	$buttonbar	= UI_HTML_Tag::create( 'div', array(
		$pagination,
	), array( 'class' => 'buttonbar' ) );
}


$panelList	= UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'h3', 'Gruppen' ),
	UI_HTML_Tag::create( 'div', array(
		$list,
		$buttonbar,
	), array( 'class' => 'content-panel-inner' ) ),
), array( 'class' => 'content-panel' ) );

return $panelList;
