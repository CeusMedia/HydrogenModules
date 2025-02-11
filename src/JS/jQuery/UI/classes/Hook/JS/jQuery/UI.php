<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Hook;

class Hook_JS_jQuery_UI extends Hook
{
	/**
	 *	...
	 *	@access		public
	 *	@return		void
	 */
	public function onPageApplyModules(): void
	{
		$pathJs		= $this->env->getConfig()->get( 'path.scripts' );
		$version	= $this->module->config['version']->value;
		$theme		= $this->module->config['theme']->value;
		$this->context->addJavaScript( $pathJs.'jquery-ui-'.$version.'.min.js' );
		$this->context->addCommonStyle( 'jquery-ui-'.$version.'-'.$theme.'.min.css' );
		$this->context->addBodyClass( 'uses-jQuery-UI' );
	}
}
