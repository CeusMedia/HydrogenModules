<?php
class Controller_Index extends CMF_Hydrogen_Controller
{
	public function index( $arg1 = NULL, $arg2 = NULL, $arg3 = NULL )
	{
		$config			= $this->env->getConfig();
		$request		= $this->env->getRequest();
		$session		= $this->env->getSession();
		$language		= $this->env->getLanguage()->getLanguage();

		$pathLocales	= $config->get( 'path.locales' );
		$pathHtml		= $pathLocales.$language.'/html/index/';

		$pathByFrom		= $request->get( 'from' );
		$pathByPath		= $request->get( '__path' );
		$isInside		= $session->get( 'auth_user_id' ) > 0;
		$pathsSelf		= ['', 'index', 'index/index'];

		//  redirect forced by auth module ?
		$forward	= $config->getAll( 'module.resource_authentication.login.forward.', TRUE );
		if( $isInside && $forward->get( 'path' ) && $forward->get( 'force' ) )
			if( $this->env->getAcl()->has( $forward->get( 'path' ), '' ) )
				$this->restart( $forward->get( 'path' ) );

		if( $pathByFrom && !in_array( $pathByFrom, $pathsSelf ) )
			if( $this->env->getAcl()->has( $pathByFrom, '' ) )
				$this->restart( $pathByFrom );

		$args	= array_filter( func_get_args() );
		if( count( $args ) > 0 ){
			$pathByArgs		= $pathHtml.join( "/", $args ).'.html';
			if( file_exists( $pathByArgs ) )
				$this->addData( 'path', $pathByArgs );
			else if( !in_array( $pathByPath, $pathsSelf ) ){
				$words	= (object) $this->getWords( 'index', 'main' );
				if( isset( $words->msgPageNotFound ) )
					if( strlen( trim( @$words->msgPageNotFound ) ) )
						$this->env->getMessenger()->noteNotice( $words->msgPageNotFound );
				$this->env->getResponse()->setStatus( 404 );
			}
		}

		if( $this->env->getModules()->has( 'Resource_Authentication_Backend_Local' ) ){				//  high level: use local auth module
			$logic	= $this->env->getLogic()->get( 'Authentication' );
			if( $logic->isAuthenticated() )
				$this->setData( [
					'user'	=> $logic->getCurrentUser(),
					'role'	=> $logic->getCurrentRole(),
				] );
		}
		else if( $this->env->getModules()->has( 'Resource_Users' ) ){								//  fallback: no local auth, but local users
			$userId		= $session->get( 'auth_user_id' );
			$roleId		= $session->get( 'auth_role_id' );
			if( $userId ){
				$this->addData( 'user', $this->getModel( 'user' )->get( $userId ) );
				if( $roleId )
					$this->addData( 'role', $this->getModel( 'role' )->get( $roleId ) );
			}
		}
		$this->addData( 'isInside', $isInside );
		$this->addData( 'pathLocales', $pathLocales );
		$this->addData( 'pathHtml', $pathHtml );
		$this->addData( 'language', $language );
		$this->addData( 'sessionId', session_id() );
		$this->addData( 'sessionData', $session->getAll() );
	}
}
