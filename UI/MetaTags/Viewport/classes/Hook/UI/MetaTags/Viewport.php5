<?php
class Hook_UI_MetaTags_Viewport extends CMF_Hydrogen_Hook{

	/**
	 *	@static
	 *	@param		CMF_Hydrogen_Environment	$env		Environment object
	 *	@param		object						$context	Caller object
	 *	@param		object						$module		Module config data object
	 *	@param		array						$payload	Map of payload data
	 *	@return		void
	 */
	static public function onPageApplyModules( CMF_Hydrogen_Environment $env, $context, $module, $payload = array() ){
		$moduleConfig	= $env->getConfig()->getAll( 'module.ui_metatags_viewport.', TRUE );
		if( !$moduleConfig->get( 'active' ) )
			return;
		$options		= array();
		foreach( $moduleConfig->getAll() as $key => $value )
			if( strlen( trim( $value ) ) )
				if( $key !== 'active' )
					$options[]	= $key.'='.htmlentities( $value, ENT_QUOTES, 'UTF-8' );
		$context->addMetaTag( 'name', 'viewport', join( ', ', $options ) );
	}
}
