<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web;

/** @var Web $env */
/** @var ?string $filterStatus */
/** @var ?string $filterSort */
/** @var ?string $filterOrder */

$iconFilter		= HtmlTag::create( 'i', '', ['class' => 'icon-search icon-white'] );
$iconReset		= HtmlTag::create( 'i', '', ['class' => 'icon-zoom-out icon-white'] );
$buttonFilter	= HtmlTag::create( 'button', $iconFilter.' filtern', [
	'type'	=> 'submit',
	'name'	=> 'filter',
	'class'	=> 'btn btn-info',
] );
$buttonReset	= HtmlTag::create( 'a', $iconReset.' zurücksetzen', [
	'href'	=> './manage/ip/lock/order/reset',
	'class'	=> 'btn btn-small btn-inverse',
] );

$optStatus = [
	Model_IP_Lock::STATUS_DISABLED_BY_REASON	=> 'deaktiviert',
	Model_IP_Lock::STATUS_UNLOCKED				=> 'unlocked',
	Model_IP_Lock::STATUS_CANCELLED				=> '...',
	''	=> '- alle -',
	Model_IP_Lock::STATUS_REQUEST_LOCK			=> 'lock requested',
	Model_IP_Lock::STATUS_LOCKED				=> 'locked',
	Model_IP_Lock::STATUS_REQUEST_UNLOCK		=> 'unlock requested',

];
$optStatus	= HtmlElements::Options( $optStatus, $filterStatus );

$optSort	= [
	'lockedAt'	=> 'Sperrung',
	'IP'		=> 'IP',
];
$optSort	= HtmlElements::Options( $optSort, $filterSort );

$optOrder	= [
	'asc'	=> 'aufsteigend',
	'desc'	=> 'absteigend',
];
$optOrder	= HtmlElements::Options( $optOrder, $filterOrder );

$panelFilter	= HTML::DivClass( 'content-panel',
	HtmlTag::create( 'h3', 'Filter' ).
	HTML::DivClass( 'content-panel-inner',
		HtmlTag::create( 'form', [
			HTML::DivClass( 'row-fluid',
				HTML::DivClass( 'span12', [
					HtmlTag::create( 'label', 'IP-Adresse', ['for' => 'input_ip'] ),
					HtmlTag::create( 'input', NULL, ['type' => 'text', 'name' => 'ip', 'id' => 'input_id', 'class' => 'span12', 'value' => htmlentities( $filterIp ?? '', ENT_QUOTES, 'UTF-8' )] ),
				] )
			),
			HTML::DivClass( 'row-fluid',
				HTML::DivClass( 'span12', [
					HtmlTag::create( 'label', 'Status', ['for' => 'input_status'] ),
					HtmlTag::create( 'select', $optStatus, ['name' => 'status', 'id' => 'input_status', 'class' => 'span12'] ),
				] )
			),
			HTML::DivClass( 'row-fluid',
				HTML::DivClass( 'span12', [
					HtmlTag::create( 'label', 'Sortierung', ['for' => 'input_sort'] ),
					HtmlTag::create( 'select', $optSort, ['name' => 'sort', 'id' => 'input_sort', 'class' => 'span12'] ),
				] )
			),
			HTML::DivClass( 'row-fluid',
				HTML::DivClass( 'span12', [
					HtmlTag::create( 'label', 'Richtung', ['for' => 'input_order'] ),
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
