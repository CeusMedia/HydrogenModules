<?php

$tabs	= View_Work_Billing_Corporation::renderTabs( $env, $corporationId, 1 );

$helper	= new View_Work_Billing_Helper_Transactions( $env );
$helper->setMode( View_Work_Billing_Helper_Transactions::MODE_CORPORATION );
$helper->setTransactions( $transactions );
$helper->setFilterUrl( './work/billing/corporation/transaction/filter/'.$corporationId );
$helper->setFilterPrefix( $filterSessionPrefix );
$panelTransactions	= $helper->render();

return '<h2 class="autocut"><span class="muted">Unternehmen</span> '.$corporation->title.'</h2>
'.$tabs.'
'.$panelTransactions;
?>
