<?php
class Hook_UI_Font_FontAwesome/* extends CMF_Hook*/{

	static protected function addV5CdnLink( $env, $style = 'all' ){
		$config			= $env->getConfig()->getAll( 'module.ui_font_fontawesome.', TRUE );
		$urlTemplate	= 'https://%s.fontawesome.com/releases/v%s/css/%s.css';
		$env->getPage()->addHead( UI_HTML_Tag::create( 'link', NULL, array(
			'rel'			=> 'stylesheet',
			'href'			=> vsprintf( $urlTemplate, array(
				$config->get( 'v5.license' ) === 'pro' ? 'pro' : 'use',
				$config->get( 'version' ),
				$style,
			) ),
			'crossorigin'	=> 'anonymous',
		) ) );
	}

	static public function onPageApplyModules( CMF_Hydrogen_Environment $env, $module, $context, $data = array() ){
		$config	= $env->getConfig();
		$mc		= $config->getAll( 'module.ui_font_fontawesome.', TRUE );
		if( !$config->get( 'module.ui_font.enabled' ) )
			return;
		if( !$config->get( 'module.ui_font_fontawesome.enabled' ) )
			return;

		if( version_compare( $mc->get( 'version' ), 5 ) < 0 ){
			$url	= $config->get( 'module.ui_font.uri' ).'FontAwesome/font-awesome.min.css';
			$env->getPage()->css->theme->addUrl( $url );
			return;
		}
		$license	= $mc->get( 'v5.license' );
		$styles		= $mc->getAll( 'v5.'.$license.'.', TRUE );
		if( $styles->get( 'all' ) )
			self::addV5CdnLink( $env, 'all' );
		else{
			if( $styles->get( 'solid' ) )
				self::addV5CdnLink( $env, 'solid' );
			if( $styles->get( 'regular' ) )
				self::addV5CdnLink( $env, 'regular' );
			if( $styles->get( 'light' ) && $license === 'pro' )
				self::addV5CdnLink( $env, 'light' );
			if( $styles->get( 'brand' ) )
				self::addV5CdnLink( $env, 'brand' );
			self::addV5CdnLink( $env, 'fontawesome' );
		}
	}
}
