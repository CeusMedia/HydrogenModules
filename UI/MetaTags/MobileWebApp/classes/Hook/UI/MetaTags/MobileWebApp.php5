<?php
class Hook_UI_MetaTags_MobileWebApp extends CMF_Hydrogen_Hook{

	/**
	 *	@static
	 *	@param		CMF_Hydrogen_Environment	$env		Environment object
	 *	@param		object						$context	Caller object
	 *	@param		object						$module		Module config data object
	 *	@param		array						$payload	Map of payload data
	 *	@return		void
	 */
	static public function onPageApplyModules( CMF_Hydrogen_Environment $env, $context, $module, $payload = array() ){
		$moduleConfig	= $env->getConfig()->getAll( 'module.ui_metatags_mobilewebapp.', TRUE );
		if( !$moduleConfig->get( 'active' ) )
			return;

		$context->addMetaTag( 'name', 'mobile-web-app-capable', 'yes' );
		$context->addMetaTag( 'name', 'apple-mobile-web-app-capable', 'yes' );

/*		$options	= array();
		foreach( $moduleConfig->getAll() as $key => $value )
			if( strlen( trim( $value ) ) && $key !== 'active' )
				$options[]	= $key.'='.htmlentities( $value, ENT_QUOTES, 'UTF-8' );
		$context->addMetaTag( 'name', '...', join( ', ', $options ) );*/
	}
}
