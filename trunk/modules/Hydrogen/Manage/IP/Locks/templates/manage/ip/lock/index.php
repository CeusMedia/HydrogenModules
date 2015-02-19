<?php

$states	= array(
	-2	=> 'unlocked',
	-1	=> '...',
	0	=> 'lock requested',
	1	=> 'locked',
	2	=> 'unlock requested',
);

$iconLock	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-lock icon-white' ) );
$iconUnlock	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-ok icon-white' ) );
$iconRemove	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-trash' ) );

$list	= '<div><em><small>Keine IP-Locks gefunden.</small></em></div>';
if( $locks ){
	$list	= array();
	foreach( $locks as $lock ){
		$buttonLock		= UI_HTML_Tag::create( 'a', $iconLock, array(
			'href'		=> './manage/ip/lock/lock/'.$lock->ipLockId,
			'class'		=> 'btn btn-small btn-danger'.( $lock->status == 1 ? ' disabled' : '' ),
			'title'		=> 'lock',
		) );
		$buttonUnlock	= UI_HTML_Tag::create( 'a', $iconUnlock, array(
			'href'		=> './manage/ip/lock/unlock/'.$lock->ipLockId,
			'class'		=> 'btn btn-small btn-success'.( $lock->status != 1 ? ' disabled' : '' ),
			'title'		=> 'unlock',
		) );
		$buttonRemove	= UI_HTML_Tag::create( 'a', $iconRemove, array(
			'href'		=> './manage/ip/lock/remove/'.$lock->ipLockId,
			'class'		=> 'btn btn-small'.( in_array( $lock->status, array( 1, 2 ) ) ? ' disabled' : '' ),
			'title'		=> 'remove',
		) );
		$list[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', $lock->IPv4, array( 'class' => 'lock-ip' ) ),
			UI_HTML_Tag::create( 'td', $states[$lock->status], array( 'class' => 'lock-status' ) ),
			UI_HTML_Tag::create( 'td', $lock->reason->title, array( 'class' => 'lock-reason-title' ) ),
			UI_HTML_Tag::create( 'td', $buttonUnlock.$buttonLock.$buttonRemove, array( 'class' => 'lock-buttons' ) ),
		) );
	}
	$heads	= array(
		'IP-Adresse',
		'Zustand',
		'Grund',
		'Aktion',
	);
	$thead	= UI_HTML_Tag::create( 'thead', UI_HTML_Elements::TableHeads( $heads ) );
	$tbody	= UI_HTML_Tag::create( 'tbody', $list );
	$list	= UI_HTML_Tag::create( 'table', $thead.$tbody, array( 'class' => 'table table-condensed' ) );
}

$iconAdd		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-plus icon-white' ) );
$buttonAdd		= UI_HTML_Tag::create( 'a', $iconAdd.' hinzufügen', array(
	'href'	=> './manage/ip/lock/add',
	'class'	=> 'btn btn-primary',
) );

$tabs   = View_Manage_Ip_Lock::renderTabs( $env );
//$tabs = $view->renderTabs();

return $tabs.HTML::DivClass( 'row-fluid', '
<h2>IP-Locks</h2>
'.$list.'
<br/>
'.$buttonAdd
);
