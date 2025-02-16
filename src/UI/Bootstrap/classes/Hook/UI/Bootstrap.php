<?php

use CeusMedia\Bootstrap\Base\Element as BootstrapBaseElement;
use CeusMedia\Bootstrap\Base\Structure as BootstrapBaseStructure;
use CeusMedia\Bootstrap\Icon as BootstrapIcon;
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
		if( !class_exists( BootstrapIcon::class ) ){
			switch( $optionsMissing->get( 'library' ) ){
				case 'note':
					$env->getMessenger()->noteFailure( join( '<br/>', [
						'<strong>Bootstrap Code Library is not found.</strong>',
						'Please install by: <code><tt>composer require ceus-media/bootstrap</tt></code>',
					] ) );
					return;
				case 'throw':
				default:
					$exception	= new RuntimeException( 'Bootstrap library (ceus-media/bootstrap) is not installed - please use composer to install' );
//					$payload	= ['exception' => $exception];
//					$env->getCaptain()->callHook( 'App', 'onException', $context, $payload );
					throw $exception;
			}
		}
		if( !$modules->has( 'UI_Font_FontAwesome' ) ){
			switch( $optionsMissing->get( 'fontawesome' ) ){
				case 'note':
					$env->getMessenger()->noteFailure( join( '<br/>', [
						'<strong>Module "UI:Font:FontAwesome" is not installed.</strong>',
						'Please install by: <code><tt>hymn app-install UI_Font_FontAwesome</tt></code>',
					] ) );
					break;
				case 'throw':
				default:
					$exception	= new RuntimeException( 'Module "UI:Font:FontAwesome" is not installed - please use hymn to install' );
					$payload	= ['exception' => $exception];
					$env->getCaptain()->callHook( 'App', 'onException', $context, $payload );
//					throw $exception;
			}
		}
		else{
			$configAwesome		= $config->getAll( 'module.ui_font_fontawesome.', TRUE );
			$configBootstrap	= $config->getAll( 'module.ui_bootstrap.', TRUE );
			$versionBootstrap	= $configBootstrap->get( 'version' );

			$versionAwesomeParts	= explode( '.', $configAwesome->get( 'version' ) );
			$versionAwesomeMajor	= (int) array_shift( $versionAwesomeParts );

			BootstrapBaseStructure::$defaultBsVersion	= $versionBootstrap;
			BootstrapBaseElement::$defaultBsVersion	= $versionBootstrap;

			BootstrapIcon::$defaultSet	= 'fontawesome'.$versionAwesomeMajor;
			if( $configBootstrap->get( 'icon.fixedWidth' ) )
				BootstrapIcon::$defaultSize	= ['fixed'];
			if( $versionAwesomeMajor === 5 && $configAwesome->get( 'v5.style' ) )
				BootstrapIcon::$defaultStyle	= $configAwesome->get( 'v5.style' );
		}
	}

	public function onPageApplyModules(): void
	{
		if( !$this->env->getConfig()->get( 'module.ui_bootstrap.active' ) )
			return;

		$options		= $this->env->getConfig()->getAll( 'module.ui_bootstrap.', TRUE );
		$majorVersion	= self::getMajorVersion( $options->get( 'version' ) );
		$pathCdn		= sprintf( $options->get( 'cdn.path' ), $options->get( 'version' ) );
		$pathLocal		= sprintf( $options->get( 'local.path' ), $options->get( 'version' ) );
		$suffix			= $options->get( 'minified' ) ? '.min' : '';
		$loadMap		= $options->get( 'minified' ) && $options->get( 'map' );
		$script			= 'css/bootstrap'.$suffix.'.css';

		if( $options->get( 'cdn' ) ){
			//  CSS
			$this->context->addThemeStyle( $pathCdn.$script, Captain::LEVEL_TOP, ['crossorigin' => 'anonymous'] );
			if( in_array( $majorVersion, [3, 4, 5], TRUE ) ){
				if( $options->get( 'map' ) ){
					$script	= 'css/bootstrap'.$suffix.'.css.map';
					$this->context->addThemeStyle( $pathCdn.$script, Captain::LEVEL_TOP, ['crossorigin' => 'anonymous'] );
				}
			}
			//  JS
			$this->context->js->addUrl( $pathCdn.'js/bootstrap'.$suffix.'.js' );
			if(  in_array( $majorVersion, [4, 5], TRUE ) && $loadMap )
				$this->context->js->addUrl( $pathCdn.'js/bootstrap'.$suffix.'.js.map' );
		}
		else if( $options->get( 'local' ) ){
			//  CSS
			$this->context->addCommonStyle( $pathLocal.$script, Captain::LEVEL_TOP );
			if( 2 === $majorVersion ){
				if( $options->get( 'responsive' ) ){
					$script	= 'css/bootstrap-responsive'.$suffix.'.css';
					$this->context->addCommonStyle( $pathLocal.$script, Captain::LEVEL_TOP );
				}
			}
			else if( in_array( $majorVersion, [3, 4, 5], TRUE ) ){
				if( $loadMap ){
					$script	= 'css/bootstrap'.$suffix.'.css.map';
					$this->context->addCommonStyle( $pathLocal.$script, Captain::LEVEL_END );
				}
			}
			//  JS
			$pathLocalScripts	= $this->env->getConfig()->get( 'path.scripts' );
			$this->context->js->addUrl( $pathLocalScripts.$pathLocal.'bootstrap'.$suffix.'.js' );
			if( in_array( $majorVersion, [4, 5], TRUE ) && $loadMap )
				$this->context->js->addUrl( $pathCdn.'js/bootstrap'.$suffix.'.js.map' );
		}
		if( $options->get( 'local.theme' ) ){
			$script	= 'css/bootstrap-'.$options->get( 'local.theme' ).$suffix.'.css';
			$this->context->addCommonStyle( $pathLocal.$script, Captain::LEVEL_TOP );
		}
		$this->context->addBodyClass( 'uses-bootstrap bootstrap'.$majorVersion );
	}

	public function onPageBuild(): void
	{
		if( !$this->env->getConfig()->get( 'module.ui_bootstrap.active' ) )
			return;
		$options		= $this->env->getConfig()->getAll( 'module.ui_bootstrap.', TRUE );
		$majorVersion	= self::getMajorVersion( $options->get( 'version' ) );
		$cssPrefix		= 'bs'.$majorVersion.'-';
		if( !substr_count( $this->payload['content'], $cssPrefix ) )
			return;
		while( preg_match( '/ class="[^"]*'.$cssPrefix.'/', $this->payload['content'] ) ){
			$pattern	= '/(class=")([^"]*)?('.$cssPrefix.')([^ "]+)([^"]*)(")/';
			$this->payload['content']	= preg_replace( $pattern, '\\1\\2\\4\\5\\6', $this->payload['content'] );
		}
		$otherVersions	= array_diff( [2, 3, 4], [$majorVersion] );
		foreach( $otherVersions as $version ){
			$pattern	= '/(class=")([^"]*)(bs'.$version.'-[^ "]+)([^"]*)(")/';
			$this->payload['content']	= preg_replace( $pattern, '\\1\\2\\4\\5', $this->payload['content'] );
		}
		$this->payload['content']	= preg_replace( '/(class=")\s*([^ ]*)\s*(")/', '\\1\\2\\3', $this->payload['content'] );
		$this->payload['content']	= preg_replace( '/ class=""/', '', $this->payload['content'] );
	}

	protected static function getMajorVersion( string $version ): int
	{
		$versionParts	= explode( '.', $version );
		return (int) array_shift( $versionParts );
	}
}
