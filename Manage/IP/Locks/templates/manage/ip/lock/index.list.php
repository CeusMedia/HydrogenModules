<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$states	= array(
	-10	=> '<abbr title="Grund für diese Sperre wurde deaktiviert">deaktiviert</abbr>',
	-2	=> 'unlocked',
	-1	=> '...',
	0	=> 'lock requested',
	1	=> 'locked',
	2	=> 'unlock requested',
);

$iconView	= HtmlTag::create( 'i', '', array( 'class' => 'icon-eye-open' ) );
$iconEdit	= HtmlTag::create( 'i', '', array( 'class' => 'icon-pencil' ) );
$iconLock	= HtmlTag::create( 'i', '', array( 'class' => 'icon-ok icon-white' ) );
$iconUnlock	= HtmlTag::create( 'i', '', array( 'class' => 'icon-remove icon-white' ) );
$iconRemove	= HtmlTag::create( 'i', '', array( 'class' => 'icon-trash icon-white' ) );

$helperTime = FALSE;
if( $env->getModules()->has( 'UI_Helper_TimePhraser' ) ){
    $helperTime     = new View_Helper_TimePhraser( $env );
}

$urlSuffixFrom	= '';
if( $page > 0 )
	$urlSuffixFrom	= '?from=manage/ip/lock/'.$limit.'/'.$page;

$list	= '<div><em><small>Keine IP-Locks gefunden.</small></em></div>';
if( $locks ){
	$list	= [];
	foreach( $locks as $lock ){
		if( $lock->reason->status < 1 )
			$lock->status = -10;
		$buttonEdit		= HtmlTag::create( 'a', $iconEdit, array(
			'href'		=> './manage/ip/lock/edit/'.$lock->ipLockId,
			'class'		=> 'btn btn-small',
			'title'		=> 'bearbeiten',
		) );
		$buttonStatus	= "";
		if( in_array( $lock->status, array( -2, -1, 0 ) ) ){
			$buttonStatus	= HtmlTag::create( 'a', $iconLock, array(
				'href'		=> './manage/ip/lock/lock/'.$lock->ipLockId.$urlSuffixFrom,
				'class'		=> 'btn btn-small btn-success',
				'title'		=> 'aktivieren',
			) );
		}
		else if( in_array( $lock->status, array( 1, 2 ) ) ){
			$buttonStatus	= HtmlTag::create( 'a', $iconUnlock, array(
				'href'		=> './manage/ip/lock/unlock/'.$lock->ipLockId.$urlSuffixFrom,
				'class'		=> 'btn btn-small btn-inverse',
				'title'		=> 'deaktivieren',
			) );
		}
		$buttonRemove	= "";
		if( in_array( $lock->status, array( -2, -10 ) ) ){
			$buttonRemove	= HtmlTag::create( 'a', $iconRemove, array(
				'href'		=> './manage/ip/lock/cancel/'.$lock->ipLockId.$urlSuffixFrom,
				'class'		=> 'btn btn-small btn-danger',
				'title'		=> 'cancel lock',
			) );
		}

		$unlockAt	= '<small class="muted">nie</small>';
		if( $lock->reason->duration ){
			$unlockAt	= $lock->lockedAt + $lock->reason->duration;
			$unlockDate	= HtmlTag::create( 'span', date( "Y-m-d", $unlockAt ), array(
				'class' => 'lock-unlock-date',
			) );
			$unlockTime	= HtmlTag::create( 'small', date( "H:i:s", $unlockAt ), array(
                'class' => 'lock-unlock-time muted',
            ) );
			$unlockAt	= $unlockDate.'&nbsp;'.$unlockTime;
		}

		$buttons	= HtmlTag::create( 'div', $buttonEdit.$buttonStatus.$buttonRemove, array(
			'class'		=> 'btn-group'
		) );

		$lockedAt	= date( 'Y-m-d H:i:s', $lock->lockedAt );
		if( $helperTime )
			$lockedAt	= $helperTime->convert( $lock->lockedAt, TRUE, 'vor ' );

		$link	= HtmlTag::create( 'a', '<kbd><small>'.$lock->IP.'</small></kbd>', array(
			'href'	=> './manage/ip/lock/edit/'.$lock->ipLockId,
		) );
		$reason	= HtmlTag::create( 'div', $lock->reason->title, array( 'class' => 'autocut' ) );
		$rowClass	= 'success';
		if( $lock->status < 1 )
			$rowClass	= 'warning';
		if( $lock->reason->status < 1 )
			$rowClass	= 'info';

		$list[]	= HtmlTag::create( 'tr', array(
			HtmlTag::create( 'td', $link, array( 'class' => 'lock-ip' ) ),
			HtmlTag::create( 'td', $states[$lock->status], array( 'class' => 'lock-status' ) ),
			HtmlTag::create( 'td', $lockedAt, array( 'class' => 'lock-lockedAt' ) ),
			HtmlTag::create( 'td', $unlockAt, array( 'class' => 'lock-unlockAt' ) ),
			HtmlTag::create( 'td', $reason, array( 'class' => 'lock-reason-title' ) ),
			HtmlTag::create( 'td', $buttons, array( 'class' => 'lock-buttons' ) ),
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
	$thead		= HtmlTag::create( 'thead', UI_HTML_Elements::TableHeads( $heads ) );
	$tbody		= HtmlTag::create( 'tbody', $list );
	$list		= HtmlTag::create( 'table', $colgroup.$thead.$tbody, array( 'class' => 'table table-condensed' ) );
}

$iconAdd		= HtmlTag::create( 'i', '', array( 'class' => 'icon-plus icon-white' ) );
$buttonAdd		= HtmlTag::create( 'a', $iconAdd.' hinzufügen', array(
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
	return HtmlTag::create( 'small', '('.$label.')', array( 'class' => 'muted' ) );
}

$uri			= './manage/ip/lock/'.$limit;
//$helperPages	= new View_Helper_Pagination( $env, $total, $limit, $page, $count );
//$pagination		= $helperPages->render( $uri, $total, $limit, $page, FALSE );
//$listNumbers	= $helperPages->renderListNumbers( $total, $limit, $page, $count );
$helperPages	= new \CeusMedia\Bootstrap\Nav\PageControl( './manage/ip/lock/15', $page, ceil( $total / 15 ) );
$pagination		= $helperPages->render();
$listNumbers	= renderListNumbers( $page, $limit, $count, $total );

$panelList	= HTML::DivClass( 'content-panel',
	HtmlTag::create( 'h3', 'IP-Sperren&nbsp;'.$listNumbers ).
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
