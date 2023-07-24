<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;
use CeusMedia\HydrogenFramework\View;

/** @var WebEnvironment $env */
/** @var View $view */
/** @var array $words */

$panelEdit		= $view->loadTemplateFile( 'work/mail/group/edit.details.php' );
$panelMembers	= $view->loadTemplateFile( 'work/mail/group/edit.members.php' );

$tabs			= $view->renderTabs( $env );

$layout			= HtmlTag::create( 'div', [
	HtmlTag::create( 'div', $panelEdit, ['class' => 'span6'] ),
	HtmlTag::create( 'div', $panelMembers, ['class' => 'span6'] ),
], ['class' => 'row-fluid'] );

return $tabs.$layout;
