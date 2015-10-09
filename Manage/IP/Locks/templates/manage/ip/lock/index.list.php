<?php

$states	= array(
	-10	=> '<abbr title="Grund für diese Sperre wurde deaktiviert">deaktiviert</abbr>',
	-2	=> 'unlocked',
	-1	=> '...',
	0	=> 'lock requested',
	1	=> 'locked',
	2	=> 'unlock requested',
);

$iconView	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-eye-open' ) );
$iconEdit	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-pencil' ) );
$iconLock	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-ok icon-white' ) );
$iconUnlock	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-remove icon-white' ) );
$iconRemove	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-trash icon-white' ) );

$helperTime = FALSE;
if( $env->getModules()->has( 'UI_Helper_TimePhraser' ) ){
    $helperTime     = new View_Helper_TimePhraser( $env );
}

$urlSuffixFrom	= '';
if( $page > 0 )
	$urlSuffixFrom	= '?from=manage/ip/lock/'.$limit.'/'.$page;

$list	= '<div><em><small>Keine IP-Locks gefunden.</small></em></div>';
if( $locks ){
	$list	= array();
	foreach( $locks as $lock ){
		if( $lock->reason->status < 1 )
			$lock->status = -10;
		$buttonEdit		= UI_HTML_Tag::create( 'a', $iconEdit, array(
			'href'		=> './manage/ip/lock/edit/'.$lock->ipLockId,
			'class'		=> 'btn btn-small',
			'title'		=> 'bearbeiten',
		) );
		$buttonStatus	= "";
		if( in_array( $lock->status, array( -2, -1, 0 ) ) ){
			$buttonStatus	= UI_HTML_Tag::create( 'a', $iconLock, array(
				'href'		=> './manage/ip/lock/lock/'.$lock->ipLockId.$urlSuffixFrom,
				'class'		=> 'btn btn-small btn-success',
				'title'		=> 'aktivieren',
			) );
		}
		else if( in_array( $lock->status, array( 1, 2 ) ) ){
			$buttonStatus	= UI_HTML_Tag::create( 'a', $iconUnlock, array(
				'href'		=> './manage/ip/lock/unlock/'.$lock->ipLockId.$urlSuffixFrom,
				'class'		=> 'btn btn-small btn-inverse',
				'title'		=> 'deaktivieren',
			) );
		}
		$buttonRemove	= "";
		if( in_array( $lock->status, array( -2, -10 ) ) ){
			$buttonRemove	= UI_HTML_Tag::create( 'a', $iconRemove, array(
				'href'		=> './manage/ip/lock/remove/'.$lock->ipLockId.$urlSuffixFrom,
				'class'		=> 'btn btn-small btn-danger',
				'title'		=> 'remove',
			) );
		}

		$unlockAt	= '<small class="muted">nie</small>';
		if( $lock->reason->duration ){
			$unlockAt	= $lock->lockedAt + $lock->reason->duration;
			$unlockDate	= UI_HTML_Tag::create( 'span', date( "Y-m-d", $unlockAt ), array(
				'class' => 'lock-unlock-date',
			) );
			$unlockTime	= UI_HTML_Tag::create( 'small', date( "H:i:s", $unlockAt ), array(
                'class' => 'lock-unlock-time muted',
            ) );
			$unlockAt	= $unlockDate.'&nbsp;'.$unlockTime;
		}

		$buttons	= UI_HTML_Tag::create( 'div', $buttonEdit.$buttonStatus.$buttonRemove, array(
			'class'		=> 'btn-group'
		) );

		$lockedAt	= date( 'Y-m-d H:i:s', $lock->lockedAt );
		if( $helperTime )
			$lockedAt	= $helperTime->convert( $lock->lockedAt, TRUE, 'vor ' );

		$link	= UI_HTML_Tag::create( 'a', '<kbd>'.$lock->IPv4.'</kbd>', array(
			'href'	=> './manage/ip/lock/edit/'.$lock->ipLockId,
		) );
		$reason	= UI_HTML_Tag::create( 'div', $lock->reason->title, array( 'class' => 'autocut' ) );
		$rowClass	= 'success';
		if( $lock->status < 1 )
			$rowClass	= 'warning';
		if( $lock->reason->status < 1 )
			$rowClass	= 'info';

		$list[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', $link, array( 'class' => 'lock-ip' ) ),
			UI_HTML_Tag::create( 'td', $states[$lock->status], array( 'class' => 'lock-status' ) ),
			UI_HTML_Tag::create( 'td', $lockedAt, array( 'class' => 'lock-lockedAt' ) ),
			UI_HTML_Tag::create( 'td', $unlockAt, array( 'class' => 'lock-unlockAt' ) ),
			UI_HTML_Tag::create( 'td', $reason, array( 'class' => 'lock-reason-title' ) ),
			UI_HTML_Tag::create( 'td', $buttons, array( 'class' => 'lock-buttons' ) ),
		), array( 'class' => $rowClass ) );
	}
	$heads	= array(
		'IP-Adresse',
		'Zustand',
		'Sperrung',
		'Aufhebung',
		'Grund',
		'Aktion',
	);
	$colgroup	= UI_HTML_Elements::ColumnGroup( "140px", "10%", "120px", "140px", "", "110px" );
	$thead		= UI_HTML_Tag::create( 'thead', UI_HTML_Elements::TableHeads( $heads ) );
	$tbody		= UI_HTML_Tag::create( 'tbody', $list );
	$list		= UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody, array( 'class' => 'table table-condensed' ) );
}

$iconAdd		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-plus icon-white' ) );
$buttonAdd		= UI_HTML_Tag::create( 'a', $iconAdd.' hinzufügen', array(
	'href'	=> './manage/ip/lock/add',
	'class'	=> 'btn btn-primary',
) );


function renderListNumbers( $page, $limit, $count, $total ){
	$label	= $count;
	if( $total > $limit ){
		$spanTotal	= '<span class="list-number-total">'.$total.'</span>';
		$spanCount	= '<span class="list-number-view">'.$count.'</span>';
		$spanRange	= '<span class="list-number-range">'.( $page * $limit + 1 ).'</span>';
		if( $count > 1 ){
			$spanFrom	= '<span class="list-number-from">'.( $page * $limit + 1 ).'</span>';
			$spanTo		= '<span class="list-number-to">'.( $page * $limit + $count ).'</span>';
			$spanRange	= '<span class="list-number-range">'.$spanFrom.'&minus;'.$spanTo.'</span>';
		}
		$label	= $spanRange.' von '.$spanTotal;
	}
	return UI_HTML_Tag::create( 'small', '('.$label.')', array( 'class' => 'muted' ) );
}

$uri			= './manage/ip/lock/'.$limit;
$helperPages	= new View_Helper_Pagination( $env, $total, $limit, $page, $count );
$pagination		= $helperPages->render( $uri, $total, $limit, $page, FALSE );
$listNumbers	= $helperPages->renderListNumbers( $total, $limit, $page, $count );

$panelList	= HTML::DivClass( 'content-panel',
	UI_HTML_Tag::create( 'h3', 'IP-Sperren&nbsp;'.$listNumbers ).
	HTML::DivClass( 'content-panel-inner',
		$list.
		HTML::DivClass( 'buttonbar',
			HTML::DivClass( 'btn-toolbar',
				$pagination.
				$buttonAdd
			)
		)
	)
);
return $panelList;
