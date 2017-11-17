<?php
//print_m( $client );die;

$panelCompany		= $view->loadTemplateFile( 'admin/payment/mangopay/client/panelCompany.php' );
$panelHeadquarter	= $view->loadTemplateFile( 'admin/payment/mangopay/client/panelHeadquarter.php' );
$panelEmails		= $view->loadTemplateFile( 'admin/payment/mangopay/client/panelEmails.php' );
$panelLogo			= $view->loadTemplateFile( 'admin/payment/mangopay/client/panelLogo.php' );
$panelColor			= $view->loadTemplateFile( 'admin/payment/mangopay/client/panelColor.php' );
$panelWallets		= $view->loadTemplateFile( 'admin/payment/mangopay/client/panelWallets.php' );

$panelData	= UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'h3', 'Daten' ),
	UI_HTML_Tag::create( 'div', array(
		UI_HTML_Tag::create( 'small', print_m( $client, NULL, NULL, TRUE ), array( 'class' => 'muted' ) ),
	), array( 'class' => 'content-panel-inner', 'style' => 'max-height: 200px; overflow-y: auto' ) ),
), array( 'class' => 'content-panel' ) );

$tabs	= View_Admin_Payment_Mangopay::renderTabs( $env );

return $tabs.UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'div', array( $panelCompany, $panelHeadquarter, $panelEmails ), array( 'class' => 'span8') ),
	UI_HTML_Tag::create( 'div', array( $panelWallets, $panelLogo, $panelColor, $panelData ), array( 'class' => 'span4') ),
), array( 'class' => 'row-fluid' ) );
