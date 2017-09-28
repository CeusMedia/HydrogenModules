<?php
$panel	= new View_Helper_Panel_Mangopay_Cards( $env );
return $panel->setData( $cards )->render();
