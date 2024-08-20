<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Hook;

class Hook_JS_jQuery_Mmenu extends Hook
{
	/**
	 *	@return		void
	 */
	public function onPageApplyModules(): void
	{
		$config		= $this->env->getConfig()->getAll( 'module.js_jquery_mmenu.', TRUE );
		$version	= $config->get( 'version' );
		$pathJs		= $this->env->getConfig()->get( 'path.scripts' ).'mmenu/'.$version.'/';
		$files		= (object) ['js' => [], 'css' => []];
		if( $config->get( 'version' ) === '7.0.5' ){
			if( $config->get( 'load' ) == "all" ){
				$files->js[]	= 'jquery.mmenu.all.min.js';
				$files->css[]	= 'jquery.mmenu.all.min.css';
			}
			else{
				$files->js[]	= 'jquery.mmenu.min.js';
				$files->css[]	= 'jquery.mmenu.min.css';
			}
		}
		else{
			if( $config->get( 'load' ) == "all" ){
				$files->js[]	= 'jquery.mmenu.min.all.js';
				$files->css[]	= 'jquery.mmenu.all.css';
			}
			else{
				$files->js[]	= 'jquery.mmenu.min.js';
				$files->css[]	= 'jquery.mmenu.css';
			}
		}

		// @todo support addons and extensions if core was loaded, only
		// @todo support CDN: https://cdnjs.com/libraries/jQuery.mmenu

		foreach( $files->js as $filePath )
			$this->context->js->addUrl( $pathJs.$filePath );
		foreach( $files->css as $filePath )
			$this->context->addCommonStyle( 'mmenu/'.$version.'/'.$filePath );
	}
}
