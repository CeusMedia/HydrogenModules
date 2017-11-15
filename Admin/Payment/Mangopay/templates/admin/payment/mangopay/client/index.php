<?php
//print_m( $client );die;

$panelCompany		= $view->loadTemplateFile( 'admin/payment/mangopay/client/panelCompany.php' );
$panelHeadquarter	= $view->loadTemplateFile( 'admin/payment/mangopay/client/panelHeadquarter.php' );
$panelEmails		= $view->loadTemplateFile( 'admin/payment/mangopay/client/panelEmails.php' );
$panelLogo			= $view->loadTemplateFile( 'admin/payment/mangopay/client/panelLogo.php' );
$panelColor			= $view->loadTemplateFile( 'admin/payment/mangopay/client/panelColor.php' );

$tabs	= View_Admin_Payment_Mangopay::renderTabs( $env );

return $tabs.UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'div', array( $panelCompany, $panelHeadquarter, $panelEmails ), array( 'class' => 'span8') ),
	UI_HTML_Tag::create( 'div', array( $panelLogo, $panelColor ), array( 'class' => 'span4') ),
), array( 'class' => 'row-fluid' ) );
