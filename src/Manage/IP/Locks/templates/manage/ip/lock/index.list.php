<?php

use CeusMedia\Bootstrap\Nav\PageControl;
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

/** @var \CeusMedia\HydrogenFramework\Environment $env */
/** @var array<object> $locks */
/** @var int $total */
/** @var int $count */
/** @var int $limit */
/** @var int $page */

$states	= [
	-10	=> '<abbr title="Grund für diese Sperre wurde deaktiviert">deaktiviert</abbr>',
	-2	=> 'unlocked',
	-1	=> '...',
	0	=> 'lock requested',
	1	=> 'locked',
	2	=> 'unlock requested',
];

$iconView	= HtmlTag::create( 'i', '', ['class' => 'icon-eye-open'] );
$iconEdit	= HtmlTag::create( 'i', '', ['class' => 'icon-pencil'] );
$iconLock	= HtmlTag::create( 'i', '', ['class' => 'icon-ok icon-white'] );
$iconUnlock	= HtmlTag::create( 'i', '', ['class' => 'icon-remove icon-white'] );
$iconRemove	= HtmlTag::create( 'i', '', ['class' => 'icon-trash icon-white'] );

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
		$buttonEdit		= HtmlTag::create( 'a', $iconEdit, [
			'href'		=> './manage/ip/lock/edit/'.$lock->ipLockId,
			'class'		=> 'btn btn-small',
			'title'		=> 'bearbeiten',
		] );
		$buttonStatus	= "";
		if( in_array( $lock->status, [-2, -1, 0] ) ){
			$buttonStatus	= HtmlTag::create( 'a', $iconLock, [
				'href'		=> './manage/ip/lock/lock/'.$lock->ipLockId.$urlSuffixFrom,
				'class'		=> 'btn btn-small btn-success',
				'title'		=> 'aktivieren',
			] );
		}
		else if( in_array( $lock->status, [1, 2] ) ){
			$buttonStatus	= HtmlTag::create( 'a', $iconUnlock, [
				'href'		=> './manage/ip/lock/unlock/'.$lock->ipLockId.$urlSuffixFrom,
				'class'		=> 'btn btn-small btn-inverse',
				'title'		=> 'deaktivieren',
			] );
		}
		$buttonRemove	= "";
		if( in_array( $lock->status, [-2, -10] ) ){
			$buttonRemove	= HtmlTag::create( 'a', $iconRemove, [
				'href'		=> './manage/ip/lock/cancel/'.$lock->ipLockId.$urlSuffixFrom,
				'class'		=> 'btn btn-small btn-danger',
				'title'		=> 'cancel lock',
			] );
		}

		$unlockAt	= '<small class="muted">nie</small>';
		if( $lock->reason->duration ){
			$unlockAt	= $lock->lockedAt + $lock->reason->duration;
			$unlockDate	= HtmlTag::create( 'span', date( "Y-m-d", $unlockAt ), [
				'class' => 'lock-unlock-date',
			] );
			$unlockTime	= HtmlTag::create( 'small', date( "H:i:s", $unlockAt ), [
                'class' => 'lock-unlock-time muted',
            ] );
			$unlockAt	= $unlockDate.'&nbsp;'.$unlockTime;
		}

		$buttons	= HtmlTag::create( 'div', $buttonEdit.$buttonStatus.$buttonRemove, [
			'class'		=> 'btn-group'
		] );

		$lockedAt	= date( 'Y-m-d H:i:s', $lock->lockedAt );
		if( $helperTime )
			$lockedAt	= $helperTime->convert( $lock->lockedAt, TRUE, 'vor ' );

		$link	= HtmlTag::create( 'a', '<kbd><small>'.$lock->IP.'</small></kbd>', [
			'href'	=> './manage/ip/lock/edit/'.$lock->ipLockId,
		] );
		$reason	= HtmlTag::create( 'div', $lock->reason->title, ['class' => 'autocut'] );
		$rowClass	= 'success';
		if( $lock->status < 1 )
			$rowClass	= 'warning';
		if( $lock->reason->status < 1 )
			$rowClass	= 'info';

		$list[]	= HtmlTag::create( 'tr', array(
			HtmlTag::create( 'td', $link, ['class' => 'lock-ip'] ),
			HtmlTag::create( 'td', $states[$lock->status], ['class' => 'lock-status'] ),
			HtmlTag::create( 'td', $lockedAt, ['class' => 'lock-lockedAt'] ),
			HtmlTag::create( 'td', $unlockAt, ['class' => 'lock-unlockAt'] ),
			HtmlTag::create( 'td', $reason, ['class' => 'lock-reason-title'] ),
			HtmlTag::create( 'td', $buttons, ['class' => 'lock-buttons'] ),
		), ['class' => $rowClass] );
	}
	$heads	= [
		'IP-Adresse',
		'Zustand',
		'Sperrung',
		'Aufhebung',
		'Grund',
		'Aktion',
	];
	$colgroup	= HtmlElements::ColumnGroup( "140px", "10%", "120px", "140px", "", "110px" );
	$thead		= HtmlTag::create( 'thead', HtmlElements::TableHeads( $heads ) );
	$tbody		= HtmlTag::create( 'tbody', $list );
	$list		= HtmlTag::create( 'table', $colgroup.$thead.$tbody, ['class' => 'table table-condensed'] );
}

$iconAdd		= HtmlTag::create( 'i', '', ['class' => 'icon-plus icon-white'] );
$buttonAdd		= HtmlTag::create( 'a', $iconAdd.' hinzufügen', [
	'href'	=> './manage/ip/lock/add',
	'class'	=> 'btn btn-primary',
] );


function renderListNumbers( $page, $limit, $count, $total ): string
{
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
	return HtmlTag::create( 'small', '('.$label.')', ['class' => 'muted'] );
}

$uri			= './manage/ip/lock/'.$limit;
//$helperPages	= new View_Helper_Pagination( $env, $total, $limit, $page, $count );
//$pagination		= $helperPages->render( $uri, $total, $limit, $page, FALSE );
//$listNumbers	= $helperPages->renderListNumbers( $total, $limit, $page, $count );
$helperPages	= new PageControl( './manage/ip/lock/15', $page, ceil( $total / 15 ) );
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
