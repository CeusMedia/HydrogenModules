<?php

$env->getPage()->js->addScriptOnReady('Auth.Login.init();');

$panelLogin	= $view->loadTemplateFile( 'auth/local/login.form.php' );

extract( $view->populateTexts( array( 'top', 'info', 'bottom' ), 'html/auth/local/login/', array( 'from' => $from ) ) );

$tabs	= View_Auth::renderTabs( $env, 'auth/local/login' );

if( strlen( trim( strip_tags( $textInfo ) ) ) ){
	return $tabs.$textTop.
		HTML::DivClass( "row-fluid", array(
			HTML::DivClass( "span4", $panelLogin ),
			HTML::DivClass( "span8", $textInfo ),
		) ).$textBottom;
}
if( strlen( trim( strip_tags( $textTop ) ) ) && strlen( trim( strip_tags( $textBottom ) ) ) ){
	return $tabs.$textTop.$panelLogin.$textBottom;
}

return $tabs.'<br/></br/><br/><br/><br/><br/>'.$textTop.
	HTML::DivClass( "row-fluid", array(
		HTML::DivClass( "span4 offset4", $panelLogin )
	) ).$textBottom;
?>
