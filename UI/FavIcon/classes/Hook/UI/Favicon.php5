<?php
class Hook_UI_Favicon extends CMF_Hydrogen_Hook
{
	public static function onPageBuild( CMF_Hydrogen_Environment $env, $context, $module, $payload )
	{
		$config			= $env->getConfig();
		$configFav		= $config->getAll( 'module.ui_favicon.favorite.', TRUE );
		$configTouch	= $config->getAll( 'module.ui_favicon.touch.', TRUE );
		$pathImages		= $config->get( 'path.images' );
		$pathTheme		= $config->get( 'path.themes' ).$config->get( 'layout.theme' ).'/img/';

		if( $configFav->get( 'active' ) ){
			$path		= $configFav->get( 'fromTheme' ) ? $pathTheme : $pathImages;

			//  @todo 	use the line below after CeusMedia/Common supports MIME types depending on extension */
		//		$context->addFavouriteIcon( $path.$configFav->get( 'name' ) );

			//  @todo 	remove this solution afterwards
			$url	= $path.$configFav->get( 'name' );
			$ext	= strtolower( pathinfo( $url, PATHINFO_EXTENSION ) );
			$type	= "image/x-icon";
			if( $ext === "png" )
				$type	= "image/png";
			else if( $ext === "gif" )
				$type	= "image/gif";
			$attributes	= array( 'rel' => "icon", 'type' => $type, 'href' => $url );
			$link		= UI_HTML_Tag::create( 'link', NULL, $attributes );
			$context->addHead( $link );
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
