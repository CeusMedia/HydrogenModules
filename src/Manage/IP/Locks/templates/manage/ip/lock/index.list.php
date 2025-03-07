<?php

use CeusMedia\Bootstrap\Nav\PageControl;
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;

/** @var WebEnvironment $env */
/** @var array<Entity_Server_IP_Lock> $locks */
/** @var int $total */
/** @var int $count */
/** @var int $limit */
/** @var int $page */

$helperTime = FALSE;
if( $env->getModules()->has( 'UI_Helper_TimePhraser' ) )
    $helperTime     = new View_Helper_TimePhraser( $env );

$urlSuffixFrom	= '';
if( $page > 0 )
	$urlSuffixFrom	= '?from=manage/ip/lock/'.$limit.'/'.$page;

//$uri			= './manage/ip/lock/'.$limit;
$table			= renderList( $locks, $urlSuffixFrom, $helperTime );
$buttonAdd		= renderAddButton( $env );
$pagination		= new PageControl( './manage/ip/lock/15', $page, ceil( $total / 15 ) );
$listNumbers	= renderListNumbers( $page, $limit, $count, $total );

$panelList	= HTML::DivClass( 'content-panel',
	HtmlTag::create( 'h3', 'IP-Sperren&nbsp;'.$listNumbers ).
	HTML::DivClass( 'content-panel-inner',
		$table.
		HTML::DivClass( 'buttonbar',
			HTML::DivClass( 'btn-toolbar',
				$pagination.
				$buttonAdd
			)
		)
	)
);

function renderAddButton( WebEnvironment $env ): string
{
	$iconAdd	= HtmlTag::create( 'i', '', ['class' => 'icon-plus icon-white'] );
	if( $env->getModules()->has( 'UI_Font_FontAwesome' ) )
		$iconAdd	= HtmlTag::create( 'b', '', ['class' => 'fa fa-fw fa-plus'] );
	return HtmlTag::create( 'a', $iconAdd.' hinzufügen', [
		'href'	=> './manage/ip/lock/add',
		'class'	=> 'btn btn-primary',
	] );
}
function renderList( array $locks, string $urlSuffixFrom, $helperTime ): string
{
	if( [] === $locks )
		return '<div><em><small>Keine IP-Locks gefunden.</small></em></div>';

	$list	= [];
	foreach( $locks as $lock ){
		if( $lock->reason->status < Model_IP_Lock_Reason::STATUS_ENABLED )
			$lock->status = Model_IP_Lock::STATUS_DISABLED_BY_REASON;
		$list[]	= renderRow( $lock, $urlSuffixFrom, $helperTime );
	}

	$heads	= [
		'IP-Adresse',
		'Zustand',
		'Sperrung',
		'Aufhebung',
		'Grund',
		'Aktion',
	];
	$colgroup	= HtmlElements::ColumnGroup( "140px", "12%", "120px", "130px", "", "90px" );
	$thead		= HtmlTag::create( 'thead', HtmlElements::TableHeads( $heads ) );
	$tbody		= HtmlTag::create( 'tbody', $list );
	return HtmlTag::create( 'table', $colgroup.$thead.$tbody, ['class' => 'table table-condensed'] );
}

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

