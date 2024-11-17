<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\HydrogenFramework\Controller;

class Controller_Index extends Controller
{
	protected array $pathsSelf		= ['', 'index', 'index/index'];

	public function index( $arg1 = NULL, $arg2 = NULL, $arg3 = NULL ): void
	{
		$config			= $this->env->getConfig();
		$session		= $this->env->getSession();
		$language		= $this->env->getLanguage()->getLanguage();

		$pathLocales	= $config->get( 'path.locales' );
		$pathHtml		= $pathLocales.$language.'/html/index/';

		if( !$this->authenticateByModule() )
			$this->authenticateByResource( $session );

		$this->mayForwardAfterLogin();
		$this->mayRewindToFrom();
		$this->dispatchByLocaleHtmlFile( $pathHtml );

		$isInside		= $session->get( 'auth_user_id' ) > 0;

		$this->addData( 'isInside', $isInside );
		$this->addData( 'pathLocales', $pathLocales );
		$this->addData( 'pathHtml', $pathHtml );
		$this->addData( 'language', $language );
		$this->addData( 'sessionId', session_id() );
		$this->addData( 'sessionData', $session->getAll() );
	}


	/**
	 *	high level: use local auth module
	 *	@return		bool
	 *	@throws		ReflectionException
	 */
	protected function authenticateByModule(): bool
	{
		if( !$this->env->getModules()->has( 'Resource_Authentication_Backend_Local' ) )
			return FALSE;

		$logic	= $this->env->getLogic()->get( 'Authentication' );
		if( $logic->isAuthenticated() )
			$this->setData( [
				'user'	=> $logic->getCurrentUser(),
				'role'	=> $logic->getCurrentRole(),
			] );
		return TRUE;
	}

	/**
	 *	fallback: no local auth, but local users
	 *	@return		bool
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function authenticateByResource( Dictionary $session ): bool
	{
		if( !$this->env->getModules()->has( 'Resource_Users' ) )
			return FALSE;

		$userId		= $session->get( 'auth_user_id' );
		$roleId		= $session->get( 'auth_role_id' );
		if( $userId ){
			$this->addData( 'user', $this->getModel( 'user' )->get( $userId ) );
			if( $roleId )
				$this->addData( 'role', $this->getModel( 'role' )->get( $roleId ) );
		}
		return TRUE;
	}

	protected function mayForwardAfterLogin(): void
	{
		$config			= $this->env->getConfig();
		$isInside		= $this->env->getSession()->get( 'auth_user_id' ) > 0;

		//  redirect forced by auth module ?
		$forward		= $config->getAll( 'module.resource_authentication.login.forward.', TRUE );
		$forwardPath	= $forward->get( 'path' ) ?? '';
		if( $isInside && $forwardPath && $forward->get( 'force' ) )
			if( $this->env->getAcl()->has( $forwardPath, '' ) )
				$this->restart( $forwardPath );
	}

	protected function mayRewindToFrom(): void
	{
		$pathByFrom		= $this->env->getRequest()->get( 'from' );
		if( $pathByFrom && !in_array( $pathByFrom, $this->pathsSelf ) )
			if( $this->env->getAcl()->has( $pathByFrom, '' ) )
				$this->restart( $pathByFrom );
	}

	protected function dispatchByLocaleHtmlFile( string $pathHtml ): void
	{
		$args	= array_filter( func_get_args() );
		if( count( $args ) > 0 ){
			$pathByArgs		= join( "/", $args );
			$fileByArgs		= $pathHtml.join( "/", $args ).'.html';
			if( file_exists( $fileByArgs ) )
				$this->addData( 'path', $fileByArgs );
			else if( !in_array( $pathByArgs, $this->pathsSelf ) ){
				$words	= (object) $this->getWords( 'index', 'main' );
				if( isset( $words->msgPageNotFound ) )
					if( strlen( trim( @$words->msgPageNotFound ) ) )
						$this->env->getMessenger()->noteNotice( $words->msgPageNotFound );
				$this->env->getResponse()->setStatus( 404 );
			}
		}
	}
}
