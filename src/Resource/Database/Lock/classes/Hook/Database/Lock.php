<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Hook;

class Hook_Database_Lock extends Hook
{
	public function onAuthLogout(): void
	{
		$model		= new Model_Lock( $this->env );
		$model->removeByIndices( [
			'userId'	=> $this->payload['userId'],
		] );
	}

	public function onRegisterDashboardPanels(): void
	{
		if( !$this->env->getAcl()->has( 'database/lock', 'ajaxRenderDashboardPanel' ) )
			return;
		$this->context->registerPanel( 'resource-database-locks', [
			'url'			=> 'ajax/database/lock/renderDashboardPanel',
			'title'			=> 'Datenbank-Sperren',
			'heading'		=> 'Datenbank-Sperren',
			'icon'			=> 'fa fa-fw fa-lock',
			'rank'			=> 90,
			'refresh'		=> 10,
		] );
	}

	public function onAutoModuleLockRelease(): bool|int
	{
		$request	= $this->env->getRequest();
		if( $request->isAjax() )
			return FALSE;
//		error_log( time().": ".json_encode( $request->getAll() )."\n", 3, "unlock.log" );
		$payload	= [
			'userId'		=> $this->env->getSession()->get( 'auth_user_id' ),
			'request'		=> $request,
			'controller'	=> $request->get( '__controller' ),
			'action'		=> $request->get( '__action' ),
			'uri'			=> getEnv( 'REQUEST_URI' ),
		];
		return $this->env->getModules()->callHookWithPayload( 'Database_Lock', 'checkRelease', $this->context, $payload );
	}
}