function renderRow( Entity_Server_IP_Lock $lock, string $urlSuffixFrom, $helperTime ): string
{
	$statuses	= [
		Model_IP_Lock::STATUS_DISABLED_BY_REASON	=> '<abbr title="Grund für diese Sperre wurde deaktiviert">deaktiviert</abbr>',
		Model_IP_Lock::STATUS_UNLOCKED				=> 'unlocked',
		Model_IP_Lock::STATUS_CANCELLED				=> '...',
		Model_IP_Lock::STATUS_REQUEST_LOCK			=> 'lock requested',
		Model_IP_Lock::STATUS_LOCKED				=> 'locked',
		Model_IP_Lock::STATUS_REQUEST_UNLOCK		=> 'unlock requested',
	];

	$lockedAt	= renderRowLockDate( $lock, $helperTime );
	$unlockAt	= renderRowUnlockDate( $lock );
	$buttons	= renderRowButtons( $lock, $urlSuffixFrom );

	$link	= HtmlTag::create( 'a', '<kbd><small>'.$lock->IP.'</small></kbd>', [
		'href'	=> './manage/ip/lock/edit/'.$lock->ipLockId,
	] );
	$reason	= HtmlTag::create( 'div', $lock->reason->title, ['class' => 'autocut'] );
	$rowClass	= 'success';
	if( $lock->status < 1 )
		$rowClass	= 'warning';
	if( $lock->reason->status < 1 )
		$rowClass	= 'info';

	return HtmlTag::create( 'tr', [
		HtmlTag::create( 'td', '<small>'.$link.'</small>', ['class' => 'lock-ip'] ),
		HtmlTag::create( 'td', $statuses[$lock->status], ['class' => 'lock-status'] ),
		HtmlTag::create( 'td', '<small>'.$lockedAt.'</small>', ['class' => 'lock-lockedAt'] ),
		HtmlTag::create( 'td', '<small>'.$unlockAt.'</small>', ['class' => 'lock-unlockAt'] ),
		HtmlTag::create( 'td', '<small>'.$reason.'</small>', ['class' => 'lock-reason-title'] ),
		HtmlTag::create( 'td', $buttons, ['class' => 'lock-buttons'] ),
	], ['class' => $rowClass] );
}
function renderRowButtons( Entity_Server_IP_Lock $lock, string $urlSuffixFrom ): string
{
//	$iconView	= HtmlTag::create( 'i', '', ['class' => 'icon-eye-open'] );
	$iconEdit	= HtmlTag::create( 'i', '', ['class' => 'icon-pencil'] );
	$iconLock	= HtmlTag::create( 'i', '', ['class' => 'icon-ok icon-white'] );
	$iconUnlock	= HtmlTag::create( 'i', '', ['class' => 'icon-remove icon-white'] );
	$iconRemove	= HtmlTag::create( 'i', '', ['class' => 'icon-trash icon-white'] );

	if( $lock->reason->status < Model_IP_Lock_Reason::STATUS_ENABLED )
		$lock->status = Model_IP_Lock::STATUS_DISABLED_BY_REASON;
	$buttonEdit		= HtmlTag::create( 'a', $iconEdit, [
		'href'		=> './manage/ip/lock/edit/'.$lock->ipLockId,
		'class'		=> 'btn btn-small',
		'title'		=> 'bearbeiten',
	] );
	$buttonStatus	= '';
	$statusesLockable		= [
		Model_IP_Lock::STATUS_UNLOCKED,
		Model_IP_Lock::STATUS_CANCELLED,
		Model_IP_Lock::STATUS_REQUEST_LOCK
	];
	$statusesUnlockable		= [
		Model_IP_Lock::STATUS_LOCKED,
		Model_IP_Lock::STATUS_REQUEST_UNLOCK,
	];
	$statusesRemovable		= [
		Model_IP_Lock::STATUS_UNLOCKED,
		Model_IP_Lock::STATUS_DISABLED_BY_REASON,
	];
	if( in_array( $lock->status, $statusesLockable, TRUE ) ){
		$buttonStatus	= HtmlTag::create( 'a', $iconLock, [
			'href'		=> './manage/ip/lock/lock/'.$lock->ipLockId.$urlSuffixFrom,
			'class'		=> 'btn btn-small btn-success',
			'title'		=> 'aktivieren',
		] );
	}
	else if( in_array( $lock->status, $statusesUnlockable, TRUE ) ){
		$buttonStatus	= HtmlTag::create( 'a', $iconUnlock, [
			'href'		=> './manage/ip/lock/unlock/'.$lock->ipLockId.$urlSuffixFrom,
			'class'		=> 'btn btn-small btn-inverse',
			'title'		=> 'deaktivieren',
		] );
	}
	$buttonRemove	= '';
	if( in_array( $lock->status, $statusesRemovable, TRUE ) ){
		$buttonRemove	= HtmlTag::create( 'a', $iconRemove, [
			'href'		=> './manage/ip/lock/cancel/'.$lock->ipLockId.$urlSuffixFrom,
			'class'		=> 'btn btn-small btn-danger',
			'title'		=> 'cancel lock',
		] );
	}

	return HtmlTag::create( 'div', $buttonEdit.$buttonStatus.$buttonRemove, [
		'class'		=> 'btn-group'
	] );
}

function renderRowLockDate( Entity_Server_IP_Lock $lock, $helperTime = NULL ): string
{
	$lockedAt	= date( 'Y-m-d H:i:s', $lock->lockedAt );
	if( $helperTime )
		$lockedAt	= $helperTime->convert( $lock->lockedAt, TRUE, 'vor ' );
	return $lockedAt;
}

function renderRowUnlockDate( Entity_Server_IP_Lock $lock ): string
{
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
	return $unlockAt;
}

return $panelList;
