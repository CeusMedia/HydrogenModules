<?php
use CeusMedia\HydrogenFramework\Environment\Web;
use CeusMedia\HydrogenFramework\View;

/** @var Web $env */
/** @var View $view */
/** @var bool $canAdd */
/** @var bool $canCancel */
/** @var bool $canEdit */
/** @var bool $canLock */
/** @var bool $canOrder */
/** @var bool $canUnlock */

$panelFilter	= $view->loadTemplateFile( 'manage/ip/lock/index.filter.php' );
$panelList		= $view->loadTemplateFile( 'manage/ip/lock/index.list.php' );

$tabs	= View_Manage_IP_Lock::renderTabs( $env );
return $tabs.HTML::DivClass( 'row-fluid', [
	HTML::DivClass( 'span3', $panelFilter ),
	HTML::DivClass( 'span9', $panelList )
] );
