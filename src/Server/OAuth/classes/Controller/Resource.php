<?php

use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;

class Controller_Resource extends Controller
{
	public function __construct( WebEnvironment $env, $setupView = TRUE )
	{
		parent::__construct( $env, FALSE );
	}

	/**
	 *	@return		void
	 *	@throws		JsonException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function me(): void
	{
		$this->checkToken();
		$this->checkPostRequest();
		$request	= $this->env->getRequest();
		$modelToken	= new Model_Oauth_AccessToken( $this->env );
		$modelUser	= new Model_User( $this->env );
		$token		= $modelToken->getByIndex( 'token', $request->get( 'access_token' ) );
		if( $token->userId < 1 )
			$this->handleJsonResponse( 'error', [
				'message'		=> 'not_an_user_token',
				'description'	=> '...',
			], 404 );
		$user		= $modelUser->get( $token->userId );
		if( !$user )
			$this->handleJsonResponse( 'error', [
				'message'		=> 'invalid_user',
				'description'	=> '...',
			], 404 );
		$this->handleJsonResponse( 'data', [
			'user'	=> $user,
		] );
	}

	/**
	 *	@param		string		$username
	 *	@return		void
	 *	@throws		JsonException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function user( string $username ): void
	{
		$this->checkToken();
//		$this->checkPostRequest();
		$model		= new Model_User( $this->env );
		$user		= $model->getByIndex( 'username', $username );
		if( !$user ){
			$this->handleJsonResponse( 'error', [
				'message'		=> 'invalid_username',
				'description'	=> '...',
			], 404 );
		}
		if( $this->isWriteAction() ){
			$data = $this->getPostData();
			if( isset( $data['password'] ) )
				unset( $data['password'] );
			$model->edit( $user->userId, $data );
			$user	= $model->getByIndex( 'username', $username );
		}
		unset( $user->password );
		$this->handleJsonResponse( 'data', [
			'user'	=> $user,
		] );
	}

	/**
	 *	@param		int|string $userId
	 *	@return		void
	 *	@throws		JsonException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function userId( int|string $userId ): void
	{
		$this->checkToken();
		$this->checkPostRequest();
		$model		= new Model_User( $this->env );
		$user		= $model->get( $userId );
		if( !$user ){
			$this->handleJsonResponse( 'error', [
				'message'		=> 'invalid_user_id',
				'description'	=> '...',
			], 404 );
		}
		if( $this->isWriteAction() ){
			$data = $this->getPostData();
			if( isset( $data['password'] ) )
				unset( $data['password'] );
			$model->edit( $user->userId, $data );
			$user		= $model->get( $userId );
		}
		unset( $user->password );
		$this->handleJsonResponse( 'data', [
			'user'	=> $user,
		] );
	}

	//  --  PROTECTED  --  //

	/**
	 *	@return		TRUE|void
	 *	@throws		JsonException
	 */
	protected function checkPostRequest()
	{
		$request	= $this->env->getRequest();
		if( $request->getMethod()->isPost() )
			return TRUE;
		$this->handleJsonErrorResponse( [
			'message'		=> 'invalid_request_method',
			'description'	=> 'Use POST HTTP request method for this resource.'
		] );
	}

	/**
	 *	@return		void
	 *	@throws		JsonException
	 */
	protected function checkToken(): void
	{
		$request	= $this->env->getRequest();
		$token		= $request->get( 'access_token' );
		if( !$token ){
			$header	= $request->getHeadersByName( 'Authorization', TRUE );
			if( !$header )
				$this->handleJsonErrorResponse( [
					'message'		=> 'missing_authorization',
					'description'	=> 'Request to resource needs authorization header or access token in request data.'
				] );
			if( !preg_match( "/^(token|bearer) (.+)$/i", $header->getValue() ) )
				$this->handleJsonErrorResponse( [
					'message'		=> 'missing_authorization_type',
					'description'	=> 'Used authorization header type is not supported. Use "Bearer" or "Token" instead.',
				] );
			$token	= preg_replace( "/^(token|bearer) (.+)$/i", "\\2", $header->getValue() );
			$request->set( 'access_token', $token );
		}
		$modelToken		= new Model_Oauth_AccessToken( $this->env );
		if( !$modelToken->getByIndex( 'token', $token ) )
			$this->handleJsonErrorResponse( [
				'message'		=> 'invalid_access_token',
				'description'	=> 'Access token provided by request is invalid or outdated.',
			] );
	}

	/**
	 *	@return		array
	 */
	protected function getPostData(): array
	{
		$data	= [];
		$body	= $this->env->getRequest()->getRawPostData();
		if( strlen( trim( $body ) ) ){
			if( function_exists( 'mb_parse_str' ) )
				mb_parse_str( $body, $data );
			else
				parse_str( $body, $data );
		}
		return $data;
	}

	protected function isWriteAction(): bool
	{
		return strlen( trim( $this->env->getRequest()->getRawPostData() ) ) > 0;
	}

	protected function hasWriteAccess( $userId, $resource ): bool
	{
		return TRUE;
	}
}
