<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Hook;

class Hook_UI_DevLayers_Profiler extends Hook
{
	public static function onPageBuild( Environment $env, $context, $module, $payload )
	{
		if( $env->getConfig()->get( 'module.ui_devlayers_profiler.active' ) ){
			$context->addThemeStyle( 'module.ui.dev.layer.profiler.css' );
			try{
				$content	= View_Helper_DevProfiler::render( $env );
				View_Helper_DevLayers::add( 'profiler', 'Profiler', $content );
			}
			catch( Exception $e ){
//				print_m( $e->getMessage() );
//				die("!");
				$payload	= ['exception' => $e];
				$env->getCaptain()->callHook( 'App', 'logException', $context, $payload );
			}
		}
	}
}
