<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web;
use CeusMedia\HydrogenFramework\View;

/** @var Web $env */
/** @var View $view */
/** @var array<array<string,string>> $words */
/** @var object $server */

$panelLogin	= $view->loadTemplateFile( 'auth/local/login.form.php' );

extract( $view->populateTexts( ['top', 'info', 'bottom'], 'html/auth/local/login/', ['from' => $from] ) );

$tabs	= View_Auth::renderTabs( $env, 'auth/local/login' );

if( strlen( trim( strip_tags( $textInfo ) ) ) ){
	return $tabs.$textTop.
		HTML::DivClass( "bs2-row-fluid bs3-row bs4-row", array(
			HTML::DivClass( "bs2-span4 bs3-col-md-4 bs4-col-md-4", $panelLogin ),
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

$env->getPage()->addBodyClass( 'auth-centered' );
return HtmlTag::create( 'div', array(
	HtmlTag::create( 'div', $panelLogin, ['class' => 'centered-pane'] )
), ['class' => 'centered-pane-container'] );
