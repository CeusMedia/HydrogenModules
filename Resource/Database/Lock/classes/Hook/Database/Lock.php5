<?php
class Hook_Database_Lock extends CMF_Hydrogen_Hook
{
	static public function onAuthLogout( CMF_Hydrogen_Environment $env, $context, $module, $payload = array() )
	{
		$model		= new Model_Lock( $env );
		$model->removeByIndices( array(
			'userId'	=> $payload['userId'],
		) );
	}

	static public function onRegisterDashboardPanels( CMF_Hydrogen_Environment $env, $context, $module, $payload )
	{
		if( !$env->getAcl()->has( 'database/lock', 'ajaxRenderDashboardPanel' ) )
			return;
		$context->registerPanel( 'resource-database-locks', array(
			'url'			=> 'database/lock/ajaxRenderDashboardPanel',
			'title'			=> 'Datenbank-Sperren',
			'heading'		=> 'Datenbank-Sperren',
			'icon'			=> 'fa fa-fw fa-lock',
			'rank'			=> 90,
			'refresh'		=> 10,
		) );
	}

	static public function onAutoModuleLockRelease( CMF_Hydrogen_Environment $env, $context, $module, $payload = array() )
	{
		$request	= $env->getRequest();
		if( $request->isAjax() )
			return FALSE;
//		error_log( time().": ".json_encode( $request->getAll() )."\n", 3, "unlock.log" );
		return $env->getModules()->callHook( 'Database_Lock', 'checkRelease', $context, array(
			'userId'		=> $env->getSession()->get( 'userId' ),
			'request'		=> $request,
			'controller'	=> $request->get( '__controller' ),
			'action'		=> $request->get( '__action' ),
			'uri'			=> getEnv( 'REQUEST_URI' ),
		) );
	}
}
