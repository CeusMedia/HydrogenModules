<?php
class Hook_UI_DevLayers_Profiler extends CMF_Hydrogen_Hook
{
	public static function onPageBuild( CMF_Hydrogen_Environment $env, $context, $module, $arguments = array() )
	{
		if( $env->getConfig()->get( 'module.ui_devlayers_profiler.active' ) ){
			$context->addThemeStyle( 'module.ui.dev.layer.profiler.css' );
			try{
				$content	= View_Helper_DevProfiler::render( $env );
			}
			catch( Exception $e ){
//				print_m( $e->getMessage() );
//				die("!");
				$env->getCaptain()->callHook( 'App', 'logException', $context, array( 'exception' => $e ) );
			}
			View_Helper_DevLayers::add( 'profiler', 'Profiler', $content );
		}
	}
}
