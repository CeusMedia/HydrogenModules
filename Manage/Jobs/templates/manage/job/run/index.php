<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$tabs	= View_Manage_Job::renderTabs( $env, 'run' );

$panelList		= $view->loadTemplateFile( 'manage/job/run/index.list.php' );
$panelFilter	= $view->loadTemplateFile( 'manage/job/run/index.filter.php' );

return $tabs.HtmlTag::create( 'div', array(
	HtmlTag::create( 'div', $panelFilter, array( 'class' => 'span3' ) ),
	HtmlTag::create( 'div', $panelList, array( 'class' => 'span9' ) ),
), array( 'class' => 'row-fluid' ) );
