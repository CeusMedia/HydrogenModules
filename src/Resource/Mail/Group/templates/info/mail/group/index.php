<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$iconJoin		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-sign-in'] );
$iconRegister	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-bell-o'] );
$iconUnregister	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-sign-out'] );

$list	= HtmlTag::create( 'div', 'Keine gefunden.', ['class' => 'alert alert-info'] );
if( $groups ){
	$list	= [];
	foreach( $groups as $group ){
		$labelMessages	= $group->messages == 1 ? $group->messages.' Nachricht' : $group->messages.' Nachrichten';
		$labelMembers	= $group->members == 1 ? $group->members.' Mitglied' : $group->members.' Mitglieder';
		$description	= $group->description ?: '<em class="muted">Keine Beschreibung derzeit.</em>';
		$participation	= HtmlTag::create( 'abbr', $words['types'][$group->type], ['title' => $words['types-description'][$group->type]] );
		$address		= HtmlTag::create( 'kbd', $group->address );
		$facts			= HtmlTag::create( 'small', join( '&nbsp;&nbsp;|&nbsp;&nbsp;', [
			$labelMessages,
			$labelMembers,
			'Teilnahme: '.$participation,
			'Adresse: '.$address,
		] ), ['class' => "not-muted"] );

		$title		= HtmlTag::create( 'a', $group->title, ['href' => './info/mail/group/view/'.$group->mailGroupId] );
		$subtitle	= strlen( trim( $group->subtitle ) ) ? HtmlTag::create( 'strong', $group->subtitle ).'<br/>' : '';

		$buttons	= [];
		if( in_array( $group->type, [Model_Mail_Group::TYPE_AUTOJOIN, Model_Mail_Group::TYPE_JOIN, Model_Mail_Group::TYPE_REGISTER] ) )
			$buttons[]	= HtmlTag::create( 'a', $iconJoin.'&nbsp;beitreten', [
				'href'	=> './info/mail/group/join/'.$group->mailGroupId,
				'class'	=> 'btn not-btn-small btn-primary',
			] );
		$buttons[]	= HtmlTag::create( 'a', $iconUnregister.'&nbsp;austreten', [
			'href'	=> './info/mail/group/leave/'.$group->mailGroupId,
			'class'	=> 'btn not-btn-small btn-inverse',
		] );
		$type	= HtmlTag::create( 'abbr', $words['types'][$group->type], ['title' => $words['types-description'][$group->type]] );
		$list[]	= HtmlTag::create( 'tr', array(
			HtmlTag::create( 'td', $title.'<br/>'.$subtitle.$facts ),
			HtmlTag::create( 'td', join( ' ', $buttons ), ['style' => 'text-align: right'] ),
		) );
	}
	$colgroup	= HtmlElements::ColumnGroup( ['', '240px'] );
	$tbody	= HtmlTag::create(' tbody', $list );
	$list	= HtmlTag::create( 'table', [$colgroup, $tbody], ['class' => 'table table-striped table-fixed'] );
}

$buttonbar	= '';
if( $filterPages > 1 ){
	$pagination	= new \CeusMedia\Bootstrap\PageControl( './info/mail/group', $filterPage, $filterPages );
	$buttonbar	= HtmlTag::create( 'div', [
		$pagination,
	], ['class' => 'buttonbar'] );
}


$panelList	= HtmlTag::create( 'div', array(
	HtmlTag::create( 'h3', 'Gruppen' ),
	HtmlTag::create( 'div', [
		$list,
		$buttonbar,
	], ['class' => 'content-panel-inner'] ),
), ['class' => 'content-panel'] );

return $panelList;
