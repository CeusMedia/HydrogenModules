<?php
$panel	= new View_Helper_Mangopay_List_BankAccounts_Big( $env );
$panel->setBankAccounts( $bankAccounts );
$panel->setLink( 'manage/my/mangopay/bank/view/%s' );
//$panel->setFrom( $from );
$panel->allowAdd( TRUE );
return '<h2>Bankkonten</h2>'.$panel;
