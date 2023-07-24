<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$list	= HtmlTag::create( 'div', 'Keine OAuth2-Provider verfügbar.', ['class' => 'alert alert-error'] );

if( $providers ){
	$list	= [];
	foreach( $providers as $provider ){
		$icon	= '';
		if( $provider->icon )
			$icon	= HtmlTag::create( 'i', '', ['class' => $provider->icon] ).'&nbsp;';
		$link	= HtmlTag::create( 'a', $icon.$provider->title, [
			'href'	=> './auth/oauth2/login/'.$provider->oauthProviderId,
			'class'	=> 'btn btn-info',
		] );
		$list[]	= HtmlTag::create( 'li', $link );
	}
	$list	= HtmlTag::create( 'ul', $list, ['class' => 'unstyled'] );
}
$panelProviders	= HtmlTag::create( 'div', array(
	HtmlTag::create( 'h3', 'Anmelden mit Anbieter-Konto' ),
	HtmlTag::create( 'div', [
		$list,
	], ['class' => 'content-panel-inner'] ),
), ['class' => 'content-panel'] );

extract( $view->populateTexts( ['top', 'bottom', 'info'], 'html/auth/oauth2/login/' ) );

$tabs	= View_Auth::renderTabs( $env, 'auth/oauth2/login' );

return $tabs.$textTop.HtmlTag::create( 'div', array(
	HtmlTag::create( 'div', [
		$panelProviders,
	], ['class' => 'span6'] ),
	HtmlTag::create( 'div', [
		$textInfo
	], ['class' => 'span6'] ),
), ['class' => 'row-fluid'] ).$textBottom;
