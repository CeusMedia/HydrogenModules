<?php

$tabs	= View_Manage_Job::renderTabs( $env, 'run' );

$panelList		= $view->loadTemplateFile( 'manage/job/run/index.list.php' );
$panelFilter	= $view->loadTemplateFile( 'manage/job/run/index.filter.php' );

return $tabs.UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'div', $panelFilter, array( 'class' => 'span3' ) ),
	UI_HTML_Tag::create( 'div', $panelList, array( 'class' => 'span9' ) ),
), array( 'class' => 'row-fluid' ) );
