<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Hook;

class Hook_Database_Lock extends Hook
{
	static public function onAuthLogout( Environment $env, $context, $module, $payload = [] )
	{
		$model		= new Model_Lock( $env );
		$model->removeByIndices( [
			'userId'	=> $payload['userId'],
		] );
	}

	static public function onRegisterDashboardPanels( Environment $env, $context, $module, $payload )
	{
		if( !$env->getAcl()->has( 'database/lock', 'ajaxRenderDashboardPanel' ) )
			return;
		$context->registerPanel( 'resource-database-locks', [
			'url'			=> 'database/lock/ajaxRenderDashboardPanel',
			'title'			=> 'Datenbank-Sperren',
			'heading'		=> 'Datenbank-Sperren',
			'icon'			=> 'fa fa-fw fa-lock',
			'rank'			=> 90,
			'refresh'		=> 10,
		] );
	}

	static public function onAutoModuleLockRelease( Environment $env, $context, $module, $payload = [] )
	{
		$request	= $env->getRequest();
		if( $request->isAjax() )
			return FALSE;
//		error_log( time().": ".json_encode( $request->getAll() )."\n", 3, "unlock.log" );
		$payload	= array(
			'userId'		=> $env->getSession()->get( 'auth_user_id' ),
			'request'		=> $request,
			'controller'	=> $request->get( '__controller' ),
			'action'		=> $request->get( '__action' ),
			'uri'			=> getEnv( 'REQUEST_URI' ),
		);
		return $env->getModules()->callHookWithPayload( 'Database_Lock', 'checkRelease', $context, $payload );
	}
}
