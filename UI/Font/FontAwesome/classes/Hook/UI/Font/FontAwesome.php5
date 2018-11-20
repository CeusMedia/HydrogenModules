<?php
class Hook_UI_Font_FontAwesome/* extends CMF_Hook*/{

	static protected function addV5CdnResource( $env, $style = 'all' ){
		$config			= $env->getConfig()->getAll( 'module.ui_font_fontawesome.', TRUE );
		$urlTemplateCss	= 'https://%s.fontawesome.com/releases/v%s/css/%s.css';
		$urlTemplateJs	= 'https://%s.fontawesome.com/releases/v%s/js/%s.js';
		if( $config->get( 'v5.mode' ) === 'css+font' ){
			$env->getPage()->addHead( UI_HTML_Tag::create( 'link', NULL, array(
				'href'			=> vsprintf( $urlTemplateCss, array(
					$config->get( 'v5.license' ) === 'pro' ? 'pro' : 'use',
					$config->get( 'version' ),
					$style,
				) ),
				'rel'			=> 'stylesheet',
				'crossorigin'	=> 'anonymous',
			) ) );
		}
		if( $config->get( 'v5.mode' ) === 'js+svg' ){
			$env->getPage()->addHead( UI_HTML_Tag::create( 'script', '', array(
				'src'			=> vsprintf( $urlTemplateJs, array(
					$config->get( 'v5.license' ) === 'pro' ? 'pro' : 'use',
					$config->get( 'version' ),
					$style,
				) ),
				'defer'			=> 'defer',
				'crossorigin'	=> 'anonymous',
			) ) );
		}
	}

	static public function onPageApplyModules( CMF_Hydrogen_Environment $env, $module, $context, $data = array() ){
		$config	= $env->getConfig();
		$mc		= $config->getAll( 'module.ui_font_fontawesome.', TRUE );
		if( !$config->get( 'module.ui_font.active' ) )
			return;
		if( !$config->get( 'module.ui_font_fontawesome.active' ) )
			return;

		if( version_compare( $mc->get( 'version' ), 5 ) < 0 ){
			$url	= $config->get( 'module.ui_font.uri' ).'FontAwesome/font-awesome.min.css';
			if( $mc->get( 'v4.cdn' ) && $mc->get( 'version' ) === '4.7.0' )
				$url	= 'https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css';
			$env->getPage()->css->theme->addUrl( $url );
			return;
		}

		$license	= $mc->get( 'v5.license' );
		$styles		= $mc->getAll( 'v5.'.$license.'.', TRUE );
		if( $styles->get( 'all' ) )
			self::addV5CdnResource( $env, 'all' );
		else{
			if( $styles->get( 'solid' ) )
				self::addV5CdnResource( $env, 'solid' );
			if( $styles->get( 'regular' ) )
				self::addV5CdnResource( $env, 'regular' );
			if( $styles->get( 'light' ) && $license === 'pro' )
				self::addV5CdnResource( $env, 'light' );
			if( $styles->get( 'brand' ) )
				self::addV5CdnResource( $env, 'brand' );
			self::addV5CdnResource( $env, 'fontawesome' );
		}

		if( 1 || $mc->get( 'v5.shim' ) ){
			self::addV5CdnResource( $env, 'v4-shims' );
		}
	}
}
