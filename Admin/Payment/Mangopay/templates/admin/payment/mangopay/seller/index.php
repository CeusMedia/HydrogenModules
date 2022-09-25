<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;


$panelUser			= $view->loadTemplateFile( 'admin/payment/mangopay/seller/panelUser.php' );
//$panelHeadquarter	= $view->loadTemplateFile( 'admin/payment/mangopay/seller/panelHeadquarter.php' );
$panelWallets		= $view->loadTemplateFile( 'admin/payment/mangopay/seller/panelWallets.php' );
$panelBanks			= $view->loadTemplateFile( 'admin/payment/mangopay/seller/panelBanks.php' );


$panelData	= HtmlTag::create( 'div', array(
	HtmlTag::create( 'h3', 'Daten' ),
	HtmlTag::create( 'div', array(
		HtmlTag::create( 'small', print_m( $sellerUser, NULL, NULL, TRUE ), array( 'class' => 'muted' ) ),
	), array( 'class' => 'content-panel-inner', 'style' => 'max-height: 200px; overflow-y: auto' ) ),
), array( 'class' => 'content-panel' ) );


$tabs	= View_Admin_Payment_Mangopay::renderTabs( $env, 'seller' );

return $tabs.HtmlTag::create( 'div', array(
	HtmlTag::create( 'div', array(
		$panelUser,
	), array( 'class' => 'span6' ) ),
	HtmlTag::create( 'div', array(
		$panelWallets,
		$panelBanks,
		$panelData
	), array( 'class' => 'span6' ) ),
), array( 'class' => 'row-fluid' ) );
