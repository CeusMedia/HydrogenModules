<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$table	= print_m( $hook, NULL, NULL, TRUE );

$tabs	= View_Admin_Payment_Mangopay::renderTabs( $env, 'hook' );

return $tabs.HtmlTag::create( 'div', array(
	HtmlTag::create( 'h3', 'Hook' ),
	HtmlTag::create( 'div', array(
		$table,
		HtmlTag::create( 'div', array(
			HtmlTag::create( 'a', '<i class="fa fa-fw fa-arrow-left"></i> zurück', array(
				'href'	=> './admin/payment/mangopay/hook',
				'class'	=> 'btn',
			) ),
		), ['class' => 'buttonbar'] )
	), ['class' => 'content-panel-inner'] )
), ['class' => 'content-panel'] );
?>
