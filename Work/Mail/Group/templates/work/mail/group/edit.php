<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$panelEdit		= $view->loadTemplateFile( 'work/mail/group/edit.details.php' );
$panelMembers	= $view->loadTemplateFile( 'work/mail/group/edit.members.php' );

$tabs			= $view->renderTabs( $env );

$layout			= HtmlTag::create( 'div', array(
	HtmlTag::create( 'div', $panelEdit, array( 'class' => 'span6' ) ),
	HtmlTag::create( 'div', $panelMembers, array( 'class' => 'span6' ) ),
), array( 'class' => 'row-fluid' ) );

return $tabs.$layout;
