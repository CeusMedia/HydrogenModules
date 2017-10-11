<?php

$steps	= json_decode( $payin->data );
unset( $payin->data );

$iconList	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-list' ) );

$panelFacts	= UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'h3', 'Payin' ),
	UI_HTML_Tag::create( 'div', array(
		print_m( $payin, NULL, NULL, TRUE ),
		UI_HTML_Tag::create( 'div', array(
			UI_HTML_Tag::create( 'a', $iconList.' zurück', array( 'href' => './mangopay/payin', 'class' => 'btn' ) )
		), array( 'class' => 'buttonbar' ) )
	), array( 'class' => 'content-panel-inner' ) ),
), array( 'class' => 'content-panel' ) );

$panelSteps	= array();
foreach( $steps as $key => $item ){
	if( !$item )
		continue;
	$panelSteps[]	= UI_HTML_Tag::create( 'div', array(
		UI_HTML_Tag::create( 'div', array(
			UI_HTML_Tag::create( 'h3', $key ),
			UI_HTML_Tag::create( 'div', array(
				print_m( $item, NULL, NULL, TRUE ),
			), array( 'class' => 'content-panel-inner' ) ),
		), array( 'class' => 'content-panel' ) ),
	), array( 'class' => 'span6' ) );
}
$panelSteps	= UI_HTML_Tag::create( 'div', $panelSteps, array( 'class' => 'row-fluid' ) );

return $panelFacts.$panelSteps;
