<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;

/** @var WebEnvironment $env */
/** @var View_Manage_Job_Definition $view */

$panelFilter	= $view->loadTemplateFile( 'manage/job/definition/index.filter.php' );
$panelList		= $view->loadTemplateFile( 'manage/job/definition/index.list.php' );

$tabs	= View_Manage_Job::renderTabs( $env, 'definition' );

return $tabs.HtmlTag::create( 'div', [
	HtmlTag::create( 'div', [
		$panelFilter,
	], ['class' => 'span3'] ),
	HtmlTag::create( 'div', [
		$panelList,
	], ['class' => 'span9'] ),
], ['class' => 'row-fluid'] );

//return print_m( $schedule, NULL, NULL, TRUE );
