<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;
use View_Manage_Job_Run as View;

/** @var WebEnvironment $env */
/** @var View $view */
/** @var array $wordsGeneral */
/** @var array $words */
/** @var array<object> $definitions */

$tabs	= View_Manage_Job::renderTabs( $env, 'run' );

$panelList		= $view->loadTemplateFile( 'manage/job/run/index.list.php' );
$panelFilter	= $view->loadTemplateFile( 'manage/job/run/index.filter.php' );

return $tabs.HtmlTag::create( 'div', [
	HtmlTag::create( 'div', $panelFilter, ['class' => 'span3'] ),
	HtmlTag::create( 'div', $panelList, ['class' => 'span9'] ),
], ['class' => 'row-fluid'] );
