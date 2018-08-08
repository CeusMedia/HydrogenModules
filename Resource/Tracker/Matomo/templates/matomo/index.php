<?php

$serverUrl	= $env->getConfig( 'module.resource_tracker_matomo.server.URL' );
return $view->loadContentFile( 'html/matomo/index.html', array( 'serverUrl', $serverUrl ) );

?>
