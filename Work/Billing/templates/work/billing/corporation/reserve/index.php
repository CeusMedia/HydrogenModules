<?php

$iconPerson		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-user' ) );
$iconCompany	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-building-o' ) );

$helper	= new View_Work_Billing_Helper_Transactions( $env );
$helper->setHeading( 'Einnahmen / Rücklagen' );
$helper->setTransactions( $reserves );
$helper->setFilterUrl( './work/billing/corporation/reserve/filter/'.$corporation->corporationId );
$helper->setFilterPrefix( $filterSessionPrefix );
$panelTransactions	= $helper->render();

$tabs	= View_Work_Billing_Corporation::renderTabs( $env, $corporationId, 1 );

return '<h2 class="autocut"><span class="muted">Unternehmen</span> '.$corporation->title.'</h2>
'.$tabs.'
'.$panelTransactions.'
';
?>
