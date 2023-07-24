<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web;
use CeusMedia\HydrogenFramework\View;

/** @var Web $env */
/** @var View $view */
/** @var array<array<string,string>> $words */
/** @var object[] $sellerUser */

$panelUser			= $view->loadTemplateFile( 'admin/payment/mangopay/seller/panelUser.php' );
//$panelHeadquarter	= $view->loadTemplateFile( 'admin/payment/mangopay/seller/panelHeadquarter.php' );
$panelWallets		= $view->loadTemplateFile( 'admin/payment/mangopay/seller/panelWallets.php' );
$panelBanks			= $view->loadTemplateFile( 'admin/payment/mangopay/seller/panelBanks.php' );


$panelData	= HtmlTag::create( 'div', [
	HtmlTag::create( 'h3', 'Daten' ),
	HtmlTag::create( 'div', [
		HtmlTag::create( 'small', print_m( $sellerUser, NULL, NULL, TRUE ), ['class' => 'muted'] ),
	], ['class' => 'content-panel-inner', 'style' => 'max-height: 200px; overflow-y: auto'] ),
], ['class' => 'content-panel'] );


$tabs	= View_Admin_Payment_Mangopay::renderTabs( $env, 'seller' );

return $tabs.HtmlTag::create( 'div', [
	HtmlTag::create( 'div', [
		$panelUser,
	], ['class' => 'span6'] ),
	HtmlTag::create( 'div', [
		$panelWallets,
		$panelBanks,
		$panelData
	], ['class' => 'span6'] ),
], ['class' => 'row-fluid'] );
