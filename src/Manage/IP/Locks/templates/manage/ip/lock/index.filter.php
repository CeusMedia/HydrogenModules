<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web;

/** @var Web $env */
/** @var ?string $filterStatus */
/** @var ?string $filterSort */
/** @var ?string $filterOrder */
/** @var bool $canOrder */
/** @var array<string,array<string,string>> $words */

$w	= (object) $words['index.filter'];

$iconFilter		= HtmlTag::create( 'i', '', ['class' => 'icon-search icon-white'] );
$iconReset		= HtmlTag::create( 'i', '', ['class' => 'icon-zoom-out icon-white'] );
$buttonFilter	= HtmlTag::create( 'button', $iconFilter.' '.$w->buttonFilter, [
	'type'	=> 'submit',
	'name'	=> 'filter',
	'class'	=> 'btn btn-info',
] );
$buttonReset	= HtmlTag::create( 'a', $iconReset.' '.$w->buttonReset, [
	'href'	=> './manage/ip/lock/order/reset',
	'class'	=> 'btn btn-small btn-inverse',
] );

$optStatus = [
	Model_IP_Lock::STATUS_DISABLED_BY_REASON	=> $words['statuses'][Model_IP_Lock::STATUS_DISABLED_BY_REASON],
	Model_IP_Lock::STATUS_UNLOCKED				=> $words['statuses'][Model_IP_Lock::STATUS_UNLOCKED],
	Model_IP_Lock::STATUS_CANCELLED				=> $words['statuses'][Model_IP_Lock::STATUS_CANCELLED],
	''	=> $w->optionAll,
	Model_IP_Lock::STATUS_REQUEST_LOCK			=> $words['statuses'][Model_IP_Lock::STATUS_REQUEST_LOCK],
	Model_IP_Lock::STATUS_LOCKED				=> $words['statuses'][Model_IP_Lock::STATUS_LOCKED],
	Model_IP_Lock::STATUS_REQUEST_UNLOCK		=> $words['statuses'][Model_IP_Lock::STATUS_REQUEST_UNLOCK],

];
$optStatus	= HtmlElements::Options( $optStatus, $filterStatus );

$optSort	= [
	'lockedAt'	=> $w->sortLockedAt,
	'IP'		=> $w->sortIp,
];
$optSort	= HtmlElements::Options( $optSort, $filterSort );

$optOrder	= [
	'asc'	=> $w->orderAsc,
	'desc'	=> $w->orderDesc,
];
$optOrder	= HtmlElements::Options( $optOrder, $filterOrder );

$panelFilter	= HTML::DivClass( 'content-panel',
	HtmlTag::create( 'h3', $w->heading ).
	HTML::DivClass( 'content-panel-inner',
		HtmlTag::create( 'form', [
			HTML::DivClass( 'row-fluid',
				HTML::DivClass( 'span12', [
					HtmlTag::create( 'label', $w->labelIp, ['for' => 'input_ip'] ),
					HtmlTag::create( 'input', NULL, ['type' => 'text', 'name' => 'ip', 'id' => 'input_id', 'class' => 'span12', 'value' => htmlentities( $filterIp ?? '', ENT_QUOTES, 'UTF-8' )] ),
				] )
			),
			HTML::DivClass( 'row-fluid',
				HTML::DivClass( 'span12', [
					HtmlTag::create( 'label', $w->labelStatus, ['for' => 'input_status'] ),
					HtmlTag::create( 'select', $optStatus, ['name' => 'status', 'id' => 'input_status', 'class' => 'span12'] ),
				] )
			),
			HTML::DivClass( 'row-fluid',
				HTML::DivClass( 'span12', [
					HtmlTag::create( 'label', $w->labelSort, ['for' => 'input_sort'] ),
					HtmlTag::create( 'select', $optSort, ['name' => 'sort', 'id' => 'input_sort', 'class' => 'span12'] ),
				] )
			),
			HTML::DivClass( 'row-fluid',
				HTML::DivClass( 'span12', [
					HtmlTag::create( 'label', $w->labelOrder, ['for' => 'input_order'] ),
					HtmlTag::create( 'select', $optOrder, ['name' => 'order', 'id' => 'input_order', 'class' => 'span12'] ),
				] )
			),
			HTML::DivClass( 'buttonbar',
				HTML::DivClass( 'btn-toolbar', [
					$buttonFilter,
					$buttonReset
				] )
			)
		], ['action' => './manage/ip/lock/order', 'method' => 'post'] )
	)
);
return $panelFilter;
