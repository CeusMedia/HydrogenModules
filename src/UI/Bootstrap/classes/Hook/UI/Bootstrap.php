<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Environment\Remote as RemoteEnvironment;
use CeusMedia\HydrogenFramework\Environment\Resource\Captain;
use CeusMedia\HydrogenFramework\Hook;

class Hook_UI_Bootstrap extends Hook
{
	public static function onEnvInit( Environment $env, object $context, object $module, array & $payload ): void
	{
		if( get_class( $env ) === RemoteEnvironment::class )
			return;
		$config			= $env->getConfig();
		$modules		= $env->getModules();
		$moduleConfig	= $config->getAll( 'module.ui_bootstrap.', TRUE );
		$optionsMissing	= $moduleConfig->getAll( 'missing.', TRUE );
		if( !class_exists( '\CeusMedia\Bootstrap\Icon' ) ){
			switch( $optionsMissing->get( 'library' ) ){
				case 'note':
					$env->getMessenger()->noteFailure( join( '<br/>', array(
						'<strong>Bootstrap Code Library is not found.</strong>',
						'Please install by: <code><tt>composer require ceus-media/bootstrap</tt></code>',
					) ) );
					return;
				case 'throw':
				default:
					$exception	= new RuntimeException( 'Bootstrap library (ceus-media/bootstrap) is not installed - please use composer to install' );
//					$env->getCaptain()->callHook( 'App', 'onException', $context, ['exception' => $exception] );
					throw $exception;
			}
		}
		if( !$modules->has( 'UI_Font_FontAwesome' ) ){
			switch( $optionsMissing->get( 'fontawesome' ) ){
				case 'note':
					$env->getMessenger()->noteFailure( join( '<br/>', array(
						'<strong>Module "UI:Font:FontAwesome" is not installed.</strong>',
						'Please install by: <code><tt>hymn app-install UI_Font_FontAwesome</tt></code>',
					) ) );
				case 'throw':
				default:
					$exception	= new RuntimeException( 'Module "UI:Font:FontAwesome" is not installed - please use hymn to install' );
					$env->getCaptain()->callHook( 'App', 'onException', $context, ['exception' => $exception] );
//					throw $exception;
			}
		}
		else{
			$configAwesome		= $config->getAll( 'module.ui_font_fontawesome.', TRUE );
			$configBootstrap	= $config->getAll( 'module.ui_bootstrap.', TRUE );
			$versionBootstrap	= $configBootstrap->get( 'version' );

			$versionAwesomeParts	= explode( '.', $configAwesome->get( 'version' ) );
			$versionAwesomeMajor	= (int) array_shift( $versionAwesomeParts );

			$libraryVersion		= 0;
			if( class_exists( '\\CeusMedia\\Bootstrap\\Base\\Component' ) )							//  Bootstrap library (>=0.5) with base classes
				$libraryVersion	= \CeusMedia\Bootstrap\Base\Component::$version;
			else if( class_exists( '\\CeusMedia\\Bootstrap\\Component' ) )							//  Bootstrap library is below 0.4.7
				$libraryVersion	= \CeusMedia\Bootstrap\Component::getVersion();

			if( version_compare( $libraryVersion, '0.5', '>=' ) ){
				\CeusMedia\Bootstrap\Base\Structure::$defaultBsVersion	= $versionBootstrap;
				\CeusMedia\Bootstrap\Base\Component::$defaultBsVersion	= $versionBootstrap;
			}
			else
				\CeusMedia\Bootstrap\Component::$bsVersion	= $versionBootstrap;

			//  Bootstrap library (>=0.4.7) has support for Font Awesome 5
			if( property_exists( '\CeusMedia\Bootstrap\Icon', 'defaultSet' ) ){
				\CeusMedia\Bootstrap\Icon::$defaultSet	= 'fontawesome'.$versionAwesomeMajor;
				if( $configBootstrap->get( 'icon.fixedWidth' ) )
					\CeusMedia\Bootstrap\Icon::$defaultSize	= ['fixed'];
				if( $versionAwesomeMajor === 5 && $configAwesome->get( 'v5.style' ) )
					\CeusMedia\Bootstrap\Icon::$defaultStyle	= $configAwesome->get( 'v5.style' );
			}
			//  Bootstrap library is below 0.4.7
			else if( property_exists( '\CeusMedia\Bootstrap\Icon', 'iconSet' ) )
				\CeusMedia\Bootstrap\Icon::$iconSet		= 'fontawesome'.$versionAwesomeMajor;

		}
	}

