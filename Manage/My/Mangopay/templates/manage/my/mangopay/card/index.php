<?php
$panel	= new View_Helper_Panel_Mangopay_Cards( $env );
$panel->setOption( 'linkAdd', 'manage/my/mangopay/card/registration' );
return $panel->setData( $cards )->render();
