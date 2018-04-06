<?php

$list	= UI_HTML_Tag::create( 'div', 'Keine OAuth2-Provider verfügbar.', array( 'class' => 'alert alert-error' ) );

if( $providers ){
	$list	= array();
	foreach( $providers as $provider ){
		$icon	= '';
		if( $provider->icon )
			$icon	= UI_HTML_Tag::create( 'i', '', array( 'class' => $provider->icon ) ).'&nbsp;';
		$link	= UI_HTML_Tag::create( 'a', $icon.$provider->title, array(
			'href'	=> './auth/oauth2/login/'.$provider->oauthProviderId,
			'class'	=> 'btn btn-info',
		) );
		$list[]	= UI_HTML_Tag::create( 'li', $link );
	}
	$list	= UI_HTML_Tag::create( 'ul', $list, array( 'class' => 'unstyled' ) );
}
$panelProviders	= UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'h3', 'Anmelden mit Anbieter-Konto' ),
	UI_HTML_Tag::create( 'div', array(
		$list,
	), array( 'class' => 'content-panel-inner' ) ),
), array( 'class' => 'content-panel' ) );

extract( $view->populateTexts( array( 'top', 'bottom', 'info' ), 'html/auth/oauth2/login/' ) );

$tabs	= View_Auth::renderTabs( $env, 'auth/oauth2/login' );

return $tabs.$textTop.UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'div', array(
		$panelProviders,
	), array( 'class' => 'span6' ) ),
	UI_HTML_Tag::create( 'div', array(
		$textInfo
	), array( 'class' => 'span6' ) ),
), array( 'class' => 'row-fluid' ) ).$textBottom;
