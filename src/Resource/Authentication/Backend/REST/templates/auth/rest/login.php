<?php

/** @var \CeusMedia\HydrogenFramework\View $view */
/** @var ?string $from */

$panelLogin	= $view->loadTemplateFile( 'auth/rest/login.form.php' );

extract( $view->populateTexts( ['top', 'info', 'bottom'], 'html/auth/rest/login/', ['from' => $from] ) );

$tabs	= View_Auth::renderTabs( $env, 'auth/rest/login' );



if( strlen( trim( strip_tags( $textInfo ) ) ) ){
	return $tabs.HTML::DivClass( "auth-login-text-top", $textTop ).
	HTML::DivClass( "row-fluid", [
		HTML::DivClass( "span4", $panelLogin ),
		HTML::DivClass( "span8", HTML::DivClass( "auth-login-text-info", $textInfo ) )
	] ).
	HTML::DivClass( "auth-login-text-bottom", $textBottom );
}
return $tabs.'<br/></br/><br/><br/><br/><br/>'.$textTop.
	HTML::DivClass( "row-fluid", [
		HTML::DivClass( "span4 offset4", $panelLogin )
	] ).$textBottom;
