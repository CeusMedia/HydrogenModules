<?php
class Hook_UI_DevLayers extends CMF_Hydrogen_Hook
{
	/**
	 *	...
	 *	@access		public
	 *	@static
	 *	@param		CMF_Hydrogen_Environment	$env		Environment object
	 *	@param		object						$context	Caller object
	 *	@param		object						$module		Module config data object
	 *	@param		array						$payload	Map of payload data
	 *	@return		void
	 */
	public static function onAppRespond( CMF_Hydrogen_Environment $env, $context, $module, $payload = [] )
	{
		if( !$env->getConfig()->get( 'module.ui_devlayers.active' ) )
			return;
		$helper		= new View_Helper_DevLayers( $env );
		$context->addBody( $helper->render() );
	}

	/**
	 *	...
	 *	@access		public
	 *	@static
	 *	@param		CMF_Hydrogen_Environment	$env		Environment object
	 *	@param		object						$context	Caller object
	 *	@param		object						$module		Module config data object
	 *	@param		array						$payload	Map of payload data
	 *	@return		void
	 */
	public static function onPageApplyModules( CMF_Hydrogen_Environment $env, $context, $module, $payload = [] )
	{
		if( !$env->getConfig()->get( 'module.ui_devlayers.active' ) )
			return;
	}
}
