<?php

$panelLogin	= $view->loadTemplateFile( 'auth/local/login.form.php' );

extract( $view->populateTexts( array( 'top', 'info', 'bottom' ), 'html/auth/local/login/', array( 'from' => $from ) ) );

$tabs	= View_Auth::renderTabs( $env, 'auth/local/login' );

if( strlen( trim( strip_tags( $textInfo ) ) ) ){
	return $tabs.$textTop.
		HTML::DivClass( "bs2-row-fluid bs3-row bs4-row", array(
			HTML::DivClass( "bs2-span4 bs3-col-md-4 bs2-col-md-4", $panelLogin ),
			HTML::DivClass( "bs2-span8 bs3-col-md-8 bs4-col-md-8", $textInfo ),
		) ).$textBottom;
}
if( strlen( trim( strip_tags( $textTop ) ) ) || strlen( trim( strip_tags( $textBottom ) ) ) ){
	return $tabs.$textTop.$panelLogin.$textBottom;
}
if( $tabs ){
	return $tabs.'<br/></br/><br/><br/><br/><br/>'.
	HTML::DivClass( "bs2-row-fluid bs3-row bs4-row", array(
		HTML::DivClass( "bs2-span4 bs2-offset4 bs3-col-md-4 bs3-md-offset-4 bs4-col-md-4 bs4-offset-md-4", $panelLogin )
	) );
}

return UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'div', $panelLogin, array( 'class' => 'centered-pane' ) )
), array( 'class' => 'centered-pane-container' ) );
?>
