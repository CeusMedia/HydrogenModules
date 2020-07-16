<?php

$panelFilter	= $view->loadTemplateFile( 'manage/job/definition/index.filter.php' );
$panelList		= $view->loadTemplateFile( 'manage/job/definition/index.list.php' );

$tabs	= View_Manage_Job::renderTabs( $env, 'definition' );

return $tabs.UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'div', array(
		$panelFilter,
	), array( 'class' => 'span3' ) ),
	UI_HTML_Tag::create( 'div', array(
		$panelList,
	), array( 'class' => 'span9' ) ),
), array( 'class' => 'row-fluid' ) );

//return print_m( $schedule, NULL, NULL, TRUE );
