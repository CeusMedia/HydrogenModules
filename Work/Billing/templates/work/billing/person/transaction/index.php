<?php

$helper	= new View_Work_Billing_Helper_Transactions( $env );
$helper->setTransactions( $transactions );
$helper->setMode( View_Work_Billing_Helper_Transactions::MODE_PERSON );
$helper->setFilterUrl( './work/billing/person/transaction/filter/'.$person->personId );
$helper->setFilterPrefix( $filterSessionPrefix );
$panelTransactions	= $helper->render();

$tabs	= View_Work_Billing_Person::renderTabs( $env, $personId, 1 );

return '<h2 class="autocut"><span class="muted">Person</span> '.$person->firstname.' '.$person->surname.'</h2>
'.$tabs.'
'.$panelTransactions;
?>
