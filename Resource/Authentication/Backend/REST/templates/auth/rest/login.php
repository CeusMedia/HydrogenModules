<?php

$panelLogin	= $view->loadTemplateFile( 'auth/rest/login.form.php' );

extract( $view->populateTexts( array( 'top', 'info', 'bottom' ), 'html/auth/rest/login/', array( 'from' => $from ) ) );

return HTML::DivClass( "auth-login-text-top", $textTop ).
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
