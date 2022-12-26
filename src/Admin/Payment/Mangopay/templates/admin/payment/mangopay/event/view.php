<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

/** @var Environment $env */
/** @var int $page */

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
$event	= $this->getData( 'event' );
$table	= HtmlTag::create( 'table', [
	HtmlTag::create( 'colgroup', [
		HtmlTag::create( 'col', NULL, ['width' => '200px'] ),
		HtmlTag::create( 'col', NULL, ['width' => ''] ),
	] ),
	HtmlTag::create( 'tr', [
		HtmlTag::create( 'th', 'Type' ),
		HtmlTag::create( 'td', $event->type ),
	] ),
	HtmlTag::create( 'tr', [
		HtmlTag::create( 'th', 'Resource ID' ),
		HtmlTag::create( 'td', $event->id ),
	] ),
	HtmlTag::create( 'tr', [
		HtmlTag::create( 'th', 'Status' ),
		HtmlTag::create( 'td', [
			HtmlTag::create( 'label', $statuses[(int) $event->status], array( 'class' => 'label '.$colors[(int) $event->status] ) )
		] ),
	] ),
	HtmlTag::create( 'tr', [
		HtmlTag::create( 'th', 'Comment' ),
		HtmlTag::create( 'td', [
			HtmlTag::create( 'pre', $event->output )
		] ),
	] ),
	HtmlTag::create( 'tr', [
		HtmlTag::create( 'th', 'Triggered' ),
		HtmlTag::create( 'td', date( 'Y-m-d H:i:s', (float) $event->triggeredAt ) ),
	] ),
	HtmlTag::create( 'tr', [
		HtmlTag::create( 'th', 'Received' ),
		HtmlTag::create( 'td', date( 'Y-m-d H:i:s', (float) $event->receivedAt ) ),
	] ),
	HtmlTag::create( 'tr', [
		HtmlTag::create( 'th', 'Handled' ),
		HtmlTag::create( 'td', date( 'Y-m-d H:i:s', (float) $event->handledAt ) ),
	] ),
], ['class' => 'table'] );

$buttonReset	= HtmlTag::create( 'a', '<i class="fa fa-fw fa-undo"></i> zurücksetzen', [
	'href'	=> './admin/payment/mangopay/event/retry/'.$event->eventId.'?page='.$page,
	'class'	=> 'btn btn-primary',
] );
$buttonClose	= HtmlTag::create( 'a', '<i class="fa fa-fw fa-check"></i> schließen', array(
	'href'	=> './admin/payment/mangopay/event/close/'.$event->eventId.'?page='.$page,
	'class'	=> 'btn btn-success',
) );
if( $event->status != Model_Mangopay_Event::STATUS_FAILED && $event->status != Model_Mangopay_Event::STATUS_HANDLED )
	$buttonReset	= HtmlTag::create( 'button', '<i class="fa fa-fw fa-undo"></i> zurücksetzen', [
		'type'		=> 'button',
		'class'		=> 'btn btn-primary',
		'disabled'	=> 'disabled'
	] );
if( $event->status == Model_Mangopay_Event::STATUS_CLOSED ){
	$buttonClose	= HtmlTag::create( 'button', '<i class="fa fa-fw fa-check"></i> schließen', [
		'type'		=> 'button',
		'class'		=> 'btn btn-success',
		'disabled'	=> 'disabled'
	] );
}

$tabs	= View_Admin_Payment_Mangopay::renderTabs( $env, 'event' );

return $tabs.HtmlTag::create( 'div', [
	HtmlTag::create( 'h3', 'Event' ),
	HtmlTag::create( 'div', [
		$table,
		HtmlTag::create( 'div', [
			HtmlTag::create( 'a', '<i class="fa fa-fw fa-list"></i> zur Liste', ['href' => './admin/payment/mangopay/event/'.$page, 'class' => 'btn'] ),
			' ',
			$buttonReset,
			' ',
			$buttonClose,
		], ['class' => 'buttonbar'] )
	], ['class' => 'content-panel-inner'] )
], ['class' => 'content-panel'] );
