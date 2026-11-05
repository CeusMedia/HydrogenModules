<?php

use CeusMedia\Bootstrap\Icon;
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web;

/** @var Web $env */
/** @var array<Entity_IP_Lock_Filter> $filters */
/** @var bool $canAdd */
/** @var bool $canEdit */
/** @var bool $canActivate */
/** @var bool $canDeactivate */
/** @var bool $canRemove */
/** @var array<string,array<string,string>> $words */

$w	= (object) $words['index'];

$statuses	= [
	Model_IP_Lock_Filter::STATUS_DISABLED_BY_REASON	=> '<abbr title="'.htmlentities( $w->hintDisabledByReason, ENT_QUOTES, 'UTF-8' ).'">'.$words['statuses'][Model_IP_Lock_Filter::STATUS_DISABLED_BY_REASON].'</abbr>',
	Model_IP_Lock_Filter::STATUS_DISABLED			=> $words['statuses'][Model_IP_Lock_Filter::STATUS_DISABLED],
	Model_IP_Lock_Filter::STATUS_ENABLED			=> $words['statuses'][Model_IP_Lock_Filter::STATUS_ENABLED],
];

$statusLabels	= [
	Model_IP_Lock_Filter::STATUS_DISABLED_BY_REASON	=> HtmlTag::create( 'span', $statuses[Model_IP_Lock_Filter::STATUS_DISABLED_BY_REASON], ['class' => 'label label-inverse'] ),
	Model_IP_Lock_Filter::STATUS_DISABLED			=> HtmlTag::create( 'span', $statuses[Model_IP_Lock_Filter::STATUS_DISABLED], ['class' => 'label label-inverse'] ),
	Model_IP_Lock_Filter::STATUS_ENABLED			=> HtmlTag::create( 'span', $statuses[Model_IP_Lock_Filter::STATUS_ENABLED], ['class' => 'label label-success'] ),
];



$iconAdd		= HtmlTag::create( 'i', '', ['class' => 'icon-plus icon-white'] );
$iconEdit		= HtmlTag::create( 'i', '', ['class' => 'icon-pencil'] );
$iconRemove		= HtmlTag::create( 'i', '', ['class' => 'icon-trash icon-white'] );
$iconActivate	= HtmlTag::create( 'i', '', ['class' => 'icon-ok icon-white'] );
$iconDeactivate	= HtmlTag::create( 'i', '', ['class' => 'icon-remove icon-white'] );
if( $env->getModules()->has( 'UI_Bootstrap' ) ){
	$iconAdd		= new Icon( 'plus' );
	$iconEdit		= new Icon( 'pencil' );
	$iconRemove		= new Icon( 'trash' );
	$iconActivate	= new Icon( 'check' );
	$iconDeactivate	= new Icon( 'times' );
}
else if( $env->getModules()->has( 'UI_Font_FontAwesome' ) ){
	$iconAdd		= HtmlTag::create( 'b', '', ['class' => 'fa fa-fw fa-plus fa-inverse'] );
	$iconEdit		= HtmlTag::create( 'b', '', ['class' => 'fa fa-fw fa-pencil'] );
	$iconRemove		= HtmlTag::create( 'b', '', ['class' => 'fa fa-fw fa-trash fa-inverse'] );
	$iconActivate	= HtmlTag::create( 'b', '', ['class' => 'fa fa-fw fa-check fa-inverse'] );
	$iconDeactivate	= HtmlTag::create( 'b', '', ['class' => 'fa fa-fw fa-times fa-inverse'] );
}
$helperTime	= FALSE;
if( $env->getModules()->has( 'UI_Helper_TimePhraser' ) ){
	$helperTime		= new View_Helper_TimePhraser( $env );
}

$lockStates	= $words['lockStatusesShort'];

