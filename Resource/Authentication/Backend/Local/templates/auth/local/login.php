<?php

$panelLogin	= $view->loadTemplateFile( 'auth/local/login.form.php' );

extract( $view->populateTexts( array( 'top', 'info', 'bottom' ), 'html/auth/local/login/', array( 'from' => $from ) ) );

$tabs	= View_Auth::renderTabs( $env, 'auth/local/login' );

$env->getPage()->js->addScriptOnReady('Auth.Login.init();');

return $tabs.HTML::DivClass( "auth-login-text-top", $textTop ).
HTML::DivClass( "row-fluid", array(
	HTML::DivClass( "span4",
		$panelLogin
	),
	HTML::DivClass( "span8",
		HTML::DivClass( "auth-login-text-info", $textInfo )
	)
) ).
HTML::DivClass( "auth-login-text-bottom", $textBottom );
?>
