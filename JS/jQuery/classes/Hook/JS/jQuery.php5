<?php
class Hook_JS_jQuery extends CMF_Hydrogen_Hook{

	/**
	 *	@static
	 *	@param		CMF_Hydrogen_Environment	$env		Environment object
	 *	@param		object						$context	Caller object
	 *	@param		object						$module		Module config data object
	 *	@param		array						$payload	Map of payload data
	 *	@return		void
	 */
	static public function onPageApplyModules( CMF_Hydrogen_Environment $env, $context, $module, $payload = array() ){
		$pathJs		= $env->getConfig()->get( 'path.scripts' );
		$version	= $module->config['version']->value;
		$minified	= $module->config['load.minified']->value;
		if( $minified ){
			$context->addJavaScript( $pathJs.'jquery-'.$version.'.min.js' );
			if( $module->config['load.map']->value ){
				$versions	= array( '1.10.2', '1.11.1', '3.3.1' );
				if( in_array( $version, $versions ) )
					$context->js->addUrl( $pathJs.'jquery-'.$version.'.min.map', 9 );
			}
		}
		else
			$context->addJavaScript( $pathJs.'jquery-'.$version.'.js' );

		if( $module->config['migrate']->value ){
			$debug	= $module->config['migrate.debug']->value;
			if( $debug === "off" || $debug === "auto" && $minified )
				$context->addJavaScript( $pathJs.'jquery-migrate-3.0.1.min.js' );
			else
				$context->addJavaScript( $pathJs.'jquery-migrate-3.0.1.js' );
		}
		$context->addBodyClass( 'uses-jQuery' );
	}
}
