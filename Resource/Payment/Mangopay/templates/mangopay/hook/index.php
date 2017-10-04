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
	'ENABLED'	=> 'label-success',
	'DISABLED'	=> 'label-warning',
	'VALID'		=> 'label-info',
	'INVALID'	=> 'label-important',
);


$list	= array();
foreach( $eventTypes as $topic => $types ){
	$sublist	= array();
	foreach( $types as $type ){
		if( !array_key_exists( $type, $hookedEventTypes ) )
			continue;
		$hook	= $hookedEventTypes[$type];
		$labelType	= substr( $type, strlen( $topic ) );
		$labelType	= ucwords( strtolower( str_replace( '_', ' ', $labelType ) ) );
		$labelTag	= UI_HTML_Tag::create( 'small', $hook->Tag ? '('.$hook->Tag.')' : '', array( 'class' => 'muted' ) );
		$link		= UI_HTML_Tag::create( 'a', $labelType.' '.$labelTag, array( 'href' => './mangopay/hook/view/'.$hook->Id ) );

		$labelUrl	= UI_HTML_Tag::create( 'small', $hook->Url, array( 'class' => 'not-muted' ) );
		$sublist[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', $link ),
			UI_HTML_Tag::create( 'td', $labelUrl ),
			UI_HTML_Tag::create( 'td', UI_HTML_Tag::create( 'label', $hook->Status, array( 'class' => 'label '.$colors[$hook->Status] ) ) ),
			UI_HTML_Tag::create( 'td', UI_HTML_Tag::create( 'label', $hook->Validity, array( 'class' => 'label '.$colors[$hook->Validity] ) ) ),
		) );
	}
	if( $sublist ){
		$topic	= UI_HTML_Tag::create( 'h4', $topic );
		$list[]	= UI_HTML_Tag::create( 'tr', UI_HTML_Tag::create( 'td', $topic, array( 'colspan' => 4 ) ) );
		$list[]	= $sublist;
	}
}
$tbody	= UI_HTML_Tag::create( 'tbody', $list );
$colgroup	= UI_HTML_Elements::ColumnGroup( array( '30%', '', '90', '80' ) );
$list	= UI_HTML_Tag::create( 'table', $colgroup.$tbody, array( 'class' => 'table table-fixed table-condensed' ) );

return UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'h3', 'Hooks' ),
	UI_HTML_Tag::create( 'div', array(
		$list,
		UI_HTML_Tag::create( 'div', array(
			UI_HTML_Tag::create( 'a', '<i class="fa fa-fw fa-plus"></i> neuer Hook', array( 'disabled' => 'disabled', 'href' => './mangopay/hook/add', 'class' => 'btn btn-success btn-small' ) ),
			' ',
			UI_HTML_Tag::create( 'a', '<i class="fa fa-fw fa-cogs"></i> URL für Hooks setzen', array( 'href' => './mangopay/hook/apply', 'class' => 'btn btn-small' ) ),

		), array( 'class' => 'buttonbar' ) )
	), array( 'class' => 'content-panel-inner' ) )
), array( 'class' => 'content-panel' ) );
?>
