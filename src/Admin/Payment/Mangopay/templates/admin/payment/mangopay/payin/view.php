<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web;
use CeusMedia\HydrogenFramework\View;

/** @var Web $env */
/** @var View $view */
/** @var array<array<string,string>> $words */
/** @var object $payin */

$steps	= json_decode( $payin->data );
unset( $payin->data );

$iconList	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-list'] );

$panelFacts	= HtmlTag::create( 'div', array(
	HtmlTag::create( 'h3', 'Payin' ),
	HtmlTag::create( 'div', array(
		print_m( $payin, NULL, NULL, TRUE ),
		HtmlTag::create( 'div', array(
			HtmlTag::create( 'a', $iconList.' zurück', ['href' => './admin/payment/mangopay/payin', 'class' => 'btn'] )
		), ['class' => 'buttonbar'] )
	), ['class' => 'content-panel-inner'] ),
), ['class' => 'content-panel'] );

$panelSteps	= [];
foreach( $steps as $key => $item ){
	if( !$item )
		continue;
	$panelSteps[]	= HtmlTag::create( 'div', array(
		HtmlTag::create( 'div', array(
			HtmlTag::create( 'h3', $key ),
			HtmlTag::create( 'div', array(
				print_m( $item, NULL, NULL, TRUE ),
			), ['class' => 'content-panel-inner'] ),
		), ['class' => 'content-panel'] ),
	), ['class' => 'span6'] );
}
$panelSteps	= HtmlTag::create( 'div', $panelSteps, ['class' => 'row-fluid'] );

$tabs	= View_Admin_Payment_Mangopay::renderTabs( $env, 'payin' );

return $tabs.$panelFacts.$panelSteps;
