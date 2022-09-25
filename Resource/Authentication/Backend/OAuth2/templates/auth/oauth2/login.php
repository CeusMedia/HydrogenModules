<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$list	= HtmlTag::create( 'div', 'Keine OAuth2-Provider verfügbar.', array( 'class' => 'alert alert-error' ) );

if( $providers ){
	$list	= [];
	foreach( $providers as $provider ){
		$icon	= '';
		if( $provider->icon )
			$icon	= HtmlTag::create( 'i', '', array( 'class' => $provider->icon ) ).'&nbsp;';
		$link	= HtmlTag::create( 'a', $icon.$provider->title, array(
			'href'	=> './auth/oauth2/login/'.$provider->oauthProviderId,
			'class'	=> 'btn btn-info',
		) );
		$list[]	= HtmlTag::create( 'li', $link );
	}
	$list	= HtmlTag::create( 'ul', $list, array( 'class' => 'unstyled' ) );
}
$panelProviders	= HtmlTag::create( 'div', array(
	HtmlTag::create( 'h3', 'Anmelden mit Anbieter-Konto' ),
	HtmlTag::create( 'div', array(
		$list,
	), array( 'class' => 'content-panel-inner' ) ),
), array( 'class' => 'content-panel' ) );

extract( $view->populateTexts( array( 'top', 'bottom', 'info' ), 'html/auth/oauth2/login/' ) );

$tabs	= View_Auth::renderTabs( $env, 'auth/oauth2/login' );

return $tabs.$textTop.HtmlTag::create( 'div', array(
	HtmlTag::create( 'div', array(
		$panelProviders,
	), array( 'class' => 'span6' ) ),
	HtmlTag::create( 'div', array(
		$textInfo
	), array( 'class' => 'span6' ) ),
), array( 'class' => 'row-fluid' ) ).$textBottom;
