<?php
$panel	= new View_Helper_Mangopay_List_Cards_Big( $env );
$panel->setCards( $cards );
$panel->setLink( 'manage/my/mangopay/card/view/%s' );
$panel->setFrom( $from );
$panel->allowAdd( TRUE );
return '<h2>Kreditkarten</h2>'.$panel;
