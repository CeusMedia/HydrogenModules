<?php

$states	= array(
	-2	=> 'unlocked',
	-1	=> '...',
	0	=> 'lock requested',
	1	=> 'locked',
	2	=> 'unlock requested',
);

$iconAdd	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-plus icon-white' ) );
$iconEdit	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-pencil' ) );
$iconRemove	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-trash' ) );
$iconActivate	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-check icon-white' ) );
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

$lockStates	= array(
	0	=> 'nur Sperranfrage',
	1	=> 'aktive Sperre',
);

$lockStates	= array(
	0	=> 'Anfrage',
	1	=> 'Sperre',
);

$list	= '<div><em><small>Keine IP-Lock-Filter gefunden.</small></em></div>';
if( $filters ){
	$list	= array();
	foreach( $filters as $filter ){
		$buttonEdit		= UI_HTML_Tag::create( 'a', $iconEdit, array(
			'href'		=> './manage/ip/lock/filter/edit/'.$filter->ipLockFilterId,
			'class'		=> 'btn not-btn-primary btn-small btn-mini',
			'title'		=> 'edit',
		) );
		$buttonStatus	= UI_HTML_Tag::create( 'a', $iconActivate, array(
			'href'		=> './manage/ip/lock/filter/activate/'.$filter->ipLockFilterId,
			'class'		=> 'btn btn-success btn-small btn-mini',
			'title'		=> 'activate',
		) );
		if( $filter->status ){
			$buttonStatus	= UI_HTML_Tag::create( 'a', $iconDeactivate, array(
				'href'		=> './manage/ip/lock/filter/deactivate/'.$filter->ipLockFilterId,
				'class'		=> 'btn btn-danger btn-small btn-mini',
				'title'		=> 'deactivate',
			) );
		}
		$buttonRemove	= UI_HTML_Tag::create( 'a', $iconRemove, array(
			'href'		=> './manage/ip/lock/filter/remove/'.$filter->ipLockFilterId,
			'class'		=> 'btn btn-inverse btn-small btn-mini',
			'title'		=> 'remove',
		) );
		$appliedAt	= $filter->appliedAt ? date( 'd.m.Y H:i:s', $filter->appliedAt ) : '-';
		if( $filter->appliedAt && $helperTime )
			$appliedAt	= 'vor '.$helperTime->convert( $filter->appliedAt );

		$method		= $filter->method ? $filter->method : '<span class="muted">alle</span>';
		$lockStatus	= $lockStates[$filter->lockStatus];
		$buttons	= UI_HTML_Tag::create( 'div', $buttonEdit.$buttonStatus.$buttonRemove, array( 'class' => 'btn-group' ) );
		$list[]		= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', $method, array( 'class' => 'lock-filter-method' ) ),
			UI_HTML_Tag::create( 'td', $filter->title, array( 'class' => 'lock-filter-title' ) ),
//			UI_HTML_Tag::create( 'td', $filter->pattern, array( 'class' => 'lock-filter-pattern' ) ),
			UI_HTML_Tag::create( 'td', $lockStatus, array( 'class' => 'lock-filter-lock-status' ) ),
			UI_HTML_Tag::create( 'td', $filter->reason->title, array( 'class' => 'lock-filter-reason' ) ),
			UI_HTML_Tag::create( 'td', '<small>'.$appliedAt.'</small>', array( 'class' => 'lock-filter-applied' ) ),
			UI_HTML_Tag::create( 'td', $buttons, array( 'class' => 'lock-buttons' ) ),
		), array( 'class' => $filter->status ? 'success' : 'warning' ) );
	}
	$heads	= array(
		'Methode',
		'Titel',
//		'Muster',
		'Folge',
		'Grund',
		'Anwendung',
		'Aktion',
	);
	$colgroup	= UI_HTML_Elements::ColumnGroup( "80", "", /*"", */"80", "", "110", "100" );
	$thead	= UI_HTML_Tag::create( 'thead', UI_HTML_Elements::TableHeads( $heads ) );
	$tbody	= UI_HTML_Tag::create( 'tbody', $list );
	$list	= UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody, array( 'class' => 'table table-condensed' ) );
}

$buttonAdd		= UI_HTML_Tag::create( 'a', $iconAdd.' hinzufügen', array(
	'href'	=> './manage/ip/lock/filter/add',
	'class'	=> 'btn btn-primary',
) );

$tabs   = View_Manage_Ip_Lock::renderTabs( $env, 'filter' );

return $tabs.HTML::DivClass( 'row-fluid', '
<h2>IP-Lock-Filter</h2>
'.$list.'
'.$buttonAdd
);