	public static function onPageApplyModules( Environment $env, object $context, object $module, array & $payload ): void
	{
		if( !$env->getConfig()->get( 'module.ui_bootstrap.active' ) )
			return;

		$options		= $env->getConfig()->getAll( 'module.ui_bootstrap.', TRUE );
		$majorVersion	= self::getMajorVersion( $options->get( 'version' ) );
		$pathCdn		= sprintf( $options->get( 'cdn.path' ), $options->get( 'version' ) );
		$pathLocal		= sprintf( $options->get( 'local.path' ), $options->get( 'version' ) );
		$suffix			= $options->get( 'minified' ) ? '.min' : '';
		$loadMap		= $options->get( 'minified' ) && $options->get( 'map' );
		$script			= 'css/bootstrap'.$suffix.'.css';

		if( $options->get( 'cdn' ) ){
			//  CSS
			$context->addThemeStyle( $pathCdn.$script, Captain::LEVEL_TOP, ['crossorigin' => 'anonymous'] );
			if( $majorVersion === 3 || $majorVersion === 4 ){
				if( $options->get( 'map' ) ){
					$script	= 'css/bootstrap'.$suffix.'.css.map';
					$context->addThemeStyle( $pathCdn.$script, Captain::LEVEL_TOP, ['crossorigin' => 'anonymous'] );
				}
			}
			//  JS
			$context->js->addUrl( $pathCdn.'js/bootstrap'.$suffix.'.js' );
			if( $majorVersion === 4 && $loadMap )
				$context->js->addUrl( $pathCdn.'js/bootstrap'.$suffix.'.js.map' );
		}
		else if( $options->get( 'local' ) ){
			//  CSS
			$context->addCommonStyle( $pathLocal.$script, Captain::LEVEL_TOP );
			if( $majorVersion === 2 ){
				if( $options->get( 'responsive' ) ){
					$script	= 'css/bootstrap-responsive'.$suffix.'.css';
					$context->addCommonStyle( $pathLocal.$script, Captain::LEVEL_TOP );
				}
			}
			else if( $majorVersion === 3 || $majorVersion === 4 ){
				if( $loadMap ){
					$script	= 'css/bootstrap'.$suffix.'.css.map';
					$context->addCommonStyle( $pathLocal.$script, Captain::LEVEL_END );
				}
			}
			//  JS
			$pathLocalScripts	= $env->getConfig()->get( 'path.scripts' );
			$context->js->addUrl( $pathLocalScripts.$pathLocal.'bootstrap'.$suffix.'.js' );
			if( $majorVersion === 4 && $loadMap )
				$context->js->addUrl( $pathCdn.'js/bootstrap'.$suffix.'.js.map' );
		}
		if( $options->get( 'local.theme' ) ){
			$script	= 'css/bootstrap-'.$options->get( 'local.theme' ).$suffix.'.css';
			$context->addCommonStyle( $pathLocal.$script, Captain::LEVEL_TOP );
		}
//		$context->addCommonStyle( 'bootstrap.print.css', Captain::LEVEL_TOP );
		$context->addBodyClass( 'uses-bootstrap bootstrap'.$majorVersion );
	}

	public static function onPageBuild( Environment $env, object $context, object $module, array & $payload ): void
	{
		if( !$env->getConfig()->get( 'module.ui_bootstrap.active' ) )
			return;
		$options		= $env->getConfig()->getAll( 'module.ui_bootstrap.', TRUE );
		$majorVersion	= self::getMajorVersion( $options->get( 'version' ) );
		$cssPrefix		= 'bs'.$majorVersion.'-';
		if( !substr_count( $payload['content'], $cssPrefix ) )
			return;
		while( preg_match( '/ class="[^"]*'.$cssPrefix.'/', $payload['content'] ) ){
			$pattern		= '/(class=")([^"]*)?('.$cssPrefix.')([^ "]+)([^"]*)(")/';
			$payload['content']	= preg_replace( $pattern, '\\1\\2\\4\\5\\6', $payload['content'] );
		}
		$otherVersions	= array_diff( [2, 3, 4], [$majorVersion] );
		foreach( $otherVersions as $version ){
			$pattern		= '/(class=")([^"]*)(bs'.$version.'-[^ "]+)([^"]*)(")/';
			$payload['content']	= preg_replace( $pattern, '\\1\\2\\4\\5', $payload['content'] );
		}
		$payload['content']	= preg_replace( '/(class=")\s*([^ ]*)\s*(")/', '\\1\\2\\3', $payload['content'] );
		$payload['content']	= preg_replace( '/ class=""/', '', $payload['content'] );
	}

	protected static function getMajorVersion( string $version ): int
	{
		$versionParts	= explode( '.', $version );
		return (int) array_shift( $versionParts );
	}
}
