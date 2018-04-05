<?php

$list	= UI_HTML_Tag::create( 'div', 'Keine OAuth2-Provider verfügbar.', array( 'class' => 'alert alert-error' ) );

if( $providers ){
	$list	= array();
	foreach( $providers as $provider ){
		$link	= UI_HTML_Tag::create( 'a', $provider->title, array( 'href' => './auth/oauth2/login/'.$provider->oauthProviderId ) );
		$list[]	= UI_HTML_Tag::create( 'li', $link );
	}
	$list	= UI_HTML_Tag::create( 'ul', $list );
}

return '
<h2>Providers</h2>'.$list;
