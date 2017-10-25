<?php
$panel	= new View_Helper_Mangopay_List_Wallets_Big( $env );
$panel->set( $wallets );
$panel->setLink( 'manage/my/mangopay/wallet/view/%s' );
//$panel->setFrom( $from );
$panel->allowAdd( TRUE );
return '<h2>Portmoney</h2>'.$panel;
