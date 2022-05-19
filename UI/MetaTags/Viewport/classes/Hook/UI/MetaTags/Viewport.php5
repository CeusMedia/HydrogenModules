<?php
class Hook_UI_MetaTags_Viewport extends CMF_Hydrogen_Hook
{
	/**
	 *	@param		CMF_Hydrogen_Environment	$env		Environment object
	 *	@static
	 *	@param		object						$context	Caller object
	 *	@param		object						$module		Module config data object
	 *	@param		array						$payload	Map of payload data
	 *	@return		void
	 */
	public static function onPageApplyModules( CMF_Hydrogen_Environment $env, $context, $module, $payload )
	{
		$moduleConfig	= $env->getConfig()->getAll( 'module.ui_metatags_viewport.', TRUE );
		if( !$moduleConfig->get( 'active' ) )
			return;
		$options		= [];
		foreach( $moduleConfig->getAll() as $key => $value )
			if( strlen( trim( $value ) ) )
				if( $key !== 'active' )
					$options[]	= $key.'='.htmlentities( $value, ENT_QUOTES, 'UTF-8' );
		$context->addMetaTag( 'name', 'viewport', join( ', ', $options ) );
	}
}
