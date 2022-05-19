<?php
$iconJoin		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-sign-in' ) );
$iconRegister	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-bell-o' ) );
$iconUnregister	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-sign-out' ) );

$list	= UI_HTML_Tag::create( 'div', 'Keine gefunden.', array( 'class' => 'alert alert-info' ) );
if( $groups ){
	$list	= [];
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

		$title		= UI_HTML_Tag::create( 'a', $group->title, array( 'href' => './info/mail/group/view/'.$group->mailGroupId ) );
		$subtitle	= strlen( trim( $group->subtitle ) ) ? UI_HTML_Tag::create( 'strong', $group->subtitle ).'<br/>' : '';

		$buttons	= [];
		if( in_array( $group->type, array( Model_Mail_Group::TYPE_AUTOJOIN, Model_Mail_Group::TYPE_JOIN, Model_Mail_Group::TYPE_REGISTER ) ) )
			$buttons[]	= UI_HTML_Tag::create( 'a', $iconJoin.'&nbsp;beitreten', array(
				'href'	=> './info/mail/group/join/'.$group->mailGroupId,
				'class'	=> 'btn not-btn-small btn-primary',
			) );
		$buttons[]	= UI_HTML_Tag::create( 'a', $iconUnregister.'&nbsp;austreten', array(
			'href'	=> './info/mail/group/leave/'.$group->mailGroupId,
			'class'	=> 'btn not-btn-small btn-inverse',
		) );
		$type	= UI_HTML_Tag::create( 'abbr', $words['types'][$group->type], array( 'title' => $words['types-description'][$group->type] ) );
		$list[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', $title.'<br/>'.$subtitle.$facts ),
			UI_HTML_Tag::create( 'td', join( ' ', $buttons ), array( 'style' => 'text-align: right' ) ),
		) );
	}
	$colgroup	= UI_HTML_Elements::ColumnGroup( array( '', '240px' ) );
	$tbody	= UI_HTML_Tag::create(' tbody', $list );
	$list	= UI_HTML_Tag::create( 'table', array( $colgroup, $tbody ), array( 'class' => 'table table-striped table-fixed' ) );
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
