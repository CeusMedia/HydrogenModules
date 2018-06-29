<?php
class Hook_UI_Bootstrap/* extends CMF_Hook*/{

	static protected function getMajorVersion( $version ){
		$versionParts	= explode( '.', $version );
		return (int) array_shift( $versionParts );
	}

	static public function onPageApplyModules( CMF_Hydrogen_Environment $env, $context, $module, $data = array() ){
		if( !$env->getConfig()->get( 'module.ui_bootstrap.enabled' ) )
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
			$context->addThemeStyle( $pathCdn.$script, 'top', array( 'crossorigin' => 'anonymous' ) );
			if( $majorVersion === 3 || $majorVersion === 4 ){
				if( $options->get( 'map' ) ){
					$script	= 'css/bootstrap'.$suffix.'.css.map';
					$context->addThemeStyle( $pathCdn.$script, 'top', array( 'crossorigin' => 'anonymous' ) );
				}
			}
			//  JS
			$context->js->addUrl( $pathCdn.'js/bootstrap'.$suffix.'.js' );
			if( $majorVersion === 4 && $loadMap )
				$context->js->addUrl( $pathCdn.'js/bootstrap'.$suffix.'.js.map' );
		}
		else if( $options->get( 'local' ) ){
			//  CSS
			$context->addCommonStyle( $pathLocal.$script, 'top' );
			if( $majorVersion === 2 ){
				if( $options->get( 'responsive' ) ){
					$script	= 'css/bootstrap-responsive'.$suffix.'.css';
					$context->addCommonStyle( $pathLocal.$script, 'top' );
				}
			}
			else if( $majorVersion === 3 || $majorVersion === 4 ){
				if( $loadMap ){
					$script	= 'css/bootstrap'.$suffix.'.css.map';
					$context->addCommonStyle( $pathLocal.$script, 'bottom' );
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
			$context->addCommonStyle( $pathLocal.$script, 'top' );
		}
//		$context->addCommonStyle( 'bootstrap.print.css', 'top' );
		$context->addBodyClass( 'uses-bootstrap bootstrap'.$majorVersion );
	}

	static public function onPageBuild( CMF_Hydrogen_Environment $env, $context, $module, $data = array() ){
		if( !$env->getConfig()->get( 'module.ui_bootstrap.enabled' ) )
			return;
		$options		= $env->getConfig()->getAll( 'module.ui_bootstrap.', TRUE );
		$majorVersion	= self::getMajorVersion( $options->get( 'version' ) );
		$cssPrefix		= 'bs'.$majorVersion.'-';
		if( !substr_count( $data->content, $cssPrefix ) )
			return;
		while( preg_match( '/ class="[^"]*'.$cssPrefix.'/', $data->content ) ){
			$pattern		= '/(class=")([^"]*)?('.$cssPrefix.')([^ "]+)([^"]*)(")/';
			$data->content	= preg_replace( $pattern, '\\1\\2\\4\\5\\6', $data->content );
		}
		$otherVersions	= array_diff( array( 2, 3, 4 ), array( $majorVersion ) );
		foreach( $otherVersions as $version ){
			$pattern		= '/(class=")([^"]*)(bs'.$version.'-[^ "]+)([^"]*)(")/';
			$data->content	= preg_replace( $pattern, '\\1\\2\\4\\5', $data->content );
		}
		$data->content	= preg_replace( '/(class=")\s*([^ ]*)\s*(")/', '\\1\\2\\3', $data->content );
		$data->content	= preg_replace( '/ class=""/', '', $data->content );
	}
}
?>
