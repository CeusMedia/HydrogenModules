<?php

$helper	= new View_Helper_Panel_Mangopay_Wallets( $env );
return $helper->setData( $wallets )->render();
