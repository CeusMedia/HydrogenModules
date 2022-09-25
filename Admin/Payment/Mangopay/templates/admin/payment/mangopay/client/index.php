<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

//print_m( $client );die;

$panelCompany		= $view->loadTemplateFile( 'admin/payment/mangopay/client/panelCompany.php' );
$panelHeadquarter	= $view->loadTemplateFile( 'admin/payment/mangopay/client/panelHeadquarter.php' );
$panelEmails		= $view->loadTemplateFile( 'admin/payment/mangopay/client/panelEmails.php' );
$panelLogo			= $view->loadTemplateFile( 'admin/payment/mangopay/client/panelLogo.php' );
$panelColor			= $view->loadTemplateFile( 'admin/payment/mangopay/client/panelColor.php' );
$panelWallets		= $view->loadTemplateFile( 'admin/payment/mangopay/client/panelWallets.php' );

$panelData	= HtmlTag::create( 'div', array(
	HtmlTag::create( 'h3', 'Daten' ),
	HtmlTag::create( 'div', array(
		HtmlTag::create( 'small', print_m( $client, NULL, NULL, TRUE ), array( 'class' => 'muted' ) ),
	), array( 'class' => 'content-panel-inner', 'style' => 'max-height: 200px; overflow-y: auto' ) ),
), array( 'class' => 'content-panel' ) );

$tabs	= View_Admin_Payment_Mangopay::renderTabs( $env );

return $tabs.HtmlTag::create( 'div', array(
	HtmlTag::create( 'div', array( $panelCompany, $panelHeadquarter, $panelEmails ), array( 'class' => 'span8') ),
	HtmlTag::create( 'div', array( $panelWallets, $panelLogo, $panelColor, $panelData ), array( 'class' => 'span4') ),
), array( 'class' => 'row-fluid' ) );
