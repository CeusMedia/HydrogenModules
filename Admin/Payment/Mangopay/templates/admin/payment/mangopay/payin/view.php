<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$steps	= json_decode( $payin->data );
unset( $payin->data );

$iconList	= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-list' ) );

$panelFacts	= HtmlTag::create( 'div', array(
	HtmlTag::create( 'h3', 'Payin' ),
	HtmlTag::create( 'div', array(
		print_m( $payin, NULL, NULL, TRUE ),
		HtmlTag::create( 'div', array(
			HtmlTag::create( 'a', $iconList.' zurück', array( 'href' => './admin/payment/mangopay/payin', 'class' => 'btn' ) )
		), array( 'class' => 'buttonbar' ) )
	), array( 'class' => 'content-panel-inner' ) ),
), array( 'class' => 'content-panel' ) );

$panelSteps	= [];
foreach( $steps as $key => $item ){
	if( !$item )
		continue;
	$panelSteps[]	= HtmlTag::create( 'div', array(
		HtmlTag::create( 'div', array(
			HtmlTag::create( 'h3', $key ),
			HtmlTag::create( 'div', array(
				print_m( $item, NULL, NULL, TRUE ),
			), array( 'class' => 'content-panel-inner' ) ),
		), array( 'class' => 'content-panel' ) ),
	), array( 'class' => 'span6' ) );
}
$panelSteps	= HtmlTag::create( 'div', $panelSteps, array( 'class' => 'row-fluid' ) );

$tabs	= View_Admin_Payment_Mangopay::renderTabs( $env, 'payin' );

return $tabs.$panelFacts.$panelSteps;
