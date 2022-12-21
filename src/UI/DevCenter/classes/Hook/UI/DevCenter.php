<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Hook;

class Hook_UI_DevCenter extends Hook
{
	/**
	 *	...
	 *	@access		public
	 *	@static
	 *	@param		Environment		$env		Environment object
	 *	@param		object			$context	Caller object
	 *	@param		object			$module		Module config data object
	 *	@param		array			$payload	Map of payload data
	 *	@return		void
	 */
	public static function onAppRespond( Environment $env, $context, $module, $payload = [] )
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
	 *	@param		Environment		$env		Environment object
	 *	@param		object			$context	Caller object
	 *	@param		object			$module		Module config data object
	 *	@param		array			$payload	Map of payload data
	 *	@return		void
	 */
	public static function onEnvInitModules( Environment $env, $context, $module, $payload = [] )
	{
		if( $env->getConfig()->get( 'module.ui_devcenter.active' ) ){
		}
	}

	/**
	 *	...
	 *	@access		public
	 *	@static
	 *	@param		Environment		$env		Environment object
	 *	@param		object			$context	Caller object
	 *	@param		object			$module		Module config data object
	 *	@param		array			$payload	Map of payload data
	 *	@return		void
	 */
	public static function onPageApplyModules( Environment $env, $context, $module, $payload = [] )
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
