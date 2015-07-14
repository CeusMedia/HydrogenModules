<?php

$states	= array(
	-10	=> '<abbr title="Grund für diese Sperre wurde deaktiviert">deaktiviert</abbr>',
	0	=> 'inaktiv',
	1	=> 'aktiv',
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
		if( $filter->reason->status < 1 )
			$filter->status	= -10;

		$buttonEdit		= UI_HTML_Tag::create( 'a', $iconEdit, array(
			'href'		=> './manage/ip/lock/filter/edit/'.$filter->ipLockFilterId,
			'class'		=> 'btn not-btn-primary btn-small btn-mini',
			'title'		=> 'edit',
		) );
		$buttonStatus	= "";
		if( in_array( $filter->status, array( 0 ) ) ){
			$buttonStatus	= UI_HTML_Tag::create( 'a', $iconActivate, array(
				'href'		=> './manage/ip/lock/filter/activate/'.$filter->ipLockFilterId,
				'class'		=> 'btn btn-success btn-small btn-mini',
				'title'		=> 'aktivieren',
			) );
		}
		else if( in_array( $filter->status, array( 1 ) ) ){
			$buttonStatus	= UI_HTML_Tag::create( 'a', $iconDeactivate, array(
				'href'		=> './manage/ip/lock/filter/deactivate/'.$filter->ipLockFilterId,
				'class'		=> 'btn btn-inverse btn-small btn-mini',
				'title'		=> 'deaktivieren',
			) );
		}
/*		$buttonRemove	= UI_HTML_Tag::create( 'a', $iconRemove, array(
			'href'		=> './manage/ip/lock/filter/remove/'.$filter->ipLockFilterId,
			'class'		=> 'btn btn-inverse btn-small btn-mini',
			'title'		=> 'remove',
		) );*/
		$appliedAt	= $filter->appliedAt ? date( 'd.m.Y H:i:s', $filter->appliedAt ) : '-';
		if( $filter->appliedAt && $helperTime )
			$appliedAt	= 'vor '.$helperTime->convert( $filter->appliedAt, TRUE );

		$method		= $filter->method ? $filter->method : '<span class="muted">alle</span>';
		$lockStatus	= $lockStates[$filter->lockStatus];
		$buttons	= UI_HTML_Tag::create( 'div', $buttonEdit.$buttonStatus/*.$buttonRemove*/, array( 'class' => 'btn-group' ) );
		$link		= UI_HTML_Tag::create( 'a', $filter->title, array( 'href' => './manage/ip/lock/filter/edit/'.$filter->ipLockFilterId ) );
		$title		= UI_HTML_Tag::create( 'div', $link, array( 'class' => 'autocut' ) );
		$rowClass	= 'success';
		if( $filter->status < 1 )
			$rowClass	= 'warning';
		if( $filter->status < 0 )
			$rowClass	= 'info';

		$reason	= UI_HTML_Tag::create( 'div', $filter->reason->title, array( 'class' => 'autocut' ) );
		$list[]		= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', $method, array( 'class' => 'lock-filter-method' ) ),
			UI_HTML_Tag::create( 'td', $title, array( 'class' => 'lock-filter-title' ) ),
			UI_HTML_Tag::create( 'td', $reason, array( 'class' => 'lock-filter-reason' ) ),
			UI_HTML_Tag::create( 'td', $lockStatus, array( 'class' => 'lock-filter-lock-status' ) ),
			UI_HTML_Tag::create( 'td', $states[$filter->status], array( 'class' => 'lock-filter-status' ) ),
			UI_HTML_Tag::create( 'td', '<small>'.$appliedAt.'</small>', array( 'class' => 'lock-filter-applied' ) ),
			UI_HTML_Tag::create( 'td', $buttons, array( 'class' => 'lock-buttons' ) ),
		), array( 'class' => $rowClass ) );
	}
	$heads	= array(
		'Methode',
		'Titel',
		'Grund',
		'Folge',
		'Zustand',
		'Anwendung',
		'Aktion',
	);
	$colgroup	= UI_HTML_Elements::ColumnGroup( "80px", "", "", "90px", "120px", "110px", "80px" );
	$thead	= UI_HTML_Tag::create( 'thead', UI_HTML_Elements::TableHeads( $heads ) );
	$tbody	= UI_HTML_Tag::create( 'tbody', $list );
	$list	= UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody, array( 'class' => 'table table-condensed' ) );
}

$buttonAdd		= UI_HTML_Tag::create( 'a', $iconAdd.' hinzufügen', array(
	'href'	=> './manage/ip/lock/filter/add',
	'class'	=> 'btn btn-primary',
) );

$panelList	= HTML::DivClass( 'content-panel',
	HTML::H3( 'IP-Sperr-Filter' ).
	HTML::DivClass( 'content-panel-inner',
		$list.
		HTML::DivClass( 'buttonbar',
			$buttonAdd
		)
	)
);

$tabs	= View_Manage_Ip_Lock::renderTabs( $env, 'filter' );
return $tabs.$panelList;
