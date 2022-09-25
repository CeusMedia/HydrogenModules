<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$iconPerson		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-user-o' ) );
$iconCompany	= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-building-o' ) );

$helper	= new View_Work_Billing_Helper_Transactions( $env );
$helper->setHeading( 'Einnahmen / Rücklagen' );
$helper->setTransactions( $reserves );
$helper->setFilterUrl( './work/billing/corporation/reserve/filter/'.$corporation->corporationId );
$helper->setFilterPrefix( $filterSessionPrefix );
$panelTransactions	= $helper->render();

$tabs		= View_Work_Billing_Corporation::renderTabs( $env, $corporationId, 1 );
$heading	= '<h2 class="autocut"><span class="muted">Unternehmen</span> '.$corporation->title.'</h2>';

return $heading.$tabs.$panelTransactions;
