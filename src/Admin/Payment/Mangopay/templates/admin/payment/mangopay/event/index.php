<?php

use CeusMedia\Bootstrap\PageControl;
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

/** @var Environment $env */
/** @var array $events */
/** @var int $pages */
/** @var int $page */

//return print_m( $hooks, NULL, NULL, TRUE );

/*print_m( $hooks );
print_m( $eventTypes );
print_m( $hookedEventTypes );
die;*/

$statuses	= [
	Model_Mangopay_Event::STATUS_RECEIVED	=> 'RECEIVED',
	Model_Mangopay_Event::STATUS_FAILED		=> 'FAILED',
	Model_Mangopay_Event::STATUS_HANDLED	=> 'HANDLED',
	Model_Mangopay_Event::STATUS_CLOSED		=> 'CLOSED',
];
$colors		= [
	Model_Mangopay_Event::STATUS_RECEIVED	=> 'label-warning',
	Model_Mangopay_Event::STATUS_FAILED		=> 'label-important',
	Model_Mangopay_Event::STATUS_HANDLED	=> 'label-info',
	Model_Mangopay_Event::STATUS_CLOSED		=> 'label-success',
];

$list	= [];
foreach( $events as $item ){
	$labelType		= ucwords( strtolower( str_replace( '_', ' ', $item->type ) ) );
	$labelStatus	= HtmlTag::create( 'label', $statuses[$item->status], ['class' => 'label '.$colors[$item->status]] );
	$link			= HtmlTag::create( 'a', $labelType, ['href' => './admin/payment/mangopay/event/view/'.$item->eventId.'?page='.$page] );

	$list[]	= HtmlTag::create( 'tr', [
		HtmlTag::create( 'td', $link ),
		HtmlTag::create( 'td', $labelStatus ),
		HtmlTag::create( 'td', HtmlTag::create( 'small', date( 'Y-m-d H:i:s', $item->triggeredAt ) ) ),
	] );
}
$tbody	= HtmlTag::create( 'tbody', $list );
$colgroup	= HtmlElements::ColumnGroup( ['', '80', '140'] );
$list	= HtmlTag::create( 'table', $colgroup.$tbody, ['class' => 'table table-fixed table-condensed'] );

$iconRefresh	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-refresh'] );

$buttonReload	= HtmlTag::create( 'a', $iconRefresh.' aktualisieren', [
	'href'		=> './admin/payment/mangopay/event'.( $page ? '/'.$page : '' ).'?'.time(),
	'class'		=> 'btn',
] );

$pagination	= new PageControl( './admin/payment/mangopay/event', $page, $pages );

$tabs	= View_Admin_Payment_Mangopay::renderTabs( $env, 'event' );

return $tabs.HtmlTag::create( 'div', [
	HtmlTag::create( 'h3', 'Events' ),
	HtmlTag::create( 'div', [
		$list,
		HtmlTag::create( 'div', [
			$pagination, ' ',
			$buttonReload, ' ',
		], ['class' => 'buttonbar'] )
	], ['class' => 'content-panel-inner'] )
], ['class' => 'content-panel'] );

