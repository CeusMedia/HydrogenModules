<?php
class View_Helper_Favicon{

	static public function ___onPageBuild( $env, $context, $module, $data = array() ){
		$config			= $env->getConfig();
		$configFav		= $config->getAll( 'module.ui_favicon.favorite.', TRUE );
		$configTouch	= $config->getAll( 'module.ui_favicon.touch.', TRUE );
		$pathImages		= $config->get( 'path.images' );
		$pathTheme		= $config->get( 'path.themes' ).$config->get( 'layout.theme' ).'/img/';

		if( $configFav->get( 'active' ) ){
			$path		= $configFav->get( 'fromTheme' ) ? $pathTheme : $pathImages;
			$context->addFavouriteIcon( $path.$configFav->get( 'name' ) );
		}

		if( $configTouch->get( 'active' ) ){
			$path		= $configTouch->get( 'fromTheme' ) ? $pathTheme : $pathImages;
			$url		= $path.$configTouch->get( 'name' );
			$attributes	= array( 'rel' => 'apple-touch-icon', 'href' => $url );
			$link		= UI_HTML_Tag::create( 'link', NULL, $attributes );
			$context->addHead( $link );
		}
	}
}
?>
