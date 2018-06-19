<?php
class Hook_Auth_Local/* extends CMF_Hydrogen_Hook*/{

	static protected $configPrefix	= 'module.resource_authentication_backend_local.';

	static public function onAuthRegisterBackend( CMF_Hydrogen_Environment $env, $context, $module, $data = array() ){
		if( !$env->getConfig()->get( self::$configPrefix.'enabled' ) )
			return;
		$words	= $env->getLanguage()->getWords( 'auth/local' );
		$context->registerBackend( 'Local', 'local', $words['backend']['title'] );
	}

	static public function onAuthRegisterLoginTab( CMF_Hydrogen_Environment $env, $context, $module, $data = array() ){
		if( !$env->getConfig()->get( self::$configPrefix.'enabled' ) )
			return;
		$words		= (object) $env->getLanguage()->getWords( 'auth/local' );					//  load words
		$rank		= $env->getConfig()->get( self::$configPrefix.'login.rank' );
		$context->registerTab( 'auth/local/login', $words->login['tab'], $rank );				//  register main tab
	}

	static public function onGetRelatedUsers( CMF_Hydrogen_Environment $env, $context, $module, $data ){
		if( !$env->getConfig()->get( self::$configPrefix.'relateToAllUsers' ) )
			return;
		$modelUser	= new Model_User( $env );
		$words		= $env->getLanguage()->getWords( 'auth/local' );
		$conditions	= array( 'status' => '>0' );
		$users		= $modelUser->getAll( $conditions, array( 'username' => 'ASC' ) );
		$data->list	= array( (object) array(
			'module'		=> $moduleId,
			'label'			=> $words['hook-getRelatedUsers']['label'],
			'count'			=> count( $users ),
			'list'			=> $users,
		) );
		return TRUE;
	}

/*	static public function onPageApplyModules( CMF_Hydrogen_Environment $env, $context, $module, $data = array() ){
		$userId		= (int) $env->getSession()->get( 'userId' );								//  get ID of current user (or zero)
		$cookie		= new Net_HTTP_Cookie( parse_url( $env->url, PHP_URL_PATH ) );
		$remember	= (bool) $cookie->get( 'auth_remember' );
		$env->getSession()->set( 'isRemembered', $remember );
		$script		= 'Auth.init('.$userId.','.json_encode( $remember ).');';					//  initialize Auth class with user ID
		$env->getPage()->js->addScriptOnReady( $script, 1 );									//  enlist script to be run on ready
	}*/
}
