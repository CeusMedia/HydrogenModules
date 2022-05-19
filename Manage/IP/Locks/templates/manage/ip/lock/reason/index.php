<?php

$states	= array(
	0	=> 'disabled',
	1	=> 'active',
);

$iconAdd	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-plus icon-white' ) );
$iconEdit	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-pencil' ) );
$iconRemove	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-trash icon-white' ) );
$iconActivate	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-ok icon-white' ) );
$iconDeactivate	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-remove icon-white' ) );
if( $env->getModules()->has( 'UI_Font_FontAwesome' ) ){
	$iconAdd		= UI_HTML_Tag::create( 'b', '', array( 'class' => 'fa fa-fw fa-plus fa-inverse' ) );
	$iconEdit		= UI_HTML_Tag::create( 'b', '', array( 'class' => 'fa fa-fw fa-pencil' ) );
	$iconRemove		= UI_HTML_Tag::create( 'b', '', array( 'class' => 'fa fa-fw fa-trash fa-inverse' ) );
	$iconActivate	= UI_HTML_Tag::create( 'b', '', array( 'class' => 'fa fa-fw fa-check fa-inverse' ) );
	$iconDeactivate	= UI_HTML_Tag::create( 'b', '', array( 'class' => 'fa fa-fw fa-times fa-inverse' ) );
}
$helperTime	= FALSE;
if( $env->getModules()->has( 'UI_Helper_TimePhraser' ) ){
	$helperTime		= new View_Helper_TimePhraser( $env );
}

$list	= '<div><em><small>Keine IP-Lock-Gründe gefunden.</small></em></div>';
if( $reasons ){
	$list	= [];
	foreach( $reasons as $reason ){
		$buttonEdit		= UI_HTML_Tag::create( 'a', $iconEdit, array(
			'href'		=> './manage/ip/lock/reason/edit/'.$reason->ipLockReasonId,
			'class'		=> 'btn not-btn-primary btn-small btn-mini',
			'title'		=> 'bearbeiten',
		) );
		$buttonStatus	= UI_HTML_Tag::create( 'a', $iconActivate, array(
			'href'		=> './manage/ip/lock/reason/activate/'.$reason->ipLockReasonId,
			'class'		=> 'btn btn-success btn-small btn-mini',
			'title'		=> 'aktivieren',
		) );
		if( $reason->status ){
			$buttonStatus	= UI_HTML_Tag::create( 'a', $iconDeactivate, array(
				'href'		=> './manage/ip/lock/reason/deactivate/'.$reason->ipLockReasonId,
				'class'		=> 'btn btn-inverse btn-small btn-mini',
				'title'		=> 'deaktivieren',
			) );
		}
/*		$buttonRemove	= UI_HTML_Tag::create( 'a', $iconRemove, array(
			'href'		=> './manage/ip/lock/reason/remove/'.$reason->ipLockReasonId,
			'class'		=> 'btn btn-inverse btn-small btn-mini',
			'title'		=> 'entfernen',
		) );*/
		$createdAt	= $reason->createdAt ? date( 'd.m.Y H:i:s', $reason->createdAt ) : '-';
		if( $reason->createdAt && $helperTime )
			$createdAt	= 'vor '.$helperTime->convert( $reason->createdAt, TRUE );
		$appliedAt	= $reason->appliedAt ? date( 'd.m.Y H:i:s', $reason->appliedAt ) : '-';
		if( $reason->appliedAt && $helperTime )
			$appliedAt	= 'vor '.$helperTime->convert( $reason->appliedAt, TRUE );
		$httpCode	= UI_HTML_Tag::create( 'abbr', $reason->code, array( 'title' => Net_HTTP_Status::getText( $reason->code ) ) );
		$duration	= $reason->duration ? $reason->duration : '-';
		if( $reason->duration && $helperTime )
			$duration	= 'nach '.$helperTime->convert( time() - $reason->duration, !TRUE );

		$link		= UI_HTML_Tag::create( 'a', $reason->title, array( 'href' => './manage/ip/lock/reason/edit/'.$reason->ipLockReasonId ) );
		$buttons	= UI_HTML_Tag::create( 'div', $buttonEdit.$buttonStatus/*.$buttonRemove*/, array( 'class' => 'btn-group' ) );
		$list[]		= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', $httpCode, array( 'class' => 'lock-reason-code' ) ),
			UI_HTML_Tag::create( 'td', $link, array( 'class' => 'lock-reason-title' ) ),
			UI_HTML_Tag::create( 'td', '<small>'.$duration.'</small>', array( 'class' => 'lock-reason-duration' ) ),
			UI_HTML_Tag::create( 'td', '<small>'.$createdAt.'</small>', array( 'class' => 'lock-reason-created' ) ),
			UI_HTML_Tag::create( 'td', '<small>'.$appliedAt.'</small>', array( 'class' => 'lock-reason-applied' ) ),
			UI_HTML_Tag::create( 'td', $buttons, array( 'class' => 'lock-buttons' ) ),
		), array( 'class' => $reason->status ? 'success' : 'warning' ) );
	}
	$heads	= array(
		UI_HTML_Tag::create( 'abbr', 'Code', array( 'title' => 'HTTP-Status-Code' ) ),
		'Titel',
		UI_HTML_Tag::create( 'abbr', 'Aufhebung', array( 'title' => 'Automatische Aufhebung der Sperre' ) ),
		'Erstellung',
		UI_HTML_Tag::create( 'abbr', 'Anwendung', array( 'title' => 'Letzte Sperrung aus diesem Grund' ) ),
		'Aktion',
	);
	$colgroup	= UI_HTML_Elements::ColumnGroup( "50", "", "120", "110", "110", "100" );
	$thead	= UI_HTML_Tag::create( 'thead', UI_HTML_Elements::TableHeads( $heads ) );
	$tbody	= UI_HTML_Tag::create( 'tbody', $list );
	$list	= UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody, array( 'class' => 'table table-condensed' ) );
}

$buttonAdd		= UI_HTML_Tag::create( 'a', $iconAdd.' hinzufügen', array(
	'href'	=> './manage/ip/lock/reason/add',
	'class'	=> 'btn btn-primary',
) );

$panelList	= HTML::DivClass( 'content-panel',
	UI_HTML_Tag::create( 'h3', 'IP-Speer-Gründe' ).
	HTML::DivClass( 'content-panel-inner',
		$list.
		HTML::DivClass( 'buttonbar',
			$buttonAdd
		)
	)
);

$tabs	= View_Manage_Ip_Lock::renderTabs( $env, 'reason' );
return $tabs.$panelList;
