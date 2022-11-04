<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$panelFilter	= $view->loadTemplateFile( 'manage/job/definition/index.filter.php' );
$panelList		= $view->loadTemplateFile( 'manage/job/definition/index.list.php' );

$tabs	= View_Manage_Job::renderTabs( $env, 'definition' );

return $tabs.HtmlTag::create( 'div', array(
	HtmlTag::create( 'div', array(
		$panelFilter,
	), ['class' => 'span3'] ),
	HtmlTag::create( 'div', array(
		$panelList,
	), ['class' => 'span9'] ),
), ['class' => 'row-fluid'] );

//return print_m( $schedule, NULL, NULL, TRUE );
