<?php
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
$table	= UI_HTML_Tag::create( 'table', array(
	UI_HTML_Tag::create( 'colgroup', array(
		UI_HTML_Tag::create( 'col', NULL, array( 'width' => '200px' ) ),
		UI_HTML_Tag::create( 'col', NULL, array( 'width' => '' ) ),
	) ),
	UI_HTML_Tag::create( 'tr', array(
		UI_HTML_Tag::create( 'th', 'Type' ),
		UI_HTML_Tag::create( 'td', $event->type ),
	) ),
	UI_HTML_Tag::create( 'tr', array(
		UI_HTML_Tag::create( 'th', 'Resource ID' ),
		UI_HTML_Tag::create( 'td', $event->id ),
	) ),
	UI_HTML_Tag::create( 'tr', array(
		UI_HTML_Tag::create( 'th', 'Status' ),
		UI_HTML_Tag::create( 'td', array(
			UI_HTML_Tag::create( 'label', $statuses[(int) $event->status], array( 'class' => 'label '.$colors[(int) $event->status] ) )
		) ),
	) ),
	UI_HTML_Tag::create( 'tr', array(
		UI_HTML_Tag::create( 'th', 'Comment' ),
		UI_HTML_Tag::create( 'td', array(
			UI_HTML_Tag::create( 'pre', $event->output )
		) ),
	) ),
	UI_HTML_Tag::create( 'tr', array(
		UI_HTML_Tag::create( 'th', 'Triggered' ),
		UI_HTML_Tag::create( 'td', date( 'Y-m-d H:i:s', (float) $event->triggeredAt ) ),
	) ),
	UI_HTML_Tag::create( 'tr', array(
		UI_HTML_Tag::create( 'th', 'Received' ),
		UI_HTML_Tag::create( 'td', date( 'Y-m-d H:i:s', (float) $event->receivedAt ) ),
	) ),
	UI_HTML_Tag::create( 'tr', array(
		UI_HTML_Tag::create( 'th', 'Handled' ),
		UI_HTML_Tag::create( 'td', date( 'Y-m-d H:i:s', (float) $event->handledAt ) ),
	) ),
), array( 'class' => 'table' ) );

$buttonReset	= UI_HTML_Tag::create( 'a', '<i class="fa fa-fw fa-undo"></i> zurücksetzen', array(
	'href'	=> './admin/payment/mangopay/event/retry/'.$event->eventId.'?page='.$page,
	'class'	=> 'btn btn-primary',
) );
$buttonClose	= UI_HTML_Tag::create( 'a', '<i class="fa fa-fw fa-check"></i> schließen', array(
	'href'	=> './admin/payment/mangopay/event/close/'.$event->eventId.'?page='.$page,
	'class'	=> 'btn btn-success',
) );
if( $event->status != Model_Mangopay_Event::STATUS_FAILED && $event->status != Model_Mangopay_Event::STATUS_HANDLED )
	$buttonReset	= UI_HTML_Tag::create( 'button', '<i class="fa fa-fw fa-undo"></i> zurücksetzen', array(
		'type'		=> 'button',
		'class'		=> 'btn btn-primary',
		'disabled'	=> 'disabled'
	) );
if( $event->status == Model_Mangopay_Event::STATUS_CLOSED ){
	$buttonClose	= UI_HTML_Tag::create( 'button', '<i class="fa fa-fw fa-check"></i> schließen', array(
		'type'		=> 'button',
		'class'		=> 'btn btn-success',
		'disabled'	=> 'disabled'
	) );
}

$tabs	= View_Admin_Payment_Mangopay::renderTabs( $env, 'event' );

return $tabs.UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'h3', 'Event' ),
	UI_HTML_Tag::create( 'div', array(
		$table,
		UI_HTML_Tag::create( 'div', array(
			UI_HTML_Tag::create( 'a', '<i class="fa fa-fw fa-list"></i> zur Liste', array( 'href' => './admin/payment/mangopay/event/'.$page, 'class' => 'btn' ) ),
			' ',
			$buttonReset,
			' ',
			$buttonClose,
		), array( 'class' => 'buttonbar' ) )
	), array( 'class' => 'content-panel-inner' ) )
), array( 'class' => 'content-panel' ) );
?>
