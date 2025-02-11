<?php


$panelRegister	= $view->loadTemplateFile( 'auth/rest/register.form.php' );

extract( $view->populateTexts( ['above', 'below', 'top', 'info', 'bottom'], 'html/auth/rest/register/', ['from' => $from] ) );

$tabs	= View_Auth::renderTabs( $env, 'auth/rest/login' );

return HTML::DivClass( "auth-register-text-above", $textAbove ).
HTML::DivClass( "row-fluid", [
	HTML::DivClass( "span8", [
		HTML::DivClass( "auth-register-text-top", $textTop ),
		$panelRegister,
		HTML::DivClass( "auth-register-text-bottom", $textBottom )
	] ),
	HTML::DivClass( "span4",
		HTML::DivClass( "auth-register-text-info", $textInfo )
	)
] ).
HTML::DivClass( "auth-register-text-below", $textBelow );
