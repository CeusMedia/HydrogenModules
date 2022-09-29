<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$iconAdd			= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-plus'] );
$iconCancel			= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-arrow-left'] );
$iconGroups			= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-users'] );
$iconUsers			= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-user'] );

$helperTimestamp	= new View_Helper_TimePhraser( $env );

$list	= HtmlTag::create( 'div', 'Keine gefunden.', ['class' => 'alert alert-info'] );
if( count( $servers ) ){
	$list	= [];
	foreach( $servers as $server ){
		$label	= HtmlTag::create( 'a', $server->title, ['href' => './work/mail/group/server/edit/'.$server->mailGroupServerId] );
//		$status	= HtmlTag::create( 'span', $statusLabels[$group->status], ['class' => 'label '.$statusClasses[$group->status]] );
		$list[]	= HtmlTag::create( 'tr', array(
			HtmlTag::create( 'td', $label ),
			HtmlTag::create( 'td', $server->imapHost.':'.$server->imapPort ),
			HtmlTag::create( 'td', $server->smtpHost.':'.$server->smtpPort ),
	//		HtmlTag::create( 'td', $status ),
	//		HtmlTag::create( 'td', count( $group->members ) ),
			HtmlTag::create( 'td', $helperTimestamp->convert( $server->createdAt, TRUE, 'vor' ) ),
		) );
	}
	$thead	= HtmlTag::create( 'thead', HtmlElements::TableHeads( array(
		'Titel',
		'IMAP-Server',
		'SMTP-Server',
//		'Zustand',
//		$iconUsers,
		'erstellt',
	) ) );
	$tbody	= HtmlTag::create( 'tbody', $list );
	$list	= HtmlTag::create( 'table', [$thead, $tbody], ['class' => 'table table-fixed'] );
}

$panelServers	= HtmlTag::create( 'div', array(
	HtmlTag::create( 'h3', 'E-Mail-Servers' ),
	HtmlTag::create( 'div', array(
		$list,
		HtmlTag::create( 'div', array(
/*			HtmlTag::create( 'a', $iconCancel.'&nbsp;...', ['href' => './work/mail/group', 'class' => 'btn'] ),*/
			HtmlTag::create( 'a', $iconAdd.'&nbsp;hinzufügen', ['href' => './work/mail/group/server/add', 'class' => 'btn btn-primary'] ),
		), ['class' => 'buttonbar'] )
	), ['class' => 'content-panel-inner'] )
), ['class' => 'content-panel'] );

$tabs	= $view->renderTabs( $env, 'server' );

return $tabs.$panelServers;
