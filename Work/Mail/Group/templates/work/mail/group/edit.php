<?php

$panelEdit		= $view->loadTemplateFile( 'work/mail/group/edit.details.php' );
$panelMembers	= $view->loadTemplateFile( 'work/mail/group/edit.members.php' );

$tabs			= $view->renderTabs( $env );

$layout			= UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'div', $panelEdit, array( 'class' => 'span6' ) ),
	UI_HTML_Tag::create( 'div', $panelMembers, array( 'class' => 'span6' ) ),
), array( 'class' => 'row-fluid' ) );

return $tabs.$layout;
