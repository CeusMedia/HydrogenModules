<?php
$helper	= new View_Helper_Tool_Calculator( $env );
$helper->setId( 'calc-view' );
$env->getPage()->js->addScriptOnReady( 'let calc = new Calculator("#calc-view")' );
return $helper->render();
