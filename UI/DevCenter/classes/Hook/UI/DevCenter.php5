<?php
class Hook_UI_DevCenter extends CMF_Hydrogen_Hook
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
	public static function onAppRespond( CMF_Hydrogen_Environment $env, $context, $module, $payload = array() )
	{
		if( $env->getConfig()->get( 'module.ui_devcenter.active' ) ){
			$center		= Resource_DevCenter::getInstance( $env );
			$helper		= new View_Helper_DevCenter( $env );
			$label		= '<b>Dev</b><span class="muted">Center</span>';
			$context->addBody( $helper->render( $center, $label ) );
		}
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
	public static function onEnvInitModules( CMF_Hydrogen_Environment $env, $context, $module, $payload = array() )
	{
		if( $env->getConfig()->get( 'module.ui_devcenter.active' ) ){
		}
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
	public static function onPageApplyModules( CMF_Hydrogen_Environment $env, $context, $module, $payload = array() )
	{
		if( $env->getConfig()->get( 'module.ui_devcenter.active' ) ){
			$center	= Resource_DevCenter::getInstance( $env );
			$center->add( 'request', "Request", $env->getRequest()->getAll() );
			$center->add( 'session', "Session", $env->getSession()->getAll() );
			$center->addByModule( 'cookie' );
			$center->addByModule( 'files', "Upload" );
			$center->addByModule( 'env' );
			$center->addByModule( 'server' );
			$context->addScript( "$(document).ready(function(){UI.DevCenter.init();});" );
		}
	}
}
