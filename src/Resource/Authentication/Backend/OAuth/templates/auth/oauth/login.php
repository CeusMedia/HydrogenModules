<?php

/** @var \CeusMedia\HydrogenFramework\View $view */
/** @var ?string $from */

$panelLogin	= $view->loadTemplateFile( 'auth/oauth/login.form.php' );

extract( $view->populateTexts( ['top', 'info', 'bottom'], 'html/auth/oauth/login/', ['from' => $from] ) );

$tabs	= View_Auth::renderTabs( $env, 'auth/oauth/login' );

return $tabs.HTML::DivClass( "auth-login-text-top", $textTop ).
HTML::DivClass( "row-fluid", [
	HTML::DivClass( "span4", $panelLogin ),
	HTML::DivClass( "span8", HTML::DivClass( "auth-login-text-info", $textInfo ) )
] ).
HTML::DivClass( "auth-login-text-bottom", $textBottom );
