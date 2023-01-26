<?php

use CeusMedia\HydrogenFramework\Hook;

class Hook_JS_jQuery extends Hook
{
	/**
	 *	@return		void
	 */
	public function onPageApplyModules(): void
	{
		$pathJs		= $this->env->getConfig()->get( 'path.scripts' );
		$version	= $this->module->config['version']->value;
		$minified	= $this->module->config['load.minified']->value;
		if( $minified ){
			$this->context->addJavaScript( $pathJs.'jquery-'.$version.'.min.js' );
			if( $this->module->config['load.map']->value ){
				$versions	= ['1.10.2', '1.11.1', '3.3.1'];
				if( in_array( $version, $versions ) )
					$this->context->js->addUrl( $pathJs.'jquery-'.$version.'.min.map', 9 );
			}
		}
		else
			$this->context->addJavaScript( $pathJs.'jquery-'.$version.'.js' );

		if( $this->module->config['migrate']->value ){
			$debug	= $this->module->config['migrate.debug']->value;
			if( $debug === "off" || $debug === "auto" && $minified )
				$this->context->addJavaScript( $pathJs.'jquery-migrate-3.0.1.min.js' );
			else
				$this->context->addJavaScript( $pathJs.'jquery-migrate-3.0.1.js' );
		}
		$this->context->addBodyClass( 'uses-jQuery' );
	}
}
