<?php
/*
$helperNav		= new View_Helper_Navigation();
$helperNav->setEnv( $this->env );
$helperNav->setCurrent( $this->env->getRequest()->get( 'path' ) );
*/

#$pathTheme		= "themes/plain/";
#$pathCDN		= "http://cdn.int1a.net/";

#$page->addFavouriteIcon( $pathTheme.'img/favicon.ico' );

$body	= '
<div id="layout-container">
	<div id="layout-header">
	</div>
	<div class="container">
		<div id="layout-messenger">'.$messenger->buildMessages().'</div>
		<div id="layout-content">
			'.$content.'
		</div>
		<div id="layout-footer">
		</div>
	</div>
</div>';

$page->addBody( $body );

/*
if( $config->get( 'theme.primer' ) ){
	$page->css->primer->addUrl( 'layout.messenger.css' );
	$page->css->primer->addUrl( 'layout.messenger.exception.css' );
}
#$page->css->theme->addUrl( 'layout.messenger.css' );
#$page->css->theme->addUrl( 'layout.messenger.exception.css' );
*/

#$page->js->addUrl( 'javascripts/script.js' );

return $page->build();
?>