$list	= '<div><em><small>'.$w->noEntries.'</small></em></div>';
if( $filters ){
	$list	= [];
	foreach( $filters as $filter ){
		if( $filter->reason->status < Model_IP_Lock_Reason::STATUS_ENABLED )
			$filter->status	= Model_IP_Lock_Filter::STATUS_DISABLED_BY_REASON;

		$buttonEdit		= '';
		if( $canEdit )
			$buttonEdit		= HtmlTag::create( 'a', $iconEdit, [
				'href'		=> './manage/ip/lock/filter/edit/'.$filter->ipLockFilterId,
				'class'		=> 'btn not-btn-primary btn-small btn-mini',
				'title'		=> $w->buttonEdit,
			] );
		$buttonStatus	= "";

		if( $canActivate && Model_IP_Lock_Filter::STATUS_DISABLED === $filter->status ){
			$buttonStatus	= HtmlTag::create( 'a', $iconActivate, [
				'href'		=> './manage/ip/lock/filter/activate/'.$filter->ipLockFilterId,
				'class'		=> 'btn btn-success btn-small btn-mini',
				'title'		=> $w->buttonActivate,
			] );
		}
		else if( $canDeactivate && Model_IP_Lock_Filter::STATUS_ENABLED === $filter->status ){
			$buttonStatus	= HtmlTag::create( 'a', $iconDeactivate, [
				'href'		=> './manage/ip/lock/filter/deactivate/'.$filter->ipLockFilterId,
				'class'		=> 'btn btn-inverse btn-small btn-mini',
				'title'		=> $w->buttonDeactivate,
			] );
		}
/*		$buttonRemove	= HtmlTag::create( 'a', $iconRemove, [
			'href'		=> './manage/ip/lock/filter/remove/'.$filter->ipLockFilterId,
			'class'		=> 'btn btn-inverse btn-small btn-mini',
			'title'		=> 'remove',
		] );*/
		$appliedAt	= $filter->appliedAt ? date( 'd.m.Y H:i:s', $filter->appliedAt ) : '-';
		if( $filter->appliedAt && $helperTime )
			$appliedAt	= $helperTime->convert( $filter->appliedAt, TRUE, $w->timePrefix, $w->timeSuffix );

		$method		= $filter->method ?: '<span class="muted">'.$w->methodAll.'</span>';
		$lockStatus	= $lockStates[$filter->lockStatus];
		$buttons	= HtmlTag::create( 'div', $buttonEdit.$buttonStatus/*.$buttonRemove*/, ['class' => 'btn-group'] );

		$link		= $filter->title;
		if( $canEdit )
			$link		= HtmlTag::create( 'a', $filter->title, ['href' => './manage/ip/lock/filter/edit/'.$filter->ipLockFilterId] );

		$title		= HtmlTag::create( 'div', $link, ['class' => 'autocut'] );
		$rowClass	= 'success';
		if( $filter->status < Model_IP_Lock_Filter::STATUS_ENABLED )
			$rowClass	= 'warning';
		if( $filter->status < Model_IP_Lock_Filter::STATUS_DISABLED )
			$rowClass	= 'info';

		$reason	= HtmlTag::create( 'div', $filter->reason->title, ['class' => 'autocut'] );
		$list[]		= HtmlTag::create( 'tr', [
			HtmlTag::create( 'td', $method, ['class' => 'lock-filter-method'] ),
			HtmlTag::create( 'td', $title, ['class' => 'lock-filter-title'] ),
			HtmlTag::create( 'td', $reason, ['class' => 'lock-filter-reason'] ),
			HtmlTag::create( 'td', $lockStatus, ['class' => 'lock-filter-lock-status'] ),
			HtmlTag::create( 'td', $statusLabels[$filter->status], ['class' => 'lock-filter-status'] ),
			HtmlTag::create( 'td', '<small>'.$appliedAt.'</small>', ['class' => 'lock-filter-applied'] ),
			HtmlTag::create( 'td', $buttons, ['class' => 'lock-buttons'] ),
		] );
	}
	$heads	= [
		$w->headMethod,
		$w->headTitle,
		$w->headReason,
		$w->headLockStatus,
		$w->headStatus,
		$w->headAppliedAt,
		$w->headActions,
	];
	$colgroup	= HtmlElements::ColumnGroup( '80px', '', '', '90px', '120px', '110px', '80px' );
	$thead	= HtmlTag::create( 'thead', HtmlElements::TableHeads( $heads ) );
	$tbody	= HtmlTag::create( 'tbody', $list );
	$list	= HtmlTag::create( 'table', $colgroup.$thead.$tbody, ['class' => 'table table-condensed'] );
}

$buttonAdd		= HtmlTag::create( 'a', $iconAdd.' '.$w->buttonAdd, [
	'href'	=> './manage/ip/lock/filter/add',
	'class'	=> 'btn btn-primary',
] );

$panelList	= HTML::DivClass( 'content-panel',
	HTML::H3( $w->heading ).
	HTML::DivClass( 'content-panel-inner',
		$list.
		HTML::DivClass( 'buttonbar',
			$buttonAdd
		)
	)
);

$tabs	= View_Manage_IP_Lock::renderTabs( $env, 'filter' );
return $tabs.$panelList;
