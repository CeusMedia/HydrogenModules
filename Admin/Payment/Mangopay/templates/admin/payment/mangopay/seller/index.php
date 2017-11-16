<?php


$panelUser			= $view->loadTemplateFile( 'admin/payment/mangopay/seller/panelUser.php' );
//$panelHeadquarter	= $view->loadTemplateFile( 'admin/payment/mangopay/seller/panelHeadquarter.php' );
$panelWallets		= $view->loadTemplateFile( 'admin/payment/mangopay/seller/panelWallets.php' );
$panelBanks			= $view->loadTemplateFile( 'admin/payment/mangopay/seller/panelBanks.php' );

$tabs	= View_Admin_Payment_Mangopay::renderTabs( $env, 'seller' );

return $tabs.UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'div', array(
		$panelUser,
	), array( 'class' => 'span6' ) ),
	UI_HTML_Tag::create( 'div', array(
		$panelWallets,
		$panelBanks,
		print_m( $sellerUser, NULL, NULL, TRUE ),
	), array( 'class' => 'span6' ) ),
), array( 'class' => 'row-fluid' ) );
