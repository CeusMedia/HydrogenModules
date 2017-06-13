<?php

$panelLogin	= $view->loadTemplateFile( 'auth/json/login.form.php' );

extract( $view->populateTexts( array( 'top', 'info', 'bottom' ), 'html/auth/json/login/', array( 'from' => $from ) ) );

$tabs	= View_Auth::renderTabs( $env, 'auth/json/login' );

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
