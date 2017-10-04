<?php

//return print_m( $hooks, NULL, NULL, TRUE );

/*print_m( $hooks );
print_m( $eventTypes );
print_m( $hookedEventTypes );
die;*/


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


$list	= array();
foreach( $events as $item ){
	$labelType		= ucwords( strtolower( str_replace( '_', ' ', $item->type ) ) );
	$labelStatus	= UI_HTML_Tag::create( 'label', $statuses[$item->status], array( 'class' => 'label '.$colors[$item->status] ) );
	$link			= UI_HTML_Tag::create( 'a', $labelType, array( 'href' => './mangopay/event/view/'.$item->eventId ) );

	$list[]	= UI_HTML_Tag::create( 'tr', array(
		UI_HTML_Tag::create( 'td', $link ),
		UI_HTML_Tag::create( 'td', $labelStatus ),
		UI_HTML_Tag::create( 'td', UI_HTML_Tag::create( 'small', date( 'Y-m-d H:i:s', $item->triggeredAt ) ) ),
	) );
}
$tbody	= UI_HTML_Tag::create( 'tbody', $list );
$colgroup	= UI_HTML_Elements::ColumnGroup( array( '', '80', '140' ) );
$list	= UI_HTML_Tag::create( 'table', $colgroup.$tbody, array( 'class' => 'table table-fixed table-condensed' ) );

$pagination	= new \CeusMedia\Bootstrap\PageControl( './mangopay/event', $page, $pages );

return UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'h3', 'Events' ),
	UI_HTML_Tag::create( 'div', array(
		$list,
		UI_HTML_Tag::create( 'div', array(
			$pagination,
		), array( 'class' => 'buttonbar' ) )
	), array( 'class' => 'content-panel-inner' ) )
), array( 'class' => 'content-panel' ) );
?>
