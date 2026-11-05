<?php

use CeusMedia\Bootstrap\Icon;
use CeusMedia\Common\Net\HTTP\Status as HttpStatus;
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web;

/** @var Web $env */
/** @var array $reasons */
/** @var bool $canAdd */
/** @var bool $canEdit */
/** @var bool $canActivate */
/** @var bool $canDeactivate */
/** @var bool $canRemove */
/** @var array<string,array<string,string>> $words */

$w	= (object) $words['index'];

$iconAdd	= HtmlTag::create( 'i', '', ['class' => 'icon-plus icon-white'] );
$iconEdit	= HtmlTag::create( 'i', '', ['class' => 'icon-pencil'] );
$iconRemove	= HtmlTag::create( 'i', '', ['class' => 'icon-trash icon-white'] );
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

$statusLabels	= [
	Model_IP_Lock_Reason::STATUS_DISABLED	=> HtmlTag::create( 'span', $words['statuses'][Model_IP_Lock_Reason::STATUS_DISABLED], ['class' => 'label label-inverse'] ),
	Model_IP_Lock_Reason::STATUS_ENABLED	=> HtmlTag::create( 'span', $words['statuses'][Model_IP_Lock_Reason::STATUS_ENABLED], ['class' => 'label label-success'] ),
];



$list	= '<div><em><small>'.$w->noEntries.'</small></em></div>';
if( $reasons ){
	$list	= [];
	foreach( $reasons as $reason ){
		$buttonEdit		= HtmlTag::create( 'a', $iconEdit, [
			'href'		=> './manage/ip/lock/reason/edit/'.$reason->ipLockReasonId,
			'class'		=> 'btn not-btn-primary btn-small btn-mini',
			'title'		=> $w->buttonEdit,
		] );
		$buttonStatus	= HtmlTag::create( 'a', $iconActivate, [
			'href'		=> './manage/ip/lock/reason/activate/'.$reason->ipLockReasonId,
			'class'		=> 'btn btn-success btn-small btn-mini',
			'title'		=> $w->buttonActivate,
		] );
		if( Model_IP_Lock_Reason::STATUS_ENABLED === $reason->status ){
			$buttonStatus	= HtmlTag::create( 'a', $iconDeactivate, [
				'href'		=> './manage/ip/lock/reason/deactivate/'.$reason->ipLockReasonId,
				'class'		=> 'btn btn-inverse btn-small btn-mini',
				'title'		=> $w->buttonDeactivate,
			] );
		}
/*		$buttonRemove	= HtmlTag::create( 'a', $iconRemove, [
			'href'		=> './manage/ip/lock/reason/remove/'.$reason->ipLockReasonId,
			'class'		=> 'btn btn-inverse btn-small btn-mini',
			'title'		=> 'entfernen',
		] );*/
		$createdAt	= $reason->createdAt ? date( 'd.m.Y H:i:s', $reason->createdAt ) : '-';
		if( $reason->createdAt && $helperTime )
			$createdAt	= $helperTime->convert( $reason->createdAt, TRUE, $w->timePrefix, $w->timeSuffix );
		$appliedAt	= $reason->appliedAt ? date( 'd.m.Y H:i:s', $reason->appliedAt ) : '-';
		if( $reason->appliedAt && $helperTime )
			$appliedAt	= $helperTime->convert( $reason->appliedAt, TRUE, $w->timePrefix, $w->timeSuffix );
		$httpCode	= HtmlTag::create( 'abbr', $reason->code, ['title' => HttpStatus::getText( $reason->code )] );
		$duration	= $reason->duration ?: '-';
		if( $reason->duration && $helperTime )
			$duration	= $helperTime->convert( time() - $reason->duration, !TRUE, $w->durationPrefix );

		$link		= HtmlTag::create( 'a', $reason->title, ['href' => './manage/ip/lock/reason/edit/'.$reason->ipLockReasonId] );
		$buttons	= HtmlTag::create( 'div', $buttonEdit.$buttonStatus/*.$buttonRemove*/, ['class' => 'btn-group'] );
		$list[]		= HtmlTag::create( 'tr', [
			HtmlTag::create( 'td', $httpCode, ['class' => 'lock-reason-code'] ),
			HtmlTag::create( 'td', $link, ['class' => 'lock-reason-title'] ),
			HtmlTag::create( 'td', '<small>'.$duration.'</small>', ['class' => 'lock-reason-duration'] ),
			HtmlTag::create( 'td', $statusLabels[$reason->status], ['class' => 'lock-reason-duration'] ),
			HtmlTag::create( 'td', '<small>'.$createdAt.'</small>', ['class' => 'lock-reason-created'] ),
			HtmlTag::create( 'td', '<small>'.$appliedAt.'</small>', ['class' => 'lock-reason-applied'] ),
			HtmlTag::create( 'td', $buttons, ['class' => 'lock-buttons'] ),
		] );
	}
	$heads	= [
		HtmlTag::create( 'abbr', $w->headCode, ['title' => $w->headCode_title] ),
		$w->headTitle,
		HtmlTag::create( 'abbr', $w->headDuration, ['title' => $w->headDuration_title] ),
		$w->headStatus,
		$w->headCreatedAt,
		HtmlTag::create( 'abbr', $w->headAppliedAt, ['title' => $w->headAppliedAt_title] ),
		$w->headActions,
	];
	$colgroup	= HtmlElements::ColumnGroup( "50", "", "120", "100", "110", "110", "100" );
	$thead		= HtmlTag::create( 'thead', HtmlElements::TableHeads( $heads ) );
	$tbody		= HtmlTag::create( 'tbody', $list );
	$list		= HtmlTag::create( 'table', $colgroup.$thead.$tbody, ['class' => 'table table-condensed'] );
}

$buttonAdd		= HtmlTag::create( 'a', $iconAdd.' '.$w->buttonAdd, [
	'href'	=> './manage/ip/lock/reason/add',
	'class'	=> 'btn btn-primary',
] );

$panelList	= HTML::DivClass( 'content-panel',
	HtmlTag::create( 'h3', $w->heading ).
	HTML::DivClass( 'content-panel-inner',
		$list.
		HTML::DivClass( 'buttonbar',
			$buttonAdd
		)
	)
);

$tabs	= View_Manage_IP_Lock::renderTabs( $env, 'reason' );
return $tabs.$panelList;
