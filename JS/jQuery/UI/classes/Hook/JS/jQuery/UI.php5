<?php
class Hook_JS_jQuery_UI extends CMF_Hydrogen_Hook{

	/**
	 *	...
	 *	@static
	 *	@access		public
	 *	@param		CMF_Hydrogen_Environment	$env		Environment object
	 *	@param		object						$context	Caller object
	 *	@param		object						$module		Module config data object
	 *	@param		array						$payload	Map of payload data
	 *	@return		void
	 */
	static public function onPageApplyModules( CMF_Hydrogen_Environment $env, $context, $module, $payload = [] ){
		$pathJs		= $env->getConfig()->get( 'path.scripts' );
		$version	= $module->config['version']->value;
		$theme		= $module->config['theme']->value;
		$context->addJavaScript( $pathJs.'jquery-ui-'.$version.'.min.js' );
		$context->addCommonStyle( 'jquery-ui-'.$version.'-'.$theme.'.min.css' );
		$context->addBodyClass( 'uses-jQuery-UI' );
	}
}
