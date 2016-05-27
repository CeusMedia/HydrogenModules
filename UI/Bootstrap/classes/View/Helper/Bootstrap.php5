<?php
class View_Helper_Bootstrap{

	static public function ___onPageApplyModules( $env, $context, $module, $data = array() ){
		$options		= $env->getConfig()->getAll( 'module.ui_bootstrap.', TRUE );
		if( !$options->get( 'enabled' ) )
			return;
		self::load( $env );
	}

	static public function load( $env ){
		$options		= $env->getConfig()->getAll( 'module.ui_bootstrap.', TRUE );
		$context		= $env->getPage();
		$context->addBodyClass( 'uses-bootstrap' );
		$majorVersion	= (int) substr( $options->get( 'version' ), 0, 1 );
		$pathCdn		= sprintf( $options->get( 'cdn.path' ), $options->get( 'version' ) );
		$pathLocal		= sprintf( $options->get( 'local.path' ), $options->get( 'version' ) );
		$suffix			= $options->get( 'minified' ) ? '.min' : '';
		$script			= 'css/bootstrap'.$suffix.'.css';

		if( $options->get( 'cdn' ) ){
			$context->addThemeStyle( $pathCdn.$script, 'top', array( 'crossorigin' => 'anonymous' ) );
			$context->js->addUrl( $pathCdn.'js/bootstrap'.$suffix.'.js' );
			if( $majorVersion === 3 ){
				if( $options->get( 'map' ) ){
					$script	= 'css/bootstrap'.$suffix.'.css.map';
					$context->addThemeStyle( $pathCdn.$script, 'top', array( 'crossorigin' => 'anonymous' ) );
				}
			}
		}
		else if( $options->get( 'local' ) ){
			$context->addCommonStyle( $pathLocal.$script, 'top' );
			$context->js->addUrl( $env->getConfig()->get( 'path.scripts' ).$pathLocal.'bootstrap'.$suffix.'.js' );
			if( $majorVersion === 2 ){
				if( $options->get( 'responsive' ) ){
					$script	= 'css/bootstrap-responsive'.$suffix.'.css';
					$context->addCommonStyle( $pathLocal.$script, 'top' );
				}
			}
			else if( $majorVersion === 3 ){
				if( $options->get( 'map' ) ){
					$script	= 'css/bootstrap'.$suffix.'.css.map';
					$context->addCommonStyle( $pathLocal.$script, 'top' );
				}
			}
		}
		if( $options->get( 'local.theme' ) ){
			$script	= 'css/bootstrap-'.$options->get( 'local.theme' ).$suffix.'.css';
			$context->addCommonStyle( $pathLocal.$script, 'top' );
		}
		$context->addCommonStyle( 'bootstrap.print.css', 'top' );
	}
}
?>
