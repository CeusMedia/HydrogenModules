<?php
class Hook_Database_Lock/* extends CMF_Hydrogen_Hook*/{

	static public function ___onAuthLogout( CMF_Hydrogen_Environment $env, $context, $module, $data = array() ){
		$model		= new Model_Lock( $env );
		$model->removeByIndices( array(
			'userId'	=> $data['userId'],
		) );
	}

	static public function ___onRegisterDashboardPanels( CMF_Hydrogen_Environment $env, $context, $module, $data ){
		if( !$env->getAcl()->has( 'work/time', 'ajaxRenderDashboardPanel' ) )
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

	static public function ___onAutoModuleLockRelease( CMF_Hydrogen_Environment $env, $context/*, $module, $data = array()*/ ){
		$request	= $env->getRequest();
		if( $request->isAjax() )
			return FALSE;
//		error_log( time().": ".json_encode( $request->getAll() )."\n", 3, "unlock.log" );
		return $env->getModules()->callHook( 'Database_Lock', 'checkRelease', $context, array(
			'userId'		=> $env->getSession()->get( 'userId' ),
			'request'		=> $request,
			'controller'	=> $request->get( 'controller' ),
			'action'		=> $request->get( 'action' ),
			'uri'			=> getEnv( 'REQUEST_URI' ),
		) );
	}
}
