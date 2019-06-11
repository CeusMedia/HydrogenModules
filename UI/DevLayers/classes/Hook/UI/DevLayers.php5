<?php
class Hook_UI_DevLayers extends CMF_Hydrogen_Hook{

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
	static public function onAppRespond( CMF_Hydrogen_Environment $env, $context, $module, $payload = array() ){
		if( !$env->getConfig()->get( 'module.ui_devlayers.active' ) )
			return;
		$helper		= new View_Helper_DevLayers( $env );
		$context->addBody( $helper->render() );
	}

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
	static public function onPageApplyModules( CMF_Hydrogen_Environment $env, $context, $module, $payload = array() ){
		if( !$env->getConfig()->get( 'module.ui_devlayers.active' ) )
			return;
	}
}
