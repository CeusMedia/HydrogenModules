<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

use CeusMedia\HydrogenFramework\Environment;

/** @var Environment $env */
/** @var object $hook */

$table	= print_m( $hook, NULL, NULL, TRUE );

$tabs	= View_Admin_Payment_Mangopay::renderTabs( $env, 'hook' );

return $tabs.HtmlTag::create( 'div', [
	HtmlTag::create( 'h3', 'Hook' ),
	HtmlTag::create( 'div', [
		$table,
		HtmlTag::create( 'div', [
			HtmlTag::create( 'a', '<i class="fa fa-fw fa-arrow-left"></i> zurück', [
				'href'	=> './admin/payment/mangopay/hook',
				'class'	=> 'btn',
			] ),
		], ['class' => 'buttonbar'] )
	], ['class' => 'content-panel-inner'] )
], ['class' => 'content-panel'] );
