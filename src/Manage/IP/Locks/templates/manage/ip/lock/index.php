<?php
$panelFilter	= $view->loadTemplateFile( 'manage/ip/lock/index.filter.php' );
$panelList		= $view->loadTemplateFile( 'manage/ip/lock/index.list.php' );

$tabs	= View_Manage_Ip_Lock::renderTabs( $env );
return $tabs.HTML::DivClass( 'row-fluid', array(
	HTML::DivClass( 'span3', $panelFilter ),
	HTML::DivClass( 'span9', $panelList )
) );
