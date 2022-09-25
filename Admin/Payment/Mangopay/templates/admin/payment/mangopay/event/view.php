<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$statuses	= array(
	Model_Mangopay_Event::STATUS_RECEIVED	=> 'RECEIVED',
	Model_Mangopay_Event::STATUS_FAILED		=> 'FAILED',
	Model_Mangopay_Event::STATUS_HANDLED	=> 'HANDLED',
	Model_Mangopay_Event::STATUS_CLOSED		=> 'CLOSED',
);
$colors		= array(
	Model_Mangopay_Event::STATUS_RECEIVED	=> 'label-warning',
	Model_Mangopay_Event::STATUS_FAILED		=> 'label-important',
	Model_Mangopay_Event::STATUS_HANDLED	=> 'label-info',
	Model_Mangopay_Event::STATUS_CLOSED		=> 'label-success',
);
$event	= $this->getData( 'event' );
$table	= HtmlTag::create( 'table', array(
	HtmlTag::create( 'colgroup', array(
		HtmlTag::create( 'col', NULL, array( 'width' => '200px' ) ),
		HtmlTag::create( 'col', NULL, array( 'width' => '' ) ),
	) ),
	HtmlTag::create( 'tr', array(
		HtmlTag::create( 'th', 'Type' ),
		HtmlTag::create( 'td', $event->type ),
	) ),
	HtmlTag::create( 'tr', array(
		HtmlTag::create( 'th', 'Resource ID' ),
		HtmlTag::create( 'td', $event->id ),
	) ),
	HtmlTag::create( 'tr', array(
		HtmlTag::create( 'th', 'Status' ),
		HtmlTag::create( 'td', array(
			HtmlTag::create( 'label', $statuses[(int) $event->status], array( 'class' => 'label '.$colors[(int) $event->status] ) )
		) ),
	) ),
	HtmlTag::create( 'tr', array(
		HtmlTag::create( 'th', 'Comment' ),
		HtmlTag::create( 'td', array(
			HtmlTag::create( 'pre', $event->output )
		) ),
	) ),
	HtmlTag::create( 'tr', array(
		HtmlTag::create( 'th', 'Triggered' ),
		HtmlTag::create( 'td', date( 'Y-m-d H:i:s', (float) $event->triggeredAt ) ),
	) ),
	HtmlTag::create( 'tr', array(
		HtmlTag::create( 'th', 'Received' ),
		HtmlTag::create( 'td', date( 'Y-m-d H:i:s', (float) $event->receivedAt ) ),
	) ),
	HtmlTag::create( 'tr', array(
		HtmlTag::create( 'th', 'Handled' ),
		HtmlTag::create( 'td', date( 'Y-m-d H:i:s', (float) $event->handledAt ) ),
	) ),
), array( 'class' => 'table' ) );

$buttonReset	= HtmlTag::create( 'a', '<i class="fa fa-fw fa-undo"></i> zurücksetzen', array(
	'href'	=> './admin/payment/mangopay/event/retry/'.$event->eventId.'?page='.$page,
	'class'	=> 'btn btn-primary',
) );
$buttonClose	= HtmlTag::create( 'a', '<i class="fa fa-fw fa-check"></i> schließen', array(
	'href'	=> './admin/payment/mangopay/event/close/'.$event->eventId.'?page='.$page,
	'class'	=> 'btn btn-success',
) );
if( $event->status != Model_Mangopay_Event::STATUS_FAILED && $event->status != Model_Mangopay_Event::STATUS_HANDLED )
	$buttonReset	= HtmlTag::create( 'button', '<i class="fa fa-fw fa-undo"></i> zurücksetzen', array(
		'type'		=> 'button',
		'class'		=> 'btn btn-primary',
		'disabled'	=> 'disabled'
	) );
if( $event->status == Model_Mangopay_Event::STATUS_CLOSED ){
	$buttonClose	= HtmlTag::create( 'button', '<i class="fa fa-fw fa-check"></i> schließen', array(
		'type'		=> 'button',
		'class'		=> 'btn btn-success',
		'disabled'	=> 'disabled'
	) );
}

$tabs	= View_Admin_Payment_Mangopay::renderTabs( $env, 'event' );

return $tabs.HtmlTag::create( 'div', array(
	HtmlTag::create( 'h3', 'Event' ),
	HtmlTag::create( 'div', array(
		$table,
		HtmlTag::create( 'div', array(
			HtmlTag::create( 'a', '<i class="fa fa-fw fa-list"></i> zur Liste', array( 'href' => './admin/payment/mangopay/event/'.$page, 'class' => 'btn' ) ),
			' ',
			$buttonReset,
			' ',
			$buttonClose,
		), array( 'class' => 'buttonbar' ) )
	), array( 'class' => 'content-panel-inner' ) )
), array( 'class' => 'content-panel' ) );
?>
