<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web;
use CeusMedia\HydrogenFramework\View;

/** @var Web $env */
/** @var View $view */
/** @var array<array<string,string>> $words */

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
		HtmlTag::create( 'small', print_m( $client, NULL, NULL, TRUE ), ['class' => 'muted'] ),
	), ['class' => 'content-panel-inner', 'style' => 'max-height: 200px; overflow-y: auto'] ),
), ['class' => 'content-panel'] );

$tabs	= View_Admin_Payment_Mangopay::renderTabs( $env );

return $tabs.HtmlTag::create( 'div', array(
	HtmlTag::create( 'div', [$panelCompany, $panelHeadquarter, $panelEmails], array( 'class' => 'span8') ),
	HtmlTag::create( 'div', [$panelWallets, $panelLogo, $panelColor, $panelData], array( 'class' => 'span4') ),
), ['class' => 'row-fluid'] );
